#!/usr/bin/env bash
set -Eeuo pipefail

release_id="${1:-}"

if [[ -z "$release_id" ]]; then
  echo "Usage: $0 <release-id>" >&2
  exit 2
fi

if [[ ! "$release_id" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "Release id may only contain letters, numbers, dots, underscores, and hyphens." >&2
  exit 2
fi

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
build_root="$repo_root/release-artifacts/build-$release_id"
artifact_dir="$repo_root/release-artifacts"
artifact_path="$artifact_dir/mcprices-wordpress-$release_id.zip"
include_worktree="${INCLUDE_WORKTREE:-false}"

rm -rf "$build_root"
mkdir -p "$build_root" "$artifact_dir"

case "$include_worktree" in
  true|TRUE|1|yes|YES)
    if command -v python3 >/dev/null 2>&1; then
      python3 "$repo_root/scripts/deploy/copy-worktree.py" "$repo_root" "$build_root"
    elif command -v python >/dev/null 2>&1; then
      python "$repo_root/scripts/deploy/copy-worktree.py" "$repo_root" "$build_root"
    else
      echo "python3 or python is required when INCLUDE_WORKTREE=true." >&2
      exit 1
    fi
    ;;
  *)
    (
      cd "$repo_root"
      git archive --format=tar HEAD | tar -xf - -C "$build_root"
    )
    ;;
esac

if [[ -f "$build_root/wp-content/themes/kadence/package.json" ]]; then
  (
    cd "$build_root/wp-content/themes/kadence"
    npm ci
    npm run build
  )
fi

if [[ -f "$build_root/.htaccess" ]]; then
  sed -i \
    -e 's#RewriteBase /wordpress/#RewriteBase /#g' \
    -e 's#RewriteRule . /wordpress/index\.php \[L\]#RewriteRule . /index.php [L]#g' \
    "$build_root/.htaccess"
fi

rm -rf \
  "$build_root/.github" \
  "$build_root/.private" \
  "$build_root/outputs" \
  "$build_root/release-artifacts" \
  "$build_root/deploy-artifacts" \
  "$build_root/test-results" \
  "$build_root/wp-content/uploads" \
  "$build_root/wp-content/cache" \
  "$build_root/wp-content/upgrade" \
  "$build_root/wp-content/litespeed"

find "$build_root" -name ".env" -o -name ".env.*" | while read -r env_file; do
  case "$env_file" in
    *.example) ;;
    *) rm -f "$env_file" ;;
  esac
done

(
  cd "$build_root"
  if command -v zip >/dev/null 2>&1; then
    zip -qr "$artifact_path" .
  elif command -v python3 >/dev/null 2>&1; then
    python3 "$repo_root/scripts/deploy/zip-directory.py" "$build_root" "$artifact_path"
  elif command -v python >/dev/null 2>&1; then
    python "$repo_root/scripts/deploy/zip-directory.py" "$build_root" "$artifact_path"
  else
    echo "zip, python3, or python is required to create the release artifact." >&2
    exit 1
  fi
)

echo "$artifact_path"
