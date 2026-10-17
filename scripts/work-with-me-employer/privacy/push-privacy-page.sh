#!/usr/bin/env bash
# Add the Privacy Policy page and its footer link on staging31 or live (S_TARGET=staging31|live; default staging31). Live was added on Ryan's go (2026-10-17: "publish that. add the slug and go"),
# after staging31 had been applied, checked and rolled back/re-applied.
#
#   ./push-privacy-page.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-privacy-page.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check the public pages
#                                             -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-privacy-page.sh --rollback FILE    remove the page, reset the privacy option and restore the footer widget, from a privacy-page-before-*.json snapshot on the server
# What it changes is described at the top of install-privacy-page.php (one page, one option, one footer link; the license line is not touched).
# The backup is part of the apply and cannot be skipped. After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target>.
# Access: credentials file + ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock). The SSH account also holds live, so every write is conditional
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
target="${S_TARGET:-staging31}"
case "$target" in staging31|live) ;; *) echo "S_TARGET must be staging31 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-privacy (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-privacy-page.php privacy-policy.html \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-privacy" && mkdir -p "$HOME/wwm-package-privacy" && tar -xf - -C "$HOME/wwm-package-privacy"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging31) want="staging31.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging31 or live, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-privacy/install-privacy-page.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-privacy-page-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    code=$(curl -s -o /tmp/wwm-privacy-page.$$ -w '%{http_code}' --max-time 30 "https://$want/privacy-policy/" || true)
    echo "public check: https://$want/privacy-policy/ -> HTTP $code (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)"
    [ "$code" = 200 ] || { rm -f /tmp/wwm-privacy-page.$$; echo "The page does not answer 200 yet; use --rollback if it persists." >&2; exit 1; }
    grep -q 'Last updated: October 17, 2026' /tmp/wwm-privacy-page.$$ || { rm -f /tmp/wwm-privacy-page.$$; echo "The page answers but does not contain the reviewed text; use --rollback." >&2; exit 1; }
    rm -f /tmp/wwm-privacy-page.$$
    home=$(curl -s --max-time 30 "https://$want/" || true)
    grep -q 'href="/privacy-policy/">Privacy&nbsp;Policy</a>' <<<"$home" || { echo "The footer link is not on the public home page (cache?); use --rollback if it persists." >&2; exit 1; }
    grep -q 'Oregon License LMT-29905' <<<"$home" || { echo "The license line is missing from the public home page; use --rollback at once." >&2; exit 1; }
    echo "public check: the footer link and the license line are both on the home page" ;;
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
    echo "[4b] Do the public pages show it?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-privacy-page.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../performance/push-perf-snippet.sh, ../secure-backups.sh — same connection/guard pattern; ../../../remote_execution.txt — gitignored access details (never printed)
