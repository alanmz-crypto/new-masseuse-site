#!/usr/bin/env bash
# Remove "Coming soon." from STAGING26 ONLY (https://staging26.thaisolitude.com): the banner on seven pages (+ About's one repeated
# sentence) and the footer panel text. Never production.
#
#   ./push-coming-soon-staging26.sh                       dry run: connect, locate staging26, export its database, upload, preflight both installers
#   ./push-coming-soon-staging26.sh --apply                the same, then apply both (each keeps a before-snapshot)
#   ./push-coming-soon-staging26.sh --rollback-pages FILE  restore the banners from a coming-soon-before-*.json snapshot
#   ./push-coming-soon-staging26.sh --rollback-footer FILE restore the footer panel from a footer-before-*.json snapshot
#
# Access: same as push-staging26.sh (Host/Port/User from the gitignored remote_execution.txt, key held by an ssh-agent):
#   SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock ./push-coming-soon-staging26.sh [--apply]
# The SSH account also holds the live site, so every write is conditional on `wp option get home` being exactly staging26 (checked here
# to pick the folder, and again inside both installers, whose host allow-lists do not contain production).
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
  --rollback-pages) mode=rollback-pages; snap="${2:-}"; [ -n "$snap" ] || { echo "needs the snapshot file path on the server" >&2; exit 1; } ;;
  --rollback-footer) mode=rollback-footer; snap="${2:-}"; [ -n "$snap" ] || { echo "needs the snapshot file path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $1" >&2; exit 1 ;;
esac

# Target: staging26 (default) or staging27. Anything else, production included, is refused here and again inside the installers.
target="${S_TARGET:-staging26}"
case "$target" in staging26|staging27) ;; *) echo "S_TARGET must be staging26 or staging27 (never production)." >&2; exit 1 ;; esac
echo "Target site: $target.thaisolitude.com"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1/3] Connecting ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key (ssh-add -l)." >&2; exit 2; }

echo "[2/3] Uploading the two installers to ~/wwm-package-cs (outside every web root) ..."
tar -C "$here" -cf - install-coming-soon.php footer-location.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-cs" && mkdir -p "$HOME/wwm-package-cs" && tar -xf - -C "$HOME/wwm-package-cs"'

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
pages() { wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-cs/install-coming-soon.php" "$@"; }
footer() { wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-cs/footer-location.php" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }

case "$mode" in
  rollback-pages)  pages rollback "$snap"; purge; exit 0 ;;
  rollback-footer) footer rollback "$snap"; purge; exit 0 ;;
esac

dump="$HOME/private-backups/${target}-before-coming-soon-$(date +%Y%m%d-%H%M%S).sql"
wp --path="$doc" db export "$dump" >/dev/null
[ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
echo "fresh database export: $dump ($(stat -c%s "$dump") bytes)"
pages preflight
footer preflight
if [ "$mode" = apply ]; then
  pages apply
  footer apply
  purge
else
  echo "Dry run only. Re-run with --apply to write."
fi
REMOTE

# ref: ./install-coming-soon.php, ./footer-location.php — the guarded installers this runs on the server
# ref: ./push-staging26.sh — the hiring-page push this mirrors; ../../remote_execution.txt — gitignored access details (never printed)
