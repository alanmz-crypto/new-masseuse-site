#!/usr/bin/env bash
# Accessibility page VERSION 4 (names the Deep Tissue Massage page among the pages with collapsible research summaries, and the new date) on staging36 or live (S_TARGET=staging36|live; default staging36).
# Live was enabled on Ryan's go ("Prepare the Accessibility update for live", 2026-10-24); Ryan merges the PR and starts the live run himself.
# Ryan, 2026-10-24: "Add the Deep Tissue line to the Accessibility page". The installer refuses unless the page holds exactly version 3 AND the Thai Massage, Therapeutic Massage and Deep Tissue Massage pages on that site
# carry their research sections.
#
#   ./push-accessibility-page-v4.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-accessibility-page-v4.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check the public page
#                                                      -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-accessibility-page-v4.sh --rollback FILE    put version 3 back, from an accessibility-page-v4-before-*.json snapshot on the server
# What it changes is described at the top of install-accessibility-page-v4.php (the page content only). The backup is part of the apply and cannot be skipped.
# After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target>.
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
target="${S_TARGET:-staging36}"
case "$target" in staging36|live) ;; *) echo "S_TARGET must be staging36 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-accessibility-v4 (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-accessibility-page-v4.php accessibility-page-v3.html accessibility-page.html \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-accessibility-v4" && mkdir -p "$HOME/wwm-package-accessibility-v4" && tar -xf - -C "$HOME/wwm-package-accessibility-v4"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging36) want="staging36.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging36 or live, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-accessibility-v4/install-accessibility-page-v4.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-accessibility-page-v4-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    tmp=$(mktemp)
    code=$(curl -s -o "$tmp" -w '%{http_code}' --max-time 30 "https://$want/accessibility/" || true)
    echo "public check: https://$want/accessibility/ -> HTTP $code (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)"
    [ "$code" = 200 ] || { rm -f "$tmp"; echo "The page does not answer 200 yet; use --rollback if it persists." >&2; exit 1; }
    grep -q 'collapsible research summaries on the Thai Massage, Therapeutic Massage and Deep Tissue Massage pages open and close' "$tmp" && grep -q 'No screen reader was used in this testing' "$tmp" && grep -q 'switches the site between light and dark colors' "$tmp" || { rm -f "$tmp"; echo "The new sentences (or the kept dark/light paragraph) are not on the page (cache?); use --rollback if it persists." >&2; exit 1; }
    grep -q 'Last updated: October 24, 2026' "$tmp" && grep -q '>Known limitations<' "$tmp" && grep -q '>Feedback<' "$tmp" || { rm -f "$tmp"; echo "The page answers but the reviewed headings or date are missing; use --rollback." >&2; exit 1; }
    rm -f "$tmp"
    echo "public check: the new research summaries sentence (Deep Tissue named) and the screen reader sentence, the kept dark/light paragraph, the Known limitations and Feedback sections and the new date are on the page" ;;
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
    echo "[4b] Does the public page show version 4?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-accessibility-page-v4.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ./push-accessibility-page-v2.sh — the script this was copied from (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
