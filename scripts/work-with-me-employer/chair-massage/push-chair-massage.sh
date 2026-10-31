#!/usr/bin/env bash
# Chair Massage: ADD the new service page, its one picture, a Services row and 'chair-massage' in the strip and jump-links snippet lists, on staging38 or live (S_TARGET=staging38|live; default staging38).
# Live was enabled on Ryan's go ("Go live", 2026-10-31) after version 2 was rehearsed, applied on staging38 and checked; Ryan merges the pull request and starts the live run himself.
# Ryan, 2026-10-30 (cloud session): "recreate those pages ... this time the service is chair massage ... the experience should feel seamless"; "your original attempt at work begins on staging38".
#
#   ./push-chair-massage.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-chair-massage.sh --apply            export the DB -> preflight -> COPY the export off the server and verify it -> apply -> purge cache -> check the public pages -> copy the new before-snapshot
#   ./push-chair-massage.sh --rollback FILE    take everything out again, from a chair-massage-before-*.json snapshot on the server
#   ./push-chair-massage.sh --check            read-only: the public checks of the --apply run, on a site that already has the change
# What it changes is described at the top of install-chair-massage.php (everything is ADDED; the two snippet edits are one-line insertions by exact replacement).
# The backup is part of the apply and cannot be skipped. After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target>.
# Access: credentials file + ssh-agent. The SSH account also holds live, so every write is conditional on `wp option get home` being exactly the chosen site
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
target="${S_TARGET:-staging38}"
case "$target" in staging38|live) ;; *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-chair-massage (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-chair-massage.php chair-massage-content.php images/chair-massage-tree.webp \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-chair-massage" && mkdir -p "$HOME/wwm-package-chair-massage" && tar -xf - -C "$HOME/wwm-package-chair-massage"'

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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-chair-massage/install-chair-massage.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-chair-massage-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). Every page is fetched once.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    fetch() { local code; code=$(curl -s -o "$tmp/$1.html" -w '%{http_code}' --max-time 30 "https://$want$2" || true); echo "  $code $2"; [ "$code" = 200 ] || { echo "$2 does not answer 200 (202 would be SiteGround's bot challenge = INVALID, retry later); use --rollback if it persists." >&2; return 1; }; }
    count() { grep -o -F -- "$2" "$tmp/$1.html" | wc -l; }
    need() { [ "$(count "$1" "$2")" = "$3" ] || { echo "CHECK FAILED: $1 should contain '$2' exactly $3 time(s), has $(count "$1" "$2") (cache?); use --rollback if it persists." >&2; return 1; }; }
    atleast() { [ "$(count "$1" "$2")" -ge "$3" ] || { echo "CHECK FAILED: $1 should contain '$2' at least $3 time(s), has $(count "$1" "$2") (cache?); use --rollback if it persists." >&2; return 1; }; }
    band='<div class="ts-corp-band">'
    fetch chair /chair-massage/
    need chair '>Chair Massage in Portland</h1>' 1
    need chair '<title>Chair Massage | Butsaba Dusrai, Oregon LMT' 1
    atleast chair 'content="Chair massage in Portland with Butsaba Dusrai, LMT: short, seated, fully clothed sessions for workplaces and events, with pressure adjusted as you go."' 1
    need chair "$band" 1                                                   # the strip, as on the other service pages
    need chair 'class="ts-jump"' 1                                         # the jump-links row (the snippet writes class first, then aria-label)
    for a in ts-what-it-is ts-common-reasons ts-who-can-benefit ts-what-to-expect; do need chair "href=\"#$a\"" 1; need chair "id=\"$a\"" 1; done
    need chair '>Questions About Chair Massage</h2>' 1
    need chair '>What the Research Says About Chair Massage</h2>' 1
    need chair '<cite class="ts-source">' 32
    need chair 'class="uagb-question"' 11
    atleast chair 'LMT-29905' 1
    need chair 'chair-massage-tree.webp" alt=' 1                             # the one picture, once (the hero); its srcset/src repeat the URL but only one img tag
    need chair 'corporate-group-massage.webp' 0
    pic=$(grep -o 'src="/wp-content/uploads/[^"]*chair-massage-tree\.webp"' "$tmp/chair.html" | head -1 | sed 's/^src="//; s/"$//')
    picinfo=$(curl -s -o /dev/null -w '%{http_code} %{content_type}' --max-time 30 "https://$want$pic" || true); echo "  $picinfo $pic (the picture)"
    [ "$picinfo" = "200 image/webp" ] || { echo "CHECK FAILED: the picture does not answer 200 image/webp ($picinfo); use --rollback if it persists." >&2; return 1; }
    fetch services /services/
    need services 'href="/chair-massage/">Chair Massage</a>' 1
    need services 'chair-massage-tree.webp" alt=' 1                          # the same picture, once, on the Services row
    need services 'href="/corporate-massage-portland/">Corporate &amp; Group Massage</a>' 1
    for existing in thai-massage therapeutic-massage deep-tissue-massage; do atleast services "href=\"https://$want/$existing/\"" 1; done
    for pg in therapeutic-massage deep-tissue-massage thai-massage about; do   # the strip and the jump links still show where they did
      fetch "$pg" "/$pg/"; need "$pg" "$band" 1
      [ "$pg" = about ] || need "$pg" 'class="ts-jump"' 1
    done
    fetch corp /corporate-massage-portland/
    need corp "$band" 0                                                    # the corporate page keeps no strip
    echo "public check: the page (title, description, strip, jump links, FAQ and research headings, 32 sources, 11 questions, the one picture as the hero), the Services row with the same picture, and the strip/jump links unchanged elsewhere" ;;
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

# ref: ./install-chair-massage.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../corporate/push-corporate-massage.sh — the script this was copied from (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
