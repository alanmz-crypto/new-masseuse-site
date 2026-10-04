#!/usr/bin/env bash
# Restore the latest backup into a THROWAWAY stack on :8091 to verify it works.
#
# Fully isolated from your live :8090 stack:
#   - separate compose project name (masseuse-restore-test)
#   - separate DB volume + container names
#   - restored files live under /tmp, never in this repo's public_html
#   - your live containers, DB, and files are never touched
#
# Usage:
#   ./scripts/test-restore.sh          # restore + start test stack on :8091
#   ./scripts/test-restore.sh --down   # stop and delete the test stack + volume + tmp files
#   RESTORE_PROJECT=restore-check MASSEUSE_RESTORE_ROOT=/tmp/restore-check TEST_PORT=8092 ./scripts/test-restore.sh
#   Use those same overrides with --down to remove that isolated run.
#   RESTORE_FILES_SNAPSHOT=<id> RESTORE_DB_SNAPSHOT=<id> select snapshots instead of latest.
#
# The restore run fails unless the DB options are local, FORBIDDEN_HOST is absent,
# / and /services/ load, and their upload images exist and return HTTP success.
set -euo pipefail

SITE_DIR="${SITE_DIR:-$HOME/WordPress/new-masseuse-site}"
cd "$SITE_DIR"

export RESTIC_REPOSITORY="${MASSEUSE_RESTIC_REPO:-$SITE_DIR/backups/restic-repo}"
export RESTIC_PASSWORD_FILE="${MASSEUSE_RESTIC_PASSWORD_FILE:-$SITE_DIR/backups/restic.password}"

PROJECT="${RESTORE_PROJECT:-masseuse-restore-test}"
TEST_PORT="${TEST_PORT:-8091}"
TEST_URL="http://localhost:$TEST_PORT"
LIVE_URL="http://localhost:8090"
RESTORE_ROOT="${MASSEUSE_RESTORE_ROOT:-/tmp/masseuse-restore-stack}"
COMPOSE_FILE="$RESTORE_ROOT/docker-compose.yml"
DB_CONTAINER="${PROJECT}-db-1"
DB_NAME="new_masseuse_db"
FORBIDDEN_HOST="${FORBIDDEN_HOST:-thaisolitude.com}"
FILES_SNAPSHOT="${RESTORE_FILES_SNAPSHOT:-latest}"
DB_SNAPSHOT="${RESTORE_DB_SNAPSHOT:-latest}"
export RESTIC_CACHE_DIR="${MASSEUSE_RESTIC_CACHE_DIR:-/tmp/masseuse-restore-restic-cache}"

fail() {
  echo "[FAIL] $*" >&2
  exit 1
}

teardown() {
  echo "Tearing down test stack '$PROJECT' ..."
  if [[ -f "$COMPOSE_FILE" ]]; then
    docker compose -p "$PROJECT" -f "$COMPOSE_FILE" down -v 2>/dev/null || true
    HOST_OWNER="$(id -u):$(id -g)"
    if [[ -d "$RESTORE_ROOT/public_html/wp-content" ]]; then
      docker run --rm -v "$RESTORE_ROOT/public_html:/var/www/html" \
        --user 0 --entrypoint chown wordpress:cli -R "$HOST_OWNER" /var/www/html/wp-content \
        >/dev/null 2>&1 || echo "[cleanup] Could not reset wp-content ownership; checking filesystem cleanup next." >&2
    fi
  else
    docker compose -p "$PROJECT" down -v 2>/dev/null || true
  fi
  if ! rm -rf "$RESTORE_ROOT" 2>/dev/null; then
    echo "[error] Test containers stopped, but temporary files remain at $RESTORE_ROOT." >&2
    return 1
  fi
  echo "Done. Live :8090 stack untouched."
}

if [[ "${1:-}" == "--down" ]]; then
  teardown
  exit 0
fi

if [[ -e "$RESTORE_ROOT" ]]; then
  fail "Restore workspace already exists at $RESTORE_ROOT; inspect it, run --down, or choose MASSEUSE_RESTORE_ROOT."
fi
for container_name in "${PROJECT}-db-1" "${PROJECT}-wordpress-1"; do
  if docker ps -a --format '{{.Names}}' | grep -Fqx "$container_name"; then
    fail "Restore container '$container_name' already exists; inspect it, run --down, or choose RESTORE_PROJECT."
  fi
done

if ! restic snapshots --compact >/dev/null; then
  echo "[error] Cannot read snapshots from $RESTIC_REPOSITORY." >&2
  echo "        Check the repository path and password before starting a restore test." >&2
  exit 1
fi

wp_test() {
  docker compose -p "$PROJECT" -f "$COMPOSE_FILE" run --rm --no-deps wpcli \
    wp --allow-root "$@"
}

validate_page() {
  local route="$1"
  local minimum_images="$2"
  local page_name="$3"
  local page_file="$RESTORE_ROOT/$page_name.html"
  local image_url image_path image_count=0

  echo "[check] $TEST_URL$route renders ..."
  curl --fail --silent --show-error --location "$TEST_URL$route" -o "$page_file" \
    || fail "Could not load $TEST_URL$route"

  if grep -Fqi "$FORBIDDEN_HOST" "$page_file"; then
    fail "$TEST_URL$route still contains $FORBIDDEN_HOST"
  fi

  while IFS= read -r image_url; do
    [[ "$image_url" == *"/wp-content/uploads/"* ]] || continue

    case "$image_url" in
      "$TEST_URL"/*) image_path="${image_url#"$TEST_URL"}" ;;
      /wp-content/uploads/*) image_path="$image_url" ;;
      *) fail "$TEST_URL$route has a non-local upload URL: $image_url" ;;
    esac

    image_path="${image_path%%\?*}"
    image_path="${image_path%%\#*}"
    [[ "$image_path" == /wp-content/uploads/* ]] \
      || fail "$TEST_URL$route has an invalid upload path: $image_url"
    [[ -f "$RESTORE_ROOT/public_html$image_path" ]] \
      || fail "Restored upload is missing: $image_path"
    curl --fail --silent --show-error --location --head "$TEST_URL$image_path" >/dev/null \
      || fail "Restored upload does not load: $TEST_URL$image_path"
    image_count=$((image_count + 1))
  done < <({
    grep -oE '<img[^>]+>' "$page_file" | grep -oE 'src="[^"]+"' | cut -d '"' -f 2
    grep -oE '<img[^>]+>' "$page_file" | grep -oE 'srcset="[^"]+"' \
      | cut -d '"' -f 2 | tr ',' '\n' | awk '{ print $1 }'
  } | sort -u)

  (( image_count >= minimum_images )) \
    || fail "$TEST_URL$route has $image_count local upload images; expected at least $minimum_images"
  echo "[pass] $TEST_URL$route and $image_count local upload image(s)"
}

echo "=============================================="
echo " Restore TEST stack (throwaway, :$TEST_PORT)"
echo " Live :8090 stack is NOT touched."
echo "=============================================="

mkdir -p "$RESTORE_ROOT"

echo "[1/9] Restore files snapshot '$FILES_SNAPSHOT' -> $RESTORE_ROOT/public_html ..."
restic restore "$FILES_SNAPSHOT" --tag files --target "$RESTORE_ROOT/extract" >/dev/null
# restic restores the absolute source path; relocate to a clean public_html
SRC="$RESTORE_ROOT/extract$SITE_DIR/public_html"
[[ -d "$SRC" ]] || SRC="$(dirname "$(find "$RESTORE_ROOT/extract" -maxdepth 8 -name wp-load.php | head -1)")"
[[ -f "$SRC/wp-load.php" ]] || fail "Restored files snapshot does not contain wp-load.php"
mv "$SRC" "$RESTORE_ROOT/public_html"

echo "[2/9] Restore DB snapshot '$DB_SNAPSHOT' -> $RESTORE_ROOT/db.sql ..."
restic dump "$DB_SNAPSHOT" "$DB_NAME.sql" --tag db > "$RESTORE_ROOT/db.sql"
[[ -s "$RESTORE_ROOT/db.sql" ]] || fail "Restored database dump is empty"

echo "[3/9] Write throwaway wp-config-local.php + compose file ..."
cat > "$RESTORE_ROOT/public_html/wp-config-local.php" <<EOF
<?php
define( 'DB_NAME',     '$DB_NAME' );
define( 'DB_USER',     'root' );
define( 'DB_PASSWORD', 'newrootpassword' );
define( 'DB_HOST',     'db' );
define( 'WP_HOME',    '$TEST_URL' );
define( 'WP_SITEURL', '$TEST_URL' );
define( 'WP_CACHE', false );
define( 'FS_METHOD', 'direct' );
EOF
# make sure wp-config.php loads the local override
CFG="$RESTORE_ROOT/public_html/wp-config.php"
if [[ -f "$CFG" ]] && ! grep -q 'wp-config-local.php' "$CFG"; then
  sed -i "/^<?php/a if ( file_exists( __DIR__ . '/wp-config-local.php' ) ) { require_once __DIR__ . '/wp-config-local.php'; }" "$CFG"
fi

cat > "$COMPOSE_FILE" <<EOF
name: $PROJECT
services:
  db:
    image: mariadb:10.6
    container_name: ${PROJECT}-db-1
    environment:
      MYSQL_ROOT_PASSWORD: newrootpassword
      MYSQL_DATABASE: $DB_NAME
    volumes:
      - test_db_data:/var/lib/mysql
  wordpress:
    image: wordpress:latest
    container_name: ${PROJECT}-wordpress-1
    depends_on: [db]
    ports:
      - "127.0.0.1:$TEST_PORT:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_NAME: $DB_NAME
      WORDPRESS_DB_USER: root
      WORDPRESS_DB_PASSWORD: newrootpassword
    volumes:
      - $RESTORE_ROOT/public_html:/var/www/html
  wpcli:
    image: wordpress:cli
    depends_on: [db, wordpress]
    volumes:
      - $RESTORE_ROOT/public_html:/var/www/html
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_NAME: $DB_NAME
      WORDPRESS_DB_USER: root
      WORDPRESS_DB_PASSWORD: newrootpassword
volumes:
  test_db_data:
EOF

echo "[4/9] Start isolated test stack on :$TEST_PORT ..."
docker compose -p "$PROJECT" -f "$COMPOSE_FILE" up -d db wordpress
echo "    waiting for MariaDB ..."
for i in $(seq 1 45); do
  if docker exec "$DB_CONTAINER" mysql -u root -pnewrootpassword -e 'SELECT 1' >/dev/null 2>&1; then
    break
  fi
  [[ "$i" -eq 45 ]] && { echo "DB not ready" >&2; exit 1; }
  sleep 2
done

echo "[5/9] Import restored DB ..."
sed 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' "$RESTORE_ROOT/db.sql" \
  | docker exec -i "$DB_CONTAINER" mysql -u root -pnewrootpassword "$DB_NAME"

echo "[6/9] Rewrite URLs $LIVE_URL -> $TEST_URL ..."
wp_test search-replace "$LIVE_URL" "$TEST_URL" --all-tables-with-prefix --precise --report-changed-only
docker exec -u root "${PROJECT}-wordpress-1" \
  chown -R www-data:www-data /var/www/html/wp-content 2>/dev/null || true

echo "[7/9] Check restored WordPress options and production-domain residue ..."
for option_name in home siteurl; do
  option_value="$(wp_test option get "$option_name" | tail -n 1)"
  [[ "$option_value" == "$TEST_URL" ]] \
    || fail "Restored $option_name is '$option_value'; expected '$TEST_URL'"
  echo "[pass] $option_name=$option_value"
done

# These plugin records contain historical source-site references but are not
# public links or configuration. Clear them only in the throwaway restore so
# the residue check still catches any other remote-domain reference.
for history_option in wpvivid_backup_list ai_builder_recent_import_log_file; do
  if wp_test option get "$history_option" >/dev/null 2>&1; then
    wp_test option delete "$history_option" >/dev/null
  fi
done

forbidden_report="$(wp_test search-replace "$FORBIDDEN_HOST" "$TEST_URL" \
  --all-tables-with-prefix --precise --dry-run --report-changed-only 2>&1)" \
  || fail "Could not scan the restored database for $FORBIDDEN_HOST"
if [[ "$forbidden_report" != *"Success: 0 replacements to be made."* ]]; then
  echo "$forbidden_report" >&2
  fail "Restored database still contains $FORBIDDEN_HOST"
fi
echo "[pass] No $FORBIDDEN_HOST references remain in the restored database"

echo "[8/9] Wait for the restored site to serve HTTP ..."
for i in $(seq 1 30); do
  if curl --fail --silent --show-error --location "$TEST_URL/" -o /dev/null 2>/dev/null; then
    break
  fi
  [[ "$i" -lt 30 ]] || fail "Restored WordPress did not serve $TEST_URL/"
  sleep 1
done

echo "[9/9] Check restored pages and local media ..."
validate_page "/" 1 "homepage"
validate_page "/services/" 2 "services"
validate_page "/work-with-me/" 1 "work-with-me"
grep -Fq 'Contact Butsaba' "$RESTORE_ROOT/work-with-me.html" \
  || fail "Restored Work With Me contact action is missing"
grep -Fq 'View Résumé' "$RESTORE_ROOT/work-with-me.html" \
  || fail "Restored Work With Me résumé action is missing"
curl --fail --silent --show-error --head \
  "$TEST_URL/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf" >/dev/null \
  || fail "Restored résumé PDF does not load"

echo ""
echo "=============================================="
echo " Restore quality checks: PASS"
echo " Restore test LIVE at: $TEST_URL"
echo " Checks: DB options, forbidden host, routes, uploads, résumé, HTTP responses"
echo " Your live site on :8090 is untouched."
echo " Tear down when done:  ./scripts/test-restore.sh --down"
echo "=============================================="
