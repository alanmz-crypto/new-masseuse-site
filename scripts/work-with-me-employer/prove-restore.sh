#!/usr/bin/env bash
# Prove that a DB export really restores: import the newest local copy of a site's export into a THROWAWAY database on this computer,
# run identical fingerprint queries against the restored copy and against the real site (read-only), and compare.
#
#   ./prove-restore.sh <target> [export.sql]       target: live staging24 staging25 staging26 staging27 staging30 staging31 staging32 staging33 staging34 staging36 staging37 staging38 staging39
#
# Uses the off-server copy made by sync-server-backups.sh (run that first). Compares: exact row counts of every table, a hash of every
# published page (the site's own address normalised), the Additional CSS, every SureRank SEO field, and the key settings.
# Differences are EXPECTED only for things changed after the export was taken. It refuses to say anything unless both sides returned data.
# Changes nothing on any server, and removes its throwaway container (and its data) when it finishes.
# shellcheck disable=SC2016  # the backticks in the sed/grep patterns below are literal SQL identifier quotes, not command substitution
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
root=$(cd "$here/../.." && pwd)
creds="${S26_CREDS:-$root/remote_execution.txt}"
dest="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
target="${1:-}"
case "$target" in
  live) want="thaisolitude.com" ;;
  staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38|staging39) want="$target.thaisolitude.com" ;;
  *) echo "Usage: $0 <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38|staging39> [export.sql]" >&2; exit 1 ;;
esac
sql="${2:-$(find "$dest" -maxdepth 1 -name "${target}-before-*.sql" -printf '%T@ %p\n' 2>/dev/null | sort -rn | head -1 | cut -d' ' -f2-)}"
[ -n "$sql" ] && [ -s "$sql" ] || { echo "No export for $target in $dest (run sync-server-backups.sh first)." >&2; exit 1; }
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"

work="${XDG_CACHE_HOME:-$HOME/.cache}/ts-restore-proof"; mkdir -p "$work"
img=$(sed -n 's/^[[:space:]]*image:[[:space:]]*\(mariadb[^[:space:]]*\).*/\1/p' "$root/docker-compose.yml" | head -1)
c="ts-restore-proof-$$"
trap 'docker rm -vf "$c" >/dev/null 2>&1' EXIT
echo "Target: $target ($want)    export: $(basename "$sql")    modified $(date -r "$sql" '+%Y-%m-%d %H:%M:%S')"
docker run -d --name "$c" -e MARIADB_ROOT_PASSWORD=proofpw -e MARIADB_DATABASE=proof "$img" >/dev/null || { echo "TEST INVALID: could not start the throwaway database"; exit 2; }
ready=0
for _ in $(seq 1 90); do
  if docker logs "$c" 2>&1 | grep -q 'init process done' && docker exec "$c" mariadb -uroot -pproofpw -e 'select 1' >/dev/null 2>&1; then
    sleep 2; docker exec "$c" mariadb -uroot -pproofpw -e 'select 1' >/dev/null 2>&1 && { ready=1; break; }
  fi
  sleep 1
done
[ "$ready" -eq 1 ] || { echo "TEST INVALID: the throwaway database never became ready"; exit 2; }

sed 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' "$sql" | docker exec -i "$c" mariadb -uroot -pproofpw --default-character-set=utf8mb4 proof
import_rc=${PIPESTATUS[1]}; echo "import exit code: $import_rc"

p=$(sed -n 's/^CREATE TABLE `\(.*\)commentmeta`.*/\1/p' "$sql" | head -1)
tables=$(grep -oE '^CREATE TABLE `[^`]+`' "$sql" | sed 's/CREATE TABLE //; s/`//g')
q="$work/check-$target.sql"
{
  echo "SET NAMES utf8mb4;"
  for t in $tables; do echo "SELECT 'rows', '$t', COUNT(*) FROM \`$t\`;"; done
  echo "SELECT 'page', post_name, SHA2(REPLACE(REPLACE(post_content,'https://$want','HOST'),'http://$want','HOST'),256) FROM \`${p}posts\` WHERE post_type='page' AND post_status='publish' ORDER BY post_name;"
  echo "SELECT 'css', ID, SHA2(post_content,256) FROM \`${p}posts\` WHERE post_type='custom_css' AND post_status='publish' ORDER BY ID;"
  echo "SELECT 'snippet', ID, CONCAT(post_status, ':', SHA2(post_content,256)) FROM \`${p}posts\` WHERE post_type='wpcode' ORDER BY ID;"
  echo "SELECT 'seo', CONCAT(post_id,':',meta_key), SHA2(meta_value,256) FROM \`${p}postmeta\` WHERE meta_key LIKE 'surerank_settings_%' ORDER BY post_id, meta_key;"
  echo "SELECT 'option', option_name, SHA2(option_value,256) FROM \`${p}options\` WHERE option_name IN ('astra-settings','widget_block','nav_menu_locations','blogname','blogdescription','siteurl','home','active_plugins','template','stylesheet','permalink_structure','wpvivid_schedule_setting','surerank_settings','wpcode_snippets') ORDER BY option_name;"
} > "$q"
docker exec -i "$c" mariadb -uroot -pproofpw --default-character-set=utf8mb4 -N -B proof < "$q" > "$work/restored-$target.txt"
ssh -p "$port" -o BatchMode=yes -o ConnectTimeout=20 "$user@$host" "wp --path=\"\$HOME/www/$want/public_html\" --skip-plugins --skip-themes db query --skip-column-names" < "$q" 2>"$work/live-$target.err" | grep -v '^$' > "$work/real-$target.txt"

nr=$(wc -l < "$work/restored-$target.txt"); nl=$(wc -l < "$work/real-$target.txt")
echo "fingerprints: restored copy $nr, real site $nl"
if [ "$import_rc" -ne 0 ] || [ "$nr" -lt 40 ] || [ "$nl" -lt 40 ]; then echo "TEST INVALID: import exit code $import_rc, restored rows $nr, real-site rows $nl (expected at least 40 each). No conclusion can be drawn."; head -3 "$work/live-$target.err"; exit 2; fi
echo "compared: $(cut -f1 "$work/real-$target.txt" | sort | uniq -c | awk '{printf "%s %s, ", $2, $1}' | sed 's/, $//')"
if diff -q "$work/restored-$target.txt" "$work/real-$target.txt" >/dev/null; then
  echo "IDENTICAL: the export restores to exactly what $want holds now."
else
  echo "DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:"
  diff "$work/restored-$target.txt" "$work/real-$target.txt" | grep '^[<>]' | cut -f1-2 | cut -c1-120
  exit 3
fi

# ref: ./sync-server-backups.sh — makes the local copy of the exports this restores; ./secure-backups.sh, ./push-human-facing-staging.sh — what writes them
# ref: ../../docker-compose.yml — source of the pinned MariaDB image
