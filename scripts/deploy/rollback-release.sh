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

if [[ -z "${ROLLBACK_RELEASE:-}" ]]; then
  if [[ -L "$DEPLOY_ROOT/previous" ]]; then
    rollback_path="$(readlink "$DEPLOY_ROOT/previous")"
    ROLLBACK_RELEASE="$(basename "$rollback_path")"
  else
    echo "ROLLBACK_RELEASE is required when no previous release link exists." >&2
    exit 2
  fi
fi

if [[ ! "$ROLLBACK_RELEASE" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "ROLLBACK_RELEASE may only contain letters, numbers, dots, underscores, and hyphens." >&2
  exit 2
fi

export RELEASE_ID="$ROLLBACK_RELEASE"
export ALLOW_LIVE_PATH_REPLACE=false

bash "$DEPLOY_ROOT/releases/$ROLLBACK_RELEASE/scripts/deploy/activate-release.sh"
echo "Rolled back to release $ROLLBACK_RELEASE"
