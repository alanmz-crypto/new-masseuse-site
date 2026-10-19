#!/usr/bin/env bash
# Thai Massage and Therapeutic Massage pages: replace the research sections with the "sandwich" version (positive findings, then the weaker, mixed and negative ones in full, then an encouraging close, plus the results the
# 2026-10-19 audit found missing) on staging34 ONLY (S_TARGET=staging34, the default). Both pages together.
# Live is NOT enabled: it needs Ryan's explicit go, a tested enable-live patch (the auto-mode classifier refuses an agent adding a live host) and his own --apply.
# Ryan, 2026-10-19: "double check on both of the pages ... so we can sandwich the discouraging results between two assertive positive claims", then "you can sandwich with the method you used on the Therapeutic page".
#
#   ./push-sandwich-update.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-sandwich-update.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check both public pages
#                                                -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-sandwich-update.sh --rollback FILE    put both old research sections back, from a sandwich-update-before-*.json snapshot on the server
# What it changes is described at the top of install-sandwich-update.php (the research section of both pages, nothing above it). The backup is part of the apply and cannot be skipped.
# After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target> (the two pages differ from live until live has the change).
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
target="${S_TARGET:-staging34}"
case "$target" in staging34) ;; *) echo "S_TARGET must be staging34 (live is not enabled for this change)." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-sandwich-update (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-sandwich-update.php sandwich-update-content.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-sandwich-update" && mkdir -p "$HOME/wwm-package-sandwich-update" && tar -xf - -C "$HOME/wwm-package-sandwich-update"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging34) want="staging34.thaisolitude.com" ;; *) echo "Remote refused: target must be staging34, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-sandwich-update/install-sandwich-update.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-sandwich-update-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    tmp=$(mktemp)
    fail=0
    chk() { # name, want, got
      if [ "$3" = "$2" ]; then echo "  ok   $1 ($3)"; else echo "  FAIL $1: want $2, got $3" >&2; fail=1; fi
    }
    for page in thai-massage therapeutic-massage; do
      code=$(curl -s -o "$tmp" -w '%{http_code}' --max-time 30 "https://$want/$page/" || true)
      echo "public check: https://$want/$page/ -> HTTP $code (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)"
      [ "$code" = 200 ] || { rm -f "$tmp"; echo "The page does not answer 200 yet; use --rollback if it persists." >&2; exit 1; }
      count() { grep -o -F -- "$1" "$tmp" | wc -l; }
      chk "$page: scope note" 1 "$(count 'studies of massage used for specific medical conditions are not summarized here')"
      chk "$page: 'included the disappointing results' sentence" 1 "$(count 'I included the studies I found with disappointing results, not only the favorable ones.')"
      chk "$page: adverse-events review" 1 "$(count '138 adverse events')"
      chk "$page: encouraging safety close" 1 "$(count 'Reviews describe serious events as rare')"
      chk "$page: five collapsible panels" 5 "$(count '<details class="wp-block-details')"
      chk "$page: licence number in the footer" 1 "$([ "$(count 'LMT-29905')" -ge 1 ] && echo 1 || echo 0)"
      if [ "$page" = thai-massage ]; then
        chk "$page: research heading once" 1 "$(count 'What the Research Says About Thai Massage')"
        chk "$page: 31 source entries" 31 "$(count '<cite class="ts-source">')"
        chk "$page: the Thai case report" 1 "$(count 'spinal epidural hematoma')"
        chk "$page: the null shoulder-strength result, quoted" 1 "$(count 'strength in shoulders was not significantly different')"
        chk "$page: old first sentence gone" 0 "$(count 'The review noted that the reduction')"
      else
        chk "$page: research heading once" 1 "$(count 'What the Research Says About Therapeutic Massage')"
        chk "$page: 30 source entries" 30 "$(count '<cite class="ts-source">')"
        chk "$page: lower vigor after hard exercise" 1 "$(count 'a lower mood score for vigor')"
        chk "$page: placebo massage as good as real for back pain" 1 "$(count 'adding real massage or a placebo massage for three weeks both reduced pain')"
        chk "$page: old first sentence gone" 0 "$(count 'found that none of its conclusions was rated high-certainty')"
        chk "$page: the earlier sentences above the section are untouched (encouraging sentence before the mixed one)" 1 "$(count 'and I adapt the pace and pressure to what suits you.')"
      fi
      sleep 5
    done
    rm -f "$tmp"
    [ "$fail" = 0 ] || { echo "The public page does not show what was applied (cache?); use --rollback if it persists." >&2; exit 1; }
    echo "public check: both research sections show the new text on the public pages" ;;
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

# ref: ./install-sandwich-update.php, ./sandwich-update-content.php — the guarded installer and its generated content, both uploaded; ../sync-server-backups.sh — the off-server copy step
# ref: ../thai/push-thai-evidence.sh — the script this was copied from; ../links/push-thammasat-link.sh — the script this was copied from (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
