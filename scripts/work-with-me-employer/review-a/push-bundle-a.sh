#!/usr/bin/env bash
# Bundle A on staging38 or live after Ryan's release go. Dry by default; writes take
# a fresh DB export and verified encrypted off-site copy before writing.
# See install-bundle-a.php and README.md for exact scope and rollback.
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
target="${S_TARGET:-staging38}"
case "$target" in staging38|live) ;; *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-bundle-a (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-bundle-a.php bundle-a-content.php \
  | "${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-package-bundle-a" && tar -xf - -C "$HOME/wwm-package-bundle-a"'

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
run() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-bundle-a/install-bundle-a.php" "$m" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-bundle-a-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) run apply; purge ;;
  verify)
    fail=0
    tmp=$(mktemp); trap 'rm -f "$cfgs" "$tmp"' EXIT
    has() { grep -Fq -- "$1" "$tmp" || { echo "FAIL $slug: missing $1" >&2; fail=1; }; }
    for slug in home about services thai-massage therapeutic-massage deep-tissue-massage chair-massage corporate-massage-portland contact; do
      path="/$slug/"; [ "$slug" != home ] || path=/
      code=$(curl -s -o "$tmp" -w '%{http_code}' --max-time 40 "https://$want$path" || true)
      [ "$code" = 200 ] || { echo "FAIL $slug: HTTP $code; 202 is INVALID (bot challenge)" >&2; exit 1; }
      has LMT-29905
      case "$slug" in
        home) has 'href="/therapeutic-massage/"'; has 'href="/deep-tissue-massage/"'; has 'href="/thai-massage/"'; has 'href="/about/"'; has 'href="/contact/"' ;;
        about) has 'ts-review-next'; has 'ts-review-license' ;;
        services) has 'ts-review-next'; has 'Right now I offer corporate and group massage only'; has 'Corporate &amp; group' ;;
        thai-massage|therapeutic-massage|deep-tissue-massage|chair-massage) has 'ts-review-next'; has 'ts-review-other'; has 'Request a corporate massage quote' ;;
        corporate-massage-portland) has 'ts-corp__sticky'; has 'Read more about'; has 'href="/chair-massage/"' ;;
        contact) has 'ts-review-contact'; has 'id="corporate-quote"'; has 'Corporate quote'; has 'Hiring a massage therapist?' ;;
      esac
      echo "public check: $slug HTTP 200; expected Bundle A content checked"
    done
    [ "$fail" = 0 ] || exit 1 ;;
  rollback) snap=${snap/#\$HOME/$HOME}; run rollback "$snap"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[2] Preflight (read-only):"; remote preflight "$target"; echo "Dry run only. Re-run with --apply to write." ;;
  apply)
    echo "[2] Backup on the server + preflight:"; remote prepare "$target"
    echo "[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):"; "$here/../sync-server-backups.sh"
    echo "[4] Applying:"; remote apply "$target"
    echo "[4b] Does the public page show the change?"; remote verify "$target"
    echo "[5] Copying the new before-snapshot off the server:"; "$here/../sync-server-backups.sh" ;;
  rollback)
    echo "[2] Fresh backup before rollback:"; remote prepare "$target"
    "$here/../sync-server-backups.sh"
    echo "[3] Rolling back:"; remote rollback "$target" "$snap"
    remote preflight "$target"
    "$here/../sync-server-backups.sh" ;;
esac

# ref: ./install-bundle-a.php; ../contact-address/push-contact-address.sh (connection/export pattern)
