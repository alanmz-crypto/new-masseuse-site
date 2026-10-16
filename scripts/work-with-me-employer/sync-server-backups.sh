#!/usr/bin/env bash
# Keep an OFF-SERVER copy of every pre-change DB export and rollback manifest. Copies only (never deletes); safe to run any time, any number of times.
#
#   ./sync-server-backups.sh            pull new *.sql and *.json from the server's ~/private-backups to this computer, then verify by checksum
#
# Destination (outside the git repo, owner-only): $TS_SERVER_BACKUPS or ~/WordPress/thai-solitude-server-backups
# Not copied: the large WPvivid zips (they stay in the server's private vault folders; SiteGround's own backups cover files).
# Run it right after any change that took a backup, and before relying on a backup you have not seen on this computer.
# Access: credentials file + ssh-agent, as the other tools here (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock).
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
dest="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
command -v rsync >/dev/null || { echo "rsync is required on this computer" >&2; exit 1; }

mkdir -p "$dest"; chmod 700 "$dest"
rs=(rsync -a "--chmod=F600,D700" --include='*.sql' --include='*.json' --exclude='*' -e "ssh -p $port -o BatchMode=yes -o ConnectTimeout=20")
src="$user@$host:private-backups/"

echo "Copying new exports and manifests to $dest ..."
"${rs[@]}" "$src" "$dest/"

# Verify: a second pass in checksum mode must find nothing left to transfer, and the file counts and total sizes must agree.
left=$("${rs[@]}" --checksum --dry-run --itemize-changes "$src" "$dest/" | grep -c '^>f' || true)
remote=$(ssh -p "$port" -o BatchMode=yes -o ConnectTimeout=20 "$user@$host" 'cd "$HOME/private-backups" && ls -1 *.sql *.json 2>/dev/null | wc -l; cat *.sql *.json 2>/dev/null | wc -c' | tr '\n' ' ')
local_n=$(find "$dest" -maxdepth 1 \( -name '*.sql' -o -name '*.json' \) | wc -l)
local_b=$(cat "$dest"/*.sql "$dest"/*.json 2>/dev/null | wc -c)
echo "server: $(echo "$remote" | awk '{print $1" files, "$2" bytes"}')   |   this computer: $local_n files, $local_b bytes"
echo "files differing after a checksum comparison: $left"
if [ "$left" -eq 0 ] && [ "$(echo "$remote" | awk '{print $1" "$2}')" = "$local_n $local_b" ]; then echo "VERIFIED: the off-server copy is complete and identical."; else echo "NOT VERIFIED: investigate before trusting the copy." >&2; exit 1; fi

# ref: ./secure-backups.sh, ./push-human-facing-staging.sh — the tools that write the exports and manifests this copies
# ref: ../../remote_execution.txt — gitignored access details (never printed)
