#!/usr/bin/env bash
# Build the disposable WordPress stack that rehearse.py drives. Nothing here touches a real site.
# Usage: rehearsal-up.sh FRESH_DIR WPCODE_DIR ASTRA_DIR SPECTRA_DIR WORDPRESS_CORE_DIR
# All supplied trees are local. The stack's private network has no Internet route.
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
dir=${1:?}; wpcode=${2:?}; astra=${3:?}; spectra=${4:?}; core=${5:?}
[ ! -e "$dir/docker-compose.yml" ] || { echo 'Use a fresh stack directory' >&2; exit 1; }
[ -f "$wpcode/ihaf.php" ] && [ -f "$spectra/ultimate-addons-for-gutenberg.php" ] && [ -f "$astra/style.css" ] && [ -f "$core/wp-includes/version.php" ]
if [ -n "$(docker ps -aq --filter label=com.docker.compose.project=peek-prod-rehearsal)" ]; then
  echo 'A rehearsal stack already exists' >&2; exit 1
fi
mkdir -p "$dir/backups" "$dir/archives"; chmod 777 "$dir/backups" "$dir/archives"
dir=$(cd "$dir" && pwd)
cat > "$dir/docker-compose.yml" <<YML
name: peek-prod-rehearsal
services:
  db:
    image: mariadb:10.6@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
    environment: {MYSQL_ROOT_PASSWORD: r, MYSQL_DATABASE: wp, MYSQL_USER: wp, MYSQL_PASSWORD: wp}
  wordpress:
    image: wordpress:php8.5-apache@sha256:c12bed9c5af9d76261221c2af3a6216a074dd80b4fc2459588b3cbf2dd8e1798
    depends_on: [db]
    environment: &env
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_NAME: wp
      WORDPRESS_DB_USER: wp
      WORDPRESS_DB_PASSWORD: wp
      WORDPRESS_CONFIG_EXTRA: "define('WP_HTTP_BLOCK_EXTERNAL',true); define('DISABLE_WP_CRON',true); define('WP_HOME','http://thaisolitude.com'); define('WP_SITEURL','http://thaisolitude.com');"
    volumes: ["wpdata:/var/www/html"]
  wpcli:
    image: wordpress:cli-php8.5@sha256:52d8832dc8b90022c22fe963e696e4107d86f68b9b891e9624d82fec94fdc3b7
    user: "33:33"
    depends_on: [db, wordpress]
    environment: *env
    working_dir: /home/customer/www/thaisolitude.com/public_html
    volumes: ["wpdata:/home/customer/www/thaisolitude.com/public_html", "$dir/backups:/backups", "$here/..:/pkg:ro", "$dir/archives:/archives"]
networks:
  default:
    internal: true
volumes:
  wpdata:
YML
python3 "$here/../package.py" "$dir/archives/peek-v2.zip"
unzip -p "$dir/archives/peek-v2.zip" wpcode/before.txt > "$dir/archives/before.txt"
cat > "$dir/archives/seed.php" <<'PHP'
<?php
$seed = static function ( $id, $title, $code ) {
    $saved = wp_insert_post( wp_slash( array( 'import_id' => $id, 'post_type' => 'wpcode', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $code ) ), true );
    if ( $saved !== $id ) { WP_CLI::error( 'Companion fixture ID differs.' ); }
    update_post_meta( $id, '_wpcode_auto_insert', 1 );
    update_post_meta( $id, '_wpcode_priority', 10 );
    wp_set_object_terms( $id, 'everywhere', 'wpcode_location' );
    wp_set_object_terms( $id, 'php', 'wpcode_type' );
};
$seed( 1338, 'TSSectionGuide v1', substr( file_get_contents('/archives/section-guide.php'), 6 ) );
$data_id = 1339;
foreach ( array('thai-massage','therapeutic-massage','deep-tissue-massage','chair-massage') as $slug ) {
    $data = json_decode( file_get_contents( '/archives/' . $slug . '.json' ), true, 512, JSON_THROW_ON_ERROR );
    $code = '// ts-peek-nav-data:' . $slug . "\nadd_filter( 'ts_pk_pages', static function ( \$pages ) {\n    if ( is_page( " . var_export( $slug, true ) . " ) ) {\n        \$pages[" . var_export( $slug, true ) . '] = ' . var_export( $data, true ) . ";\n    }\n    return \$pages;\n} );\n";
    $seed( $data_id++, 'TSPeekData ' . $slug . ' visitor release v1', $code );
}
$id = wp_insert_post( wp_slash( array( 'import_id' => 1343, 'post_type' => 'wpcode', 'post_status' => 'publish', 'post_title' => 'TSPeekNav visitor release v1', 'post_content' => file_get_contents( '/archives/before.txt' ) ) ), true );
if ( is_wp_error( $id ) ) { WP_CLI::error( $id->get_error_message() ); }
update_post_meta( $id, '_wpcode_auto_insert', 1 ); update_post_meta( $id, '_wpcode_priority', 10 ); update_post_meta( $id, '_wpcode_note', 'seeded' );
wp_set_object_terms( $id, 'php', 'wpcode_type' ); wp_set_object_terms( $id, 'everywhere', 'wpcode_location' );
wpcode()->cache->cache_all_loaded_snippets();
WP_CLI::success( "seeded $id sha=" . hash( 'sha256', get_post( $id )->post_content ) );
PHP
cp "$here/../../page-navigation/section-guide.php" "$dir/archives/"
cp "$here"/../../link-ideas/peek-release/payload/data/*.json "$dir/archives/"
touch "$dir/archives/real-spectra"
cd "$dir"
docker compose run --rm -T --no-deps -v "$core:/fixture:ro" --entrypoint sh wordpress -ec 'cp -a /fixture/. /var/www/html/; cp /usr/src/wordpress/wp-config-docker.php /var/www/html/; chown -R www-data:www-data /var/www/html'
wp() { docker compose run --rm -T wpcli wp "$@" 2>&1 | grep -v '^ Container'; }
docker compose up -d db wordpress
for _ in $(seq 1 60); do docker compose exec -T wordpress test -f /var/www/html/wp-config.php && break; sleep 2; done
sleep 8
wp core install --url=http://thaisolitude.com --title=rehearsal --admin_user=admin --admin_password="$(head -c12 /dev/urandom | base64)" --admin_email=a@example.com --skip-email
c=peek-prod-rehearsal-wordpress-1
docker cp "$astra" "$c:/var/www/html/wp-content/themes/astra"
docker cp "$wpcode" "$c:/var/www/html/wp-content/plugins/insert-headers-and-footers"
docker cp "$spectra" "$c:/var/www/html/wp-content/plugins/ultimate-addons-for-gutenberg"
docker exec "$c" chown -R www-data:www-data /var/www/html/wp-content
wp theme activate astra
wp plugin activate insert-headers-and-footers ultimate-addons-for-gutenberg
wp eval-file /archives/seed.php --user=1
