<?php
/** Update only the three existing OAuth pages; preserve URLs and visibility controls. */
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
$old_home = $source( 'home.html' ) . "\n\n<!-- wp:paragraph -->\n<p><a href=\"/personal-automation/terms/\">Read the Ryan Personal Automation terms of service</a>.</p>\n<!-- /wp:paragraph -->";
$pages = array(
	'personal-automation' => array( 'title' => 'Ryan Personal Automation', 'before' => $old_home, 'after' => $source( 'home-tools.html' ) ),
	'personal-automation/privacy' => array( 'title' => 'Ryan Personal Automation — Privacy', 'before' => $source( 'privacy.html' ), 'after' => $source( 'privacy-tools.html' ) ),
	'personal-automation/terms' => array( 'title' => 'Ryan Personal Automation — Terms of Service', 'before' => $source( 'terms.html' ), 'after' => $source( 'terms-tools.html' ) ),
);
foreach ( $pages as $path => &$spec ) {
	$post = get_page_by_path( $path );
	if ( ! $post ) { WP_CLI::error( 'Missing OAuth page: ' . $path ); }
	$spec['id'] = (int) $post->ID;
}
unset( $spec );
$expected_code = trim( preg_replace( '/^<\?php\s*/', '', $source( 'unlisted.php' ) ) );
$expected_code = str_replace( "'personal-automation/privacy' )", "'personal-automation/privacy', 'personal-automation/terms' )", $expected_code, $replaced );
if ( 1 !== $replaced ) { WP_CLI::error( 'Unexpected original visibility source.' ); }
$snippets = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type='wpcode' AND post_title=%s", 'PersonalAutomationUnlistedPages' ) );
if ( count( $snippets ) !== 1 ) { WP_CLI::error( 'Requires exactly one existing visibility snippet.' ); }
$snippet_id = (int) $snippets[0];
$read = static function () use ( $pages, $snippet_id, $expected_code ) {
	$s = get_post( $snippet_id ); $snippet = new WPCode_Snippet( $snippet_id );
	if ( ! $s || 'publish' !== $s->post_status || $s->post_content !== $expected_code || ! $snippet->is_active()
		|| 'php' !== $snippet->get_code_type() || 'everywhere' !== $snippet->get_location() || 1 !== (int) $snippet->get_auto_insert()
		|| get_post_meta( $snippet_id, '_wpcode_conditional_logic_enabled', true ) ) { return 'CHANGED'; }
	$before = true; $after = true;
	foreach ( $pages as $path => $spec ) {
		$p = get_page_by_path( $path );
		if ( ! $p || (int) $p->ID !== $spec['id'] || 'publish' !== $p->post_status || '' !== $p->post_password
			|| $p->post_title !== $spec['title'] || 'yes' !== get_post_meta( $p->ID, 'surerank_settings_post_no_index', true ) ) { return 'CHANGED'; }
		$before = $before && $p->post_content === $spec['before'];
		$after = $after && $p->post_content === $spec['after'];
	}
	return $before ? 'ready' : ( $after ? 'applied' : 'CHANGED' );
};
$state = $read();
WP_CLI::log( 'host=' . $authority . ' disclosures-state=' . $state );
if ( 'CHANGED' === $state ) { WP_CLI::error( 'Pages or visibility controls differ; refusing to overwrite.' ); }
if ( 'preflight' === $mode || ( 'apply' === $mode && 'applied' === $state ) ) {
	WP_CLI::success( 'No writes. Page IDs: ' . implode( ',', array_column( $pages, 'id' ) ) ); return;
}
$dir = realpath( (string) getenv( 'RPA_BACKUP_DIR' ) );
$dump = realpath( (string) getenv( 'RPA_DB_BACKUP' ) );
if ( ! $dir || ! is_writable( $dir ) || str_starts_with( $dir . '/', realpath( ABSPATH ) . '/' )
	|| ! $dump || dirname( $dump ) !== $dir || filesize( $dump ) < 10000 || time() - filemtime( $dump ) > 3600 ) {
	WP_CLI::error( 'A fresh DB export in a private writable backup directory is required.' );
}
$set = static function ( $side ) use ( $pages ) {
	foreach ( $pages as $spec ) {
		$result = wp_update_post( wp_slash( array( 'ID' => $spec['id'], 'post_content' => $spec[$side] ) ), true );
		if ( is_wp_error( $result ) || ! $result ) { throw new RuntimeException( 'Could not update page ' . $spec['id'] ); }
	}
};
if ( 'rollback' === $mode ) {
	$file = realpath( $args[1] ?? '' );
	$snap = $file && dirname( $file ) === $dir ? json_decode( file_get_contents( $file ), true ) : null;
	if ( ! $snap || ( $snap['home'] ?? '' ) !== $home || ( $snap['release'] ?? '' ) !== 'disclosures'
		|| empty( $snap['complete'] ) || ( $snap['pages'] ?? null ) !== $pages || 'applied' !== $state ) {
		WP_CLI::error( 'Rollback requires the matching completed manifest and unchanged installed pages.' );
	}
	try { $set( 'before' ); if ( 'ready' !== $read() ) { throw new RuntimeException( 'Rollback read-back failed.' ); } }
	catch ( Throwable $error ) { $set( 'after' ); WP_CLI::error( $error->getMessage() . ' Restored the installed release.' ); }
	WP_CLI::success( 'Restored the previous three page texts; URLs and visibility controls preserved.' ); return;
}
$manifest = $dir . '/personal-automation-disclosures-before-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false ) . '.json';
$snap = array( 'release' => 'disclosures', 'home' => $home, 'db_backup' => $dump, 'pages' => $pages, 'complete' => false );
$save = static function () use ( &$snap, $manifest ) {
	if ( false === file_put_contents( $manifest, wp_json_encode( $snap, JSON_PRETTY_PRINT ), LOCK_EX ) ) { throw new RuntimeException( 'Cannot save manifest.' ); }
	chmod( $manifest, 0600 );
};
try {
	$save(); $set( 'after' );
	if ( 'applied' !== $read() ) { throw new RuntimeException( 'Exact read-back failed.' ); }
	$snap['complete'] = true; $save();
} catch ( Throwable $error ) {
	$set( 'before' ); WP_CLI::error( $error->getMessage() . ' Reverted; state=' . $read() );
}
WP_CLI::success( 'Updated the three OAuth disclosures; URLs and visibility controls preserved. Manifest: ' . $manifest );
// ref: ./home.html, ./privacy.html, ./terms.html, ./unlisted.php — frozen installed preimage
// ref: ./home-tools.html, ./privacy-tools.html, ./terms-tools.html — expanded disclosures
