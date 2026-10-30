#!/usr/bin/env bash
# Booking date: corporate and group massage bookings begin November 14, 2026 instead of November 1, on staging38 or live (S_TARGET=staging38|live; default staging38). Ryan, 2026-10-30: "Change booking begins to Nov 14th. Everywhere." He starts any live run himself.
#
#
#   ./push-booking-date.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-booking-date.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check the public pages
#                                                    -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-booking-date.sh --check            read-only: the public checks of the --apply run, on a site that already has the change
#   ./push-booking-date.sh --rollback FILE    put the old texts and the old strip code back, from an booking-date-before-*.json snapshot on the server
# What it changes is described at the top of install-booking-date.php (exact replacements only; nothing else is touched).
# The backup is part of the apply and cannot be skipped. After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target>.
# Access: credentials file + ssh-agent. The SSH account also holds live, so every write is conditional on `wp option get home` being exactly the chosen site
# (checked here to pick the folder, and again inside the installer's own allow-list).
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
target="${S_TARGET:-staging38}"
case "$target" in staging38|live) ;; *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-booking-date (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-booking-date.php booking-date-content.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-booking-date" && mkdir -p "$HOME/wwm-package-booking-date" && tar -xf - -C "$HOME/wwm-package-booking-date"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging38) want="staging38.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging38 or live, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-booking-date/install-booking-date.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-booking-date-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). Every page is fetched once. They run on a site that HAS the change.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    fetch() { local code; code=$(curl -s -o "$tmp/$1.html" -w '%{http_code}' --max-time 30 "https://$want$2" || true); echo "  $code $2"; [ "$code" = 200 ] || { echo "$2 does not answer 200 (202 would be SiteGround's bot challenge = INVALID, retry later); use --rollback if it persists." >&2; return 1; }; }
    text() { sed 's/<[^>]*>/ /g' "$tmp/$1.html" | sed 's/&amp;/\&/g; s/&#038;/\&/g; s/&#8217;/'"'"'/g' | tr -s '[:space:]' ' '; }
    count() { text "$1" | { grep -o -F -- "$2" || true; } | wc -l; }   # a count of zero is a normal answer, not an error (set -e + pipefail)
    need() { [ "$(count "$1" "$2")" = "$3" ] || { echo "CHECK FAILED: $1 should contain '$2' exactly $3 time(s), has $(count "$1" "$2") (cache?); use --rollback if it persists." >&2; return 1; }; }
    raw() { { grep -o -F -- "$2" "$tmp/$1.html" || true; } | wc -l; }
    band='<div class="ts-corp-band">'
    for pg in home about services contact privacy-policy accessibility thai-massage therapeutic-massage deep-tissue-massage; do
      fetch "$pg" "$([ "$pg" = home ] && echo / || echo "/$pg/")"
      [ "$(raw "$pg" "$band")" = 1 ] || { echo "CHECK FAILED: $pg should hold exactly one strip." >&2; exit 1; }
      # before 2026-11-14 (Pacific) the strip says bookings begin that day; from then on it says they are open (a cached page may lag a day: either headline is accepted, one of them must be there)
      a=$(count "$pg" 'Currently booking corporate & group massage only.'); b=$(count "$pg" 'Now accepting corporate & group massage bookings.')
      [ "$((a + b))" = 1 ] || { echo "CHECK FAILED: $pg strip has neither (or both) headlines ($a/$b)." >&2; exit 1; }
      need "$pg" 'November 1, 2026' 0
    done
    need home 'Currently booking corporate & group massage only. Thai Solitude begins accepting corporate and group massage bookings on November 14, 2026, at suitable Portland venues.' "$(( $(date +%s) < $(TZ=America/Los_Angeles date -d '2026-11-14 00:00:00' +%s) ? 1 : 0 ))" || true
    for pg in thai-massage therapeutic-massage deep-tissue-massage; do
      need "$pg" 'Right now I offer corporate and group massage only, at suitable Portland venues, with bookings beginning November 14, 2026. Individual appointments at a dedicated Thai Solitude location are planned for summer 2027. I do not offer individual house calls.' 1
      need "$pg" 'Corporate and group massage bookings begin November 14, 2026. The Corporate and Group Massage page explains how to request a quote. Individual appointments are planned for summer 2027.' 1
    done
    fetch wwm /work-with-me/
    [ "$(raw wwm "$band")" = 0 ] || { echo "CHECK FAILED: Work With Me must not hold the strip." >&2; exit 1; }
    need wwm 'Corporate and group massage bookings begin November 14, 2026; individual appointments at a dedicated Thai Solitude location are planned for summer 2027.' 1
    need wwm 'Start — From November 1 · open for days, evenings and weekends' 1
    need wwm 'Interviews — By phone or video call now, in person from November 1' 1
    fetch corp /corporate-massage-portland/
    [ "$(raw corp "$band")" = 0 ] || { echo "CHECK FAILED: the corporate page must not hold the strip." >&2; exit 1; }
    need corp 'Corporate and group massage bookings begin November 14, 2026. Arranged through an organization or venue at a shared location. I do not make individual home visits.' 1
    need corp 'November 1, 2026' 0
    atleast_for_practices=$(raw home '>For Practices</a>'); [ "$atleast_for_practices" -ge 1 ] || { echo "CHECK FAILED: the 'For Practices' menu label must still be there." >&2; exit 1; }
    echo "public check: every page that said bookings begin November 1 now says November 14 (strip on 9 pages, three FAQ pairs, corporate page, Work With Me); the employment dates and For Practices are unchanged" ;;
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
    echo "[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):"; "$here/../sync-server-backups.sh"
    echo "[4] Applying:"; remote apply "$target"
    echo "[4b] Do the public pages show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-booking-date.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../availability/push-availability-update.sh — the script this was copied from (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
