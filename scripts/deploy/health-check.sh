#!/usr/bin/env bash
set -Eeuo pipefail

health_url="${HEALTH_URL:-${SITE_URL:-}}"
admin_health_url="${ADMIN_HEALTH_URL:-}"
retries="${HEALTH_RETRIES:-8}"
delay="${HEALTH_RETRY_DELAY:-5}"

if [[ -z "$health_url" ]]; then
  echo "HEALTH_URL or SITE_URL is required for an activated production release." >&2
  exit 2
fi

check_url() {
  local url="$1"
  local label="$2"
  local code

  for attempt in $(seq 1 "$retries"); do
    code="$(curl -k -L -sS -o /dev/null -w '%{http_code}' "$url" || true)"

    if [[ "$code" =~ ^[23][0-9][0-9]$ ]]; then
      echo "$label health check passed with HTTP $code"
      return 0
    fi

    echo "$label health check attempt $attempt/$retries returned HTTP ${code:-000}"
    sleep "$delay"
  done

  echo "$label health check failed for $url" >&2
  return 1
}

check_url "$health_url" "site"

if [[ -n "$admin_health_url" ]]; then
  check_url "$admin_health_url" "admin"
elif [[ -n "${SITE_URL:-}" ]]; then
  check_url "${SITE_URL%/}/wp-login.php" "admin"
fi
