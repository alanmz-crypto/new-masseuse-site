#!/usr/bin/env bash
# Bring up the persistent local practice site after a reboot and report its URL.
set -euo pipefail

site_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$site_dir"

docker compose up -d db wordpress

url='http://localhost:8090/'
employer_url="${url}work-with-me/"
for attempt in {1..30}; do
  if curl --fail --silent --show-error --max-time 5 --output /dev/null "$url" 2>/dev/null \
    && curl --fail --silent --show-error --max-time 5 --output /dev/null "$employer_url" 2>/dev/null; then
    printf 'Local Thai Solitude is ready: %s\n' "$url"
    printf 'Work With Me: %s\n' "$employer_url"
    exit 0
  fi
  sleep 2
done

printf 'The containers started, but %s and %s did not both answer successfully.\n' "$url" "$employer_url" >&2
docker compose ps >&2
exit 1
