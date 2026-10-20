<?php
/** Create two unlisted OAuth pages and their WPCode discovery filters. No existing content is edited.
 * WP-CLI --user=1 eval-file install.php preflight|apply|rollback [manifest]
 * Every mutation requires a fresh, separately verified DB export. Remote deploy.sh supplies it.
 * Live application is Ryan-run only; the agent tests localhost and staging36.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}
$mode = $args[0] ?? 'preflight';
global $wpdb;
$home = untrailingslashit( home_url() );
$host = wp_parse_url( $home, PHP_URL_HOST );
$port = wp_parse_url( $home, PHP_URL_PORT );
$authority = $host . ( $port ? ':' . $port : '' );
if ( ! in_array( $authority, array( 'localhost:8090', 'staging36.thaisolitude.com', 'thaisolitude.com' ), true )
	|| ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unsupported site or mode; nothing changed.' );
}
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' )
	|| ! class_exists( 'WPCode_Snippet' ) || ! function_exists( 'wpcode' ) ) {
	WP_CLI::error( 'Requires Astra, WPCode and an administrator (--user=1).' );
}
$source = static function ( $name ) {
	$file = __DIR__ . '/' . $name;
	if ( ! is_readable( $file ) || ! filesize( $file ) ) {
		WP_CLI::error( 'Missing source: ' . $name );
	}
	// Provenance stays in source control, not in the public page or snippet.
	return trim( preg_replace( '/^(?:<!-- ref:.*?-->|\/\/ ref:.*?)\s*$/m', '', file_get_contents( $file ) ) );
};
$content = array( $source( 'home.html' ), $source( 'privacy.html' ) );
$code = trim( preg_replace( '/^<\?php\s*/', '', $source( 'unlisted.php' ) ) );
$titles = array( 'Ryan Personal Automation', 'Ryan Personal Automation — Privacy' );
$snippet_title = 'PersonalAutomationUnlistedPages';
$refresh = static function () { wpcode()->cache->cache_all_loaded_snippets(); };
$read = static function () use ( $wpdb, $titles, $content, $code, $snippet_title ) {
	$parents = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type='page' AND post_name IN ('personal-automation','personal-automation__trashed')" );
	$parent = count( $parents ) === 1 ? $parents[0] : null;
	$children = $parent ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_type='page' AND post_parent=%d AND post_name IN ('privacy','privacy__trashed')", $parent->ID ) ) : array();
	$snippets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_type='wpcode' AND post_title=%s", $snippet_title ) );
	$ids = array();
	if ( ! $parents && ! $snippets ) {
		return array( 'state' => 'ready', 'ids' => $ids );
	}
	if ( count( $parents ) !== 1 || count( $children ) !== 1 || count( $snippets ) !== 1 ) {
		return array( 'state' => 'CHANGED', 'ids' => $ids );
	}
	foreach ( array( $parent, $children[0] ) as $i => $p ) {
		$ids[] = (int) $p->ID;
		if ( 'publish' !== $p->post_status || $titles[$i] !== $p->post_title || $content[$i] !== $p->post_content
			|| ( $i ? 'privacy' : 'personal-automation' ) !== $p->post_name || '' !== $p->post_password
			|| (int) $p->post_parent !== ( $i ? (int) $parent->ID : 0 )
			|| 'yes' !== get_post_meta( $p->ID, 'surerank_settings_post_no_index', true ) ) {
			return array( 'state' => 'CHANGED', 'ids' => $ids );
		}
	}
	$s = $snippets[0];
	$ids[] = (int) $s->ID;
	$snippet = new WPCode_Snippet( (int) $s->ID );
	$exact = 'publish' === $s->post_status && $s->post_content === $code && $snippet->is_active()
		&& 'php' === $snippet->get_code_type() && 'everywhere' === $snippet->get_location()
		&& 1 === (int) $snippet->get_auto_insert() && ! get_post_meta( $s->ID, '_wpcode_conditional_logic_enabled', true );
	return array( 'state' => $exact ? 'applied' : 'CHANGED', 'ids' => $ids );
};
$now = $read();
WP_CLI::log( 'host=' . $authority . ' state=' . $now['state'] );
if ( 'CHANGED' === $now['state'] ) {
	WP_CLI::error( 'Existing objects differ from this package. Refusing to overwrite them.' );
}
if ( 'preflight' === $mode || ( 'apply' === $mode && 'applied' === $now['state'] ) ) {
	WP_CLI::success( 'Preflight passed; no writes. IDs: ' . implode( ',', $now['ids'] ) );
	return;
}
$backup_dir = realpath( (string) getenv( 'RPA_BACKUP_DIR' ) );
$db_backup = realpath( (string) getenv( 'RPA_DB_BACKUP' ) );
$root = realpath( ABSPATH );
if ( ! $backup_dir || ! is_writable( $backup_dir ) || str_starts_with( $backup_dir . '/', $root . '/' )
	|| ! $db_backup || dirname( $db_backup ) !== $backup_dir || filesize( $db_backup ) < 10000
	|| time() - filemtime( $db_backup ) > 3600 ) {
	WP_CLI::error( 'A fresh nonempty DB export in a writable private directory outside WordPress is required.' );
}
if ( 'rollback' === $mode ) {
	$path = realpath( $args[1] ?? '' );
	$snap = $path && dirname( $path ) === $backup_dir ? json_decode( file_get_contents( $path ), true ) : null;
	if ( ! $snap || ( $snap['home'] ?? '' ) !== $home || empty( $snap['complete'] ) || ( $snap['ids'] ?? array() ) !== $now['ids'] || 'applied' !== $now['state'] ) {
		WP_CLI::error( 'Rollback requires this site’s complete manifest and unchanged installed objects.' );
	}
	foreach ( array_reverse( $snap['ids'] ) as $id ) {
		if ( ! wp_delete_post( $id, true ) ) {
			WP_CLI::error( 'Could not remove object ' . $id . '; inspect before continuing.' );
		}
	}
	$refresh();
	if ( 'ready' !== $read()['state'] ) {
		WP_CLI::error( 'Rollback read-back failed.' );
	}
	WP_CLI::success( 'Removed only the two new pages and their discovery-filter snippet.' );
	return;
}
$manifest = $backup_dir . '/personal-automation-before-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false ) . '.json';
$snap = array( 'home' => $home, 'db_backup' => $db_backup, 'ids' => array(), 'complete' => false );
$save_manifest = static function () use ( $manifest, &$snap ) {
	if ( false === file_put_contents( $manifest, wp_json_encode( $snap, JSON_PRETTY_PRINT ), LOCK_EX ) ) {
		throw new RuntimeException( 'Could not write rollback manifest.' );
	}
	chmod( $manifest, 0600 );
};
try {
	$save_manifest();
	foreach ( $content as $i => $html ) {
		$id = wp_insert_post( wp_slash( array(
			'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $titles[$i], 'post_content' => $html,
			'post_name' => $i ? 'privacy' : 'personal-automation', 'post_parent' => $i ? $snap['ids'][0] : 0,
			'comment_status' => 'closed', 'ping_status' => 'closed',
			'meta_input' => array( 'surerank_settings_post_no_index' => 'yes' ),
		) ), true );
		if ( is_wp_error( $id ) || ! $id ) {
			throw new RuntimeException( 'Page creation failed.' );
		}
		$snap['ids'][] = (int) $id;
		$save_manifest();
	}
	$snippet = new WPCode_Snippet( array( 'title' => $snippet_title, 'code' => $code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true ) );
	$id = $snippet->save();
	if ( ! $id ) {
		throw new RuntimeException( 'WPCode snippet creation failed.' );
	}
	$snap['ids'][] = (int) $id;
	$save_manifest();
	$result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $code ) ), true );
	$refresh();
	if ( is_wp_error( $result ) || 'applied' !== $read()['state'] ) {
		throw new RuntimeException( 'Exact read-back failed.' );
	}
	$snap['complete'] = true;
	$save_manifest();
} catch ( Throwable $error ) {
	foreach ( array_reverse( $snap['ids'] ) as $id ) {
		wp_delete_post( $id, true );
	}
	$refresh();
	WP_CLI::error( $error->getMessage() . ' Created objects removed; state=' . $read()['state'] );
}
WP_CLI::success( 'Created two unlisted pages and their snippet. Manifest: ' . $manifest );
// ref: ./home.html, ./privacy.html, ./unlisted.php, ./deploy.sh — reviewed content and backup-first deployment
