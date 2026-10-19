#!/usr/bin/env bash
# Create one of the small performance WPCode snippets on staging31 or live (S_TARGET=staging31|live; default staging31). Live was added on Ryan's go (2026-10-17), after staging31 was applied, measured, tested and rolled back/re-applied.
#
#   ./push-perf-snippet.sh <key>                       dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-perf-snippet.sh <key> --apply               export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check it really runs
#                                                      -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-perf-snippet.sh <key> --rollback FILE       delete the snippet again, from a perf-<key>-before-*.json snapshot on the server
# <key> is lazy-images (only the first image is eager) or work-sans (drop Astra's unused render-blocking Google Fonts request); see install-perf-snippet.php.
# The backup is part of the apply and cannot be skipped. After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target> and tools/perf-lcp-runs.mjs.
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

key="${1:-}"
case "$key" in lazy-images|work-sans) ;; *) echo "Usage: $0 <lazy-images|work-sans> [--apply | --rollback FILE]" >&2; exit 1 ;; esac
mode=dry; snap=""
case "${2:-}" in
  "") ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; snap="${3:-}"; [ -n "$snap" ] || { echo "--rollback needs the snapshot file path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $2" >&2; exit 1 ;;
esac
target="${S_TARGET:-staging31}"
case "$target" in staging31|live) ;; *) echo "S_TARGET must be staging31 or live." >&2; exit 1 ;; esac
echo "Target site: $target   snippet: $key   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-perf (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-perf-snippet.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-perf" && mkdir -p "$HOME/wwm-package-perf" && tar -xf - -C "$HOME/wwm-package-perf"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; key=$3; snap=${4:-}
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-perf/install-perf-snippet.php" "$m" "$key" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-perf-${key}-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    case "$key" in
      lazy-images)
        v=$(wp --path="$doc" eval 'echo wp_omit_loading_attr_threshold();' 2>/dev/null | tail -1)
        echo "runtime check in a fresh WordPress process: wp_omit_loading_attr_threshold() = $v (want 1; the WordPress default is 3)"
        [ "$v" = 1 ] || { echo "The snippet is saved but NOT running; use --rollback." >&2; exit 1; } ;;
      work-sans)
        n=$(curl -s --max-time 30 "https://$want/" | grep -c '<link[^>]*fonts\.googleapis\.com' || true)   # links only: comments in the Additional CSS may still MENTION the address
        echo "runtime check: the public page has $n <link> element(s) to fonts.googleapis.com (want 0; before: 3 = the stylesheet and two hints)"
        [ "$n" = 0 ] || { echo "The snippet is saved but the Google Fonts request is STILL on the page (cache?); use --rollback if it persists." >&2; exit 1; } ;;
    esac ;;
  rollback) snap=${snap/#\$HOME/$HOME}; run rollback "$snap"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[2] Preflight (read-only):"; remote preflight "$target" "$key"; echo "Dry run only. Re-run with --apply to write." ;;
  apply)
    echo "[2] Backup on the server + preflight:"; remote prepare "$target" "$key"
    echo "[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):"; "$here/../sync-server-backups.sh"
    echo "[4] Applying:"; remote apply "$target" "$key"
    echo "[4b] Does it really run?"; remote verify "$target" "$key"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$key" "$snap" ;;
esac

# ref: ./install-perf-snippet.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../secure-backups.sh, ../push-human-facing-staging.sh — same connection/guard pattern; ../../../remote_execution.txt — gitignored access details (never printed)
