#!/usr/bin/env bash
# Corporate & Group Massage: ADD a new page, a Services row, a menu item, an announcement strip and its CSS, on staging37 or live (S_TARGET=staging37|live; default staging37). Live was enabled on Ryan's go ("This looks good, it can be pushed to live", 2026-10-24); Ryan merges the pull request and starts the live run himself.
# Ryan, 2026-10-24: the three briefs in docs/inter-model/ ("Corporate & Group Massage"), then "Use staging37. Use staging first." and "don't change anything already established ... all you need to do is add."
#
#   ./push-corporate-massage.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-corporate-massage.sh --apply            export the DB -> preflight -> COPY the export to this computer and verify it -> apply -> purge cache -> check the public pages
#                                                -> copy the new before-snapshot to this computer. Stops before applying if the copy cannot be verified.
#   ./push-corporate-massage.sh --rollback FILE    take everything out again, from a corporate-massage-before-*.json snapshot on the server
#   ./push-corporate-massage.sh --check            read-only: the public checks of the --apply run, on a site that already has the change
# What it changes is described at the top of install-corporate-massage.php (everything is ADDED; no existing text, block, setting or CSS rule is edited).
# The backup is part of the apply and cannot be skipped. After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target>.
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
  --check) mode=check ;;
  --rollback) mode=rollback; snap="${2:-}"; [ -n "$snap" ] || { echo "--rollback needs the snapshot file path on the server" >&2; exit 1; } ;;
  *) echo "Usage: $0 [--apply | --check | --rollback FILE]" >&2; exit 1 ;;
esac
target="${S_TARGET:-staging37}"
case "$target" in staging37|live) ;; *) echo "S_TARGET must be staging37 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-corporate-massage (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-corporate-massage.php corporate-massage-content.php corporate-massage.css images/corporate-group-massage.webp \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-corporate-massage" && mkdir -p "$HOME/wwm-package-corporate-massage" && tar -xf - -C "$HOME/wwm-package-corporate-massage"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging37) want="staging37.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging37 or live, got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-corporate-massage/install-corporate-massage.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-corporate-massage-$(date +%Y%m%d-%H%M%S).sql"
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
    band='<div class="ts-corp-band">'   # the exact markup (the class names also appear once in the page's inline CSS, so they cannot be counted)
    fetch corp /corporate-massage-portland/
    need corp '>Corporate &amp; Group Massage in Portland</h1>' 1
    need corp 'mailto:butsabad@gmail.com?subject=Corporate%20massage%20quote%20request&amp;body=' 2
    atleast corp 'LMT-29905' 1
    need corp "$band" 0
    need corp '<title>Corporate Chair Massage in Portland, OR | Thai Solitude' 1
    atleast corp 'content="On-site chair massage for employee appreciation days and workplace events in Portland, with table massage at suitable host venues. Request a quote."' 1
    for pg in services about contact privacy-policy accessibility thai-massage therapeutic-massage deep-tissue-massage; do
      fetch "$pg" "/$pg/"
      need "$pg" "$band" 1
      need "$pg" 'class="ts-corp-band__link" href="https://'"$want"'/corporate-massage-portland/">Explore Corporate Massage</a>' 1
    done
    need services 'href="/corporate-massage-portland/">Corporate &amp; Group Massage</a>' 1
    need services 'class="wp-block-image size-full ts-corp-pic"' 1
    pic=$(grep -o 'src="/wp-content/uploads/[^"]*corporate-group-massage[^"]*"' "$tmp/services.html" | head -1 | sed 's/^src="//; s/"$//')
    picinfo=$(curl -s -o /dev/null -w '%{http_code} %{content_type}' --max-time 30 "https://$want$pic" || true); echo "  $picinfo $pic (the picture)"
    [ "$picinfo" = "200 image/webp" ] || { echo "CHECK FAILED: the picture does not answer 200 image/webp ($picinfo); use --rollback if it persists." >&2; return 1; }
    for existing in thai-massage therapeutic-massage deep-tissue-massage; do atleast services "href=\"https://$want/$existing/\"" 1; done
    fetch home /
    need home "$band" 0
    need home '>Corporate Massage</a>' 3
    fetch wwm /work-with-me/
    need wwm "$band" 0
    for extra in personal-automation privacy terms; do   # unrelated pages on this install: they may redirect (staging37: /privacy/ answers 301); only an answering page is checked for a strip
      if wp --path="$doc" post list --post_type=page --name="$extra" --field=ID 2>/dev/null | grep -q .; then
        code=$(curl -s -o "$tmp/x-$extra.html" -w '%{http_code}' --max-time 30 "https://$want/$extra/" || true); echo "  $code /$extra/ (unrelated page)"
        [ "$code" != 200 ] || need "x-$extra" "$band" 0
      fi
    done
    echo "public check: the page, the Services row and its picture, the menu link (header, phone menu, footer), the strip on the 8 client pages, and no strip on Home, Work With Me, the new page itself or the unrelated pages" ;;
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
    echo "[4b] Do the public pages show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot to this computer:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-corporate-massage.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../deep-tissue/push-deep-tissue-copy.sh — the script this was copied from (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
