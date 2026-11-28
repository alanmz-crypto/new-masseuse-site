#!/usr/bin/env bash
# Ryan-run live update. --apply backs up and verifies the database off-server before writing.
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
repo=$(cd "$here/../../.." && pwd)
creds="${S26_CREDS:-$repo/remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent holds no keys." >&2; exit 2; }

mode=preflight; snapshot=""
case "${1:-}" in
	"") ;;
	--preflight) mode=preflight ;;
	--apply) mode=apply ;;
	--check) mode=check ;;
	--rollback) mode=rollback; snapshot="${2:-}"; [ -n "$snapshot" ] || { echo "--rollback needs the server-side snapshot path." >&2; exit 1; } ;;
	*) echo "Usage: $0 [--preflight | --apply | --check | --rollback SNAPSHOT]" >&2; exit 1 ;;
esac

if [ "$mode" = check ]; then
	python3 "$here/verify-corporate-band-live.py"
	exit $?
fi

ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
tar -C "$here" -cf - install-corporate-band-live.php | "${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-package-corporate-band-live" && tar -xf - -C "$HOME/wwm-package-corporate-band-live"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
phase=$1; snapshot=${2:-}; want=thaisolitude.com
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
	d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
	if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports live; refusing." >&2; exit 1; }; doc=$d; fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No install reports https://$want; nothing changed." >&2; exit 1; }
echo "Live is ${doc#"$HOME"/}"
if [ "$phase" = apply ] || [ "$phase" = rollback ]; then
	mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"; export WWM_BACKUP_DIR="$HOME/private-backups"
fi
run() { wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-corporate-band-live/install-corporate-band-live.php" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "Purge cache in Site Tools if pages remain stale."; }
case "$phase" in
	preflight) run preflight ;;
	apply) run apply; purge ;;
	rollback) run rollback "$snapshot"; purge ;;
	*) echo "Unknown phase: $phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
	preflight) remote preflight; echo "Live values match the reviewed before-state; no WordPress values changed." ;;
	apply)
		remote preflight
		bash "$repo/scripts/ci/backup-now.sh" live
		apply_output=$(remote apply)
		printf '%s\n' "$apply_output"
		snapshot=$(printf '%s\n' "$apply_output" | sed -n 's/.*Rollback snapshot: //p' | tail -1)
		if [ -z "$snapshot" ]; then echo "Applied output omitted the rollback path; stop and inspect production." >&2; exit 1; fi
		bash "$repo/scripts/work-with-me-employer/sync-server-backups.sh"
		if ! python3 "$here/verify-corporate-band-live.py"; then
			echo "Public verification failed. The update remains applied; inspect first, then rollback with:" >&2
			echo "bash $here/push-corporate-band-live.sh --rollback '$snapshot'" >&2
			exit 1
		fi
		;;
	rollback)
		remote preflight
		bash "$repo/scripts/ci/backup-now.sh" live
		remote rollback "$snapshot"
		bash "$repo/scripts/work-with-me-employer/sync-server-backups.sh"
		;;
esac

# ref: ./install-corporate-band-live.php — exact guarded live write; ./verify-corporate-band-live.py — public post-apply checks
# ref: ../../ci/backup-now.sh, ../sync-server-backups.sh — fresh DB export and verified off-server copy
