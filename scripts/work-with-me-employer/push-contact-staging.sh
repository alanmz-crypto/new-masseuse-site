#!/usr/bin/env bash
# Add the Contact page's "Hiring a massage therapist?" section to a STAGING site (staging26 or staging27). Never production.
#
#   S_TARGET=staging27 ./push-contact-staging.sh                dry run: connect, locate the site, export its database, upload, preflight
#   S_TARGET=staging27 ./push-contact-staging.sh --apply        the same, then insert the section (keeps a before-snapshot)
#   S_TARGET=staging27 ./push-contact-staging.sh --rollback FILE  restore Contact from a contact-before-*.json snapshot on the server
#
# Push the employer CSS first (push-staging26.sh): the spacing for this section lives in that CSS block.
# Access: same as push-staging26.sh (credentials file + ssh-agent): SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock
# The SSH account also holds the live site, so every write is conditional on `wp option get home` being exactly the chosen staging site
# (checked here to pick the folder, and again inside the installer, whose host allow-list does not contain production).
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK); see push-staging26.sh for how to start one." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent at $SSH_AUTH_SOCK holds no keys." >&2; exit 2; }

mode=dry; snap=""
case "${1:-}" in
  "") ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; snap="${2:-}"; [ -n "$snap" ] || { echo "--rollback needs the snapshot file path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $1" >&2; exit 1 ;;
esac

# Target: staging26 (default) or staging27. Anything else, production included, is refused here and again inside the installers.
target="${S_TARGET:-staging26}"
case "$target" in staging26|staging27) ;; *) echo "S_TARGET must be staging26 or staging27 (never production)." >&2; exit 1 ;; esac
echo "Target site: $target.thaisolitude.com"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1/3] Connecting ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key (ssh-add -l)." >&2; exit 2; }

echo "[2/3] Uploading the installer and the reviewed section to ~/wwm-package-contact (outside every web root) ..."
tar -C "$here" -cf - install-contact-staging.php contact/contact-employer-section.html \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-contact" && mkdir -p "$HOME/wwm-package-contact" && tar -xf - -C "$HOME/wwm-package-contact"'

echo "[3/3] Running on the server (mode: $mode) ..."
"${ssh_cmd[@]}" bash -s -- "$mode" "$target" "$snap" <<'REMOTE'   # empty args vanish through ssh, so the optional snapshot path goes LAST
set -euo pipefail
mode=$1; target=${2:-}; snap=${3:-}
case "$target" in staging26|staging27) ;; *) echo "Remote refused: target must be staging26 or staging27, got '$target'." >&2; exit 1 ;; esac
want="${target}.thaisolitude.com"
command -v wp >/dev/null || { echo "wp-cli not found on the server" >&2; exit 1; }
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  h=$(wp --path="$d" option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then
    if [ -n "$doc" ]; then echo "More than one install reports $want; refusing to guess." >&2; exit 1; fi
    doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing changed." >&2; exit 1; }
echo "$target folder: $doc"
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
export WWM_BACKUP_DIR="$HOME/private-backups"
contact() { wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-contact/install-contact-staging.php" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }

if [ "$mode" = rollback ]; then contact rollback "$snap"; purge; exit 0; fi

dump="$HOME/private-backups/${target}-before-contact-$(date +%Y%m%d-%H%M%S).sql"
wp --path="$doc" db export "$dump" >/dev/null
[ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
echo "fresh database export: $dump ($(stat -c%s "$dump") bytes)"
contact preflight
if [ "$mode" = apply ]; then
  contact apply
  purge
else
  echo "Dry run only. Re-run with --apply to write."
fi
REMOTE

# ref: ./install-contact-staging.php, ./contact/contact-employer-section.html — the guarded installer and reviewed block this runs on the server
# ref: ./push-staging26.sh — the hiring-page push this mirrors; ../../remote_execution.txt — gitignored access details (never printed)
