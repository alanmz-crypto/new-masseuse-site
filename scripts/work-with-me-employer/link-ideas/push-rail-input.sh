#!/usr/bin/env bash
# Exact in-place Rail practice input update, staging39 only. Never live.
# Every write, including rollback, takes a fresh verified encrypted off-site backup.
# shellcheck disable=SC2016
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
mode=dry; snap=""
case "${1:-}" in
  "") [ "$#" -eq 0 ] || exit 1 ;;
  --apply) [ "$#" -eq 1 ] || exit 1; mode=apply ;;
  --check) [ "$#" -eq 1 ] || exit 1; mode=check ;;
  --rollback) [ "$#" -eq 2 ] || exit 1; mode=rollback; snap=$2 ;;
  *) echo "Usage: $0 [--apply | --check | --rollback FILE]" >&2; exit 1 ;;
esac
target="${S_TARGET:-staging39}"
case "$target" in staging39) ;; *) echo "S_TARGET must be staging39." >&2; exit 1 ;; esac
if [ "$mode" = rollback ] && [[ ! "$snap" =~ ^(\$HOME|/home/[a-zA-Z0-9_-]+)/private-backups/rail-input-before-[a-zA-Z0-9.-]+\.json$ ]]; then
  echo "Snapshot must be a rail-input-before JSON inside private-backups." >&2; exit 1
fi
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent holds no keys." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
echo "Target site: $target   mode: $mode"
echo "[1] Uploading the reviewed package outside every web root ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused." >&2; exit 2; }
tar -C "$here" -cf - rail-input/install-rail-input.php rail-ideas-v1.html rail-ideas.html \
  | "${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-package-rail-input" && tar -xf - -C "$HOME/wwm-package-rail-input"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
phase=$1; target=$2; snap=${3:-}
[ "$target" = staging39 ] || { echo "Remote refused: staging39 only." >&2; exit 1; }
want=staging39.thaisolitude.com
command -v wp >/dev/null || { echo "wp-cli missing" >&2; exit 1; }
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true)
  if [ "$h" = "http://$want" ] || [ "$h" = "https://$want" ] || [ "$h" = "http://$want/" ] || [ "$h" = "https://$want/" ]; then
    [ -z "$doc" ] || { echo "More than one install reports exact staging39; refusing." >&2; exit 1; }; doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No install reports exact staging39; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}"
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
export WWM_BACKUP_DIR="$HOME/private-backups"
package="$HOME/wwm-package-rail-input"
run() { wp --path="$doc" --user=1 eval-file "$package/rail-input/install-rail-input.php" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; review cache before relying on public results)"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    run preflight
    dump="$HOME/private-backups/${target}-before-rail-input-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Empty database export; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)" ;;
  apply) run apply; purge ;;
  rollback) snap=${snap/#\$HOME/$HOME}; run rollback "$snap"; purge ;;
  verify)
    state=$(run preflight); printf '%s\n' "$state"
    case "$state" in
      *'rail-input=applied '*) source="$package/rail-ideas.html" ;;
      *'rail-input=ready '*) source="$package/rail-ideas-v1.html" ;;
      *) echo "Cannot identify exact stored Rail runtime." >&2; exit 1 ;;
    esac
    tmp=$(mktemp); trap 'rm -f "$tmp" "$cfgs"' EXIT
    code=$(curl -s -o "$tmp" -w '%{http_code}' --max-time 40 "https://$want/rail-nav-ideas/" || true)
    [ "$code" = 200 ] || { echo "FAIL /rail-nav-ideas/: HTTP $code; 202 means INVALID access." >&2; exit 1; }
    php -r '
      $source=file_get_contents($argv[1]); $html=file_get_contents($argv[2]);
      foreach (array("script", "style") as $tag) {
        if (!preg_match("#<".$tag.">(.*?)</".$tag.">#s", $source, $m) || substr_count($html, $m[1])!==1) { fwrite(STDERR,"FAIL exact Rail ".$tag." output\n"); exit(1); }
      }
      foreach (array("id=\"tsr-lab\""=>1,"<h1>Side rail ideas</h1>"=>1,"data-idea=\""=>5,"class=\"tsr-sec\""=>6,"id=\"ts-peek-nav-runtime\""=>1,"id=\"ts-peek-nav-config\""=>1) as $needle=>$count) {
        if(substr_count($html,$needle)!==$count){fwrite(STDERR,"FAIL Rail scope: ".$needle."\n");exit(1);}
      }
      if(strpos($html,"LMT-29905")===false){fwrite(STDERR,"FAIL licence missing\n");exit(1);}
      echo "public check: HTTP200, exact stored Rail script/style, five ideas, six sections, shared strip and licence present\n";
    ' "$source" "$tmp" ;;
  *) echo "Unknown remote phase" >&2; exit 1 ;;
esac
EOS
remote() { local quoted; printf -v quoted '%q ' "$@"; "${ssh_cmd[@]}" "bash -s -- $quoted" <<<"$REMOTE"; }
case "$mode" in
  dry) remote preflight "$target"; echo "Dry run only; nothing changed." ;;
  check) remote verify "$target" ;;
  apply)
    echo "[2] Fresh export and preflight:"; remote prepare "$target"
    echo "[3] Verify encrypted off-site backup before the write:"; "$here/../sync-server-backups.sh"
    echo "[4] Apply in place:"; remote apply "$target"
    echo "[5] Verify the new snapshot off-site:"; "$here/../sync-server-backups.sh"
    echo "[6] Origin rendered check:"; remote verify "$target" ;;
  rollback)
    echo "[2] Fresh export before rollback:"; remote prepare "$target"
    "$here/../sync-server-backups.sh"
    echo "[3] Restore the exact page state:"; remote rollback "$target" "$snap"
    remote verify "$target"
    "$here/../sync-server-backups.sh" ;;
esac
