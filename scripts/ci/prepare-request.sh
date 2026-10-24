#!/usr/bin/env bash
# GitHub Actions: put the request this run should carry out at $RUNNER_TEMP/request.json.
#
#   prepare-request.sh ops     the JSON typed into "Run workflow" (REQUEST_INPUT), else ops/request.json from the commit being run
#   prepare-request.sh live    ops/live-request.json from the commit being run (main), nothing else
set -euo pipefail

: "${RUNNER_TEMP:?GitHub Actions only}"
lane="${1:?usage: prepare-request.sh ops|live}"
root=$(cd "$(dirname "$0")/../.." && pwd)
dest="$RUNNER_TEMP/request.json"
case "$lane" in
  ops)
    if [ -n "${REQUEST_INPUT:-}" ]; then printf '%s\n' "$REQUEST_INPUT" > "$dest"; src="the Run workflow form"
    else [ -f "$root/ops/request.json" ] || { echo "No ops/request.json in this commit and no request typed into the form." >&2; exit 1; }
      cp "$root/ops/request.json" "$dest"; src="ops/request.json"; fi ;;
  live)
    [ -f "$root/ops/live-request.json" ] || { echo "No ops/live-request.json on main: merge the pull request that adds it first." >&2; exit 1; }
    cp "$root/ops/live-request.json" "$dest"; src="ops/live-request.json" ;;
  *) echo "unknown lane $lane" >&2; exit 1 ;;
esac
jq . "$dest" >/dev/null 2>&1 || { echo "The request from $src is not valid JSON." >&2; exit 1; }
echo "request (from $src):"; jq . "$dest"

# ref: ./run-op.sh — checks and runs the request; ../../ops/README.md — its format
