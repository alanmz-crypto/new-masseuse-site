#!/usr/bin/env bash
# Thai Massage page: SEO title and meta description, on staging33 (S_TARGET=staging33; default). Live is NOT enabled: it needs Ryan's explicit go, and the live host is added to install-thai-meta.php
# only then (the auto-mode classifier refuses an agent adding a live host: hand him a tested patch, as for the Therapeutic page).
# Ryan, 2026-10-19: "I like yours. Go with that meta." (after he asked what a meta description is and saw the before/after).
#
#   ./push-thai-meta.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-thai-meta.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check the public page
#                                          -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-thai-meta.sh --rollback FILE    put the page's SureRank rows back exactly, from a thai-meta-before-*.json snapshot on the server
# What it changes is described at the top of install-thai-meta.php (SureRank's title and description for this one page; the page content is compared by hash before and after).
# The backup is part of the apply and cannot be skipped. After a push, test the site: scripts/work-with-me-employer/post-change-test.sh staging33.
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
target="${S_TARGET:-staging33}"
case "$target" in staging33) ;; *) echo "S_TARGET must be staging33 (live is not enabled for this change)." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-thai-meta (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-thai-meta.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-thai-meta" && mkdir -p "$HOME/wwm-package-thai-meta" && tar -xf - -C "$HOME/wwm-package-thai-meta"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging33) want="staging33.thaisolitude.com" ;; *) echo "Remote refused: target must be staging33, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-thai-meta/install-thai-meta.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-thai-meta-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    tmp=$(mktemp)
    code=$(curl -s -o "$tmp" -w '%{http_code}' --max-time 30 "https://$want/thai-massage/" || true)
    echo "public check: https://$want/thai-massage/ -> HTTP $code (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)"
    [ "$code" = 200 ] || { rm -f "$tmp"; echo "The page does not answer 200 yet; use --rollback if it persists." >&2; exit 1; }
    count() { grep -o -F -- "$1" "$tmp" | wc -l; }
    fail=0
    chk() { # name, want, got
      if [ "$3" = "$2" ]; then echo "  ok   $1 ($3)"; else echo "  FAIL $1: want $2, got $3" >&2; fail=1; fi
    }
    T='Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT'
    D='Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.'
    chk "title tag" 1 "$(count "<title>$T</title>")"
    chk "meta description" 1 "$(count "<meta name=\"description\" content=\"$D\"")"
    chk "og:title follows" 1 "$(count "<meta property=\"og:title\" content=\"$T\"")"
    chk "og:description follows" 1 "$(count "<meta property=\"og:description\" content=\"$D\"")"
    chk "twitter:title follows" 1 "$(count "<meta name=\"twitter:title\" content=\"$T\"")"
    chk "twitter:description follows" 1 "$(count "<meta name=\"twitter:description\" content=\"$D\"")"
    chk "old cut-off description gone" 0 "$(count 'Thai Massage in Mt. Tabor What Is Thai Massage?')"
    chk "canonical unchanged" 1 "$(count "<link rel=\"canonical\" href=\"https://$want/thai-massage/\">")"
    chk "page content untouched (research section still there once)" 1 "$(count 'What the Research Says About Thai Massage')"
    chk "licence number in the footer" 1 "$([ "$(count 'LMT-29905')" -ge 1 ] && echo 1 || echo 0)"
    rm -f "$tmp"
    [ "$fail" = 0 ] || { echo "The public page does not show what was applied (cache?); use --rollback if it persists." >&2; exit 1; }
    echo "public check: the new title and description show in the page head, with the previews following" ;;
  rollback) snap=${snap/#\$HOME/$HOME}; run rollback "$snap"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[2] Preflight (read-only):"; remote preflight "$target"; echo "Dry run only. Re-run with --apply to write." ;;
  apply)
    echo "[2] Backup on the server + preflight:"; remote prepare "$target"
    echo "[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):"; "$here/../sync-server-backups.sh"
    echo "[4] Applying:"; remote apply "$target"
    echo "[4b] Does the public page show the changes?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-thai-meta.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../links/push-thammasat-link.sh — the script this was copied from (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
