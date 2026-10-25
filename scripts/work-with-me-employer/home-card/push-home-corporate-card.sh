#!/usr/bin/env bash
# Home page: a Corporate & Group Massage card at the top of "The Massage I Offer", on staging38 (S_TARGET=staging38; the only target: live is NOT enabled and is added only on Ryan's explicit go).
# Ryan, 2026-10-25: "put Corporate and Group at the top of 'Massage I Offer' on the home page now. Make it harmonize with the other services on the page."
#
#   ./push-home-corporate-card.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-home-corporate-card.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check the public Home page
#                                                    -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-home-corporate-card.sh --check            read-only: the public checks of the --apply run, on a site that already has the change
#   ./push-home-corporate-card.sh --rollback FILE    take the card and the ts-home-corp CSS block out again, from a home-corporate-card-before-*.json snapshot on the server
# What it changes is described at the top of install-home-corporate-card.php (one insertion in the Home page and one marked CSS block; nothing else is touched).
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
case "$target" in staging38) ;; *) echo "S_TARGET must be staging38 (live is not enabled for this change)." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-home-corporate-card (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-home-corporate-card.php home-card-content.php home-corporate-card.css \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-home-corporate-card" && mkdir -p "$HOME/wwm-package-home-corporate-card" && tar -xf - -C "$HOME/wwm-package-home-corporate-card"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging38) want="staging38.thaisolitude.com" ;; *) echo "Remote refused: target must be staging38, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-home-corporate-card/install-home-corporate-card.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-home-corporate-card-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). The Home page is fetched once. They run on a site that HAS the change.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    code=$(curl -s -o "$tmp/home.html" -w '%{http_code}' --max-time 30 "https://$want/" || true); echo "  $code /"
    [ "$code" = 200 ] || { echo "/ does not answer 200 (202 would be SiteGround's bot challenge = INVALID, retry later); use --rollback if it persists." >&2; exit 1; }
    raw() { { grep -o -E -- "$1" "$tmp/home.html" || true; } | wc -l; }
    need() { [ "$(raw "$1")" = "$2" ] || { echo "CHECK FAILED: the Home page should contain /$1/ exactly $2 time(s), has $(raw "$1") (cache?); use --rollback if it persists." >&2; return 1; }; }
    need '<h3 class="uagb-heading-text">Corporate &amp; Group Massage</h3>' 1
    need '<p class="uagb-desc-text">On-site chair massage for employee appreciation days and workplace events, with table massage at suitable host venues\.</p>' 1
    need 'uagb-button__link">Corporate</div>' 1
    need 'class="wp-block-uagb-container[^"]*uagb-block-e7a31c58"' 1   # Spectra adds its layout class between the two, so the middle is open
    need 'alt="Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them\."' 1
    # the three existing cards are all still there, once each, and the new card is the FIRST of the four (after the section heading)
    for t in 'Therapeutic Massage' 'Deep Tissue Massage' 'Thai Massage'; do need "<h3 class=\"uagb-heading-text\">$t</h3>" 1; done
    off() { { grep -b -o -F -- "$1" "$tmp/home.html" || true; } | head -1 | cut -d: -f1; }
    h=$(off 'The Massage I Offer'); c=$(off '<h3 class="uagb-heading-text">Corporate &amp; Group Massage</h3>'); t=$(off '<h3 class="uagb-heading-text">Therapeutic Massage</h3>')
    [ -n "$h" ] && [ -n "$c" ] && [ -n "$t" ] && [ "$h" -lt "$c" ] && [ "$c" -lt "$t" ] || { echo "CHECK FAILED: the order is not heading ($h), Corporate card ($c), Therapeutic card ($t)." >&2; exit 1; }
    # the frame rules for the new card are in the page's CSS (three rules), and the picture file itself is served
    need 'uagb-block-e7a31c58 *> *figure\.wp-block-image' 3
    src=$(grep -o -E 'src="[^"]*corporate-group-massage[^"]*\.webp"' "$tmp/home.html" | head -1 | sed 's/^src="//; s/"$//')
    [ -n "$src" ] || { echo "CHECK FAILED: no corporate illustration on the Home page." >&2; exit 1; }
    case "$src" in http*) url="$src" ;; *) url="https://$want$src" ;; esac
    pc=$(curl -s -o /dev/null -w '%{http_code}' --max-time 30 "$url" || true); echo "  $pc $src"
    [ "$pc" = 200 ] || { echo "CHECK FAILED: the picture does not answer 200." >&2; exit 1; }
    echo "public check: the Corporate & Group Massage card is the first of the four on the Home page (title, description, button, picture, frame CSS), the three existing cards are unchanged in place" ;;
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
    echo "[4b] Does the public Home page show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-home-corporate-card.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../corporate/push-corporate-massage.sh — the pattern (same connection/guard steps); ../../../remote_execution.txt — gitignored access details (never printed)
