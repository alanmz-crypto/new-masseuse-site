#!/usr/bin/env bash
# Current-state recovery rehearsal only; every server operation is read-only.
set -euo pipefail
[ "$#" -eq 0 ] || { echo 'RECOVERY COPY INVALID: no arguments allowed' >&2; exit 2; }
here=$(cd "$(dirname "$0")" && pwd)
exec bash "$here/prove-live30-cold.sh" --recovery
