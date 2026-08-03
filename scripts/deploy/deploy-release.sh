#!/usr/bin/env bash
set -Eeuo pipefail

require_env() {
  local name="$1"
  if [[ -z "${!name:-}" ]]; then
    echo "$name is required" >&2
    exit 2
  fi
}

truthy() {
  case "${1:-}" in
    true|TRUE|1|yes|YES) return 0 ;;
    *) return 1 ;;
  esac
}

seed_shared_dir_from_live() {
  local dirname="$1"
  local source_dir="$LIVE_PATH/wp-content/$dirname"
  local target_dir="$shared_path/$dirname"
  local marker="$target_dir/.mcprices-seeded-from-live"

  if ! truthy "${SEED_SHARED_FROM_LIVE:-false}"; then
    return
  fi

  if [[ -e "$marker" || ! -d "$source_dir" ]]; then
    return
  fi

  mkdir -p "$target_dir"

  if command -v rsync >/dev/null 2>&1; then
    rsync -a "$source_dir/" "$target_dir/"
  else
    cp -a "$source_dir/." "$target_dir/"
  fi

  date -u +%Y-%m-%dT%H:%M:%SZ > "$marker"
}

validate_release_id() {
  if [[ ! "$RELEASE_ID" =~ ^[A-Za-z0-9._-]+$ ]]; then
    echo "RELEASE_ID may only contain letters, numbers, dots, underscores, and hyphens." >&2
    exit 2
  fi
}

require_env DEPLOY_ROOT
require_env LIVE_PATH
require_env RELEASE_ID
require_env ARTIFACT_PATH
validate_release_id

shared_path="${SHARED_PATH:-$DEPLOY_ROOT/shared}"
releases_path="${RELEASES_PATH:-$DEPLOY_ROOT/releases}"
release_path="$releases_path/$RELEASE_ID"
php_bin="${PHP_BIN:-php}"

if [[ ! -f "$ARTIFACT_PATH" ]]; then
  echo "Artifact not found: $ARTIFACT_PATH" >&2
  exit 1
fi

mkdir -p "$shared_path" "$shared_path/uploads" "$shared_path/cache" "$shared_path/litespeed" "$releases_path"
seed_shared_dir_from_live uploads
seed_shared_dir_from_live cache
seed_shared_dir_from_live litespeed

if [[ -e "$release_path" ]]; then
  if truthy "${OVERWRITE_PREPARED_RELEASE:-false}"; then
    rm -rf "$release_path"
  else
    echo "Release already exists: $release_path" >&2
    exit 1
  fi
fi

mkdir -p "$release_path"
unzip -q "$ARTIFACT_PATH" -d "$release_path"

rm -rf "$release_path/wp-content/uploads" "$release_path/wp-content/cache" "$release_path/wp-content/litespeed"
ln -s "$shared_path/uploads" "$release_path/wp-content/uploads"
ln -s "$shared_path/cache" "$release_path/wp-content/cache"
ln -s "$shared_path/litespeed" "$release_path/wp-content/litespeed"

if [[ -f "$shared_path/.env" ]]; then
  ln -sfn "$shared_path/.env" "$release_path/.env"
else
  echo "Warning: $shared_path/.env does not exist. Release is prepared but may not boot with production config." >&2
fi

printf '%s\n' "$RELEASE_ID" > "$release_path/RELEASE_ID"
printf '{"release_id":"%s","prepared_at":"%s"}\n' "$RELEASE_ID" "$(date -u +%Y-%m-%dT%H:%M:%SZ)" > "$release_path/release.json"

"$php_bin" -l "$release_path/wp-config.php" >/dev/null
"$php_bin" -l "$release_path/wp-content/themes/kadence/functions.php" >/dev/null
"$php_bin" -l "$release_path/wp-content/themes/kadence/inc/mcprices/indexing.php" >/dev/null
"$php_bin" -l "$release_path/wp-content/themes/kadence/inc/mcprices/class-mcprices-integration.php" >/dev/null
if [[ -f "$release_path/wp-content/mu-plugins/mcprices-update-safety.php" ]]; then
  "$php_bin" -l "$release_path/wp-content/mu-plugins/mcprices-update-safety.php" >/dev/null
fi

if truthy "${CHECK_WORDPRESS_BOOTSTRAP:-false}"; then
  (
    cd "$release_path"
    "$php_bin" -r 'require "wp-load.php"; echo "wordpress-bootstrap-ok\n";'
  )
fi

if truthy "${RUN_MIGRATIONS:-false}"; then
  if [[ "${MIGRATION_APPROVAL:-}" != "RUN_MIGRATIONS" ]]; then
    echo "RUN_MIGRATIONS=true requires MIGRATION_APPROVAL=RUN_MIGRATIONS" >&2
    exit 1
  fi

  wp_cli="${WP_CLI_BIN:-wp}"
  (
    cd "$release_path"
    "$wp_cli" core update-db --quiet
  )
fi

echo "Prepared release $RELEASE_ID at $release_path"

if truthy "${ACTIVATE_RELEASE:-false}"; then
  bash "$release_path/scripts/deploy/activate-release.sh"
else
  echo "Release prepared only. Live traffic was not activated."
fi
