#!/usr/bin/env bash
# Rehearse an installer on a THROWAWAY copy of a SiteGround site before it touches staging: the routine of the localhost:8090 rehearsals on
# Ryan's computer, for machines that have no practice stack (a GitHub runner; a cloud session asks Actions to run this).
#
#   rehearse.sh SOURCE INSTALLER [--with-uploads]
#     SOURCE     the site to copy: staging36 (the working copy) or any stagingNN; live is allowed too (reading it changes nothing)
#     INSTALLER  under scripts/work-with-me-employer, e.g. thai/install-thai-evidence.php: an installer with preflight / apply / rollback FILE
#                modes whose allow-list includes localhost:8090 (every reviewed installer here does)
#
# Nothing is written on the server: its database and files are streamed over SSH (uploads, caches and backups are left out unless
# --with-uploads). This machine then runs the practice stack's pinned images on http://localhost:8090, imports the copy, rewrites its address,
# and checks, as the manual rehearsals did:
#   fingerprint A -> preflight -> apply -> fingerprint B -> apply again (must change nothing) -> rollback with the snapshot apply wrote
#   -> fingerprint C (must equal A) -> apply once more (must equal B) -> the 10 pages answer 200 -> no PHP error the copy did not already log
# and prints which fingerprints the change moves (A vs B): exactly what it will touch on staging. Fingerprints = prove-restore.sh's (every
# published page, the Additional CSS, every WPCode snippet, every SureRank row, the key options; row counts are shown but not compared,
# because WordPress adds a revision on every save).
# Exit 0 = every check passed; 1 = a check failed (the output says which); 2 = could not set up (INVALID: nothing was concluded).
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server or in the container, not here
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
root=$(cd "$here/../.." && pwd)
wwm="$root/scripts/work-with-me-employer"
creds="${S26_CREDS:-$root/remote_execution.txt}"
src="${1:-}"; inst="${2:-}"; uploads=0
[ "${3:-}" = --with-uploads ] && uploads=1
invalid() { echo "REHEARSAL INVALID: $*" >&2; exit 2; }
release_rehearsal=0
read_only_discovery=0; copy_isolation=0
# This new in-place core lane is authorized only from the actual staging39 copy.
# It cannot select the separate live-sourced first-install release rehearsal.
if [ "$inst" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then
  [ "$src" = staging39 ] && [ "$uploads" = 1 ] || invalid "core-neighbours requires a fresh staging39-sourced copy with uploads"
  read_only_discovery=1; copy_isolation=1
fi
if [ "$inst" = link-ideas/peek-release/install-peek-release.php ]; then
  release_rehearsal=1; read_only_discovery=1; copy_isolation=1
  [ "$src" = live ] && [ "$uploads" = 1 ] || invalid "visitor release requires a fresh live-sourced copy with uploads"
fi
case "$src" in
  live) want="thaisolitude.com" ;;
  staging[0-9]|staging[0-9][0-9]) want="$src.thaisolitude.com" ;;
  *) invalid "usage: $0 <stagingNN|live> <path/to/install-NAME.php> [--with-uploads]" ;;
esac
[[ "$inst" =~ ^([a-z0-9_-]+/)*install-[a-z0-9-]+\.php$ ]] && [ -f "$wwm/$inst" ] || invalid "no installer scripts/work-with-me-employer/$inst"
grep -q "'localhost:8090'" "$wwm/$inst" || invalid "$inst has no localhost:8090 in its allow-list, so it cannot be rehearsed"
grep -Eq "'preflight', *'apply', *'rollback'" "$wwm/$inst" || invalid "$inst does not take the preflight / apply / rollback FILE modes"
seam="${TS_REHEARSAL_FROM_DIR:-}"   # TESTING ONLY: info.txt, db.sql.gz and files.tar in this folder stand in for the SSH reads (see the self-test in ops/README.md)
if [ -z "$seam" ]; then
  [ -f "$creds" ] || invalid "missing credentials file $creds"
  [ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || invalid "no ssh-agent socket (SSH_AUTH_SOCK)"
fi
command -v docker >/dev/null || invalid "docker is required"
if curl -s -o /dev/null -m 3 http://localhost:8090/; then invalid "something already answers on localhost:8090 (the practice stack?); rehearse on a machine without it"; fi
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
[ -n "$seam" ] || { host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"; }

img() { sed -n "s/^[[:space:]]*image:[[:space:]]*\($1[^[:space:]]*\).*/\1/p" "$root/docker-compose.yml" | head -1; }
db_img=$(img mariadb); wp_img=$(img 'wordpress:php'); cli_img=$(img 'wordpress:cli')
[ -n "$db_img" ] && [ -n "$wp_img" ] && [ -n "$cli_img" ] || invalid "could not read the pinned images from docker-compose.yml"

work="${RUNNER_TEMP:-$HOME/.cache}/ts-rehearsal-$$"   # never /tmp on Ryan's computer (its tmpfs runs out of inodes)
pub="$work/public_html"; bk="$work/backups"; out="$work/out"
mkdir -p "$pub" "$bk" "$out"; chmod 777 "$bk"
project="ts-rehearsal-$$"
release_relay_pid=""
dc() { docker compose --progress quiet -p "$project" -f "$work/compose.yml" "$@"; }
cleanup() { # files the containers wrote belong to their user, so remove those from inside a container, then the emptied folders
  if [ -n "${release_relay_pid:-}" ]; then
    kill "$release_relay_pid" 2>/dev/null || true
    wait "$release_relay_pid" 2>/dev/null || true
    release_relay_pid=""
  fi
  dc down -v --remove-orphans >/dev/null 2>&1
  case "$work" in */ts-rehearsal-[0-9]*) ;; *) echo "cleanup: refusing to remove '$work' (not this run's work folder)" >&2; return ;; esac
  rm -rf "$work" 2>/dev/null || { docker run --rm --user 0 --entrypoint sh -v "$work:/w" "$cli_img" -c 'rm -rf /w/public_html /w/backups /w/out /w/db.sql.gz /w/compose.yml /w/fp.sql' >/dev/null 2>&1; rm -rf "$work" 2>/dev/null; }
}
trap cleanup EXIT

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
want=$1; part=$2; uploads=${3:-0}; read_only_discovery=${4:-0}
# Reviewed raw source reads do not bootstrap WordPress or any MU plugins.
# config/core are before_wp_load and db query/export are before wp_load.
if [ "$read_only_discovery" = 1 ]; then
  wp() { command wp --skip-plugins --skip-themes --exec='ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' "$@"; }
fi
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT   # a file, not < <(...): the SiteGround shell has no /dev/fd for process substitution
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  if [ "$read_only_discovery" = 1 ]; then
    candidate_prefix=$(wp --path="$d" config get table_prefix --type=variable 2>/dev/null) || continue
    [[ "$candidate_prefix" =~ ^[A-Za-z0-9_]+$ ]] || continue
    h=$(wp --path="$d" db query "SELECT option_value FROM ${candidate_prefix}options WHERE option_name='home';" --skip-column-names --batch --raw 2>/dev/null) || continue
  else
    h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true)
  fi
  if [ "$read_only_discovery" = 1 ]; then
    # Only exact origins qualify; stripping prefixes would also accept malformed
    # or scheme-less values such as http://https://staging39.thaisolitude.com.
    case "$h" in "http://$want"|"https://$want"|"http://$want/"|"https://$want/") ;; *) continue ;; esac
  else
    hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
    [ "$hh" = "$want" ] || continue
  fi
  [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want." >&2; exit 1; }
case "$part" in
  info)
    if [ "$read_only_discovery" = 1 ]; then
      source_prefix=$(wp --path="$doc" config get table_prefix --type=variable)
      [[ "$source_prefix" =~ ^[A-Za-z0-9_]+$ ]] || exit 1
      source_home=$(wp --path="$doc" db query "SELECT option_value FROM ${source_prefix}options WHERE option_name='home';" --skip-column-names --batch --raw)
      printf 'folder=%s\nprefix=%s\nversion=%s\nhome=%s\n' "${doc#"$HOME"/}" "$source_prefix" "$(wp --path="$doc" core version)" "$source_home"
    else
      printf 'folder=%s\nprefix=%s\nversion=%s\nhome=%s\n' "${doc#"$HOME"/}" "$(wp --path="$doc" --skip-plugins --skip-themes db prefix)" "$(wp --path="$doc" --skip-plugins --skip-themes core version)" "$(wp --path="$doc" --skip-plugins --skip-themes option get home)"
    fi ;;
  db) wp --path="$doc" --skip-plugins --skip-themes db export - | gzip -c ;;
  files)
    ex=(--exclude=./wp-config.php --exclude=./.htaccess --exclude=./php_errorlog --exclude='*.log' --exclude='*.sql' --exclude='*.sql.gz' --exclude='*.zip' --exclude='*.tar.gz'
        --exclude=./wp-content/cache --exclude=./wp-content/upgrade --exclude='./wp-content/wpvivid*' --exclude=./wp-content/backups --exclude=./wp-content/upgrade-temp-backup --exclude=./wp-content/debug.log)
    [ "$uploads" = 1 ] || ex+=(--exclude=./wp-content/uploads)
    tar -C "$doc" -cf - "${ex[@]}" . ;;
esac
EOS
remote() { ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$want" "$1" "${2:-0}" "$read_only_discovery" <<<"$REMOTE"; }
if [ -n "$seam" ]; then
  remote() { case "$1" in info) cat "$seam/info.txt" ;; db) cat "$seam/db.sql.gz" ;; files) cat "$seam/files.tar" ;; esac; }
  echo "TEST MODE: the source is read from $seam, not from SiteGround"
fi

echo "Rehearsal of $inst on a throwaway copy of $src ($want), at http://localhost:8090 on this machine"
info=$(remote info) || invalid "could not read $want over SSH"
prefix=$(sed -n 's/^prefix=//p' <<<"$info"); home=$(sed -n 's/^home=//p' <<<"$info")
echo "$info" | sed 's/^/  source /'
[[ "$prefix" =~ ^[A-Za-z0-9_]+$ ]] || invalid "unexpected table prefix '$prefix'"

echo "[1] streaming the database and the files (read-only on the server) ..."
remote db > "$work/db.sql.gz" || invalid "database export failed"
[ "$(gzip -dc "$work/db.sql.gz" 2>/dev/null | head -c 4096 | wc -c)" -gt 1000 ] || invalid "the database export is empty"
remote files "$uploads" | tar -C "$pub" -xf - || invalid "file copy failed"
[ -f "$pub/wp-includes/version.php" ] || invalid "the file copy has no WordPress core"
chmod -R a+rwX "$pub"
echo "  database $(stat -c%s "$work/db.sql.gz") bytes gzipped, files $(du -sh "$pub" | cut -f1)"
# The server's .htaccess (SiteGround's rules) is not copied; WordPress's own rewrite block is what the pretty page addresses need.
cat > "$pub/.htaccess" <<'HT'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HT

cat > "$work/compose.yml" <<YML
services:
  db:
    image: $db_img
    environment: { MARIADB_ROOT_PASSWORD: rehearsal, MARIADB_DATABASE: wp, MARIADB_USER: wp, MARIADB_PASSWORD: wp }
  wordpress:
    image: $wp_img
    depends_on: [db]
    ports: ["127.0.0.1:8090:80"]
    volumes: ["$pub:/var/www/html"]
  wpcli:
    image: $cli_img
    depends_on: [db]
    volumes: ["$pub:/var/www/html", "$wwm:/wwm:ro", "$bk:/backups"]
    environment: { WWM_BACKUP_DIR: /backups }
YML
if [ "${copy_isolation:-$release_rehearsal}" = 1 ]; then
  # Copied plugin settings may contain external integrations. Isolate every copy
  # service before first boot. Internal networks do not reliably publish ports;
  # an owned host relay below provides the loopback browser endpoint instead.
  sed -i '/^    ports: \["127.0.0.1:8090:80"\]$/d' "$work/compose.yml"
  cat >> "$work/compose.yml" <<'YML'
networks:
  default:
    internal: true
YML
fi

echo "[2] starting the pinned practice images and importing ..."
dc up -d db >/dev/null 2>&1 || invalid "could not start the database container"
ready=0
for _ in $(seq 1 90); do
  if dc exec -T db mariadb -uroot -prehearsal -e 'select 1' >/dev/null 2>&1; then sleep 2; dc exec -T db mariadb -uroot -prehearsal -e 'select 1' >/dev/null 2>&1 && { ready=1; break; }; fi
  sleep 1
done
[ "$ready" = 1 ] || invalid "the database never became ready"
gzip -dc "$work/db.sql.gz" | sed 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' | dc exec -T db mariadb -uroot -prehearsal --default-character-set=utf8mb4 wp \
  || invalid "the database import failed"
# --entrypoint wp: the image's own entrypoint guesses whether to put "wp" in front (it runs `wp help "$1"`), and with a real site in place
# it guessed wrong on a GitHub runner ("'wp' is not a registered wp command"), so name WP-CLI directly.
wpc() { dc run --rm -T --no-deps --entrypoint wp wpcli --skip-plugins=sg-security "$@"; }   # SiteGround Security + PHP 8.5 would make every exit status 0 (AGENTS.md)
wpdb() { dc run --rm -T --no-deps --entrypoint wp wpcli --skip-plugins --skip-themes "$@"; }   # database-only commands: load no plugin or theme
dc run --rm -T --no-deps --entrypoint wp wpcli config create --dbname=wp --dbuser=wp --dbpass=wp --dbhost=db --dbprefix="$prefix" --skip-check --force --extra-php >/dev/null <<'PHP' || invalid "wp config create failed"
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'DISABLE_WP_CRON', true );
define( 'FS_METHOD', 'direct' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', '/var/www/html/wp-content/rehearsal-debug.log' );
PHP
h=${home#http://}; h=${h#https://}; h=${h%/}
for from in "https://$h" "http://$h" "https:\\/\\/$h" "http:\\/\\/$h"; do
  to="http://localhost:8090"; case "$from" in *'\/'*) to='http:\/\/localhost:8090' ;; esac
  wpdb search-replace "$from" "$to" --all-tables --precise --skip-columns=guid --quiet || invalid "search-replace $from failed"
done
wpdb option update sg_security_disable_activity_log 1 >/dev/null
wpc eval 'if ( function_exists( "wpcode" ) ) { wpcode()->cache->cache_all_loaded_snippets(); }' >/dev/null 2>&1 || true
[ "$(wpdb option get home 2>/dev/null)" = "http://localhost:8090" ] || invalid "the copy's home is not http://localhost:8090 after the rewrite"
dc up -d wordpress >/dev/null 2>&1 || invalid "could not start the web container"
if [ "${copy_isolation:-$release_rehearsal}" = 1 ]; then
  release_container=$(dc ps -q wordpress) || invalid "isolated WordPress container lookup failed"
  [[ "$release_container" =~ ^[a-f0-9]{64}$ ]] || invalid "isolated WordPress container identity is not unique"
  release_network=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.NetworkID}}{{end}}' "$release_container") || invalid "isolated WordPress network lookup failed"
  [[ "$release_network" =~ ^[a-f0-9]{64}$ ]] || invalid "isolated WordPress network identity is not unique"
  release_internal=$(docker network inspect --format '{{.Internal}}' "$release_network") || invalid "isolated WordPress network inspection failed"
  [ "$release_internal" = true ] || invalid "copied WordPress network is not internal"
  release_backend=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$release_container") || invalid "isolated WordPress address lookup failed"
  python3 -I -B "$here/rehearsal-loopback.py" "$release_backend" > "$out/release-loopback.log" 2>&1 &
  release_relay_pid=$!
  release_relay_ready=0
  for _ in $(seq 1 30); do
    kill -0 "$release_relay_pid" 2>/dev/null || break
    if grep -qxF "READY rehearsal loopback 127.0.0.1:8090 -> $release_backend:80" "$out/release-loopback.log"; then release_relay_ready=1; break; fi
    sleep 1
  done
  if [ "$release_relay_ready" != 1 ]; then
    cat "$out/release-loopback.log"
    invalid "isolated copy loopback relay did not bind"
  fi
  echo "  isolated PHP network verified; owned loopback relay ready"
fi
up=0; for _ in $(seq 1 60); do [ "$(curl -s -o /dev/null -m 10 -w '%{http_code}' http://localhost:8090/)" = 200 ] && { up=1; break; }; sleep 2; done
[ "$up" = 1 ] || invalid "the copy's home page never answered 200"
if [ -n "$seam" ] && [ -n "${TS_REHEARSAL_TEST_PREP:-}" ]; then   # TESTING ONLY: PHP run on the copy before the baseline (e.g. take a change out so it can be rehearsed)
  echo "TEST MODE: preparing the copy"; wpc --user=1 eval "$TS_REHEARSAL_TEST_PREP" | sed 's/^/    /'
fi

prepare="${inst%/*}/prepare-rehearsal.php"
if [ -f "$wwm/$prepare" ]; then
  echo "[2b] package preparation on the isolated localhost copy only"
  wpc --user=1 eval-file "/wwm/$prepare" || invalid "package rehearsal preparation failed"
fi

sql() { dc exec -T db mariadb -uroot -prehearsal --default-character-set=utf8mb4 -N -B wp "$@"; }   # the client in the wpcli image insists on TLS; query the db container directly
fp() { # fingerprint lines into $out/$1.txt (the prove-restore.sh query set)
  local q="$work/fp.sql" t fp_status
  {
    echo "SET NAMES utf8mb4;"
    for t in $(sql -e 'SHOW TABLES'); do echo "SELECT 'rows', '$t', COUNT(*) FROM \`$t\`;"; done
    echo "SELECT 'page', post_name, SHA2(post_content,256) FROM \`${prefix}posts\` WHERE post_type='page' AND post_status='publish' ORDER BY post_name;"
    echo "SELECT 'css', ID, SHA2(post_content,256) FROM \`${prefix}posts\` WHERE post_type='custom_css' AND post_status='publish' ORDER BY ID;"
    echo "SELECT 'snippet', ID, CONCAT(post_status, ':', SHA2(post_content,256)) FROM \`${prefix}posts\` WHERE post_type='wpcode' ORDER BY ID;"
    echo "SELECT 'seo', CONCAT(post_id,':',meta_key), SHA2(meta_value,256) FROM \`${prefix}postmeta\` pm JOIN \`${prefix}posts\` p ON p.ID = pm.post_id WHERE meta_key LIKE 'surerank_settings_%' AND p.post_type <> 'revision' ORDER BY post_id, meta_key;"
    echo "SELECT 'option', option_name, SHA2(option_value,256) FROM \`${prefix}options\` WHERE option_name IN ('astra-settings','widget_block','nav_menu_locations','blogname','blogdescription','siteurl','home','active_plugins','template','stylesheet','permalink_structure','surerank_settings','wpcode_snippets','ihaf_insert_header','ihaf_insert_footer','wp_page_for_privacy_policy','sidebars_widgets') ORDER BY option_name;"
  } > "$q"
  sql < "$q" | grep -v '^$' > "$out/$1.txt"
  fp_status=("${PIPESTATUS[@]}")
  [ "${fp_status[0]}" -eq 0 ] && [ "${fp_status[1]}" -eq 0 ] || invalid "fingerprint $1 query/output failed; partial rows prove nothing"
  grep -v '^rows' "$out/$1.txt" > "$out/$1.content"
  [ "$(wc -l < "$out/$1.content")" -ge 10 ] || invalid "fingerprint $1 returned almost nothing"
}
pages=(/ /about/ /services/ /thai-massage/ /therapeutic-massage/ /deep-tissue-massage/ /work-with-me/ /contact/ /privacy-policy/ /accessibility/)
log="$pub/wp-content/rehearsal-debug.log"
load_pages() { local p c curl_rc bad=0; for p in "${pages[@]}"; do c=$(curl -s -o /dev/null -m 30 -w '%{http_code}' "http://localhost:8090$p"); curl_rc=$?; printf '  %s %s\n' "$c" "$p"; [ "$c" = 200 ] && [ "$curl_rc" -eq 0 ] || bad=1; done; return $bad; }
errs() { [ -f "$log" ] && sed -E 's/^\[[^]]*\] //' "$log" | grep -E 'PHP (Fatal|Parse|Warning|Notice|Deprecated)' | sort -u || true; }

fails=0
check() { if [ "$2" -eq 0 ]; then echo "  -> PASS  $1"; else echo "  -> FAIL  $1"; fails=$((fails + 1)); fi; }
inst_run() { # The filtered line is WP-CLI's PHP 8.5 deprecation, not the installer's.
  local name=$1 status; shift
  echo; echo "[$name] wp eval-file $inst $*"
  wpc --user=1 eval-file "/wwm/$inst" "$@" 2>&1 | grep -v 'phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php' | tee "$out/$name.log" | sed 's/^/    /'
  status=("${PIPESTATUS[@]}")
  if [ "$release_rehearsal" = 1 ] || [ "$inst" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then
    [ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] && [ "${status[2]}" -eq 0 ] && [ "${status[3]}" -eq 0 ]
  else
    return "${status[0]}"
  fi
}
core_focus_snapshot_from_log() {
  local path component=core-input-focus
  if [ "${inst:-}" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then component=core-neighbours; fi
  path=$(sed -nE 's@^Success: .*Before-snapshot: (/backups/peek-template-'"$component"'-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json)$@\1@p' "$1")
  [[ "$path" =~ ^/backups/peek-template-${component}-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]] || return 1
  printf '%s\n' "$path"
}
core_focus_lane() {
  case "${inst:-}" in
    link-ideas/core-input-focus/install-peek-core-input-focus.php|link-ideas/core-neighbours/install-peek-core-neighbours.php) return 0 ;;
    *) return 1 ;;
  esac
}
core_focus_verify() {
  local label=$1 snapshot=$2; shift 2
  echo; echo "[$label] exact shared-core snapshot verification $snapshot $*"
  wpc --user=1 eval-file /wwm/link-ideas/install-peek-nav.php verify "$snapshot" "$@" 2>&1 | tee "$out/$label.log" | sed 's/^/    /'
  local status=("${PIPESTATUS[@]}")
  [ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] && [ "${status[2]}" -eq 0 ]
}
core_neighbours_require_clean() {
  [ "$inst" != link-ideas/core-neighbours/install-peek-core-neighbours.php ] || [ "$fails" -eq 0 ] || {
    echo "REHEARSAL FAILED: core-neighbours $*; no subsequent mutation or browser work attempted."; exit 1;
  }
}
core_neighbours_cleanup() {
  local result=$? file
  local evidence="${RUNNER_TEMP:-$HOME/.cache}/ops-out/core-neighbours-native"
  if mkdir -p "$evidence"; then
    # Public gate logs, hashes and route inventory only; never private snapshots,
    # SQL exports, copied source files or database credentials.
    for file in "$out"/*.log "$out"/*.content "$out"/errors-*.txt "$out"/title-strip-pages.json; do
      [ -f "$file" ] || continue
      cp "$file" "$evidence/" || { echo "FAIL core-neighbours rehearsal: could not retain ${file##*/}" >&2; result=1; }
    done
  else
    echo "FAIL core-neighbours rehearsal: cannot retain complete native evidence" >&2
    result=1
  fi
  cleanup
  exit "$result"
}
release_manifest="$wwm/link-ideas/peek-release/manifest.json"
release_evidence() { python3 -I -B "$wwm/link-ideas/peek-release/rehearsal-evidence.py" "$@"; }
release_snapshot() { release_evidence snapshot "$1" "$release_manifest" "${2:-applied}"; }
release_inventory() {
  export TS_PEEK_TITLE_INVENTORY="$out/title-strip-pages.json"
  wpc eval --skip-themes '$rows = array(); foreach (get_posts(array("post_type" => "page", "post_status" => "publish", "numberposts" => -1, "orderby" => "ID", "order" => "ASC", "suppress_filters" => true)) as $p) { $path = wp_parse_url(get_permalink($p), PHP_URL_PATH); if (!is_string($path) || substr($path, 0, 1) !== "/") { WP_CLI::error("Invalid published-page permalink"); } $rows[] = array("id" => $p->ID, "path" => $path, "title" => html_entity_decode(wp_strip_all_tags(get_the_title($p)), ENT_QUOTES | ENT_HTML5, "UTF-8")); } echo wp_json_encode(array("pages" => $rows));' > "$TS_PEEK_TITLE_INVENTORY" || return 1
  release_evidence inventory "$TS_PEEK_TITLE_INVENTORY" "$release_manifest" > "$out/release-paths.txt" || return 1
  mapfile -t pages < "$out/release-paths.txt"
  [ "${#pages[@]}" -eq 15 ]
}
release_original_render() {
  local label=$1 reference=${2:-} path code curl_rc
  local destination="$out/$label-navigation.jsonl" html="$out/release-original-page.html"
  : > "$destination" || return 1
  for path in "${pages[@]}"; do
    code=$(curl -sS -o "$html" -m 30 -w '%{http_code}' "http://localhost:8090$path"); curl_rc=$?
    [ "$curl_rc" -eq 0 ] && [ "$code" = 200 ] || { echo "FAIL release original page $path: HTTP$code curl_exit=$curl_rc"; return 1; }
    release_evidence original-html "$html" "$path" >> "$destination" || return 1
  done
  rm -f "$html"
  [ "$(wc -l < "$destination")" -eq 15 ] || return 1
  if [ -n "$reference" ] && ! cmp -s "$reference" "$destination"; then
    echo "FAIL release rendered rollback differs from original jump destinations, native guide, CSS or scroll-to-top"
    diff "$reference" "$destination" || true
    return 1
  fi
  echo "PASS release $label: all15 original pages render without Peek; navigation sources and targets verified"
}
release_cleanup() {
  local result=$? file
  local evidence="${RUNNER_TEMP:-$HOME/.cache}/ops-out/peek-release-native"
  if mkdir -p "$evidence"; then
    # Keep complete gate logs and content fingerprints, never private recovery
    # snapshots, SQL exports, source files or database credentials.
    for file in "$out"/*.log "$out"/*.content "$out"/errors-*.txt "$out"/*-navigation.jsonl "$out"/title-strip-pages.json "$out"/release-paths.txt; do
      [ -f "$file" ] || continue
      cp "$file" "$evidence/" || result=1
    done
  else
    echo "FAIL release rehearsal: cannot retain complete native evidence" >&2
    result=1
  fi
  cleanup
  exit "$result"
}

if [ "$release_rehearsal" = 1 ]; then
  trap release_cleanup EXIT
  echo; echo "[2c] exact visitor inventory and normal asset warm-up on the untouched copy"
  release_inventory || invalid "published inventory differs from the reviewed visitor release"
  load_pages || invalid "visitor pages did not answer HTTP200 before the baseline"
  inst_run warm-before preflight || invalid "visitor-release first warm preflight failed"
  load_pages || invalid "visitor pages did not answer HTTP200 on the second warm read"
  inst_run warm-after preflight || invalid "visitor-release second warm preflight failed"
  release_evidence warm "$out/warm-before.log" "$out/warm-after.log" "$release_manifest" || invalid "normal warm GETs changed protected release state"
  release_original_render original-before || invalid "original rendered navigation evidence is incomplete"
fi

if [ "$inst" = link-ideas/rail-input/install-rail-input.php ]; then
  echo; echo "[2c] observe unchanged Rail page before the baseline"
  rail_observer=/wwm/link-ideas/rail-input/observe-state.php
  [ -f "$wwm/link-ideas/rail-input/observe-state.php" ] || invalid "Rail baseline observer is missing"
  rail_observation_logs="${RUNNER_TEMP:-$HOME/.cache}/ops-out/rail-baseline"
  mkdir -p "$rail_observation_logs" || invalid "cannot retain Rail baseline observations"
  rail_observe_run() {
    local label=$1; shift
    "$@" 2>&1 | tee "$out/$label.log" "$rail_observation_logs/$label.log"
    local rail_status=("${PIPESTATUS[@]}")
    [ "${rail_status[0]}" -eq 0 ] && [ "${rail_status[1]}" -eq 0 ]
  }
  rail_observation_path() {
    sed -nE 's@.*Observation: (/backups/rail-input-observe-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json)$@\1@p' "$1"
  }
  rail_local_get() {
    local status rc
    status=$(curl -sS -o /dev/null --max-time 30 -w '%{http_code}' http://localhost:8090/rail-nav-ideas/); rc=$?
    printf 'Rail unchanged-page GET: HTTP%s; curl_exit=%s\n' "$status" "$rc"
    [ "$rc" -eq 0 ] && [ "$status" = 200 ]
  }
  # The PHP container cannot address the runner's localhost port. Both GETs
  # happen here, with no redirect, against the untouched copy before fp A.
  rail_observe_run rail-cold-record wpc --user=1 eval-file "$rail_observer" record || invalid "could not record cold Rail state"
  rail_cold=$(rail_observation_path "$out/rail-cold-record.log")
  [[ "$rail_cold" =~ ^/backups/rail-input-observe-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]] || invalid "cold Rail observation path missing or ambiguous"
  rail_observe_run rail-cold-get rail_local_get || invalid "unchanged Rail page did not answer HTTP200"
  rail_observe_run rail-cold-compare wpc --user=1 eval-file "$rail_observer" compare "$rail_cold" cold-assets || invalid "cold Rail GET changed more than the measured asset metadata"
  rail_observe_run rail-warm-record wpc --user=1 eval-file "$rail_observer" record || invalid "could not record warm Rail state"
  rail_warm=$(rail_observation_path "$out/rail-warm-record.log")
  [[ "$rail_warm" =~ ^/backups/rail-input-observe-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]] || invalid "warm Rail observation path missing or ambiguous"
  rail_observe_run rail-warm-get rail_local_get || invalid "warm Rail page did not answer HTTP200"
  rail_observe_run rail-warm-compare wpc --user=1 eval-file "$rail_observer" compare "$rail_warm" || invalid "warm Rail GET changed complete page state"
fi

if [ "$inst" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then
  trap core_neighbours_cleanup EXIT
fi

echo; echo "[3] baseline: fingerprint A and the ${#pages[@]} pages"
fp A; load_pages; check "pages answer 200 before the change" $?
errs > "$out/errors-before.txt"

inst_run preflight preflight; preflight_rc=$?; check "preflight" "$preflight_rc"
if [ "$release_rehearsal" = 1 ] && [ "$preflight_rc" -ne 0 ]; then
  echo "REHEARSAL FAILED: visitor-release preflight failed; apply not started."; exit 1
fi
if [ "$release_rehearsal" = 1 ]; then
  release_evidence warm "$out/warm-after.log" "$out/preflight.log" "$release_manifest"; check "release state remains exact after baseline page reads" $?
  [ "$fails" -eq 0 ] || { echo "REHEARSAL FAILED: release baseline gates failed; apply not started."; exit 1; }
fi
core_neighbours_require_clean "baseline/preflight gates failed"
inst_run apply apply; rc=$?; check "apply" $rc
[ "$rc" -eq 0 ] || { echo; echo "REHEARSAL FAILED: apply did not succeed, so nothing after it was tried."; exit 1; }
fp B
if [ "$inst" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then
  core_focus_first_snapshot=$(core_focus_snapshot_from_log "$out/apply.log") || invalid "core-neighbours first apply has no unique actual snapshot"
  core_focus_verify verify-first-apply "$core_focus_first_snapshot"; check "exact first core-neighbours apply snapshot" $?
  core_neighbours_require_clean "first apply snapshot verification failed"
fi
if cmp -s "$out/A.content" "$out/B.content"; then
  echo; echo "REHEARSAL INVALID: apply moved no fingerprint. The change is probably already on $src (preflight said so above), or it touches"
  echo "something the fingerprint does not cover; either way a rollback test would prove nothing."; exit 2
fi
if [ "$release_rehearsal" = 1 ]; then
  release_first_snapshot=$(release_snapshot "$out/apply.log") || invalid "first release apply has no unique verified snapshot binding"
  inst_run apply-again apply "$release_first_snapshot"; release_repeat_rc=$?
  check "strict release repeat using the actual first snapshot" "$release_repeat_rc"
  [ "$release_repeat_rc" -eq 0 ] || { echo "REHEARSAL FAILED: strict release repeat failed."; exit 1; }
  release_evidence same "$out/apply.log" "$out/apply-again.log" "$release_manifest"; release_binding_rc=$?
  check "release repeat preserves the exact complete snapshot binding" "$release_binding_rc"
  [ "$release_binding_rc" -eq 0 ] || { echo "REHEARSAL FAILED: release repeat binding differs."; exit 1; }
else
  inst_run apply-again apply; check "apply a second time" $?
fi
fp B2; cmp -s "$out/B.content" "$out/B2.content"; check "applying twice changes nothing more (idempotent)" $?
core_neighbours_require_clean "repeat apply or B/B2 idempotence failed"
if [ "$inst" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then
  core_focus_verify verify-repeat-apply "$core_focus_first_snapshot"; check "repeat apply retains exact first core-neighbours snapshot" $?
  core_neighbours_require_clean "repeat snapshot verification failed"
fi
snap_pattern='*.json'
[ "$inst" != link-ideas/rail-input/install-rail-input.php ] || snap_pattern='rail-input-before-*.json'
if [ "$release_rehearsal" = 1 ]; then
  snap=${release_first_snapshot##*/}
elif core_focus_lane; then
  core_focus_first_snapshot=$(core_focus_snapshot_from_log "$out/apply.log") || core_focus_first_snapshot=""
  snap=${core_focus_first_snapshot##*/}
else
  snap=$(find "$bk" -maxdepth 1 -name "$snap_pattern" -printf '%T@ %f\n' | sort -rn | head -1 | cut -d' ' -f2-)
fi
if [ -z "$snap" ]; then echo "  no before-snapshot was written to WWM_BACKUP_DIR"; check "apply wrote a before-snapshot" 1; else
  inst_run rollback rollback "/backups/$snap"; check "rollback with $snap" $?
  core_neighbours_require_clean "first rollback failed"
  if [ "$inst" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then
    core_focus_verify verify-first-original "$core_focus_first_snapshot" original; check "first rollback restores exact core-neighbours original snapshot" $?
    core_neighbours_require_clean "first original snapshot verification failed"
  fi
  if [ "$release_rehearsal" = 1 ]; then
    release_evidence original "$out/apply.log" "$out/rollback.log" "$release_manifest" || invalid "release rollback has no exact original-state binding"
    release_original_render original-after-rollback "$out/original-before-navigation.jsonl"; release_render_rc=$?
    check "first release rollback restores original rendered navigation on all15 pages" "$release_render_rc"
    [ "$release_render_rc" -eq 0 ] || { echo "REHEARSAL FAILED: first release rollback has stale or changed rendered navigation."; exit 1; }
    inst_run verify-original verify "$release_first_snapshot" original; release_original_rc=$?
    check "exact original release records after rollback" "$release_original_rc"
    [ "$release_original_rc" -eq 0 ] || { echo "REHEARSAL FAILED: original release state was not restored."; exit 1; }
    release_evidence original "$out/apply.log" "$out/verify-original.log" "$release_manifest" || invalid "release original-state verification binding differs"
  fi
  # WPCode's own snippet-list cache option stores each snippet's "modified" time, so an in-place edit of a snippet followed by a rollback always changes it (2026-10-25, Work With Me availability update);
  # the snippet's content hash (the 'snippet' lines) still compares exactly, so only that cache option is set aside here.
  nocache() { grep -v -P '^option\twpcode_snippets\t' "$1"; }
  fp C; cmp -s <(nocache "$out/A.content") <(nocache "$out/C.content"); check "after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)" $?
  cmp -s <(nocache "$out/A.content") <(nocache "$out/C.content") || diff <(nocache "$out/A.content") <(nocache "$out/C.content") | grep '^[<>]' | cut -f1-2 | sed 's/^/    still different: /' | head -20
  core_neighbours_require_clean "first rollback fingerprint differs from A"
  inst_run re-apply apply; check "apply again after the rollback" $?
  core_neighbours_require_clean "second apply failed"
  # A change that CREATES posts (a page, a snippet) gets new database ids when it is applied again after a rollback (2026-10-24, Corporate & Group Massage): compare with the id numbers of snippet and SEO rows set aside (and WPCode's own snippet-list cache option, whose content lists those ids); the snippets' and pages' content hashes still compare.
  idn() { sed -E '/^option\twpcode_snippets\t/d; s/^snippet\t[0-9]+\t/snippet\t#\t/; s/^seo\t[0-9]+:/seo\t#:/' "$1"; }
  fp D
  if [ "$release_rehearsal" = 1 ]; then
    release_active_snapshot=$(release_snapshot "$out/re-apply.log") || invalid "release reapply has no unique verified snapshot binding"
    release_evidence compare "$out/apply.log" "$out/B.content" "$out/re-apply.log" "$out/D.content" "$release_manifest"
    check "release reapply matches with only its five owned IDs normalized" $?
  elif core_focus_lane; then
    cmp -s <(nocache "$out/B.content") <(nocache "$out/D.content"); check "shared-core reapply exactly matches B (no ID normalization)" $?
    # Each core apply intentionally sets new modified dates. The active state is
    # bound to re-apply's actual returned snapshot, never the first apply or latest file.
    core_focus_active_snapshot=$(core_focus_snapshot_from_log "$out/re-apply.log") || core_focus_active_snapshot=""
  else
    cmp -s <(idn "$out/B.content") <(idn "$out/D.content"); check "re-applied state equals the first applied state (B; ids of re-created snippets and SEO rows set aside)" $?
  fi
fi

core_neighbours_require_clean "second apply fingerprint differs from B"

echo; echo "[4] the ${#pages[@]} pages after the change, and PHP errors"
load_pages; check "pages answer 200 with the change applied" $?
errs > "$out/errors-after.txt"
new=$(comm -13 "$out/errors-before.txt" "$out/errors-after.txt")
if [ -z "$new" ]; then echo "  no PHP error, warning or deprecation that the copy did not already log"; check "no new PHP errors" 0
else
  printf '%s\n' "$new" | head -15 | sed 's/^/    new: /'
  if grep -qE 'PHP (Fatal|Parse|Warning)' <<<"$new"; then check "no new PHP errors (fatal/parse/warning above)" 1; else echo "  (only notices/deprecations: reported, not failed)"; fi
fi

# Regulated copy must pass its rendered gates on the throwaway copy BEFORE a
# staging write. This remains inside the rehearsal, before cleanup removes it.
echo; echo "[4b] rendered copy gates on the applied throwaway copy"
python3 -I -B "$wwm/home/verify-home.py" http://localhost:8090 > "$out/home-gates.log" 2>&1
check "Home copy and links" $?
tail -n 6 "$out/home-gates.log"
python3 -I -B "$wwm/client-pages/verify-client-pages.py" http://localhost:8090 > "$out/client-gates.log" 2>&1
check "client-page regulated copy, structure and links" $?
grep '^FAIL' "$out/client-gates.log" || true
tail -n 2 "$out/client-gates.log"

# Optional package-owned browser gate. It runs only on the isolated applied
# copy, before cleanup, and must pass before any staging write is authorized.
if [ "$release_rehearsal" = 1 ]; then
  [ "$fails" -eq 0 ] && [ -n "${release_active_snapshot:-}" ] || { echo "REHEARSAL FAILED: earlier visitor-release gates failed; browser not started."; exit 1; }
  release_inventory || invalid "published release inventory changed before browser verification"
  inst_run verify-before-browser verify "$release_active_snapshot"; release_before_rc=$?
  check "exact release records before browser" "$release_before_rc"
  [ "$release_before_rc" -eq 0 ] || { echo "REHEARSAL FAILED: release snapshot no longer matches; browser not started."; exit 1; }
  release_evidence same "$out/re-apply.log" "$out/verify-before-browser.log" "$release_manifest" || invalid "release before-browser binding differs from actual reapply"
fi
if core_focus_lane; then
  [ "$fails" -eq 0 ] || { echo "REHEARSAL FAILED: earlier required shared-core gates failed; browser not started."; exit 1; }
  if [ -z "${core_focus_active_snapshot:-}" ]; then
    check "shared-core re-apply returned one actual complete snapshot" 1
    echo "REHEARSAL FAILED: shared-core active snapshot is missing or ambiguous; browser not started."; exit 1
  fi
  core_focus_verify verify-before-browser "$core_focus_active_snapshot"; core_focus_before_rc=$?
  check "exact shared core and four page states before browser" "$core_focus_before_rc"
  [ "$core_focus_before_rc" -eq 0 ] || { echo "REHEARSAL FAILED: shared-core full-state verification failed; browser not started."; exit 1; }
fi
extra="$wwm/$(dirname "$inst")/check-rehearsal.mjs"
if [ -f "$extra" ]; then
  echo; echo "[4c] package browser checks on the applied throwaway copy"
  browser_seconds=600
  # This shared-core upgrade checks four actual pages in two engines plus native
  # touch, then retains the timing-sensitive Thai gesture suite in isolation.
  browser_evidence=""
  case "$inst" in
    link-ideas/peek-release/install-peek-release.php) browser_seconds=2400; browser_evidence=peek-release-browser.log ;;
    link-ideas/core-upgrade/install-peek-core-upgrade.php) browser_seconds=900; browser_evidence=peek-core-upgrade-browser.log ;;
    link-ideas/core-summary-touch/install-peek-core-summary-touch.php) browser_seconds=900; browser_evidence=peek-core-summary-touch-browser.log ;;
    link-ideas/core-title-strip/install-peek-core-title-strip.php|link-ideas/core-title-rail/install-peek-core-title-rail.php|link-ideas/core-kiro-fixes/install-peek-core-kiro-fixes.php|link-ideas/core-input-focus/install-peek-core-input-focus.php|link-ideas/core-neighbours/install-peek-core-neighbours.php|link-ideas/rail-input/install-rail-input.php)
      browser_seconds=900; browser_evidence=peek-core-title-strip-browser.log
      if [ "$inst" = link-ideas/core-title-rail/install-peek-core-title-rail.php ]; then
        browser_seconds=1500; browser_evidence=peek-core-title-rail-browser.log
      elif [ "$inst" = link-ideas/core-kiro-fixes/install-peek-core-kiro-fixes.php ]; then
        browser_seconds=1800; browser_evidence=peek-core-kiro-fixes-browser.log
      elif [ "$inst" = link-ideas/core-input-focus/install-peek-core-input-focus.php ]; then
        browser_seconds=2100; browser_evidence=peek-core-input-focus-browser.log
      elif [ "$inst" = link-ideas/core-neighbours/install-peek-core-neighbours.php ]; then
        browser_seconds=2700; browser_evidence=peek-core-neighbours-browser.log
      elif [ "$inst" = link-ideas/rail-input/install-rail-input.php ]; then
        browser_seconds=900; browser_evidence=rail-input-browser.log
      fi
      # Enumerate this fresh copy, including unlisted/practice pages. The title
      # matrix must not silently test only a fixed menu or sitemap subset.
      export TS_PEEK_TITLE_INVENTORY="$out/title-strip-pages.json"
      wpc eval --skip-themes '$rows = array(); foreach (get_posts(array("post_type" => "page", "post_status" => "publish", "numberposts" => -1, "orderby" => "ID", "order" => "ASC", "suppress_filters" => true)) as $p) { $path = wp_parse_url(get_permalink($p), PHP_URL_PATH); if (!is_string($path) || substr($path, 0, 1) !== "/") { WP_CLI::error("Invalid published-page permalink"); } $rows[] = array("id" => $p->ID, "path" => $path, "title" => html_entity_decode(wp_strip_all_tags(get_the_title($p)), ENT_QUOTES | ENT_HTML5, "UTF-8")); } echo wp_json_encode(array("pages" => $rows));' > "$TS_PEEK_TITLE_INVENTORY"
      inventory_rc=$?
      check "all published-page browser inventory" "$inventory_rc"
      [ "$inventory_rc" -eq 0 ] || invalid "could not enumerate all published pages for title-strip checks"
      ;;
  esac
  echo "  browser budget: ${browser_seconds}s (all package checks retained)"
  timeout "$browser_seconds" node "$extra" http://localhost:8090 > "$out/package-browser.log" 2>&1
  package_browser_rc=$?
  check "package browser checks" "$package_browser_rc"
  if [ -n "$browser_evidence" ]; then
    evidence="${RUNNER_TEMP:-$HOME/.cache}/ops-out"
    mkdir -p "$evidence" && cp "$out/package-browser.log" "$evidence/$browser_evidence"
    check "complete core-upgrade browser log retained for review" $?
  fi
  [ "$package_browser_rc" -eq 0 ] || tail -n 30 "$out/package-browser.log"
  grep -E '^(FAIL|INVALID|Bundle [CDEF]:|CONTRAST|LAYOUT|MIN_CONTRAST|HEADING|HEIGHT|INFO|INLINE)' "$out/package-browser.log" || true
fi

if [ "$inst" = link-ideas/rail-input/install-rail-input.php ]; then
  echo; echo "[4d] exact Rail recovery after rendered browser activity"
  if [ "${package_browser_rc:-missing}" != 0 ] || [ -z "${snap:-}" ]; then
    check "post-browser Rail recovery prerequisites (complete browser pass and original snapshot)" 1
  else
    inst_run rollback-after-browser rollback "/backups/$snap"; rendered_rollback_rc=$?
    check "Rail full-state rollback after browser activity" "$rendered_rollback_rc"
    if [ "$rendered_rollback_rc" -eq 0 ]; then
      fp E
      cmp -s <(nocache "$out/A.content") <(nocache "$out/E.content"); rendered_baseline_rc=$?
      check "post-browser rollback matches baseline A (only existing WPCode cache exclusion)" "$rendered_baseline_rc"
      if [ "$rendered_baseline_rc" -eq 0 ]; then
        inst_run reapply-after-browser apply; rendered_reapply_rc=$?
        check "Rail reapply after post-browser recovery" "$rendered_reapply_rc"
        if [ "$rendered_reapply_rc" -eq 0 ]; then
          fp F
          cmp -s <(nocache "$out/B.content") <(nocache "$out/F.content")
          check "post-browser reapply exactly matches tested B (no ID normalization)" $?
        fi
      fi
    fi
  fi
fi

if core_focus_lane; then
  echo; echo "[4e] exact shared-core recovery after rendered browser activity"
  if [ "$fails" -ne 0 ] || [ "${package_browser_rc:-missing}" != 0 ] || [ -z "${core_focus_active_snapshot:-}" ]; then
    check "post-browser shared-core recovery prerequisites (complete browser pass and actual re-apply snapshot)" 1
  else
    core_focus_verify verify-after-browser "$core_focus_active_snapshot"; core_focus_after_rc=$?
    check "exact shared core and four page states after browser" "$core_focus_after_rc"
    if [ "$core_focus_after_rc" -eq 0 ]; then
      inst_run rollback-after-browser rollback "$core_focus_active_snapshot"; core_focus_rollback_rc=$?
      check "shared-core full-state rollback after browser" "$core_focus_rollback_rc"
      if [ "$core_focus_rollback_rc" -eq 0 ]; then
        core_focus_verify verify-original-after-browser "$core_focus_active_snapshot" original; core_focus_original_rc=$?
        check "shared-core exact original core and four page states restored" "$core_focus_original_rc"
        if [ "$core_focus_original_rc" -eq 0 ]; then
          fp E
          cmp -s <(nocache "$out/A.content") <(nocache "$out/E.content"); core_focus_baseline_rc=$?
          check "shared-core post-browser rollback matches A (no ID normalization)" "$core_focus_baseline_rc"
          if [ "$core_focus_baseline_rc" -eq 0 ]; then
            inst_run reapply-after-browser apply; core_focus_reapply_rc=$?
            check "shared-core reapply after post-browser recovery" "$core_focus_reapply_rc"
            if [ "$core_focus_reapply_rc" -eq 0 ]; then
              core_focus_final_snapshot=$(core_focus_snapshot_from_log "$out/reapply-after-browser.log"); core_focus_snapshot_rc=$?
              check "shared-core final reapply returned one actual complete snapshot" "$core_focus_snapshot_rc"
              if [ "$core_focus_snapshot_rc" -eq 0 ]; then
                core_focus_verify verify-final-reapply "$core_focus_final_snapshot"; core_focus_final_rc=$?
                check "shared-core final applied core and four page states match their new snapshot" "$core_focus_final_rc"
                if [ "$core_focus_final_rc" -eq 0 ]; then
                  fp F
                  cmp -s <(nocache "$out/B.content") <(nocache "$out/F.content")
                  check "shared-core post-browser reapply exactly matches B (no ID normalization)" $?
                fi
              fi
            fi
          fi
        fi
      fi
    fi
  fi
fi

if [ "$release_rehearsal" = 1 ]; then
  echo; echo "[4f] exact visitor-release recovery after rendered browser activity"
  if [ "$fails" -ne 0 ] || [ "${package_browser_rc:-missing}" != 0 ]; then
    check "visitor-release post-browser recovery requires complete passing browser evidence" 1
  else
    inst_run verify-after-browser verify "$release_active_snapshot"; release_after_rc=$?
    check "exact release records after browser" "$release_after_rc"
    if [ "$release_after_rc" -eq 0 ]; then
      release_evidence same "$out/re-apply.log" "$out/verify-after-browser.log" "$release_manifest"; release_binding_rc=$?
      check "browser did not change the complete release state or immutable snapshot binding" "$release_binding_rc"
      if [ "$release_binding_rc" -eq 0 ]; then
        inst_run rollback-after-browser rollback "$release_active_snapshot"; release_rollback_rc=$?
        check "exact release rollback after browser" "$release_rollback_rc"
        if [ "$release_rollback_rc" -eq 0 ]; then
          release_evidence original "$out/re-apply.log" "$out/rollback-after-browser.log" "$release_manifest"; check "post-browser rollback binds the actual active snapshot" $?
          release_original_render original-after-browser "$out/original-before-navigation.jsonl"; check "post-browser release rollback restores all15 original rendered navigation signatures" $?
          inst_run verify-original-after-browser verify "$release_active_snapshot" original; release_original_rc=$?
          check "exact original release state after browser rollback" "$release_original_rc"
          if [ "$release_original_rc" -eq 0 ]; then
            release_evidence original "$out/re-apply.log" "$out/verify-original-after-browser.log" "$release_manifest"; check "post-browser original-state verification binds the actual snapshot" $?
            fp E
            cmp -s <(nocache "$out/A.content") <(nocache "$out/E.content"); check "post-browser release rollback restores A without ID normalization" $?
            if [ "$fails" -eq 0 ]; then
              inst_run reapply-after-browser apply; release_final_rc=$?
              check "release reapply after post-browser recovery" "$release_final_rc"
              if [ "$release_final_rc" -eq 0 ]; then
                release_final_snapshot=$(release_snapshot "$out/reapply-after-browser.log") || invalid "final release apply did not return a verified snapshot"
                inst_run verify-final-reapply verify "$release_final_snapshot"; release_verify_rc=$?
                check "exact final release records and their actual snapshot" "$release_verify_rc"
                if [ "$release_verify_rc" -eq 0 ]; then
                  release_evidence same "$out/reapply-after-browser.log" "$out/verify-final-reapply.log" "$release_manifest"; check "final release verification preserves its new immutable binding" $?
                  fp F
                  release_evidence compare "$out/apply.log" "$out/B.content" "$out/reapply-after-browser.log" "$out/F.content" "$release_manifest"; check "final release matches B with only its five owned IDs normalized" $?
                  release_evidence compare "$out/re-apply.log" "$out/D.content" "$out/reapply-after-browser.log" "$out/F.content" "$release_manifest"; check "final release also has a distinct snapshot and five IDs from the second apply" $?
                fi
              fi
            fi
          fi
        fi
      fi
    fi
  fi
fi

echo; echo "[5] what the change touches (fingerprint A -> B; this is what staging will see move):"
if cmp -s "$out/A.content" "$out/B.content"; then echo "  NOTHING moved: the installer found the change already present, or it changes something the fingerprint does not cover"
else diff "$out/A.content" "$out/B.content" | grep '^>' | cut -f1-2 | sed 's/^> /  changed: /' | sort -u; fi
diff "$out/A.txt" "$out/B.txt" | grep '^>' | grep -P '^> rows\t' | cut -f2-3 | sed 's/^/  rows now: /' | head -10

echo
if [ "$fails" -eq 0 ]; then echo "REHEARSAL PASSED: $inst on a copy of $src (apply, idempotent re-apply, exact rollback, re-apply, pages 200)."; exit 0
else echo "REHEARSAL FAILED: $fails check(s) failed (see -> FAIL above)."; exit 1; fi

# ref: ../work-with-me-employer/prove-restore.sh — the fingerprint queries this reuses; ../../docker-compose.yml — the pinned images
# ref: ./run-op.sh — runs this for a cloud session's "rehearse" request; ../../remote_execution.txt — gitignored access details (never printed)
