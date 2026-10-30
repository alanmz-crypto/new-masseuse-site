#!/usr/bin/env bash
# Contact page: the Address line says "Portland, OR" instead of "Mt. Tabor, Portland", on staging38 or live (S_TARGET=staging38|live; default staging38). Live was enabled by Ryan's go ("Go live", 2026-10-29); he starts the live run himself. Before it was enabled: staging38 is applied, checked and
# proven and Ryan says go; he starts the live run himself. The Google map block on that page is NOT touched (Ryan, 2026-10-29: "Skip the map").
# Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." Item 2 of the job (one item at a time).
#
#   ./push-contact-address.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-contact-address.sh --apply            export the DB -> preflight -> COPY the export off the server and verify it -> apply -> purge cache -> check the public Contact page
#                                            -> copy the new before-snapshot off the server. Stops before applying if the copy cannot be verified.
#   ./push-contact-address.sh --rollback FILE    put the old Address text back, from a contact-address-before-*.json snapshot on the server (other edits made since are kept)
# What it changes is described at the top of install-contact-address.php (one paragraph of the Contact page's content).
# The backup is part of the apply and cannot be skipped. After a push, test the site: scripts/work-with-me-employer/post-change-test.sh staging38.
# Access: credentials file + ssh-agent. The SSH account also holds live, so every write is conditional
# on `wp option get home` being exactly the chosen site (checked here to pick the folder, and again inside the installer's own allow-list).
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
  --rollback) mode=rollback; snap="${2:-}"; [ -n "$snap" ] || { echo "--rollback needs the snapshot file path on the server" >&2; exit 1; } ;;
  *) echo "Usage: $0 [--apply | --rollback FILE]" >&2; exit 1 ;;
esac
target="${S_TARGET:-staging38}"
case "$target" in staging38|live) ;; *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-contact-address (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-contact-address.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-contact-address" && mkdir -p "$HOME/wwm-package-contact-address" && tar -xf - -C "$HOME/wwm-package-contact-address"'

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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-contact-address/install-contact-address.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-contact-address-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    tmp=$(mktemp)
    code=$(curl -s -o "$tmp" -w '%{http_code}' --max-time 30 "https://$want/contact/" || true)
    echo "public check: https://$want/contact/ -> HTTP $code (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)"
    [ "$code" = 200 ] || { rm -f "$tmp"; echo "The page does not answer 200 yet; use --rollback if it persists." >&2; exit 1; }
    count() { { grep -o -F -- "$1" "$tmp" || true; } | wc -l; }
    fail=0
    chk() { # name, want, got
      if [ "$3" = "$2" ]; then echo "  ok   $1 ($3)"; else echo "  FAIL $1: want $2, got $3" >&2; fail=1; fi
    }
    atleast1() { [ "$(count "$1")" -ge 1 ] && echo 1 || echo 0; }
    chk "Address line says Portland, OR" 1 "$(count '<p class="uagb-heading-text">Portland, OR</p>')"
    chk "old Address line gone" 0 "$(count '<p class="uagb-heading-text">Mt. Tabor, Portland</p>')"
    chk "phone number still on the page" 1 "$(atleast1 '(312) 539-4328')"
    chk "email still on the page" 1 "$(atleast1 'butsabad@gmail.com')"
    chk "'For employers' block still there" 1 "$(atleast1 'Hiring a massage therapist?')"
    chk "page title tag unchanged" 1 "$(count '<title>Contact Butsaba Dusrai | Oregon LMT</title>')"
    chk "meta description unchanged (corporate wording)" 1 "$(count '<meta name="description" content="Text (312) 539-4328 or email butsabad@gmail.com to ask about corporate and group massage in Portland with Butsaba Dusrai, Oregon LMT."')"
    chk "announcement strip still above the page" 1 "$(atleast1 'class="ts-corp-band"')"
    chk "licence number in the footer" 1 "$(atleast1 'LMT-29905')"
    echo "  info the map was NOT touched (Ryan: skip the map); mentions of the Mt. Tabor map address in the page: $(count 'Mt.%20Tabor') (url-encoded) + $(count 'Mt. Tabor') (plain)"
    rm -f "$tmp"
    [ "$fail" = 0 ] || { echo "The public page does not show what was applied (cache?); use --rollback if it persists." >&2; exit 1; }
    echo "public check: the Contact page shows the new Address line; everything else on it is unchanged" ;;
  rollback) snap=${snap/#\$HOME/$HOME}; run rollback "$snap"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[2] Preflight (read-only):"; remote preflight "$target"; echo "Dry run only. Re-run with --apply to write." ;;
  apply)
    echo "[2] Backup on the server + preflight:"; remote prepare "$target"
    echo "[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):"; "$here/../sync-server-backups.sh"
    echo "[4] Applying:"; remote apply "$target"
    echo "[4b] Does the public page show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot off the server:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-contact-address.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../portland-meta/push-portland-meta.sh — the script this was copied from (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
