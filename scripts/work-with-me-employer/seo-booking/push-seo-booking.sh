#!/usr/bin/env bash
# SEO booking path (Ryan, 2026-11-20: "make changes so the SEO gets better scores"): one FAQ sentence on Thai, Therapeutic and Deep Tissue (book an individual session at
# Winding Willow), the search-result descriptions of Thai, Deep Tissue and Home, and sameAs in the LocalBusiness structured data. S_TARGET=staging40 (default); live only on Ryan's go.
#
#   ./push-seo-booking.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-seo-booking.sh --apply            export the DB -> preflight -> COPY the export off the server and verify it -> apply -> check the public pages
#                                            -> copy the new before-snapshot off the server. Stops before applying if the copy cannot be verified.
#   ./push-seo-booking.sh --rollback FILE    put everything back, from a seo-booking-before-*.json snapshot on the server
#   ./push-seo-booking.sh --check            read-only: the public checks of the --apply run
# What it changes is described at the top of install-seo-booking.php. Access: credentials file + ssh-agent; every write is conditional on `wp option get home`
# being exactly the chosen site (checked here to pick the folder, and again inside the installer's own allow-list).
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent at $SSH_AUTH_SOCK holds no keys." >&2; exit 2; }

mode=dry; snap=""
case "${1:-}" in
  "") ;;
  --apply) mode=apply ;;
  --check) mode=check ;;
  --rollback) mode=rollback; snap="${2:-}"; [ -n "$snap" ] || { echo "--rollback needs the snapshot file path on the server" >&2; exit 1; } ;;
  *) echo "Usage: $0 [--apply | --check | --rollback FILE]" >&2; exit 1 ;;
esac
target="${S_TARGET:-staging40}"
case "$target" in
  staging40) ;;
  *) echo "S_TARGET must be staging40 (live is added only on Ryan's go)." >&2; exit 1 ;;
esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-seo-booking (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-seo-booking.php seo-booking.json \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-seo-booking" && mkdir -p "$HOME/wwm-package-seo-booking" && tar -xf - -C "$HOME/wwm-package-seo-booking"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); see AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging40) want="$target.thaisolitude.com" ;; *) echo "Remote refused: target must be staging40, got '$target'." >&2; exit 1 ;; esac
command -v wp >/dev/null || { echo "wp-cli not found on the server" >&2; exit 1; }
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  h=$(wp --path="$d" option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then
    [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }
    doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}"
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
export WWM_BACKUP_DIR="$HOME/private-backups"
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-seo-booking/install-seo-booking.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-seo-booking-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). Each page is fetched once.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    fetch() { local code; code=$(curl -s -o "$tmp/$1.html" -w '%{http_code}' --max-time 30 "https://$want$2" || true); echo "  $code $2"; [ "$code" = 200 ] || { echo "$2 does not answer 200 (202 would be SiteGround's bot challenge = INVALID, retry later)." >&2; return 1; }; }
    count() { grep -o -F -- "$2" "$tmp/$1.html" | wc -l; }
    need() { [ "$(count "$1" "$2")" = "$3" ] || { echo "CHECK FAILED: $1 should contain '$2' exactly $3 time(s), has $(count "$1" "$2") (cache?)." >&2; return 1; }; }
    atleast() { [ "$(count "$1" "$2")" -ge "$3" ] || { echo "CHECK FAILED: $1 should contain '$2' at least $3 time(s), has $(count "$1" "$2") (cache?)." >&2; return 1; }; }
    S='Until then, you can book an individual Thai, deep tissue or Swedish massage with me at Winding Willow Acupuncture &amp; Wellness, 1540 SE Clinton St, Portland.'
    for pg in thai-massage therapeutic-massage deep-tissue-massage; do
      fetch "$pg" "/$pg/"
      need "$pg" "$S" 1
      need "$pg" "href=\"https://$want/winding-willow/\" class=\"menu-link\">Winding Willow</a>" 3   # still only the menu link to the page (Ryan 2026-11-20)
      need "$pg" '/winding-willow/' 3
      need "$pg" 'windingwillowacupuncture.com' 0
    done
    need thai-massage 'content="Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: compression, rocking and assisted stretching. Book with her at Winding Willow on SE Clinton."' 1
    need deep-tissue-massage 'content="Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles. Book with her at Winding Willow on SE Clinton St."' 1
    need therapeutic-massage 'content="Therapeutic massage in Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go."' 1
    fetch home /
    need home 'content="Thai, therapeutic and deep tissue massage in Portland with Butsaba Dusrai, LMT. Book at Winding Willow, or ask about corporate and group massage."' 1
    need home '"sameAs":["https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt"]' 1
    need home '/winding-willow/' 3
    echo "public check: the sentence is on the three service pages once each, the three new descriptions and the sameAs are served, Therapeutic keeps its description, and no new link to /winding-willow/ or to their site was added" ;;
  rollback) snap=${snap/#\$HOME/$HOME}; run rollback "$snap"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[2] Preflight (read-only):"; remote preflight "$target"; echo "Dry run only. Re-run with --apply to write." ;;
  check) echo "[2] Public checks (read-only):"; remote verify "$target" ;;
  apply)
    echo "[2] Backup on the server + preflight:"; remote prepare "$target"
    echo "[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):"; "$here/../sync-server-backups.sh"
    echo "[4] Applying:"; remote apply "$target"
    echo "[4b] Does the public page show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot off the server:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-seo-booking.php — the guarded installer this runs on the server; ../winding-willow/push-winding-willow.sh — the script this is copied from
