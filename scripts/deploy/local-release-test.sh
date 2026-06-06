#!/usr/bin/env bash
set -Eeuo pipefail

release_id="${1:-local-$(date +%Y%m%d%H%M%S)}"
repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
local_root="$repo_root/release-artifacts/local-runtime"
deploy_root="$local_root/deploy"
live_path="$local_root/public_html"
php_bin="${PHP_BIN:-php}"
export INCLUDE_WORKTREE=true

if ! command -v "$php_bin" >/dev/null 2>&1; then
  if [[ -x /c/xampp/php/php.exe ]]; then
    php_bin=/c/xampp/php/php.exe
  else
    echo "PHP is required. Set PHP_BIN to your PHP executable." >&2
    exit 1
  fi
fi

bash "$repo_root/scripts/deploy/build-artifact.sh" "$release_id"

mkdir -p "$deploy_root/shared"

if [[ ! -f "$deploy_root/shared/.env" ]]; then
  cp "$repo_root/.env.example" "$deploy_root/shared/.env"
fi

export DEPLOY_ROOT="$deploy_root"
export LIVE_PATH="$live_path"
export RELEASE_ID="$release_id"
export ARTIFACT_PATH="$repo_root/release-artifacts/mcprices-wordpress-$release_id.zip"
export ACTIVATE_RELEASE=false
export ALLOW_LIVE_PATH_CREATE=true
export PHP_BIN="$php_bin"

bash "$repo_root/scripts/deploy/deploy-release.sh"

candidate_path="$deploy_root/releases/$release_id"
"$php_bin" -S 127.0.0.1:8088 -t "$candidate_path" >/tmp/mcprices-local-release-$release_id.log 2>&1 &
health_server_pid="$!"
trap 'kill "$health_server_pid" >/dev/null 2>&1 || true' EXIT

export ACTIVATE_RELEASE=true
export SITE_URL="http://127.0.0.1:8088"
export HEALTH_URL="http://127.0.0.1:8088"
export ADMIN_HEALTH_URL="http://127.0.0.1:8088/wp-login.php"

bash "$candidate_path/scripts/deploy/activate-release.sh"
kill "$health_server_pid" >/dev/null 2>&1 || true
trap - EXIT

echo "Local release activated at $live_path"
echo "Serving local release at http://127.0.0.1:8088"
"$php_bin" -S 127.0.0.1:8088 -t "$live_path"
