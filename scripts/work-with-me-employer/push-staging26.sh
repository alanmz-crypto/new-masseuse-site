#!/usr/bin/env bash
# Push the reviewed Work With Me page to STAGING26 ONLY (https://staging26.thaisolitude.com). Never production.
#
#   ./push-staging26.sh                     dry run: connect, locate staging26, export its database,
#                                           upload the package, run the read-only preflight. Changes no site data.
#   ./push-staging26.sh --apply             the same, then write the page (installer keeps a before-snapshot)
#   ./push-staging26.sh --rollback FILE     restore from a before-snapshot JSON on the server
#
# Access: Host / Port / User come from the gitignored remote_execution.txt; the passphrase-protected
# ~/.ssh/thaisolitude key must be held by an ssh-agent, exactly like scripts/prose-pass/apply.py (staging25):
#   mkdir -p /tmp/claude-1000/tsag && chmod 700 /tmp/claude-1000/tsag
#   ssh-agent -a /tmp/claude-1000/tsag/a.sock
#   SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock ssh-add ~/.ssh/thaisolitude        # type the passphrase
#   SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock ./push-staging26.sh [--apply]
# The SSH account also holds the live site, so every write is conditional on `wp option get home` being exactly
# https://staging26.thaisolitude.com (checked here to pick the folder, and again inside install-staging.php).
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || {
  echo "No ssh-agent socket (SSH_AUTH_SOCK). Start one and load the passphrase-protected key:" >&2
  echo "  mkdir -p /tmp/claude-1000/tsag && chmod 700 /tmp/claude-1000/tsag && ssh-agent -a /tmp/claude-1000/tsag/a.sock" >&2
  echo "  SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock ssh-add ~/.ssh/thaisolitude" >&2
  echo "then re-run with SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock" >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent at $SSH_AUTH_SOCK holds no keys; run ssh-add ~/.ssh/thaisolitude against it." >&2; exit 2; }

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
if ! "${ssh_cmd[@]}" true 2>/tmp/s26-ssh-error.txt; then
  echo "SSH login refused: $(tr -d '\r' </tmp/s26-ssh-error.txt | sed -E 's/u[0-9]+-[a-z0-9]+/<user>/' | tail -1)" >&2
  echo "The agent is running but the server still refused it; check that the right key is loaded (ssh-add -l)." >&2
  exit 2
fi

echo "[2/3] Uploading the reviewed package to ~/wwm-package (outside every web root) ..."
tar -C "$here" -cf - install-staging.php page-content.html employer-additions.css baseline/page-1165-before.html resume/Butsaba_Dusrai_LMT_Resume_License_proposed.pdf images/thai-massage-therapy-session-cutout.webp \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package" && mkdir -p "$HOME/wwm-package" && tar -xf - -C "$HOME/wwm-package"'

echo "[3/3] Running on the server (mode: $mode) ..."
"${ssh_cmd[@]}" bash -s -- "$mode" "$target" "$snap" <<'REMOTE'   # empty args vanish through ssh, so the optional snapshot path goes LAST
set -euo pipefail
mode=$1; target=${2:-}; snap=${3:-}
case "$target" in staging26|staging27) ;; *) echo "Remote refused: target must be staging26 or staging27, got '$target'." >&2; exit 1 ;; esac
want="${target}.thaisolitude.com"   # compared by host name; WordPress reports http:// here
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
run() { wp --path="$doc" --user=1 eval-file "$HOME/wwm-package/install-staging.php" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if the page looks stale)"; }

if [ "$mode" = rollback ]; then
  run rollback-staging "$snap"; purge
  exit 0
fi

dump="$HOME/private-backups/${target}-before-wwm-$(date +%Y%m%d-%H%M%S).sql"
wp --path="$doc" db export "$dump" >/dev/null
[ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
echo "fresh database export: $dump ($(stat -c%s "$dump") bytes)"
run                      # read-only preflight (stops here if the page is not the reviewed baseline)
if [ "$mode" = apply ]; then
  run apply-staging; purge
else
  echo "Dry run only. Re-run with --apply to write the page."
fi
REMOTE

# ref: ./install-staging.php — the guarded installer this runs on the server; ./page-content.html, ./employer-additions.css, ./baseline/page-1165-before.html are uploaded with it
# ref: ../../remote_execution.txt — gitignored access details (never printed)
