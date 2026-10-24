#!/usr/bin/env bash
# Keep an OFF-SERVER copy of every pre-change DB export and rollback manifest. Copies only (never deletes); safe to run any time, any number of times.
#
#   ./sync-server-backups.sh            pull new *.sql and *.json from the server's ~/private-backups to this computer, then verify by checksum
#
# Destination (outside the git repo, owner-only): $TS_SERVER_BACKUPS or ~/WordPress/thai-solitude-server-backups
# Not copied: the large WPvivid zips (they stay in the server's private vault folders; SiteGround's own backups cover files).
# Run it right after any change that took a backup, and before relying on a backup you have not seen on this computer.
# Access: credentials file + ssh-agent, as the other tools here (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock).
#
# Two optional settings, used by GitHub Actions (scripts/ci/setup-ssh.sh sets them; unset, the behaviour above is unchanged):
#   TS_SYNC_MAX_AGE_MIN=N   copy and verify only the files changed on the server in the last N minutes, by sha256 on both sides
#                           (a fresh runner holds no earlier copies); finding none is NOT VERIFIED, because a caller expects the backup it just took
#   TS_OFFSITE_HOOK=PROG    after a verified copy, run PROG DEST FILE... (the copied file names); if PROG fails the copy counts as NOT VERIFIED
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
dest="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
max_age="${TS_SYNC_MAX_AGE_MIN:-}"
hook="${TS_OFFSITE_HOOK:-}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
command -v rsync >/dev/null || { echo "rsync is required on this computer" >&2; exit 1; }
case "$max_age" in ''|*[!0-9]*) [ -z "$max_age" ] || { echo "TS_SYNC_MAX_AGE_MIN must be a number of minutes." >&2; exit 1; } ;; esac
[ -z "$hook" ] || [ -x "$hook" ] || { echo "TS_OFFSITE_HOOK is not an executable program: $hook" >&2; exit 1; }

mkdir -p "$dest"; chmod 700 "$dest"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o ConnectTimeout=20 "$user@$host")
rs=(rsync -a "--chmod=F600,D700" --include='*.sql' --include='*.json' --exclude='*' -e "ssh -p $port -o BatchMode=yes -o ConnectTimeout=20")
src="$user@$host:private-backups/"

if [ -n "$max_age" ]; then
  list=$(mktemp); trap 'rm -f "$list"' EXIT
  "${ssh_cmd[@]}" "cd \"\$HOME/private-backups\" && find . -maxdepth 1 -type f \( -name '*.sql' -o -name '*.json' \) -mmin -$max_age -printf '%f\n' | sort" > "$list"
  n=$(grep -c . "$list" || true)
  [ "$n" -gt 0 ] || { echo "NOT VERIFIED: no export or manifest changed on the server in the last $max_age minutes." >&2; exit 1; }
  echo "Copying the $n export(s)/manifest(s) changed in the last $max_age minutes to $dest ..."
  "${rs[@]}" --files-from="$list" "$src" "$dest/"
  # Verify: the sha256 of every listed file must be the same on the server and here.
  remote_sums=$("${ssh_cmd[@]}" 'cd "$HOME/private-backups" && xargs -d "\n" sha256sum --' < "$list")
  local_sums=$(cd "$dest" && xargs -d '\n' sha256sum -- < "$list")
  if [ -n "$remote_sums" ] && [ "$remote_sums" = "$local_sums" ]; then echo "VERIFIED: $n file(s), identical sha256 on the server and in the copy."; else echo "NOT VERIFIED: the copy's checksums differ from the server's; investigate before trusting it." >&2; exit 1; fi
  mapfile -t copied < "$list"
else
  echo "Copying new exports and manifests to $dest ..."
  "${rs[@]}" "$src" "$dest/"

  # Verify: a second pass in checksum mode must find nothing left to transfer, and the file counts and total sizes must agree.
  left=$("${rs[@]}" --checksum --dry-run --itemize-changes "$src" "$dest/" | grep -c '^>f' || true)
  remote=$("${ssh_cmd[@]}" 'cd "$HOME/private-backups" && ls -1 *.sql *.json 2>/dev/null | wc -l; cat *.sql *.json 2>/dev/null | wc -c' | tr '\n' ' ')
  local_n=$(find "$dest" -maxdepth 1 \( -name '*.sql' -o -name '*.json' \) | wc -l)
  local_b=$(cat "$dest"/*.sql "$dest"/*.json 2>/dev/null | wc -c)
  echo "server: $(echo "$remote" | awk '{print $1" files, "$2" bytes"}')   |   this computer: $local_n files, $local_b bytes"
  echo "files differing after a checksum comparison: $left"
  if [ "$left" -eq 0 ] && [ "$(echo "$remote" | awk '{print $1" "$2}')" = "$local_n $local_b" ]; then echo "VERIFIED: the off-server copy is complete and identical."; else echo "NOT VERIFIED: investigate before trusting the copy." >&2; exit 1; fi
  mapfile -t copied < <(find "$dest" -maxdepth 1 \( -name '*.sql' -o -name '*.json' \) -printf '%f\n' | sort)
fi

if [ -n "$hook" ]; then
  "$hook" "$dest" "${copied[@]}" || { echo "NOT VERIFIED: the off-site hook ($(basename "$hook")) did not confirm its copy." >&2; exit 1; }
fi

# ref: ./secure-backups.sh, ./push-human-facing-staging.sh — the tools that write the exports and manifests this copies
# ref: ../ci/setup-ssh.sh, ../ci/offsite-store.sh — the GitHub Actions settings and hook (TS_SYNC_MAX_AGE_MIN, TS_OFFSITE_HOOK)
# ref: ../../remote_execution.txt — gitignored access details (never printed)
