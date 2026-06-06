#!/usr/bin/env python3
"""Copy the current worktree into a release build directory for local testing."""

from pathlib import Path
import os
import shutil
import sys


EXCLUDED_DIRS = {
    ".git",
    ".private",
    "deploy-artifacts",
    "outputs",
    "release-artifacts",
    "test-results",
}

EXCLUDED_RELATIVE_DIRS = {
    Path("wp-content/uploads"),
    Path("wp-content/cache"),
    Path("wp-content/upgrade"),
    Path("wp-content/litespeed"),
}


def is_excluded_dir(repo_root: Path, path: Path) -> bool:
    relative_path = path.relative_to(repo_root)

    if path.name in EXCLUDED_DIRS:
        return True

    return relative_path in EXCLUDED_RELATIVE_DIRS


def main() -> int:
    if len(sys.argv) != 3:
        print("Usage: copy-worktree.py <repo-root> <build-root>", file=sys.stderr)
        return 2

    repo_root = Path(sys.argv[1]).resolve()
    build_root = Path(sys.argv[2]).resolve()

    if not repo_root.is_dir():
        print(f"Repository root does not exist: {repo_root}", file=sys.stderr)
        return 1

    build_root.mkdir(parents=True, exist_ok=True)

    for current_root, dirnames, filenames in os.walk(repo_root):
        current_path = Path(current_root)

        if current_path == build_root or build_root in current_path.parents:
            dirnames[:] = []
            continue

        kept_dirnames = []
        for dirname in dirnames:
            child_path = current_path / dirname

            if child_path == build_root or build_root in child_path.parents or is_excluded_dir(repo_root, child_path):
                continue

            kept_dirnames.append(dirname)

        dirnames[:] = kept_dirnames

        for filename in sorted(filenames):
            source_path = current_path / filename
            target_path = build_root / source_path.relative_to(repo_root)
            target_path.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(source_path, target_path)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
