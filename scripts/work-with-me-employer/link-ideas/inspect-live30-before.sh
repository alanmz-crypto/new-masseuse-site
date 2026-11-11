#!/usr/bin/env bash
# Read exactly two hashed rows from LIVE #30's immutable pre-write SQL backup.
# No WordPress bootstrap, SQL execution, site request, installer or cache purge.
# Only this fixed PHP reader is temporarily transferred outside the webroot.
# shellcheck disable=SC2016
set -euo pipefail
[[ $# -eq 0 && ${S_TARGET:-} = live ]] || { echo 'Requires S_TARGET=live and no arguments.' >&2; exit 1; }
here=$(cd "$(dirname "$0")" && pwd)
helper="$here/peek-release/inspect-live30-before.php"
expected=ea24e08d64164106e5f026a9a7522ae0cbe22cff9b53a8b3063b917c85334306
[[ $(sha256sum "$helper" | cut -d' ' -f1) = "$expected" ]] || { echo 'Fixed reader hash differs.' >&2; exit 1; }
creds=${S26_CREDS:-$here/../../../remote_execution.txt}
[[ -f "$creds" ]] || { echo 'Missing connection file.' >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user=${user%%@*}
[[ -n "$host" && "$port" =~ ^[0-9]+$ && -n "$user" ]] || { echo 'Incomplete connection fields.' >&2; exit 1; }
[[ -n ${SSH_AUTH_SOCK:-} && -S "$SSH_AUTH_SOCK" ]] || { echo 'No ssh-agent socket.' >&2; exit 2; }
read -r -d '' REMOTE <<'REMOTE' || true
set -euo pipefail
umask 077
temp=$(mktemp -d "$HOME/.peek-live30-reader.XXXXXX")
cleanup() { rm -rf -- "$temp"; [[ ! -e "$temp" ]] || { echo 'Reader cleanup failed.' >&2; exit 1; }; }
trap cleanup EXIT
tar --no-same-owner -xf - -C "$temp"
reader="$temp/inspect-live30-before.php"
[[ -f "$reader" && ! -L "$reader" && $(sha256sum "$reader" | cut -d' ' -f1) = ea24e08d64164106e5f026a9a7522ae0cbe22cff9b53a8b3063b917c85334306 ]] || { echo 'Transferred reader hash differs.' >&2; exit 1; }
php -d display_errors=0 -d log_errors=0 "$reader"
cleanup
trap - EXIT
echo 'LIVE30-BEFORE-CLEANUP PASS'
REMOTE
printf -v remote_q '%q' "$REMOTE"
tar -C "$here/peek-release" -cf - inspect-live30-before.php | ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" "bash -c $remote_q"
