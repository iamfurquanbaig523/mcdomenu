#!/usr/bin/env bash
set -Eeuo pipefail

require_env() {
  local name="$1"
  if [[ -z "${!name:-}" ]]; then
    echo "$name is required" >&2
    exit 2
  fi
}

require_env DEPLOY_ROOT
require_env LIVE_PATH
require_env RELEASE_ID

if [[ ! "$RELEASE_ID" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "RELEASE_ID may only contain letters, numbers, dots, underscores, and hyphens." >&2
  exit 2
fi

releases_path="${RELEASES_PATH:-$DEPLOY_ROOT/releases}"
release_path="$releases_path/$RELEASE_ID"
current_link="$DEPLOY_ROOT/current"
previous_link="$DEPLOY_ROOT/previous"

if [[ ! -d "$release_path" ]]; then
  echo "Release does not exist: $release_path" >&2
  exit 1
fi

previous_target=""
legacy_path=""
if [[ -L "$current_link" ]]; then
  previous_target="$(readlink "$current_link" || true)"
fi

tmp_current="$DEPLOY_ROOT/.current-$RELEASE_ID.tmp"
ln -sfn "$release_path" "$tmp_current"
mv -Tf "$tmp_current" "$current_link"

if [[ -n "$previous_target" && -d "$previous_target" ]]; then
  ln -sfn "$previous_target" "$previous_link"
fi

if [[ -L "$LIVE_PATH" ]]; then
  tmp_live="${LIVE_PATH}.tmp"
  ln -sfn "$current_link" "$tmp_live"
  mv -Tf "$tmp_live" "$LIVE_PATH"
elif [[ ! -e "$LIVE_PATH" ]]; then
  if [[ "${ALLOW_LIVE_PATH_CREATE:-true}" != "true" ]]; then
    echo "LIVE_PATH does not exist and ALLOW_LIVE_PATH_CREATE is not true: $LIVE_PATH" >&2
    exit 1
  fi
  ln -s "$current_link" "$LIVE_PATH"
elif [[ -d "$LIVE_PATH" && "${ALLOW_LIVE_PATH_REPLACE:-false}" == "true" ]]; then
  legacy_path="$DEPLOY_ROOT/legacy-live-$(date +%Y%m%d%H%M%S)"
  mv "$LIVE_PATH" "$legacy_path"
  ln -s "$current_link" "$LIVE_PATH"
  echo "$legacy_path" > "$DEPLOY_ROOT/legacy-live-path"
else
  echo "LIVE_PATH is not a symlink. Refusing to replace without ALLOW_LIVE_PATH_REPLACE=true: $LIVE_PATH" >&2
  exit 1
fi

if ! bash "$release_path/scripts/deploy/health-check.sh"; then
  echo "Health check failed; rolling back activation." >&2

  if [[ -n "$previous_target" && -d "$previous_target" ]]; then
    ln -sfn "$previous_target" "$tmp_current"
    mv -Tf "$tmp_current" "$current_link"

    if [[ -L "$LIVE_PATH" ]]; then
      tmp_live="${LIVE_PATH}.tmp"
      ln -sfn "$current_link" "$tmp_live"
      mv -Tf "$tmp_live" "$LIVE_PATH"
    fi
  elif [[ -n "$legacy_path" && -d "$legacy_path" ]]; then
    if [[ -L "$LIVE_PATH" ]]; then
      rm -f "$LIVE_PATH"
    fi

    mv "$legacy_path" "$LIVE_PATH"
  fi

  exit 1
fi

printf '%s\n' "$RELEASE_ID" > "$DEPLOY_ROOT/active-release"
echo "Activated release $RELEASE_ID"
