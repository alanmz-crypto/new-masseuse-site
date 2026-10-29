#!/usr/bin/env bash
# Home, Thai, Therapeutic and Deep Tissue pages: the SEO titles / meta descriptions say "Portland" instead of "Mt. Tabor", on staging38 (S_TARGET=staging38; default). Live is NOT enabled: it is added only after
# staging38 is applied, checked and proven and Ryan says go; he starts the live run himself.
# Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." He accepted the exact wording ("Accepted. All your changes").
#
#   ./push-portland-meta.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-portland-meta.sh --apply            export the DB -> preflight -> COPY the export off the server and verify it -> apply -> purge cache -> check the four public pages
#                                          -> copy the new before-snapshot off the server. Stops before applying if the copy cannot be verified.
#   ./push-portland-meta.sh --rollback FILE    put the four pages' SureRank rows back exactly, from a portland-meta-before-*.json snapshot on the server
# What it changes is described at the top of install-portland-meta.php (SureRank's title and description for four pages; page content is compared by hash before and after).
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
case "$target" in staging38) ;; *) echo "S_TARGET must be staging38 (live is not enabled for this change yet)." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-portland-meta (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-portland-meta.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-portland-meta" && mkdir -p "$HOME/wwm-package-portland-meta" && tar -xf - -C "$HOME/wwm-package-portland-meta"'

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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-portland-meta/install-portland-meta.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-portland-meta-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    fail=0
    chk() { # name, want, got
      if [ "$3" = "$2" ]; then echo "  ok   $1 ($3)"; else echo "  FAIL $1: want $2, got $3" >&2; fail=1; fi
    }
    # check_page PATH NEW_TITLE NEW_DESC OLD_TITLE OLD_DESC: the new text shows once in the title tag and once in the meta description (a bare & may be served as &amp;); the old text is nowhere
    check_page() {
      local path=$1 nt=$2 nd=$3 ot=$4 od=$5 tmp code
      tmp=$(mktemp)
      code=$(curl -s -o "$tmp" -w '%{http_code}' --max-time 30 "https://$want$path" || true)
      echo "public check: https://$want$path -> HTTP $code (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)"
      [ "$code" = 200 ] || { rm -f "$tmp"; echo "The page does not answer 200 yet; use --rollback if it persists." >&2; exit 1; }
      count() { { grep -o -F -- "$1" "$tmp" || true; } | wc -l; }
      count_any() { # plain + &amp; variant, counted once when they are the same string
        local plain=$1 esc=${1//&/&amp;}
        if [ "$plain" = "$esc" ]; then count "$plain"; else echo $(( $(count "$plain") + $(count "$esc") )); fi
      }
      chk "$path title tag is the new title" 1 "$(count_any "<title>$nt</title>")"
      chk "$path meta description is the new description" 1 "$(count_any "<meta name=\"description\" content=\"$nd\"")"
      if [ "$ot" != "$nt" ]; then chk "$path old title gone everywhere" 0 "$(count_any "$ot")"; fi
      chk "$path old description gone everywhere (meta, og, twitter)" 0 "$(count_any "$od")"
      chk "$path page content untouched (the announcement strip is still there)" 1 "$([ "$(count 'class="ts-corp-band"')" -ge 1 ] && echo 1 || echo 0)"
      chk "$path licence number in the footer" 1 "$([ "$(count 'LMT-29905')" -ge 1 ] && echo 1 || echo 0)"
      rm -f "$tmp"
    }
    check_page "/" \
      'Thai Solitude | Thai & Therapeutic Massage in Portland, OR' \
      'Personalized Thai, therapeutic and deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT.' \
      'Thai Solitude | Thai & Therapeutic Massage, Mt. Tabor, Portland' \
      'Personalized Thai, therapeutic and deep tissue massage in Mt. Tabor, Portland. Ease tension and unwind. Ask about a session with Butsaba Dusrai, LMT.'
    check_page "/thai-massage/" \
      'Thai Massage in Portland | Butsaba Dusrai, Oregon LMT' \
      'Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.' \
      'Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT' \
      'Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.'
    check_page "/therapeutic-massage/" \
      'Therapeutic Massage | Butsaba Dusrai, Oregon LMT' \
      'Therapeutic massage in Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.' \
      'Therapeutic Massage | Butsaba Dusrai, Oregon LMT' \
      'Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.'
    check_page "/deep-tissue-massage/" \
      'Deep Tissue Massage | Butsaba Dusrai, Oregon LMT' \
      'Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.' \
      'Deep Tissue Massage | Butsaba Dusrai, Oregon LMT' \
      'Deep tissue massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.'
    [ "$fail" = 0 ] || { echo "A public page does not show what was applied (cache?); use --rollback if it persists." >&2; exit 1; }
    echo "public check: all four pages show the new title and description in the head; their content is unchanged" ;;
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
    echo "[4b] Do the public pages show the changes?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot off the server:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-portland-meta.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../contact-meta/push-contact-meta.sh — the script this was copied from (same connection/guard pattern); ../services-meta/push-service-meta.sh — the two-page precedent; ../../../remote_execution.txt — gitignored access details (never printed)
