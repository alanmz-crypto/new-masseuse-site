#!/usr/bin/env bash
# Take a database export of one site now and keep the verified off-site copy (the same two steps every push script takes before a write).
#
#   backup-now.sh TARGET        TARGET: live or stagingNN. Writes ~/private-backups/TARGET-manual-YYYYMMDD-HHMMSS.sql on the server
#                               (outside every web root, owner-only), then runs sync-server-backups.sh (and, in GitHub Actions, its off-site hook).
# Changes no site content.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-}"
case "$target" in
  live) want="thaisolitude.com" ;;
  staging[0-9]|staging[0-9][0-9]) want="$target.thaisolitude.com" ;;
  *) echo "Usage: $0 <live|stagingNN>" >&2; exit 1 ;;
esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # true exit codes on live under PHP 8.5 (see AGENTS.md)
want=$1; target=$2
doc=""
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi
done < <(find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null)
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want." >&2; exit 1; }
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
dump="$HOME/private-backups/${target}-manual-$(date +%Y%m%d-%H%M%S).sql"
( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
[ -s "$dump" ] || { echo "Database export is empty." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}; fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
EOS
ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$want" "$target" <<<"$REMOTE"
"$here/../work-with-me-employer/sync-server-backups.sh"

# ref: ../work-with-me-employer/sync-server-backups.sh — the verified off-server copy; ./offsite-store.sh — its GitHub Actions hook
