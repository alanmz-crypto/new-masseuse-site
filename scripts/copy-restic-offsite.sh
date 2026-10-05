#!/usr/bin/env bash
# Copy this site's encrypted local restic snapshots to a separate repository.
# Read-only preview by default; --apply initializes/copies/checks the destination.
set -euo pipefail

source_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
runtime_dir="${SITE_DIR:-$source_dir}"
source_repo="${MASSEUSE_RESTIC_REPO:-$runtime_dir/backups/restic-repo}"
password_file="${MASSEUSE_RESTIC_PASSWORD_FILE:-$runtime_dir/backups/restic.password}"
export RESTIC_CACHE_DIR="${MASSEUSE_RESTIC_CACHE_DIR:-/tmp/thai-restic-offsite-cache}"
destination=''
apply=0
local_test=0

usage() {
  echo "Usage: $0 --destination /absolute/path/to/restic-repo [--apply]" >&2
}
fail() {
  echo "[error] $*" >&2
  exit 1
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --destination) destination="${2:?--destination needs a path}"; shift 2 ;;
    --apply) apply=1; shift ;;
    --local-test) local_test=1; shift ;;
    -h|--help) usage; exit 0 ;;
    *) usage; fail "Unknown argument: $1" ;;
  esac
done

[[ "$destination" == /* ]] || fail 'Destination must be an absolute path.'
[[ -f "$source_repo/config" ]] || fail "Source restic repository missing: $source_repo"
[[ -f "$password_file" ]] || fail "Source restic password file missing: $password_file"
[[ ! -L "$destination" ]] || fail 'Destination cannot be a symlink.'

source_repo="$(realpath "$source_repo")"
destination="$(realpath -m "$destination")"
parent="$(dirname "$destination")"
[[ -d "$parent" ]] || fail "Destination parent does not exist: $parent"
[[ "$destination" != "$source_repo" && "$destination" != "$source_repo/"* \
   && "$source_repo" != "$destination/"* ]] || fail 'Source and destination overlap.'

source_device="$(stat -c %d "$source_repo")"
dest_device="$(stat -c %d "$parent")"
if [[ "$source_device" == "$dest_device" ]]; then
  [[ "$local_test" -eq 1 && "$destination" == /tmp/thai-restic-copy-test* ]] \
    || fail 'Destination is on the source filesystem; choose a separate drive.'
fi

snapshot_count() {
  restic -r "$1" -p "$password_file" snapshots --json \
    | python3 -c 'import json,sys; print(len(json.load(sys.stdin)))'
}

source_count="$(snapshot_count "$source_repo")" || fail 'Cannot read source snapshots.'
(( source_count > 0 )) || fail 'Source repository has no snapshots.'
echo "Source: $source_repo ($source_count snapshots)"
echo "Destination: $destination"
echo 'Password: remains at its current path; never copied to the destination.'
if [[ "$apply" -eq 0 ]]; then
  echo 'Preview only. Re-run with --apply after selecting and authorizing this destination.'
  exit 0
fi

if [[ -e "$destination" && ! -d "$destination" ]]; then
  fail 'Destination exists and is not a directory.'
fi
if [[ ! -d "$destination" ]]; then
  [[ -w "$parent" ]] || fail "Destination parent is not writable: $parent"
  mkdir -m 700 "$destination"
fi
[[ -w "$destination" ]] || fail "Destination is not writable: $destination"
if [[ ! -f "$destination/config" ]]; then
  [[ -z "$(ls -A "$destination")" ]] \
    || fail 'Destination is nonempty but is not a restic repository.'
  echo '[init] Creating destination repo with source chunker parameters ...'
  restic -r "$destination" -p "$password_file" init \
    --from-repo "$source_repo" --from-password-file "$password_file" \
    --copy-chunker-params
fi

echo '[copy] Copying missing snapshots with restic ...'
restic -r "$destination" -p "$password_file" copy \
  --from-repo "$source_repo" --from-password-file "$password_file"

echo '[check] Reading all destination packs ...'
restic -r "$destination" -p "$password_file" check --read-data
dest_count="$(snapshot_count "$destination")" || fail 'Cannot read destination snapshots.'
(( dest_count >= source_count )) \
  || fail "Destination has $dest_count snapshots, fewer than source's $source_count."
echo "[pass] $dest_count verified encrypted snapshots at $destination"
