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
set -euo pipefail

SITE_DIR="${SITE_DIR:-$HOME/WordPress/new-masseuse-site}"
cd "$SITE_DIR"

export RESTIC_REPOSITORY="${MASSEUSE_RESTIC_REPO:-$SITE_DIR/backups/restic-repo}"
export RESTIC_PASSWORD_FILE="${MASSEUSE_RESTIC_PASSWORD_FILE:-$SITE_DIR/backups/restic.password}"

PROJECT="masseuse-restore-test"
TEST_PORT="${TEST_PORT:-8091}"
TEST_URL="http://localhost:$TEST_PORT"
LIVE_URL="http://localhost:8090"
RESTORE_ROOT="/tmp/masseuse-restore-stack"
COMPOSE_FILE="$RESTORE_ROOT/docker-compose.yml"
DB_CONTAINER="${PROJECT}-db-1"
DB_NAME="new_masseuse_db"

teardown() {
  echo "Tearing down test stack '$PROJECT' ..."
  if [[ -f "$COMPOSE_FILE" ]]; then
    docker compose -p "$PROJECT" -f "$COMPOSE_FILE" down -v 2>/dev/null || true
  else
    docker compose -p "$PROJECT" down -v 2>/dev/null || true
  fi
  rm -rf "$RESTORE_ROOT"
  echo "Done. Live :8090 stack untouched."
}

if [[ "${1:-}" == "--down" ]]; then
  teardown
  exit 0
fi

echo "=============================================="
echo " Restore TEST stack (throwaway, :$TEST_PORT)"
echo " Live :8090 stack is NOT touched."
echo "=============================================="

rm -rf "$RESTORE_ROOT"
mkdir -p "$RESTORE_ROOT"

echo "[1/6] Restore files snapshot -> $RESTORE_ROOT/public_html ..."
restic restore latest --tag files --target "$RESTORE_ROOT/extract" >/dev/null
# restic restores the absolute source path; relocate to a clean public_html
SRC="$RESTORE_ROOT/extract$SITE_DIR/public_html"
[[ -d "$SRC" ]] || SRC="$(dirname "$(find "$RESTORE_ROOT/extract" -maxdepth 8 -name wp-load.php | head -1)")"
mv "$SRC" "$RESTORE_ROOT/public_html"

echo "[2/6] Restore DB snapshot -> $RESTORE_ROOT/db.sql ..."
restic dump latest --tag db "$DB_NAME.sql" > "$RESTORE_ROOT/db.sql"

echo "[3/6] Write throwaway wp-config-local.php + compose file ..."
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

echo "[4/6] Start isolated test stack on :$TEST_PORT ..."
docker compose -p "$PROJECT" -f "$COMPOSE_FILE" up -d db wordpress
echo "    waiting for MariaDB ..."
for i in $(seq 1 45); do
  if docker exec "$DB_CONTAINER" mysql -u root -pnewrootpassword -e 'SELECT 1' >/dev/null 2>&1; then
    break
  fi
  [[ "$i" -eq 45 ]] && { echo "DB not ready" >&2; exit 1; }
  sleep 2
done

echo "[5/6] Import restored DB ..."
sed 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' "$RESTORE_ROOT/db.sql" \
  | docker exec -i "$DB_CONTAINER" mysql -u root -pnewrootpassword "$DB_NAME"

echo "[6/6] Rewrite URLs $LIVE_URL -> $TEST_URL ..."
docker compose -p "$PROJECT" -f "$COMPOSE_FILE" run --rm --no-deps wpcli \
  wp search-replace "$LIVE_URL" "$TEST_URL" --all-tables --allow-root --quiet 2>/dev/null || true
docker exec -u root "${PROJECT}-wordpress-1" \
  chown -R www-data:www-data /var/www/html/wp-content 2>/dev/null || true

echo ""
echo "=============================================="
echo " Restore test LIVE at: $TEST_URL"
echo " Verify:  curl -sI $TEST_URL/ | head -3"
echo " Your live site on :8090 is untouched."
echo " Tear down when done:  ./scripts/test-restore.sh --down"
echo "=============================================="
