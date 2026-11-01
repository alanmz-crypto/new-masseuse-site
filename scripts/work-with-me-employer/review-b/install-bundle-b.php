<?php
/**
 * Review Bundle B: search metadata, linked Person/LocalBusiness, tagline and
 * Home's table-by-arrangement wording. Approved by the 2026-11-01 handoff.
 * Staging38 / isolated localhost ONLY. No new share image is assigned here:
 * its brand-card preview needs Ryan's separate yes. No credentials or cleanup.
 *
 * preflight / apply / rollback FILE. All-or-nothing; snapshot exact page bytes,
 * SureRank rows, option values and snippet. Refuse rollback if ANY store moved.
 * New snippet syntax checked before any write. SureRank's own saver is used.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing target: localhost:8090 and staging38 only. Live disabled.' );
}
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! function_exists( 'wpcode' ) || ! class_exists( '\SureRank\Inc\API\Post' ) ) {
	WP_CLI::error( 'Requires Astra, WPCode, SureRank and administrator --user=1.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) { WP_CLI::error( 'Unknown mode.' ); }
$data = require __DIR__ . '/bundle-b-content.php';
foreach ( array( 'old', 'new' ) as $kind ) {
	if ( hash( 'sha256', $data[ 'snippet_' . $kind ] ) !== $data[ 'snippet_' . $kind . '_sha256' ] ) { WP_CLI::error( 'Frozen snippet hash mismatch.' ); }
	try { token_get_all( "<?php\n" . $data[ 'snippet_' . $kind ], TOKEN_PARSE ); }
	catch ( \ParseError $e ) { WP_CLI::error( 'Snippet syntax: ' . $e->getMessage() ); }
}
$hydrate = function ( $value ) use ( &$hydrate, $host ) {
	if ( is_string( $value ) ) {
		return str_replace( array( '{{HTTP}}', '{{HTTPS}}' ), array( 'http://' . $host, ( 'localhost:8090' === $host ? 'http://' : 'https://' ) . $host ), $value );
	}
	if ( is_array( $value ) ) { foreach ( $value as $k => $v ) { $value[ $k ] = $hydrate( $v ); } }
	return $value;
};
$data = $hydrate( $data );
$state_of = function ( $now, $old, $new ) { return $now === $new ? 'applied' : ( $now === $old ? 'ready' : 'CHANGED' ); };
$slugs = array_unique( array_merge( array_column( $data['general'], 'slug' ), array( 'home', 'about', 'contact' ) ) );
$pages = array();
foreach ( $slugs as $slug ) {
	$found = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	if ( count( $found ) !== 1 || $found[0]->post_status !== 'publish' || ( 'home' === $slug && (int) get_option( 'page_on_front' ) !== (int) $found[0]->ID ) ) { WP_CLI::error( 'Missing, duplicate, unpublished or wrong front page: ' . $slug ); }
	$pages[ $slug ] = $found[0];
}
$sn = get_posts( array( 'post_type' => 'wpcode', 'post_status' => 'publish', 'title' => $data['snippet_title'], 'numberposts' => -1, 'suppress_filters' => true ) );
if ( count( $sn ) !== 1 ) { WP_CLI::error( 'Need one published LocalBusinessInfo snippet.' ); }
$snippet_id = (int) $sn[0]->ID;
$api_data = array();
$states = array();
$report = function ( $label, $state ) use ( &$states ) { WP_CLI::log( $label . '=' . $state ); $states[] = $state; };
foreach ( $data['general'] as $cfg ) {
	$slug = $cfg['slug'];
	$now = get_post_meta( $pages[ $slug ]->ID, 'surerank_settings_general', true );
	$new = array_merge( is_array( $cfg['old_general'] ) ? $cfg['old_general'] : array(), $cfg['patch'] );
	$report( 'metadata ' . $slug, $state_of( $now, $cfg['old_general'], $new ) );
	$api_data[ $slug ] = $cfg['patch'];
}
foreach ( $data['schemas'] as $cfg ) {
	$slug = $cfg['slug'];
	$now = get_post_meta( $pages[ $slug ]->ID, 'surerank_settings_schemas', true );
	$report( 'Organization ' . $slug, $state_of( $now, $cfg['old'], $cfg['new'] ) );
	$api_data[ $slug ]['schemas'] = $cfg['new']['schemas'];
}
$social = get_post_meta( $pages['corporate-massage-portland']->ID, 'surerank_settings_social', true );
$report( 'Corporate https share image', $state_of( $social['facebook_image_url'] ?? null, $data['social_old_url'], $data['social_new_url'] ) );
$api_data['corporate-massage-portland']['facebook_image_url'] = $data['social_new_url'];
$report( 'global Person', $state_of( get_option( 'surerank_settings' ), $data['global_old'], $data['global_new'] ) );
$report( 'tagline', $state_of( get_option( 'blogdescription' ), $data['tagline_old'], $data['tagline_new'] ) );
$report( 'LocalBusinessInfo', $state_of( (string) $sn[0]->post_content, $data['snippet_old'], $data['snippet_new'] ) );
$hc = (string) $pages['home']->post_content;
$home_state = substr_count( $hc, $data['home_new'] ) === 1 && substr_count( $hc, $data['home_old'] ) === 0 ? 'applied' : ( substr_count( $hc, $data['home_old'] ) === 1 && substr_count( $hc, $data['home_new'] ) === 0 ? 'ready' : 'CHANGED' );
$report( 'Home by arrangement', $home_state );
$unique = array_values( array_unique( $states ) );
$state = count( $unique ) === 1 ? $unique[0] : 'CHANGED';
WP_CLI::log( 'host=' . $home . ' state=' . $state );
if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) { WP_CLI::error( 'Drift/partial state; nothing changed.' ); }
	WP_CLI::success( 'Preflight ' . $state . '; nothing changed.' ); return;
}
$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) { WP_CLI::error( 'Private writable backup directory required, outside ABSPATH.' ); }
$rows = function ( $id ) {
	$out = array(); foreach ( get_post_meta( $id ) as $k => $v ) { if ( 0 === strpos( $k, 'surerank_settings_' ) ) { $out[ $k ] = $v; } } ksort( $out ); return $out;
};
$capture = function () use ( $pages, $snippet_id, $rows ) {
	$out = array( 'options' => array( 'surerank_settings' => get_option( 'surerank_settings' ), 'blogdescription' => get_option( 'blogdescription' ) ), 'pages' => array(), 'snippet' => array( 'id' => $snippet_id, 'code' => (string) get_post_field( 'post_content', $snippet_id ) ) );
	foreach ( $pages as $slug => $p ) { $out['pages'][ $slug ] = array( 'id' => (int) $p->ID, 'content' => (string) get_post_field( 'post_content', $p->ID ), 'rows' => $rows( $p->ID ) ); }
	return $out;
};
$clear_cache = function () {
	\SureRank\Inc\Functions\Settings::clear_cache();
	if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { wpcode()->cache->cache_all_loaded_snippets(); }
};
$set_post = function ( $id, $content ) {
	$res = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true );
	if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', $id ) !== $content ) { throw new \RuntimeException( 'Post save/read-back failed: ' . $id ); }
};
$restore = function ( $snapshot ) use ( $rows, $set_post, $clear_cache ) {
	// Content first: page save hooks can rewrite metadata. Restore exact rows last.
	foreach ( $snapshot['pages'] as $p ) { if ( (string) get_post_field( 'post_content', $p['id'] ) !== $p['content'] ) { $set_post( $p['id'], $p['content'] ); } }
	foreach ( $snapshot['options'] as $key => $value ) { update_option( $key, $value ); }
	$set_post( $snapshot['snippet']['id'], $snapshot['snippet']['code'] );
	foreach ( $snapshot['pages'] as $p ) {
		foreach ( $rows( $p['id'] ) as $key => $values ) { delete_post_meta( $p['id'], $key ); }
		foreach ( $p['rows'] as $key => $values ) { foreach ( $values as $value ) { add_post_meta( $p['id'], $key, wp_slash( maybe_unserialize( $value ) ) ); } }
	}
	$clear_cache();
};
$save_snapshot = function ( $file, $snap ) {
	$value = wp_json_encode( $snap );
	if ( false === $value || false === file_put_contents( $file, $value, LOCK_EX ) || file_get_contents( $file ) !== $value || ! chmod( $file, 0600 ) ) { throw new \RuntimeException( 'Snapshot write/verification failed.' ); }
};
if ( 'rollback' === $mode ) {
	$file = $args[1] ?? '';
	$snap = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
	if ( ! is_array( $snap ) || ( $snap['bundle'] ?? '' ) !== 'review-b-v1' || ( $snap['home'] ?? '' ) !== $home || empty( $snap['complete'] ) || hash( 'sha256', wp_json_encode( $snap['old'] ) ) !== ( $snap['old_sha256'] ?? '' ) || hash( 'sha256', wp_json_encode( $snap['new'] ) ) !== ( $snap['new_sha256'] ?? '' ) || $capture() !== $snap['new'] ) { WP_CLI::error( 'Snapshot invalid or a resource changed since apply. Nothing changed.' ); }
	try { $restore( $snap['old'] ); if ( $capture() !== $snap['old'] ) { throw new \RuntimeException( 'Rollback read-back differs.' ); } }
	catch ( \Throwable $e ) { $restore( $snap['new'] ); WP_CLI::error( $e->getMessage() . '; applied state restored.' ); }
	WP_CLI::success( 'Exact rollback complete from ' . $file ); return;
}
if ( 'applied' === $state ) { WP_CLI::success( 'Already applied; nothing changed.' ); return; }
if ( 'ready' !== $state ) { WP_CLI::error( 'Not reviewed state; nothing changed.' ); }
$old = $capture();
$snap = array( 'bundle' => 'review-b-v1', 'home' => $home, 'created' => gmdate( 'c' ), 'complete' => false, 'old' => $old, 'old_sha256' => hash( 'sha256', wp_json_encode( $old ) ) );
$file = $backup_dir . '/bundle-b-before-' . gmdate( 'Ymd-His' ) . '.json';
try { $save_snapshot( $file, $snap ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
try {
	$set_post( $pages['home']->ID, str_replace( $data['home_old'], $data['home_new'], $hc ) );
	update_option( 'surerank_settings', $data['global_new'] );
	update_option( 'blogdescription', $data['tagline_new'] );
	$clear_cache();
	foreach ( $api_data as $slug => $values ) { \SureRank\Inc\API\Post::update_post_meta_common( (int) $pages[ $slug ]->ID, $values ); }
	$set_post( $snippet_id, $data['snippet_new'] ); $clear_cache();
	$new = $capture();
	foreach ( $new['pages'] as $slug => $p ) {
		$expected = 'home' === $slug ? str_replace( $data['home_old'], $data['home_new'], $old['pages'][ $slug ]['content'] ) : $old['pages'][ $slug ]['content'];
		if ( $p['content'] !== $expected ) { throw new \RuntimeException( 'Unexpected page-content change: ' . $slug ); }
	}
	if ( $new['options']['surerank_settings'] !== $data['global_new'] || $new['options']['blogdescription'] !== $data['tagline_new'] || $new['snippet']['code'] !== $data['snippet_new'] ) { throw new \RuntimeException( 'Option/snippet read-back differs.' ); }
	foreach ( $data['general'] as $cfg ) { $g = get_post_meta( $pages[ $cfg['slug'] ]->ID, 'surerank_settings_general', true ); foreach ( $cfg['patch'] as $k => $v ) { if ( ( $g[ $k ] ?? null ) !== $v ) { throw new \RuntimeException( 'Metadata differs: ' . $cfg['slug'] ); } } }
	foreach ( $data['schemas'] as $cfg ) { if ( get_post_meta( $pages[ $cfg['slug'] ]->ID, 'surerank_settings_schemas', true ) !== $cfg['new'] ) { throw new \RuntimeException( 'Organization rows differ: ' . $cfg['slug'] ); } }
	$s = get_post_meta( $pages['corporate-massage-portland']->ID, 'surerank_settings_social', true );
	if ( ( $s['facebook_image_url'] ?? '' ) !== $data['social_new_url'] || ( $s['facebook_image_id'] ?? null ) !== ( $social['facebook_image_id'] ?? null ) ) { throw new \RuntimeException( 'Corporate share image differs.' ); }
	$snap['new'] = $new; $snap['new_sha256'] = hash( 'sha256', wp_json_encode( $new ) ); $snap['complete'] = true; $save_snapshot( $file, $snap );
} catch ( \Throwable $e ) {
	try { $restore( $old ); if ( $capture() !== $old ) { throw new \RuntimeException( 'Restored bytes differ.' ); } }
	catch ( \Throwable $undo_error ) { WP_CLI::error( $e->getMessage() . '; restore also failed: ' . $undo_error->getMessage() . '. Stop; snapshot ' . $file ); }
	WP_CLI::error( $e->getMessage() . '; all stores restored exactly.' );
}
WP_CLI::success( 'Applied Bundle B; share-card assignment remains pending. Before-snapshot: ' . $file );
