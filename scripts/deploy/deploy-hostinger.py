#!/usr/bin/env python3
"""Build, upload, and activate a Hostinger release from a Windows workstation."""

from __future__ import annotations

import argparse
import os
import re
import shutil
import subprocess
import sys
import time
from pathlib import Path

import paramiko


DEFAULT_HOST = "46.202.172.5"
DEFAULT_PORT = 65002
DEFAULT_USER = "u913239002"
DEFAULT_DEPLOY_ROOT = "/home/u913239002/domains/mcdomenuusa.com/deploy"
DEFAULT_LIVE_PATH = "/home/u913239002/domains/mcdomenuusa.com/public_html"
DEFAULT_SITE_URL = "https://mcdomenuusa.com"


def shell_quote(value: str) -> str:
    return "'" + value.replace("'", "'\"'\"'") + "'"


def sanitize_release_id(value: str) -> str:
    if not re.fullmatch(r"[A-Za-z0-9._-]+", value):
        raise ValueError("Release id may only contain letters, numbers, dots, underscores, and hyphens.")
    return value


def run_local(command: list[str], cwd: Path) -> None:
    subprocess.run(command, cwd=str(cwd), check=True)


def remove_path(path: Path) -> None:
    if path.is_dir() and not path.is_symlink():
        shutil.rmtree(path)
    elif path.exists() or path.is_symlink():
        path.unlink()


def normalize_htaccess(build_root: Path) -> None:
    htaccess = build_root / ".htaccess"
    if not htaccess.is_file():
        return

    content = htaccess.read_text(encoding="utf-8", errors="ignore")
    content = content.replace("RewriteBase /wordpress/", "RewriteBase /")
    content = content.replace("RewriteRule . /wordpress/index.php [L]", "RewriteRule . /index.php [L]")
    htaccess.write_text(content, encoding="utf-8")


def strip_private_release_files(build_root: Path) -> None:
    for relative in (
        ".agents",
        ".claude",
        ".github",
        ".npm-cache",
        ".private",
        ".seo-cache",
        ".tmp",
        "deploy-artifacts",
        "outputs",
        "release-artifacts",
        "test-results",
        "wp-content/uploads",
        "wp-content/cache",
        "wp-content/upgrade",
        "wp-content/litespeed",
    ):
        remove_path(build_root / relative)

    for env_file in build_root.rglob(".env*"):
        if env_file.name in {".env.example", ".env.production.example"}:
            continue
        remove_path(env_file)


def lint_php(repo_root: Path, php_bin: str | None) -> None:
    if not php_bin:
        return

    php_path = Path(php_bin)
    executable = str(php_path) if php_path.exists() else php_bin

    for relative in (
        "wp-config.php",
        "wp-content/themes/kadence/functions.php",
        "wp-content/themes/kadence/inc/mcprices/class-mcprices-integration.php",
        "wp-content/themes/kadence/inc/mcprices/schema.php",
        "wp-content/mu-plugins/mcprices-update-safety.php",
    ):
        target = repo_root / relative
        if target.is_file():
            run_local([executable, "-l", str(target)], repo_root)


def build_artifact(repo_root: Path, release_id: str, python_exe: str) -> Path:
    build_root = repo_root / "release-artifacts" / f"build-{release_id}"
    artifact_dir = repo_root / "release-artifacts"
    artifact_path = artifact_dir / f"mcprices-wordpress-{release_id}.zip"

    remove_path(build_root)
    artifact_dir.mkdir(parents=True, exist_ok=True)

    run_local([python_exe, str(repo_root / "scripts/deploy/copy-worktree.py"), str(repo_root), str(build_root)], repo_root)
    normalize_htaccess(build_root)
    strip_private_release_files(build_root)

    if artifact_path.exists():
        artifact_path.unlink()

    run_local([python_exe, str(repo_root / "scripts/deploy/zip-directory.py"), str(build_root), str(artifact_path)], repo_root)
    return artifact_path


def sftp_mkdirs(sftp: paramiko.SFTPClient, path: str) -> None:
    current = ""
    for part in path.strip("/").split("/"):
        current += "/" + part
        try:
            sftp.stat(current)
        except FileNotFoundError:
            sftp.mkdir(current)


def ssh_exec(client: paramiko.SSHClient, command: str, stdin_text: str | None = None) -> str:
    stdin, stdout, stderr = client.exec_command(command)

    if stdin_text is not None:
        stdin.write(stdin_text)
        stdin.channel.shutdown_write()

    out = stdout.read().decode("utf-8", errors="replace")
    err = stderr.read().decode("utf-8", errors="replace")
    code = stdout.channel.recv_exit_status()

    combined = out + err
    if combined:
        print(combined.rstrip())

    if code != 0:
        raise RuntimeError(f"Remote command failed with exit code {code}: {command}")

    return combined


def upload_artifact(client: paramiko.SSHClient, artifact_path: Path, deploy_root: str) -> str:
    incoming_dir = deploy_root.rstrip("/") + "/incoming"
    remote_artifact = incoming_dir + "/" + artifact_path.name

    with client.open_sftp() as sftp:
        sftp_mkdirs(sftp, incoming_dir)
        print(f"Uploading {artifact_path.name} to Hostinger...")
        sftp.put(str(artifact_path), remote_artifact)

    return remote_artifact


def sync_shared_env_from_live(client: paramiko.SSHClient, deploy_root: str, live_path: str) -> None:
    command = (
        f"mkdir -p {shell_quote(deploy_root.rstrip('/') + '/shared')} && "
        f"if [ ! -f {shell_quote(deploy_root.rstrip('/') + '/shared/.env')} ] && "
        f"[ -f {shell_quote(live_path.rstrip('/') + '/.env')} ]; then "
        f"cp {shell_quote(live_path.rstrip('/') + '/.env')} {shell_quote(deploy_root.rstrip('/') + '/shared/.env')} && "
        f"chmod 600 {shell_quote(deploy_root.rstrip('/') + '/shared/.env')} && "
        "echo 'Copied live .env into deploy/shared/.env'; "
        "fi"
    )
    ssh_exec(client, command)


def run_remote_deploy(
    client: paramiko.SSHClient,
    repo_root: Path,
    release_id: str,
    remote_artifact: str,
    deploy_root: str,
    live_path: str,
    site_url: str,
    activate: bool,
    allow_live_replace: bool,
    seed_shared_from_live: bool,
) -> None:
    deploy_script = (repo_root / "scripts/deploy/deploy-release.sh").read_text(encoding="utf-8")
    env = {
        "RELEASE_ID": release_id,
        "ARTIFACT_PATH": remote_artifact,
        "DEPLOY_ROOT": deploy_root,
        "LIVE_PATH": live_path,
        "SITE_URL": site_url,
        "HEALTH_URL": site_url.rstrip("/") + "/",
        "ADMIN_HEALTH_URL": site_url.rstrip("/") + "/wp-login.php",
        "ACTIVATE_RELEASE": "true" if activate else "false",
        "ALLOW_LIVE_PATH_REPLACE": "true" if allow_live_replace else "false",
        "SEED_SHARED_FROM_LIVE": "true" if seed_shared_from_live else "false",
    }
    command = " ".join(f"{key}={shell_quote(value)}" for key, value in env.items()) + " bash -s"
    ssh_exec(client, command, deploy_script)


def purge_litespeed_cache(client: paramiko.SSHClient, deploy_root: str) -> None:
    current = deploy_root.rstrip("/") + "/current"
    php = "php"
    code = "require 'wp-load.php'; do_action('litespeed_purge_all'); echo 'LiteSpeed purge requested\\n';"
    command = f"cd {shell_quote(current)} && {php} -r {shell_quote(code)}"

    try:
        ssh_exec(client, command)
    except Exception as exc:  # noqa: BLE001 - cache purge failure should not roll back a healthy release.
        print(f"Cache purge skipped or failed: {exc}")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--release-id", default=time.strftime("%Y%m%d-%H%M%S"))
    parser.add_argument("--host", default=os.getenv("HOSTINGER_SSH_HOST", DEFAULT_HOST))
    parser.add_argument("--port", type=int, default=int(os.getenv("HOSTINGER_SSH_PORT", str(DEFAULT_PORT))))
    parser.add_argument("--user", default=os.getenv("HOSTINGER_SSH_USER", DEFAULT_USER))
    parser.add_argument("--password", default=os.getenv("HOSTINGER_SSH_PASSWORD", ""))
    parser.add_argument("--deploy-root", default=os.getenv("HOSTINGER_DEPLOY_ROOT", DEFAULT_DEPLOY_ROOT))
    parser.add_argument("--live-path", default=os.getenv("HOSTINGER_LIVE_PATH", DEFAULT_LIVE_PATH))
    parser.add_argument("--site-url", default=os.getenv("PRODUCTION_SITE_URL", DEFAULT_SITE_URL))
    parser.add_argument("--prepare-only", action="store_true", help="Upload and prepare the release without switching traffic.")
    parser.add_argument("--allow-live-path-replace", action="store_true", help="Allow first-time conversion of public_html into a release symlink.")
    parser.add_argument("--seed-shared-from-live", action="store_true", help="Copy uploads, cache, and LiteSpeed assets from the old live tree during first-time conversion.")
    parser.add_argument("--skip-shared-env-sync", action="store_true", help="Do not copy live .env into deploy/shared/.env when missing.")
    parser.add_argument("--skip-cache-purge", action="store_true", help="Do not request a LiteSpeed cache purge after activation.")
    parser.add_argument("--php-bin", default=os.getenv("LOCAL_PHP_BIN", r"C:\xampp\php\php.exe"))
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    release_id = sanitize_release_id(args.release_id)

    if not args.password:
        print("HOSTINGER_SSH_PASSWORD is required, or pass --password.", file=sys.stderr)
        return 2

    repo_root = Path(__file__).resolve().parents[2]
    python_exe = sys.executable

    lint_php(repo_root, args.php_bin)
    artifact_path = build_artifact(repo_root, release_id, python_exe)
    print(f"Built artifact: {artifact_path}")

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    try:
        client.connect(
            hostname=args.host,
            port=args.port,
            username=args.user,
            password=args.password,
            timeout=30,
            banner_timeout=30,
            auth_timeout=30,
            look_for_keys=False,
            allow_agent=False,
        )

        if not args.skip_shared_env_sync:
            sync_shared_env_from_live(client, args.deploy_root, args.live_path)

        remote_artifact = upload_artifact(client, artifact_path, args.deploy_root)
        run_remote_deploy(
            client=client,
            repo_root=repo_root,
            release_id=release_id,
            remote_artifact=remote_artifact,
            deploy_root=args.deploy_root,
            live_path=args.live_path,
            site_url=args.site_url,
            activate=not args.prepare_only,
            allow_live_replace=args.allow_live_path_replace,
            seed_shared_from_live=args.seed_shared_from_live,
        )

        if not args.prepare_only and not args.skip_cache_purge:
            purge_litespeed_cache(client, args.deploy_root)
    finally:
        client.close()

    action = "prepared" if args.prepare_only else "deployed"
    print(f"Release {release_id} {action} successfully.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
