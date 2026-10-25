#!/usr/bin/env bash
# Corporate & Group Massage page: the corporate rate is $100 per service hour in BOTH places the page says it (the rate line and the "What does it cost?" FAQ), on staging38 or live (S_TARGET=staging38|live; default staging38).
# Ryan, 2026-10-25: "the price should be $100" (live's rate line already said $100 but its FAQ said $130; staging38 said $130 in both). He starts the live run himself.
#
#   ./push-corporate-rate.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-corporate-rate.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check the public corporate page
#                                               -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-corporate-rate.sh --check            read-only: the public check of the --apply run, on a site that already has the change
#   ./push-corporate-rate.sh --rollback FILE    turn back exactly what that apply changed, from a corporate-rate-before-*.json snapshot on the server
# What it changes is described at the top of install-corporate-rate.php (two exact replacements in one page; a replacement that is already done is left alone).
# The backup is part of the apply and cannot be skipped. Access: credentials file + ssh-agent. The SSH account also holds the other sites, so every write is conditional on `wp option get home`
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
target="${S_TARGET:-staging38}"
case "$target" in staging38|live) ;; *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-corporate-rate (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-corporate-rate.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-corporate-rate" && mkdir -p "$HOME/wwm-package-corporate-rate" && tar -xf - -C "$HOME/wwm-package-corporate-rate"'

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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-corporate-rate/install-corporate-rate.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-corporate-rate-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). The corporate page is fetched once. They run on a site that HAS the change.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    code=$(curl -s -o "$tmp/page.html" -w '%{http_code}' --max-time 30 "https://$want/corporate-massage-portland/" || true); echo "  $code /corporate-massage-portland/"
    [ "$code" = 200 ] || { echo "the corporate page does not answer 200 (202 would be SiteGround's bot challenge = INVALID, retry later); use --rollback if it persists." >&2; exit 1; }
    cnt() { { grep -o -F -- "$1" "$tmp/page.html" || true; } | wc -l; }
    [ "$(cnt '<strong>$100</strong> per service hour')" = 1 ] || { echo "CHECK FAILED: the rate line should say \$100 exactly once (cache?); use --rollback if it persists." >&2; exit 1; }
    [ "$(cnt 'The proposed corporate rate is $100 per service hour.')" -ge 1 ] || { echo "CHECK FAILED: the FAQ answer should say \$100 (cache?); use --rollback if it persists." >&2; exit 1; }
    [ "$(cnt '$130')" = 0 ] || { echo "CHECK FAILED: \$130 is still on the corporate page ($(cnt '$130') time(s), cache?); use --rollback if it persists." >&2; exit 1; }
    echo "public check: the corporate page says \$100 per service hour in the rate line and in the FAQ answer, and \$130 nowhere" ;;
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
    echo "[4b] Does the public corporate page show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-corporate-rate.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ./push-corporate-massage.sh — the pattern (same connection/guard steps); ../../../remote_execution.txt — gitignored access details (never printed)
