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
dc() { docker compose --progress quiet -p "$project" -f "$work/compose.yml" "$@"; }
cleanup() { # files the containers wrote belong to their user, so remove those from inside a container, then the emptied folders
  dc down -v --remove-orphans >/dev/null 2>&1
  case "$work" in */ts-rehearsal-[0-9]*) ;; *) echo "cleanup: refusing to remove '$work' (not this run's work folder)" >&2; return ;; esac
  rm -rf "$work" 2>/dev/null || { docker run --rm --user 0 --entrypoint sh -v "$work:/w" "$cli_img" -c 'rm -rf /w/public_html /w/backups /w/out /w/db.sql.gz /w/compose.yml /w/fp.sql' >/dev/null 2>&1; rm -rf "$work" 2>/dev/null; }
}
trap cleanup EXIT

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
want=$1; part=$2; uploads=${3:-0}
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT   # a file, not < <(...): the SiteGround shell has no /dev/fd for process substitution
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want." >&2; exit 1; }
case "$part" in
  info) printf 'folder=%s\nprefix=%s\nversion=%s\nhome=%s\n' "${doc#"$HOME"/}" "$(wp --path="$doc" --skip-plugins --skip-themes db prefix)" "$(wp --path="$doc" --skip-plugins --skip-themes core version)" "$(wp --path="$doc" --skip-plugins --skip-themes option get home)" ;;
  db) wp --path="$doc" --skip-plugins --skip-themes db export - | gzip -c ;;
  files)
    ex=(--exclude=./wp-config.php --exclude=./.htaccess --exclude=./php_errorlog --exclude='*.log' --exclude='*.sql' --exclude='*.sql.gz' --exclude='*.zip' --exclude='*.tar.gz'
        --exclude=./wp-content/cache --exclude=./wp-content/upgrade --exclude='./wp-content/wpvivid*' --exclude=./wp-content/backups --exclude=./wp-content/upgrade-temp-backup --exclude=./wp-content/debug.log)
    [ "$uploads" = 1 ] || ex+=(--exclude=./wp-content/uploads)
    tar -C "$doc" -cf - "${ex[@]}" . ;;
esac
EOS
remote() { ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$want" "$@" <<<"$REMOTE"; }
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
  local q="$work/fp.sql" t
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
  grep -v '^rows' "$out/$1.txt" > "$out/$1.content"
  [ "$(wc -l < "$out/$1.content")" -ge 10 ] || invalid "fingerprint $1 returned almost nothing"
}
pages=(/ /about/ /services/ /thai-massage/ /therapeutic-massage/ /deep-tissue-massage/ /work-with-me/ /contact/ /privacy-policy/ /accessibility/)
log="$pub/wp-content/rehearsal-debug.log"
load_pages() { local p c bad=0; for p in "${pages[@]}"; do c=$(curl -s -o /dev/null -m 30 -w '%{http_code}' "http://localhost:8090$p"); printf '  %s %s\n' "$c" "$p"; [ "$c" = 200 ] || bad=1; done; return $bad; }
errs() { [ -f "$log" ] && sed -E 's/^\[[^]]*\] //' "$log" | grep -E 'PHP (Fatal|Parse|Warning|Notice|Deprecated)' | sort -u || true; }

fails=0
check() { if [ "$2" -eq 0 ]; then echo "  -> PASS  $1"; else echo "  -> FAIL  $1"; fails=$((fails + 1)); fi; }
inst_run() { local name=$1; shift; echo; echo "[$name] wp eval-file $inst $*"; wpc --user=1 eval-file "/wwm/$inst" "$@" 2>&1 | grep -v 'phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php' | tee "$out/$name.log" | sed 's/^/    /'; return "${PIPESTATUS[0]}"; }   # that one line is WP-CLI's own PHP 8.5 deprecation, not the installer's

echo; echo "[3] baseline: fingerprint A and the 10 pages"
fp A; load_pages; check "pages answer 200 before the change" $?
errs > "$out/errors-before.txt"

inst_run preflight preflight; check "preflight" $?
inst_run apply apply; rc=$?; check "apply" $rc
[ "$rc" -eq 0 ] || { echo; echo "REHEARSAL FAILED: apply did not succeed, so nothing after it was tried."; exit 1; }
fp B
if cmp -s "$out/A.content" "$out/B.content"; then
  echo; echo "REHEARSAL INVALID: apply moved no fingerprint. The change is probably already on $src (preflight said so above), or it touches"
  echo "something the fingerprint does not cover; either way a rollback test would prove nothing."; exit 2
fi
inst_run apply-again apply; check "apply a second time" $?
fp B2; cmp -s "$out/B.content" "$out/B2.content"; check "applying twice changes nothing more (idempotent)" $?
snap=$(find "$bk" -maxdepth 1 -name '*.json' -printf '%T@ %f\n' | sort -rn | head -1 | cut -d' ' -f2-)
if [ -z "$snap" ]; then echo "  no before-snapshot was written to WWM_BACKUP_DIR"; check "apply wrote a before-snapshot" 1; else
  inst_run rollback rollback "/backups/$snap"; check "rollback with $snap" $?
  # WPCode's own snippet-list cache option stores each snippet's "modified" time, so an in-place edit of a snippet followed by a rollback always changes it (2026-10-25, Work With Me availability update);
  # the snippet's content hash (the 'snippet' lines) still compares exactly, so only that cache option is set aside here.
  nocache() { grep -v -P '^option\twpcode_snippets\t' "$1"; }
  fp C; cmp -s <(nocache "$out/A.content") <(nocache "$out/C.content"); check "after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)" $?
  cmp -s <(nocache "$out/A.content") <(nocache "$out/C.content") || diff <(nocache "$out/A.content") <(nocache "$out/C.content") | grep '^[<>]' | cut -f1-2 | sed 's/^/    still different: /' | head -20
  inst_run re-apply apply; check "apply again after the rollback" $?
  # A change that CREATES posts (a page, a snippet) gets new database ids when it is applied again after a rollback (2026-10-24, Corporate & Group Massage): compare with the id numbers of snippet and SEO rows set aside (and WPCode's own snippet-list cache option, whose content lists those ids); the snippets' and pages' content hashes still compare.
  idn() { sed -E '/^option\twpcode_snippets\t/d; s/^snippet\t[0-9]+\t/snippet\t#\t/; s/^seo\t[0-9]+:/seo\t#:/' "$1"; }
  fp D; cmp -s <(idn "$out/B.content") <(idn "$out/D.content"); check "re-applied state equals the first applied state (B; ids of re-created snippets and SEO rows set aside)" $?
fi

echo; echo "[4] the 10 pages after the change, and PHP errors"
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
extra="$wwm/$(dirname "$inst")/check-rehearsal.mjs"
if [ -f "$extra" ]; then
  echo; echo "[4c] package browser checks on the applied throwaway copy"
  browser_seconds=600
  # This shared-core upgrade checks four actual pages in two engines plus native
  # touch, then retains the timing-sensitive Thai gesture suite in isolation.
  browser_evidence=""
  case "$inst" in
    link-ideas/core-upgrade/install-peek-core-upgrade.php) browser_seconds=900; browser_evidence=peek-core-upgrade-browser.log ;;
    link-ideas/core-summary-touch/install-peek-core-summary-touch.php) browser_seconds=900; browser_evidence=peek-core-summary-touch-browser.log ;;
    link-ideas/core-title-strip/install-peek-core-title-strip.php|link-ideas/core-title-rail/install-peek-core-title-rail.php|link-ideas/core-kiro-fixes/install-peek-core-kiro-fixes.php)
      browser_seconds=900; browser_evidence=peek-core-title-strip-browser.log
      if [ "$inst" = link-ideas/core-title-rail/install-peek-core-title-rail.php ]; then
        browser_seconds=1500; browser_evidence=peek-core-title-rail-browser.log
      elif [ "$inst" = link-ideas/core-kiro-fixes/install-peek-core-kiro-fixes.php ]; then
        browser_seconds=1800; browser_evidence=peek-core-kiro-fixes-browser.log
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

echo; echo "[5] what the change touches (fingerprint A -> B; this is what staging will see move):"
if cmp -s "$out/A.content" "$out/B.content"; then echo "  NOTHING moved: the installer found the change already present, or it changes something the fingerprint does not cover"
else diff "$out/A.content" "$out/B.content" | grep '^>' | cut -f1-2 | sed 's/^> /  changed: /' | sort -u; fi
diff "$out/A.txt" "$out/B.txt" | grep '^>' | grep -P '^> rows\t' | cut -f2-3 | sed 's/^/  rows now: /' | head -10

echo
if [ "$fails" -eq 0 ]; then echo "REHEARSAL PASSED: $inst on a copy of $src (apply, idempotent re-apply, exact rollback, re-apply, pages 200)."; exit 0
else echo "REHEARSAL FAILED: $fails check(s) failed (see -> FAIL above)."; exit 1; fi

# ref: ../work-with-me-employer/prove-restore.sh — the fingerprint queries this reuses; ../../docker-compose.yml — the pinned images
# ref: ./run-op.sh — runs this for a cloud session's "rehearse" request; ../../remote_execution.txt — gitignored access details (never printed)
