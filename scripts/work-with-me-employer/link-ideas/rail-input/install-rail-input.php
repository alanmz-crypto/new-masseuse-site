<?php
/**
 * Repair keyboard/focus input on the existing staging39 Rail practice page.
 * Exact v1 -> current page-content transition; never creates or deletes a page.
 * preflight | apply | rollback FILE. The push wrapper backs up every write.
 * Preserve the complete post, metadata, taxonomy and dates except post_content.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
if ( ! in_array( $home, array( 'http://localhost:8090', 'http://staging39.thaisolitude.com', 'https://staging39.thaisolitude.com' ), true ) ) { WP_CLI::error( 'Only exact localhost:8090 and staging39 origins are allowed; nothing changed.' ); }
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) { WP_CLI::error( 'Requires Astra and an administrator (--user=1).' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array(); $mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || count( $args ) > ( 'rollback' === $mode ? 2 : 1 ) || ( 'rollback' === $mode && count( $args ) !== 2 ) ) { WP_CLI::error( 'Use preflight, apply, or rollback FILE.' ); }
$old_html = file_get_contents( __DIR__ . '/../rail-ideas-v1.html' );
$new_html = file_get_contents( __DIR__ . '/../rail-ideas.html' );
if ( false === $old_html || hash( 'sha256', $old_html ) !== '26f3472e8032cb89d99b923c23999fc6281537a32d0eed07a54f61347af8d38d' || false === $new_html || $old_html === $new_html ) { WP_CLI::error( 'Frozen v1 is missing/changed or the new page does not differ; nothing changed.' ); }
$wrap = static fn( $html ) => "<!-- wp:html -->\n" . rtrim( $html ) . "\n<!-- /wp:html -->";
$old_content = $wrap( $old_html ); $new_content = $wrap( $new_html );
if ( hash( 'sha256', $old_content ) !== '0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748' ) { WP_CLI::error( 'Frozen stored v1 bytes differ.' ); }
foreach ( array( $old_html, $new_html ) as $html ) {
	if ( substr_count( $html, '<script>' ) !== 1 || substr_count( $html, '</script>' ) !== 1 || substr_count( $html, '<style>' ) !== 1 || false !== strpos( $html, '<!-- wp:' ) ) { WP_CLI::error( 'The page must remain the reviewed single HTML block.' ); }
	$js = explode( '</script>', explode( '<script>', $html )[1] )[0];
	if ( '' === trim( $js ) || false !== strpos( $js, '<' ) ) { WP_CLI::error( 'Page JavaScript contains a shortcode-scanner less-than sign.' ); }
}
// This is an input-only change: all markup, styles and visible wording stay exact.
$without_script = static fn( $html ) => preg_replace( '#<script>.*?</script>#s', '<script></script>', $html );
if ( $without_script( $old_html ) !== $without_script( $new_html ) ) { WP_CLI::error( 'Markup, styles or visible wording changed outside the reviewed input script.' ); }
$manifest = array( 'old_content_sha256' => hash( 'sha256', $old_content ), 'new_content_sha256' => hash( 'sha256', $new_content ) );
$taxonomies = get_object_taxonomies( 'page' ); sort( $taxonomies );
$capture = static function () use ( $old_content, $new_content, $taxonomies ) {
	$found = array();
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => array_values( get_post_stati() ), 'numberposts' => -1, 'suppress_filters' => true ) ) as $post ) {
		if ( (int) $post->ID === 1348 || $post->post_name === 'rail-nav-ideas' || $post->post_title === 'Side rail ideas' || $post->post_content === $old_content || $post->post_content === $new_content || '1' === (string) get_post_meta( $post->ID, '_ts_rail_ideas', true ) ) { $found[] = (int) $post->ID; }
	}
	if ( $found !== array( 1348 ) ) { throw new \RuntimeException( 'Missing, replaced or duplicate Rail practice page; expected only reviewed page #1348.' ); }
	$post = get_post( 1348, ARRAY_A, 'raw' ); $meta = get_post_meta( 1348 ); ksort( $meta ); $terms = array();
	if ( ! is_array( $post ) ) { throw new \RuntimeException( 'Rail practice page is unreadable.' ); }
	foreach ( $taxonomies as $taxonomy ) { $values = wp_get_object_terms( 1348, $taxonomy, array( 'fields' => 'slugs' ) ); if ( is_wp_error( $values ) ) { throw new \RuntimeException( 'Page taxonomy is unreadable.' ); } sort( $values ); $terms[ $taxonomy ] = $values; }
	return array( 'id' => 1348, 'post' => $post, 'meta' => $meta, 'terms' => $terms );
};
$matches = static function ( $record, $content ) {
	return is_array( $record ) && ( $record['id'] ?? null ) === 1348 && (int) ( $record['post']['ID'] ?? 0 ) === 1348 && ( $record['post']['post_type'] ?? '' ) === 'page' && ( $record['post']['post_status'] ?? '' ) === 'publish' && ( $record['post']['post_name'] ?? '' ) === 'rail-nav-ideas' && ( $record['post']['post_title'] ?? '' ) === 'Side rail ideas' && ( $record['post']['post_modified_gmt'] ?? '' ) === '2026-11-08 02:42:14' && ( $record['post']['post_content'] ?? '' ) === $content && ( $record['meta']['_ts_rail_ideas'] ?? null ) === array( '1' ) && ( $record['meta']['site-post-title'] ?? null ) === array( 'disabled' ) && is_array( $record['terms'] ?? null );
};
try { $current = $capture(); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
$state = $matches( $current, $old_content ) ? 'ready' : ( $matches( $current, $new_content ) ? 'applied' : 'CHANGED' );
WP_CLI::log( 'home=' . $home . ' rail-input=' . $state . ' page=1348 bytes=' . strlen( $current['post']['post_content'] ) . ' content_sha256=' . hash( 'sha256', $current['post']['post_content'] ) . ' full_state_sha256=' . hash( 'sha256', wp_json_encode( $current ) ) );
if ( 'CHANGED' === $state ) { WP_CLI::error( 'Page ownership, identity, content or recorded date changed; nothing changed.' ); }
if ( 'preflight' === $mode ) { WP_CLI::success( 'Preflight ' . $state . '; title/ownership verified; full post/meta/terms captured read-only.' ); return; }
if ( 'apply' === $mode && 'applied' === $state ) { WP_CLI::success( 'Already applied; nothing changed.' ); return; }
$backup_dir = getenv( 'WWM_BACKUP_DIR' ); $private = $backup_dir ? realpath( $backup_dir ) : false; $webroot = realpath( ABSPATH );
if ( ! $private || '/' !== $backup_dir[0] || ! is_dir( $private ) || ! is_writable( $private ) || ! $webroot || $private === $webroot || 0 === strpos( $private . '/', rtrim( $webroot, '/' ) . '/' ) ) { WP_CLI::error( 'WWM_BACKUP_DIR must be an existing writable directory outside the web root.' ); }
$save = static function ( $file, $snapshot ) {
	$json = wp_json_encode( $snapshot ); $mask = umask( 0077 ); $ok = false !== $json && false !== file_put_contents( $file, $json, LOCK_EX ); umask( $mask );
	if ( ! $ok || ! chmod( $file, 0600 ) || file_get_contents( $file ) !== $json ) { throw new \RuntimeException( 'Private snapshot save/read-back failed.' ); }
};
$write = static function ( $before, $after ) use ( $capture ) {
	$allowed = $before; $allowed['post']['post_content'] = $after['post']['post_content'];
	if ( $allowed !== $after || $capture() !== $before ) { throw new \RuntimeException( 'Complete page state changed before the content-only write.' ); }
	$dates = static function ( $data, $postarr ) use ( $before ) { if ( (int) ( $postarr['ID'] ?? 0 ) === 1348 && ( $data['post_type'] ?? '' ) === 'page' ) { $data['post_modified'] = $before['post']['post_modified']; $data['post_modified_gmt'] = $before['post']['post_modified_gmt']; } return $data; };
	// Keep existing revisions intact; the complete private snapshot is this update's rollback.
	$revisions = static fn( $count, $post ) => (int) $post->ID === 1348 ? 0 : $count;
	add_filter( 'wp_insert_post_data', $dates, PHP_INT_MAX, 2 ); add_filter( 'wp_revisions_to_keep', $revisions, PHP_INT_MAX, 2 );
	try { $result = wp_update_post( wp_slash( array( 'ID' => 1348, 'post_content' => $after['post']['post_content'] ) ), true ); }
	finally { remove_filter( 'wp_insert_post_data', $dates, PHP_INT_MAX ); remove_filter( 'wp_revisions_to_keep', $revisions, PHP_INT_MAX ); }
	if ( is_wp_error( $result ) || ! $result || $capture() !== $after ) { throw new \RuntimeException( 'Content save/full-state read-back failed.' ); }
};
$transition = static function ( $before, $after ) use ( $capture, $write ) {
	try { $write( $before, $after ); }
	catch ( \Throwable $e ) {
		try { $now = $capture(); if ( $now === $after ) { $write( $after, $before ); } elseif ( $now !== $before ) { throw new \RuntimeException( 'Unknown concurrent page state; refusing recovery overwrite.' ); } if ( $capture() !== $before ) { throw new \RuntimeException( 'Original page read-back differs.' ); } }
		catch ( \Throwable $r ) { throw new \RuntimeException( $e->getMessage() . '; recovery failed: ' . $r->getMessage() ); }
		throw new \RuntimeException( $e->getMessage() . '; complete original page preserved/restored and verified.' );
	}
};
if ( 'rollback' === $mode ) {
	$file = $args[1]; $path = is_string( $file ) && $file ? realpath( $file ) : false;
	if ( ! $path || ! is_file( $path ) || 0 !== strpos( $path, $private . '/' ) ) { WP_CLI::error( 'Rollback requires this update\'s snapshot inside WWM_BACKUP_DIR.' ); }
	$snapshot = json_decode( file_get_contents( $path ), true ); $old = $snapshot['old'] ?? null; $new = $snapshot['new'] ?? null;
	$valid = is_array( $snapshot ) && ( $snapshot['bundle'] ?? '' ) === 'rail-input-v1' && ( $snapshot['home'] ?? '' ) === $home && true === ( $snapshot['complete'] ?? false ) && ( $snapshot['payloads'] ?? null ) === $manifest && $matches( $old, $old_content ) && $matches( $new, $new_content ) && hash( 'sha256', wp_json_encode( $old ) ) === ( $snapshot['old_sha256'] ?? '' ) && hash( 'sha256', wp_json_encode( $new ) ) === ( $snapshot['new_sha256'] ?? '' );
	if ( $valid ) { $allowed = $old; $allowed['post']['post_content'] = $new_content; $valid = $allowed === $new; }
	if ( ! $valid ) { WP_CLI::error( 'Snapshot invalid, damaged or from another site/page/payload; nothing changed.' ); }
	if ( $current === $old ) { WP_CLI::success( 'Exact original page already restored; nothing changed.' ); return; }
	if ( $current !== $new ) { WP_CLI::error( 'Complete page state changed since apply; nothing changed.' ); }
	try { $transition( $new, $old ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Snapshot: ' . $path ); }
	WP_CLI::success( 'Exact Rail input rollback complete; original page/post/meta/terms/dates restored. Snapshot: ' . $path ); return;
}
$next = $current; $next['post']['post_content'] = $new_content;
$file = $private . '/rail-input-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
$snapshot = array( 'bundle' => 'rail-input-v1', 'home' => $home, 'created' => gmdate( 'c' ), 'complete' => false, 'payloads' => $manifest, 'old' => $current, 'old_sha256' => hash( 'sha256', wp_json_encode( $current ) ), 'new' => $next, 'new_sha256' => hash( 'sha256', wp_json_encode( $next ) ) );
try { $save( $file, $snapshot ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
try { $transition( $current, $next ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Snapshot: ' . $file ); }
try { $snapshot['complete'] = true; $save( $file, $snapshot ); }
catch ( \Throwable $e ) { try { $transition( $next, $current ); } catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; snapshot recovery failed: ' . $r->getMessage() . '. Snapshot: ' . $file ); } WP_CLI::error( $e->getMessage() . '; complete original page restored and verified.' ); }
WP_CLI::success( 'Rail input updated in place on page #1348; only post_content changed. Before-snapshot: ' . $file );
