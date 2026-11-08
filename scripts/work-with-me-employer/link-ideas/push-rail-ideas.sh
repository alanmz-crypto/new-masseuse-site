#!/usr/bin/env bash
# Side rail ideas: ADD the practice page /rail-nav-ideas/ (five thumb-zone rail designs: browse without moving, jump only when chosen) on staging39 only. Never live.
# Ryan, 2026-11-08 (cloud session): "https://staging39.thaisolitude.com use this staging ... Create a practice page ... 5 ideas ... real code that really works".
#
#   ./push-rail-ideas.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-rail-ideas.sh --apply            export the DB -> preflight -> COPY the export off the server and verify it -> apply -> purge cache -> check the public page -> copy the before-snapshot
#   ./push-rail-ideas.sh --rollback FILE    delete the page again, from a rail-ideas-before-*.json snapshot on the server
#   ./push-rail-ideas.sh --check            read-only: the public check of the --apply run
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
case "$target" in staging39) ;; *) echo "S_TARGET must be staging39 (practice page; never live)." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-rail-ideas (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-rail-ideas.php rail-ideas.html \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-rail-ideas" && mkdir -p "$HOME/wwm-package-rail-ideas" && tar -xf - -C "$HOME/wwm-package-rail-ideas"'

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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-rail-ideas/install-rail-ideas.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-rail-ideas-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public check, made from the server itself (not challenged by SiteGround's bot protection). The page is fetched once.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    code=$(curl -s -o "$tmp/p.html" -w '%{http_code}' --max-time 30 "https://$want/rail-nav-ideas/" || true); echo "  $code /rail-nav-ideas/"
    [ "$code" = 200 ] || { echo "/rail-nav-ideas/ does not answer 200 (202 = SiteGround's bot challenge = INVALID, retry later)." >&2; exit 1; }
    bad=0
    need() { local n; n=$( { grep -o -F -- "$1" "$tmp/p.html" || true; } | wc -l); if [ "$n" = "$2" ]; then echo "  ok   $n x $1"; else echo "  FAIL $n x $1 (want $2)"; bad=1; fi; }
    need 'id="tsr-lab"' 1
    need '<h1>Side rail ideas</h1>' 1
    need 'data-idea="' 5                                   # five picker buttons
    need 'class="tsr-sec"' 6
    need "document.getElementById('tsr-lab')" 1          # the script arrived unfiltered
    need '.tsr-lab .tsr-nav{position:fixed' 1          # the style arrived unfiltered
    sed -n "/document.getElementById('tsr-lab')/,/<\/script>/p" "$tmp/p.html" | grep -o -E '.{0,70}&#(8220|8217|8216|038);.{0,40}' | sed 's/^/  altered: /' || true
    [ "$(sed -n "/document.getElementById('tsr-lab')/,/<\/script>/p" "$tmp/p.html" | grep -c -F -e '&#8220;' -e '&#8217;' -e '&#038;' -e '&#8216;')" = 0 ] && echo "  ok   the page script is unaltered" || { echo "  FAIL the page script was altered by WordPress"; bad=1; }
    [ "$bad" = 0 ] || { echo "CHECK FAILED (see the lines above)"; exit 1; }
    echo "public check: the page answers 200 with its heading, 5 ideas, 6 sample sections, and its style and script intact" ;;
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

# ref: ./install-rail-ideas.php — the guarded installer this runs on the server; ../chair-massage/push-chair-massage.sh — the script this was copied from (same connection/guard pattern)
