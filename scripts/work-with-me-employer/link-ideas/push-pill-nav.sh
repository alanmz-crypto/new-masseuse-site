#!/usr/bin/env bash
# Pinned header + sticky pills on the Thai Massage page: ADD one WPCode snippet (pill-nav.php) on staging39 only. Live needs Ryan's go and an enable-live change.
# Ryan, 2026-11-08 (cloud session): "Pills is best. Can we also make the header static. You can practice on a real staging page, now." (answers: header stays on top; Thai Massage).
#
#   ./push-pill-nav.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-pill-nav.sh --apply            export the DB -> preflight -> COPY the export off the server and verify it -> apply -> purge cache -> check the public pages -> copy the before-snapshot
#   ./push-pill-nav.sh --rollback FILE    delete the snippet again, from a pill-nav-before-*.json snapshot on the server
#   ./push-pill-nav.sh --check            read-only: the public check of the --apply run
# Access: credentials file + ssh-agent. The SSH account also holds live, so every write is conditional on `wp option get home` being exactly staging39
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
target="${S_TARGET:-staging39}"
case "$target" in staging39) ;; *) echo "S_TARGET must be staging39 (practice; live needs Ryan's go)." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-pill-nav (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-pill-nav.php pill-nav.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-pill-nav" && mkdir -p "$HOME/wwm-package-pill-nav" && tar -xf - -C "$HOME/wwm-package-pill-nav"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging39) want="staging39.thaisolitude.com" ;; *) echo "Remote refused: target must be staging39, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-pill-nav/install-pill-nav.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-pill-nav-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). Each page is fetched once.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    bad=0
    fetch() { local code; code=$(curl -s -o "$tmp/$1.html" -w '%{http_code}' --max-time 30 "https://$want$2" || true); echo "  $code $2"; [ "$code" = 200 ] || { echo "$2 does not answer 200 (202 = SiteGround's bot challenge = INVALID, retry later)."; exit 1; }; }
    need() { local n; n=$(grep -o -F -- "$2" "$tmp/$1.html" | wc -l); if [ "$n" = "$3" ]; then echo "  ok   $1: $n x $2"; else echo "  FAIL $1: $n x $2 (want $3)"; bad=1; fi; }
    fetch thai /thai-massage/
    need thai 'id="ts-pill-nav-css"' 1
    need thai 'id="ts-pill-nav-runtime"' 1
    need thai 'class="ts-jump"' 1
    need thai '#masthead{position:sticky' 1
    for pg in therapeutic-massage deep-tissue-massage services; do fetch "$pg" "/$pg/"; need "$pg" 'ts-pill-nav' 0; done
    [ "$bad" = 0 ] || { echo "CHECK FAILED (see the lines above)"; exit 1; }
    echo "public check: Thai Massage carries the pill style and script once; the other service pages carry none" ;;
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
    echo "[4b] Do the public pages show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot off the server:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-pill-nav.php — the guarded installer this runs on the server; ./push-link-ideas.sh — the script this was copied from (same connection/guard pattern)
