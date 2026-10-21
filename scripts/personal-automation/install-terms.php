<?php
/** Add the terms page, link it from the app homepage, and extend its existing visibility snippet.
 * Requires the exact original two-page package. Uses the same private-backup contract as install.php.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
global $wpdb;
$mode = $args[0] ?? 'preflight';
$home = untrailingslashit( home_url() );
$authority = wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $authority, array( 'localhost:8090', 'staging36.thaisolitude.com', 'thaisolitude.com' ), true )
	|| ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || ! current_user_can( 'manage_options' )
	|| ! current_user_can( 'unfiltered_html' ) || ! function_exists( 'wpcode' ) ) {
	WP_CLI::error( 'Unsupported site, mode or permissions.' );
}
$source = static function ( $name ) {
	$file = __DIR__ . '/' . $name;
	if ( ! is_readable( $file ) || ! filesize( $file ) ) { WP_CLI::error( 'Missing source: ' . $name ); }
	return trim( preg_replace( '/^(?:<!-- ref:.*?-->|\/\/ ref:.*?)\s*$/m', '', file_get_contents( $file ) ) );
};
$old_home = $source( 'home.html' );
$new_home = $old_home . "\n\n<!-- wp:paragraph -->\n<p><a href=\"/personal-automation/terms/\">Read the Ryan Personal Automation terms of service</a>.</p>\n<!-- /wp:paragraph -->";
$old_code = trim( preg_replace( '/^<\?php\s*/', '', $source( 'unlisted.php' ) ) );
$new_code = str_replace( "'personal-automation/privacy' )", "'personal-automation/privacy', 'personal-automation/terms' )", $old_code, $replaced );
if ( 1 !== $replaced ) { WP_CLI::error( 'Unexpected original visibility source.' ); }
$terms_content = $source( 'terms.html' );
$parent = get_page_by_path( 'personal-automation' );
$privacy = get_page_by_path( 'personal-automation/privacy' );
$snippets = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='wpcode' AND post_title=%s", 'PersonalAutomationUnlistedPages' ) );
if ( ! $parent || ! $privacy || 'publish' !== $parent->post_status || 'publish' !== $privacy->post_status
	|| 'Ryan Personal Automation' !== $parent->post_title || $privacy->post_content !== $source( 'privacy.html' ) || count( $snippets ) !== 1 ) {
	WP_CLI::error( 'Requires the reviewed homepage, privacy page and visibility snippet.' );
}
$parent_id = (int) $parent->ID;
$snippet_id = (int) $snippets[0];
$read = static function () use ( $wpdb, $parent_id, $snippet_id, $old_home, $new_home, $old_code, $new_code, $terms_content ) {
	$p = get_post( $parent_id ); $s = get_post( $snippet_id );
	$found = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_type='page' AND post_parent=%d AND post_name IN ('terms','terms__trashed')", $parent_id ) );
	$snippet = new WPCode_Snippet( $snippet_id );
	$active = $s && 'publish' === $s->post_status && $snippet->is_active() && 'php' === $snippet->get_code_type()
		&& 'everywhere' === $snippet->get_location() && 1 === (int) $snippet->get_auto_insert()
		&& ! get_post_meta( $snippet_id, '_wpcode_conditional_logic_enabled', true );
	if ( $active && ! $found && $p->post_content === $old_home && $s->post_content === $old_code ) {
		return array( 'state' => 'ready', 'terms_id' => 0 );
	}
	$t = count( $found ) === 1 ? $found[0] : null;
	if ( $active && $t && $p->post_content === $new_home && $s->post_content === $new_code
		&& 'publish' === $t->post_status && 'terms' === $t->post_name && '' === $t->post_password
		&& 'Ryan Personal Automation — Terms of Service' === $t->post_title && $terms_content === $t->post_content
		&& 'yes' === get_post_meta( $t->ID, 'surerank_settings_post_no_index', true ) ) {
		return array( 'state' => 'applied', 'terms_id' => (int) $t->ID );
	}
	return array( 'state' => 'CHANGED', 'terms_id' => 0 );
};
$now = $read();
WP_CLI::log( 'host=' . $authority . ' terms-state=' . $now['state'] );
if ( 'CHANGED' === $now['state'] ) { WP_CLI::error( 'Objects differ from the reviewed package; refusing to overwrite.' ); }
if ( 'preflight' === $mode || ( 'apply' === $mode && 'applied' === $now['state'] ) ) { WP_CLI::success( 'No writes. Terms ID: ' . $now['terms_id'] ); return; }
$dir = realpath( (string) getenv( 'RPA_BACKUP_DIR' ) );
$dump = realpath( (string) getenv( 'RPA_DB_BACKUP' ) );
if ( ! $dir || ! is_writable( $dir ) || str_starts_with( $dir . '/', realpath( ABSPATH ) . '/' )
	|| ! $dump || dirname( $dump ) !== $dir || filesize( $dump ) < 10000 || time() - filemtime( $dump ) > 3600 ) {
	WP_CLI::error( 'A fresh DB export in a private writable backup directory is required.' );
}
$set = static function ( $id, $content ) {
	$result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true );
	if ( is_wp_error( $result ) || ! $result ) { throw new RuntimeException( 'Could not update object ' . $id ); }
};
$restore = static function ( $id ) use ( $parent_id, $snippet_id, $old_home, $old_code, $set ) {
	$set( $parent_id, $old_home ); $set( $snippet_id, $old_code );
	if ( $id && ! wp_delete_post( $id, true ) ) { throw new RuntimeException( 'Could not delete the new terms page.' ); }
	wpcode()->cache->cache_all_loaded_snippets();
};
if ( 'rollback' === $mode ) {
	$file = realpath( $args[1] ?? '' );
	$snap = $file && dirname( $file ) === $dir ? json_decode( file_get_contents( $file ), true ) : null;
	if ( ! $snap || ( $snap['home'] ?? '' ) !== $home || empty( $snap['complete'] ) || 'applied' !== $now['state']
		|| ( $snap['terms_id'] ?? 0 ) !== $now['terms_id'] || ( $snap['parent_id'] ?? 0 ) !== $parent_id || ( $snap['snippet_id'] ?? 0 ) !== $snippet_id ) {
		WP_CLI::error( 'Rollback requires the matching completed manifest and unchanged installed objects.' );
	}
	$restore( $now['terms_id'] );
	if ( 'ready' !== $read()['state'] ) { WP_CLI::error( 'Rollback read-back failed.' ); }
	WP_CLI::success( 'Terms page removed; original homepage and visibility snippet restored.' ); return;
}
$manifest = $dir . '/personal-automation-terms-before-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false ) . '.json';
$snap = array( 'home' => $home, 'db_backup' => $dump, 'parent_id' => $parent_id, 'snippet_id' => $snippet_id, 'terms_id' => 0, 'complete' => false );
$save = static function () use ( &$snap, $manifest ) {
	if ( false === file_put_contents( $manifest, wp_json_encode( $snap, JSON_PRETTY_PRINT ), LOCK_EX ) ) { throw new RuntimeException( 'Cannot save manifest.' ); }
	chmod( $manifest, 0600 );
};
try {
	$save();
	$id = wp_insert_post( wp_slash( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Ryan Personal Automation — Terms of Service',
		'post_name' => 'terms', 'post_parent' => $parent_id, 'post_content' => $terms_content, 'comment_status' => 'closed', 'ping_status' => 'closed',
		'meta_input' => array( 'surerank_settings_post_no_index' => 'yes' ) ) ), true );
	if ( is_wp_error( $id ) || ! $id ) { throw new RuntimeException( 'Could not create terms page.' ); }
	$snap['terms_id'] = (int) $id; $save();
	$set( $parent_id, $new_home ); $set( $snippet_id, $new_code ); wpcode()->cache->cache_all_loaded_snippets();
	if ( 'applied' !== $read()['state'] ) { throw new RuntimeException( 'Exact read-back failed.' ); }
	$snap['complete'] = true; $save();
} catch ( Throwable $error ) {
	$restore( $snap['terms_id'] );
	WP_CLI::error( $error->getMessage() . ' Reverted; state=' . $read()['state'] );
}
WP_CLI::success( 'Created terms page, added its homepage link and extended visibility controls. Manifest: ' . $manifest );
// ref: ./install.php, ./home.html, ./privacy.html, ./unlisted.php, ./terms.html — guarded extension of the original package
