<?php
/**
 * Coordinate the exact existing inline and native page-navigation snippets.
 * Staging38/localhost only. CSS is a read-only witness; only the two captured snippets may change.
 * Modes: preflight / apply / rollback FILE. Push supplies verified offsite backups.
 * Full post/meta/taxonomy snapshots and original dates preserve older rollback chains.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST );
$port = wp_parse_url( $home, PHP_URL_PORT ); $host .= $port ? ':' . $port : '';
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) || ! in_array( $home, array( 'http://localhost:8090', 'http://staging38.thaisolitude.com', 'https://staging38.thaisolitude.com' ), true ) ) { WP_CLI::error( 'Only exact localhost:8090 and staging38 origins are allowed; nothing changed.' ); }
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) { WP_CLI::error( 'Requires Astra, WPCode and an administrator (--user=1).' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array(); $mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || count( $args ) > ( 'rollback' === $mode ? 2 : 1 ) ) { WP_CLI::error( 'Use preflight, apply, or rollback FILE.' ); }
$plans = array(
	'A11yPageJumpLinks' => array(
		'old_file' => 'jump-links-before.php', 'old_file_sha256' => '1034cd2011adb1991392f26cf4b6d2fe04ef8916af9f7ce045c1023ecd4224c4',
		'old_sha256' => '225d38dbc4a87744397c1a55888b6fb5fb5fdf9df127c78c2544845e371f9a36',
		'new_file' => 'jump-links.php', 'new_file_sha256' => 'bea04499abbe10f177282ce25a69f8ab8801312faa4b334d0be85f958e789b9f',
		'note' => 'Created 2026-10-18 by scripts/work-with-me-employer/accessibility/install-jump-links.php (rollback: delete this snippet and the ts-a11y-jump CSS block).',
	),
	'TSSectionGuide v1' => array(
		'old_file' => '../section-guide/section-guide.php', 'old_file_sha256' => '071149c634d51bb6d2b7e3f4de59b695a5802a3e63c7da1939b3fa1684210e2b',
		'old_sha256' => '573ea9f5f8f0ea297d61aa72745a275cd50c999b2367d34fea901d02874c4ff5',
		'new_file' => 'section-guide.php', 'new_file_sha256' => '9c1aa136e0f61136d9f897fd44b241e6410b6cf6d2190c1070e5263a72b0f258',
		'note' => 'Created 2026-11-04 by section-guide/install-section-guide.php; exact owned-snippet rollback only.',
	),
);
$manifest = array();
foreach ( $plans as $title => &$plan ) {
	foreach ( array( 'old', 'new' ) as $kind ) {
		$bytes = file_get_contents( __DIR__ . '/' . $plan[ $kind . '_file' ] );
		if ( false === $bytes || hash( 'sha256', $bytes ) !== $plan[ $kind . '_file_sha256' ] || 0 !== strpos( $bytes, "<?php\n" ) ) { WP_CLI::error( 'Frozen PHP file differs: ' . $plan[ $kind . '_file' ] ); }
		try { token_get_all( $bytes, TOKEN_PARSE ); } catch ( \ParseError $e ) { WP_CLI::error( 'Runtime syntax: ' . $e->getMessage() ); }
		$plan[ $kind ] = substr( $bytes, 6 );
		if ( 'old' === $kind && hash( 'sha256', $plan['old'] ) !== $plan['old_sha256'] ) { WP_CLI::error( 'Frozen deployed code hash differs: ' . $title ); }
		$manifest[ $title ][ $kind . '_sha256' ] = hash( 'sha256', $plan[ $kind ] );
	}
	if ( $plan['old'] === $plan['new'] ) { WP_CLI::error( 'The packaged migration must change each reviewed runtime.' ); }
	$plan['meta'] = array( '_wpcode_auto_insert' => array( '1' ), '_wpcode_priority' => array( '10' ), '_wpcode_note' => array( $plan['note'] ), '_wpcode_shortcode_attributes' => array( 'a:0:{}' ) ); ksort( $plan['meta'] );
}
unset( $plan );
$guide_css = file_get_contents( __DIR__ . '/../section-guide/section-guide.css' );
if ( false === $guide_css || hash( 'sha256', $guide_css ) !== '3b74dcdef902a484d9c3c2abf8f7070dd2a8956f30b3b7d3c7295b2d00ea5eb7' ) { WP_CLI::error( 'Frozen existing guide CSS differs; nothing changed.' ); }
$taxonomies = get_object_taxonomies( 'wpcode' ); sort( $taxonomies );
if ( ! in_array( 'wpcode_type', $taxonomies, true ) || ! in_array( 'wpcode_location', $taxonomies, true ) ) { WP_CLI::error( 'WPCode taxonomies unavailable.' ); }
$expected_terms = array_fill_keys( $taxonomies, array() ); $expected_terms['wpcode_type'] = array( 'php' ); $expected_terms['wpcode_location'] = array( 'everywhere' );
$capture_post = static function ( $id ) use ( $taxonomies ) {
	$post = get_post( $id, ARRAY_A, 'raw' ); if ( ! $post ) { throw new \RuntimeException( 'Snippet no longer exists: ' . $id ); }
	$meta = get_post_meta( $id ); ksort( $meta ); $terms = array();
	foreach ( $taxonomies as $tax ) { $values = wp_get_object_terms( $id, $tax, array( 'fields' => 'slugs' ) ); if ( is_wp_error( $values ) ) { throw new \RuntimeException( 'Snippet taxonomy unreadable.' ); } sort( $values ); $terms[ $tax ] = $values; }
	return array( 'id' => (int) $id, 'post' => $post, 'meta' => $meta, 'terms' => $terms );
};
$matches = static function ( $record, $title, $kind ) use ( $plans, $expected_terms ) {
	return is_array( $record ) && is_int( $record['id'] ?? null ) && $record['id'] > 0 && (int) ( $record['post']['ID'] ?? 0 ) === $record['id'] && ( $record['post']['post_type'] ?? '' ) === 'wpcode' && ( $record['post']['post_status'] ?? '' ) === 'publish' && ( $record['post']['post_title'] ?? '' ) === $title && ( $record['post']['post_content'] ?? '' ) === $plans[ $title ][ $kind ] && ( $record['meta'] ?? null ) === $plans[ $title ]['meta'] && ( $record['terms'] ?? null ) === $expected_terms;
};
$capture = static function () use ( $plans, $capture_post, $guide_css ) {
	$posts = get_posts( array( 'post_type' => 'wpcode', 'post_status' => array_values( get_post_stati() ), 'numberposts' => -1, 'suppress_filters' => true ) ); $records = array();
	foreach ( $plans as $title => $plan ) {
		$found = array();
		foreach ( $posts as $post ) {
			$content = (string) $post->post_content;
			if ( $post->post_title === $title || $content === $plan['old'] || $content === $plan['new'] || ( 'TSSectionGuide v1' === $title && false !== strpos( $content, 'ts-section-guide-runtime' ) ) ) { $found[] = (int) $post->ID; }
		}
		if ( count( $found ) !== 1 ) { throw new \RuntimeException( 'Missing or duplicate owned snippet: ' . $title ); }
		$records[ $title ] = $capture_post( $found[0] );
	}
	$css = wp_get_custom_css_post( 'astra' );
	if ( ! $css || 'custom_css' !== $css->post_type || strlen( (string) $css->post_content ) < 1000 || substr_count( $css->post_content, $guide_css ) !== 1 || substr_count( $css->post_content, '/* ts-section-guide:start' ) !== 1 || substr_count( $css->post_content, '/* ts-section-guide:end' ) !== 1 || substr_count( $css->post_content, '/* ts-a11y-jump:start' ) !== 1 || substr_count( $css->post_content, '/* ts-a11y-jump:end' ) !== 1 ) { throw new \RuntimeException( 'Known navigation CSS is missing, changed or duplicated.' ); }
	return array( 'css' => array( 'id' => (int) $css->ID, 'post' => get_post( $css->ID, ARRAY_A, 'raw' ), 'sha256' => hash( 'sha256', $css->post_content ) ), 'snippets' => $records );
};
try { $current = $capture(); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
$states = array(); foreach ( $plans as $title => $plan ) { $states[] = $matches( $current['snippets'][ $title ], $title, 'old' ) ? 'ready' : ( $matches( $current['snippets'][ $title ], $title, 'new' ) ? 'applied' : 'CHANGED' ); }
$state = count( array_unique( $states ) ) === 1 ? $states[0] : 'CHANGED';
WP_CLI::log( 'home=' . $home . ' page-navigation=' . $state . ' CSS bytes=' . strlen( $current['css']['post']['post_content'] ) );
if ( 'CHANGED' === $state ) { WP_CLI::error( 'Mixed or changed snippet code/settings; nothing changed.' ); }
if ( 'preflight' === $mode ) { WP_CLI::success( 'Preflight ' . $state . '; nothing changed.' ); return; }
if ( 'apply' === $mode && 'applied' === $state ) { WP_CLI::success( 'Already applied; nothing changed.' ); return; }
$backup_dir = getenv( 'WWM_BACKUP_DIR' ); $private = $backup_dir ? realpath( $backup_dir ) : false; $webroot = realpath( ABSPATH );
if ( ! $private || '/' !== $backup_dir[0] || ! is_dir( $private ) || ! is_writable( $private ) || ! $webroot || $private === $webroot || 0 === strpos( $private . '/', rtrim( $webroot, '/' ) . '/' ) ) { WP_CLI::error( 'WWM_BACKUP_DIR must be an existing writable directory outside the web root.' ); }
$save = static function ( $file, $snapshot ) {
	$json = wp_json_encode( $snapshot ); $mask = umask( 0077 ); $ok = false !== $json && false !== file_put_contents( $file, $json, LOCK_EX ); umask( $mask );
	if ( ! $ok || ! chmod( $file, 0600 ) || file_get_contents( $file ) !== $json ) { throw new \RuntimeException( 'Private snapshot save/read-back failed.' ); }
};
$write = static function ( $before, $after ) use ( $capture_post ) {
	$allowed = $before; foreach ( array( 'post_content', 'post_modified', 'post_modified_gmt' ) as $key ) { $allowed['post'][ $key ] = $after['post'][ $key ]; }
	if ( $allowed !== $after || $capture_post( $before['id'] ) !== $before ) { throw new \RuntimeException( 'Snippet identity/settings changed before write.' ); }
	$dates = static function ( $data, $postarr ) use ( $before, $after ) { if ( (int) ( $postarr['ID'] ?? 0 ) === $before['id'] && ( $data['post_type'] ?? '' ) === 'wpcode' ) { $data['post_modified'] = $after['post']['post_modified']; $data['post_modified_gmt'] = $after['post']['post_modified_gmt']; } return $data; };
	add_filter( 'wp_insert_post_data', $dates, PHP_INT_MAX, 2 );
	try { $result = wp_update_post( wp_slash( array( 'ID' => $before['id'], 'post_content' => $after['post']['post_content'] ) ), true ); }
	finally { remove_filter( 'wp_insert_post_data', $dates, PHP_INT_MAX ); }
	if ( is_wp_error( $result ) || ! $result || $capture_post( $before['id'] ) !== $after ) { throw new \RuntimeException( 'Snippet save/full-state read-back failed.' ); }
};
$refresh = static function () { if ( ! isset( wpcode()->cache ) || ! method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { throw new \RuntimeException( 'WPCode cache refresh unavailable.' ); } wpcode()->cache->cache_all_loaded_snippets(); };
$transition = static function ( $before, $after ) use ( $capture, $write, $refresh ) {
	if ( $capture() !== $before || $before['css'] !== $after['css'] ) { throw new \RuntimeException( 'CSS or pair changed before mutation.' ); }
	try {
		$expected = $before;
		foreach ( $before['snippets'] as $title => $record ) { $write( $record, $after['snippets'][ $title ] ); $expected['snippets'][ $title ] = $after['snippets'][ $title ]; if ( $capture() !== $expected ) { throw new \RuntimeException( 'Paired write read-back differs.' ); } }
		$refresh(); if ( $capture() !== $after ) { throw new \RuntimeException( 'Final paired write read-back differs.' ); }
	} catch ( \Throwable $e ) {
		try {
			$now = $capture(); if ( $now['css'] !== $before['css'] ) { throw new \RuntimeException( 'CSS drift during recovery; refusing writes.' ); }
			foreach ( $before['snippets'] as $title => $record ) { if ( $now['snippets'][ $title ] !== $record && $now['snippets'][ $title ] !== $after['snippets'][ $title ] ) { throw new \RuntimeException( 'Unknown snippet state during recovery; refusing overwrite.' ); } }
			foreach ( array_reverse( array_keys( $before['snippets'] ) ) as $title ) { if ( $now['snippets'][ $title ] === $after['snippets'][ $title ] ) { $write( $after['snippets'][ $title ], $before['snippets'][ $title ] ); } }
			$refresh(); if ( $capture() !== $before ) { throw new \RuntimeException( 'Recovery full-state read-back differs.' ); }
		} catch ( \Throwable $r ) { throw new \RuntimeException( $e->getMessage() . '; recovery also failed: ' . $r->getMessage() ); }
		throw new \RuntimeException( $e->getMessage() . '; complete original pair restored and verified.' );
	}
};
if ( 'rollback' === $mode ) {
	$file = $args[1] ?? ''; $path = $file ? realpath( $file ) : false;
	if ( ! $path || ! is_file( $path ) || 0 !== strpos( $path, $private . '/' ) ) { WP_CLI::error( 'Rollback requires a snapshot inside WWM_BACKUP_DIR.' ); }
	$snapshot = json_decode( file_get_contents( $path ), true ); $old = $snapshot['old'] ?? null; $new = $snapshot['new'] ?? null;
	$valid = is_array( $snapshot ) && ( $snapshot['bundle'] ?? '' ) === 'page-navigation-v1' && ( $snapshot['home'] ?? '' ) === $home && ! empty( $snapshot['complete'] ) && ( $snapshot['payloads'] ?? null ) === $manifest && is_array( $old ) && is_array( $new ) && hash( 'sha256', wp_json_encode( $old ) ) === ( $snapshot['old_sha256'] ?? '' ) && hash( 'sha256', wp_json_encode( $new ) ) === ( $snapshot['new_sha256'] ?? '' ) && ( $old['css'] ?? null ) === $current['css'] && ( $new['css'] ?? null ) === $current['css'] && array_keys( $old['snippets'] ?? array() ) === array_keys( $plans ) && array_keys( $new['snippets'] ?? array() ) === array_keys( $plans );
	if ( $valid ) {
		foreach ( $plans as $title => $plan ) {
			$before = $old['snippets'][ $title ]; $after = $new['snippets'][ $title ]; $allowed = $before;
			foreach ( array( 'post_content', 'post_modified', 'post_modified_gmt' ) as $key ) { $allowed['post'][ $key ] = $after['post'][ $key ] ?? null; }
			if ( ! $matches( $before, $title, 'old' ) || ! $matches( $after, $title, 'new' ) || $allowed !== $after ) { $valid = false; break; }
		}
	}
	if ( ! $valid ) { WP_CLI::error( 'Snapshot invalid, damaged, from another site/payload, or CSS changed; nothing changed.' ); }
	if ( $current === $old ) { WP_CLI::success( 'Exact original pair already restored; CSS unchanged.' ); return; }
	if ( $current !== $new ) { WP_CLI::error( 'Complete snippet state changed since apply; nothing changed.' ); }
	try { $transition( $new, $old ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Snapshot: ' . $path ); }
	WP_CLI::success( 'Exact paired rollback complete; both prior records and dates restored; CSS unchanged. Snapshot: ' . $path ); return;
}
$next = $current; $modified = current_time( 'mysql' ); $modified_gmt = current_time( 'mysql', true );
foreach ( $plans as $title => $plan ) { $next['snippets'][ $title ]['post']['post_content'] = $plan['new']; $next['snippets'][ $title ]['post']['post_modified'] = $modified; $next['snippets'][ $title ]['post']['post_modified_gmt'] = $modified_gmt; }
$file = $private . '/page-navigation-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
$snapshot = array( 'bundle' => 'page-navigation-v1', 'home' => $home, 'created' => gmdate( 'c' ), 'complete' => false, 'payloads' => $manifest, 'old' => $current, 'old_sha256' => hash( 'sha256', wp_json_encode( $current ) ), 'new' => $next, 'new_sha256' => hash( 'sha256', wp_json_encode( $next ) ) );
try { $save( $file, $snapshot ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
try { $transition( $current, $next ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Snapshot: ' . $file ); }
try { $snapshot['complete'] = true; $save( $file, $snapshot ); }
catch ( \Throwable $e ) { try { $transition( $next, $current ); } catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; snapshot recovery failed: ' . $r->getMessage() . '. Snapshot: ' . $file ); } WP_CLI::error( $e->getMessage() . '; complete original pair restored and verified.' ); }
WP_CLI::success( 'Page navigation applied to both existing snippets; CSS unchanged. Before-snapshot: ' . $file );
