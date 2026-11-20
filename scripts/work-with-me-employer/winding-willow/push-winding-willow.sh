#!/usr/bin/env bash
# Winding Willow page: ADD /winding-willow/ ("Massage at Winding Willow"), exactly as reviewed on staging39, on staging40 (S_TARGET=staging40, the default).
# S_TARGET=staging39 is accepted for the dry run only (proves the package equals the reference copy). Live is NOT a target here: the enable-live
# change (docs/inter-model/2026-11-18-winding-willow-enable-live.patch) adds it on Ryan's go; Ryan merges that pull request and starts the live run himself.
# Ryan, 2026-11-17: "Follow the pattern of corporate/install-corporate-massage.php + push-corporate-massage.sh: allow-list, dry run by default,
# --apply with DB export + off-site copy, --rollback, idempotent re-apply."
#
#   ./push-winding-willow.sh                    dry run: connect, locate the site, upload the package, preflight (read-only on the site)
#   ./push-winding-willow.sh --apply            export the DB -> preflight -> COPY the export off the server and verify it -> apply -> check the public page
#                                               -> copy the new before-snapshot off the server. Stops before applying if the copy cannot be verified.
#   ./push-winding-willow.sh --rollback FILE    delete the page again, from a winding-willow-before-*.json snapshot on the server
#   ./push-winding-willow.sh --check            read-only: the public checks of the --apply run, on a site that already has the page
# What it changes is described at the top of install-winding-willow.php (the page, one menu item after Corporate Massage, the ts-ww CSS block).
# After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target> and prove-restore.sh <target> (GitHub runs both after a write).
# Access: credentials file + ssh-agent. The SSH account also holds live, so every write is conditional on `wp option get home` being exactly the
# chosen site (checked here to pick the folder, and again inside the installer's own allow-list).
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
target="${S_TARGET:-staging40}"
case "$target" in
  staging40) ;;
  staging39) [ "$mode" = dry ] || [ "$mode" = check ] || { echo "staging39 is the reference copy: dry run and --check only." >&2; exit 1; } ;;
  *) echo "S_TARGET must be staging40 (or staging39 for a dry run / --check)." >&2; exit 1 ;;
esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-winding-willow (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-winding-willow.php winding-willow.json winding-willow-page.html \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-winding-willow" && mkdir -p "$HOME/wwm-package-winding-willow" && tar -xf - -C "$HOME/wwm-package-winding-willow"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); see AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; snap=${3:-}
case "$target" in staging40|staging39) want="$target.thaisolitude.com" ;; *) echo "Remote refused: target must be staging40 (or staging39 read-only), got '$target'." >&2; exit 1 ;; esac
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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-winding-willow/install-winding-willow.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-winding-willow-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). Each page is fetched once.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    fetch() { local code; code=$(curl -s -o "$tmp/$1.html" -w '%{http_code}' --max-time 30 "https://$want$2" || true); echo "  $code $2"; [ "$code" = 200 ] || { echo "$2 does not answer 200 (202 would be SiteGround's bot challenge = INVALID, retry later)." >&2; return 1; }; }
    count() { grep -o -F -- "$2" "$tmp/$1.html" | wc -l; }
    need() { [ "$(count "$1" "$2")" = "$3" ] || { echo "CHECK FAILED: $1 should contain '$2' exactly $3 time(s), has $(count "$1" "$2") (cache?)." >&2; return 1; }; }
    atleast() { [ "$(count "$1" "$2")" -ge "$3" ] || { echo "CHECK FAILED: $1 should contain '$2' at least $3 time(s), has $(count "$1" "$2") (cache?)." >&2; return 1; }; }
    fetch ww /winding-willow/
    need ww 'Massage with Sa at Winding Willow Acupuncture &amp; Wellness</h1>' 1
    need ww '<title>Massage at Winding Willow | Butsaba Dusrai, Oregon LMT' 1
    atleast ww 'content="Thai, deep tissue and Swedish massage with Butsaba' 1
    atleast ww 'LMT-29905' 1
    need ww 'What the Research Says About Massage Alongside Other Care' 1
    atleast ww 'href="https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt"' 2
    for pmid in 21299184 28690703 36645811 23427384 40954653 42142427 31987237; do atleast ww "https://pubmed.ncbi.nlm.nih.gov/$pmid/" 1; done
    need ww 'staging39.thaisolitude.com' 0   # a link to the reference copy; the site-wide CSS has a harmless '/* staging39: ...' comment (live too)
    [ "$target" != live ] || need ww 'noindex' 0   # staging keeps its own no-crawl setting; on live the page must be indexable
    need ww 'Winding Willow is a separate, independent business that employs me' 1   # Ryan 2026-11-20: the top of the page says why Winding Willow is there
    need ww 'collective of independent practitioners' 0
    atleast ww '@media (max-width: 1255px)' 1   # the ts-ww CSS block (menu item hidden below 1256 px except in the phone menu)
    # Ryan 2026-11-20: the menu item "Winding Willow" is the ONE link to the page (header, phone menu, footer = 3 copies); only the winding-willow page links to their site.
    for pg in services contact about; do fetch "$pg" "/$pg/"; need "$pg" "href=\"https://$want/winding-willow/\" class=\"menu-link\">Winding Willow</a>" 3; need "$pg" '/winding-willow/' 3; need "$pg" 'windingwillowacupuncture.com' 0; done
    echo "public check: the page answers 200 with its opening sentence, H1, title, description, licence line, research heading, booking links and 7 PubMed sources; Services, Contact and About carry only the menu link to it and no link to Winding Willow" ;;
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
    echo "[4b] Does the public page show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot off the server:"; "$here/../sync-server-backups.sh" ;;
  rollback) echo "[2] Rolling back:"; remote rollback "$target" "$snap" ;;
esac

# ref: ./install-winding-willow.php — the guarded installer this runs on the server; ../sync-server-backups.sh — the off-server copy step
# ref: ../corporate/push-corporate-massage.sh — the script this follows (same connection/guard pattern); ../../../remote_execution.txt — gitignored access details (never printed)
