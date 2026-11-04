<?php
/**
 * Add the reviewed section guide: marked CSS plus one new WPCode PHP snippet.
 * WP-CLI --user=1 eval-file: preflight / apply / rollback FILE.
 * Only isolated localhost:8090 and exact staging38 origins; no live allowlist.
 * Snapshot every prior CSS byte and this run's complete new snippet identity.
 * Rollback removes only that unchanged owned snippet, as in review-c.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST );
$port = wp_parse_url( $home, PHP_URL_PORT );
$host .= $port ? ':' . $port : '';
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) || ! in_array( $home, array( 'http://localhost:8090', 'http://staging38.thaisolitude.com', 'https://staging38.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Only exact localhost:8090 and staging38 origins are allowed; nothing changed.' );
}
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) {
	WP_CLI::error( 'Requires Astra, WPCode and an administrator allowed to edit CSS and PHP (--user=1).' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || count( $args ) > ( 'rollback' === $mode ? 2 : 1 ) ) { WP_CLI::error( 'Use preflight, apply, or rollback FILE.' ); }
$block = file_get_contents( __DIR__ . '/section-guide.css' );
$runtime = file_get_contents( __DIR__ . '/section-guide.php' );
if ( false === $block || hash( 'sha256', $block ) !== '3b74dcdef902a484d9c3c2abf8f7070dd2a8956f30b3b7d3c7295b2d00ea5eb7' || false === $runtime || hash( 'sha256', $runtime ) !== 'cb68b9a5aa1159c85d6f86096278e13f6819d9b12e27e14baee483b8c6002d23' || 0 !== strpos( $runtime, "<?php\n" ) ) {
	WP_CLI::error( 'Reviewed CSS or PHP payload hash differs; nothing changed.' );
}
$code = substr( $runtime, 6 );
try { token_get_all( "<?php\n" . $code, TOKEN_PARSE ); } catch ( \ParseError $e ) { WP_CLI::error( 'Runtime PHP syntax: ' . $e->getMessage() ); }
$title = 'TSSectionGuide v1';
$note = 'Created 2026-11-04 by section-guide/install-section-guide.php; exact owned-snippet rollback only.';
$expected_meta = array( '_wpcode_auto_insert' => array( '1' ), '_wpcode_priority' => array( '10' ), '_wpcode_note' => array( $note ), '_wpcode_shortcode_attributes' => array( 'a:0:{}' ) );
ksort( $expected_meta );
$taxonomies = get_object_taxonomies( 'wpcode' ); sort( $taxonomies );
if ( ! in_array( 'wpcode_type', $taxonomies, true ) || ! in_array( 'wpcode_location', $taxonomies, true ) ) { WP_CLI::error( 'WPCode taxonomies unavailable.' ); }
$expected_terms = array_fill_keys( $taxonomies, array() );
$expected_terms['wpcode_type'] = array( 'php' ); $expected_terms['wpcode_location'] = array( 'everywhere' );
$css_state = static function ( $value ) use ( $block ) {
	$starts = substr_count( $value, '/* ts-section-guide:start' );
	$ends = substr_count( $value, '/* ts-section-guide:end' );
	if ( 0 === $starts && 0 === $ends ) { return 'ready'; }
	return 1 === $starts && 1 === $ends && 1 === substr_count( $value, $block ) ? 'applied' : 'CHANGED';
};
$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) { WP_CLI::error( 'Known site Additional CSS missing; nothing changed.' ); }
$css_id = (int) $post->ID;
$css = (string) $post->post_content;
$all_meta = static function ( $id ) { $out = get_post_meta( $id ); ksort( $out ); return $out; };
$terms = static function ( $id ) use ( $taxonomies ) {
	$out = array();
	foreach ( $taxonomies as $tax ) {
		$value = wp_get_object_terms( $id, $tax, array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $value ) ) { throw new \RuntimeException( 'WPCode terms unreadable.' ); }
		sort( $value ); $out[ $tax ] = $value;
	}
	return $out;
};
$capture_snippet = static function ( $id ) use ( $all_meta, $terms ) {
	$p = get_post( $id, ARRAY_A, 'raw' );
	return $p ? array( 'id' => (int) $id, 'post' => $p, 'meta' => $all_meta( $id ), 'terms' => $terms( $id ) ) : null;
};
$matches_snippet = static function ( $record ) use ( $title, $code, $expected_meta, $expected_terms ) {
	return is_array( $record ) && ( $record['post']['post_type'] ?? '' ) === 'wpcode' && ( $record['post']['post_status'] ?? '' ) === 'publish' && ( $record['post']['post_title'] ?? '' ) === $title && ( $record['post']['post_content'] ?? '' ) === $code && ( $record['meta'] ?? null ) === $expected_meta && ( $record['terms'] ?? null ) === $expected_terms;
};
$find_owned = static function () use ( $title ) {
	$found = array();
	foreach ( get_posts( array( 'post_type' => 'wpcode', 'post_status' => array_values( get_post_stati() ), 'numberposts' => -1, 'suppress_filters' => true ) ) as $candidate ) {
		if ( $candidate->post_title === $title || false !== strpos( (string) $candidate->post_content, 'ts-section-guide-runtime' ) ) { $found[] = (int) $candidate->ID; }
	}
	return $found;
};
try {
	$found = $find_owned();
	$snippet = count( $found ) === 1 ? $capture_snippet( $found[0] ) : null;
	$snippet_state = ! $found ? 'ready' : ( count( $found ) === 1 && $matches_snippet( $snippet ) ? 'applied' : 'CHANGED' );
} catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
$state = $css_state( $css ) === $snippet_state ? $snippet_state : 'CHANGED';
WP_CLI::log( 'home=' . $home . ' section-guide=' . $state . ' CSS bytes=' . strlen( $css ) );
if ( 'CHANGED' === $state ) { WP_CLI::error( 'Partial, changed or duplicate CSS/snippet state; nothing changed.' ); }
if ( 'preflight' === $mode ) { WP_CLI::success( 'Preflight ' . $state . '; nothing changed.' ); return; }
if ( 'apply' === $mode && 'applied' === $state ) { WP_CLI::success( 'Already applied; nothing changed.' ); return; }
$backup_dir = getenv( 'WWM_BACKUP_DIR' );
$private = $backup_dir ? realpath( $backup_dir ) : false;
$webroot = realpath( ABSPATH );
if ( ! $private || '/' !== $backup_dir[0] || ! is_dir( $private ) || ! is_writable( $private ) || ! $webroot || $private === $webroot || 0 === strpos( $private . '/', rtrim( $webroot, '/' ) . '/' ) ) { WP_CLI::error( 'WWM_BACKUP_DIR must be a writable absolute directory outside the web root.' ); }
$save = static function ( $file, $snap ) {
	$json = wp_json_encode( $snap ); $mask = umask( 0077 );
	$written = false !== $json && false !== file_put_contents( $file, $json, LOCK_EX ); umask( $mask );
	if ( ! $written || ! chmod( $file, 0600 ) || file_get_contents( $file ) !== $json ) { throw new \RuntimeException( 'Private snapshot write/read-back failed.' ); }
};
$read_css = static function () use ( $css_id ) {
	$p = wp_get_custom_css_post( 'astra' );
	if ( ! $p || (int) $p->ID !== $css_id ) { throw new \RuntimeException( 'Additional CSS post identity changed.' ); }
	return (string) $p->post_content;
};
$set_css = static function ( $value ) use ( $read_css ) {
	$result = wp_update_custom_css_post( $value, array( 'stylesheet' => 'astra' ) );
	if ( is_wp_error( $result ) || $read_css() !== $value ) { throw new \RuntimeException( 'CSS save/read-back failed.' ); }
};
$refresh = static function () {
	if ( ! isset( wpcode()->cache ) || ! method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { throw new \RuntimeException( 'WPCode cache refresh unavailable.' ); }
	wpcode()->cache->cache_all_loaded_snippets();
};
$delete_owned = static function ( $expected ) use ( $capture_snippet ) {
	if ( ! is_array( $expected ) || $capture_snippet( $expected['id'] ) !== $expected ) { throw new \RuntimeException( 'Owned snippet changed; refusing removal.' ); }
	wp_delete_post( $expected['id'], true );
	if ( get_post( $expected['id'] ) ) { throw new \RuntimeException( 'Owned snippet removal failed.' ); }
};
if ( 'rollback' === $mode ) {
	$file = $args[1] ?? ''; $path = $file ? realpath( $file ) : false;
	if ( ! $path || ! is_file( $path ) || 0 !== strpos( $path, $private . '/' ) ) { WP_CLI::error( 'Rollback requires a snapshot inside WWM_BACKUP_DIR.' ); }
	$snap = json_decode( file_get_contents( $path ), true );
	if ( ! is_array( $snap ) || ! array_key_exists( 'old_snippet', $snap ) || null !== $snap['old_snippet'] || ( $snap['expected_snippet'] ?? null ) !== array( 'title' => $title, 'code_sha256' => hash( 'sha256', $code ), 'meta' => $expected_meta, 'terms' => $expected_terms ) || ( $snap['bundle'] ?? '' ) !== 'section-guide-v1' || ( $snap['home'] ?? '' ) !== $home || ( $snap['css_id'] ?? 0 ) !== $css_id || empty( $snap['complete'] ) || ! is_string( $snap['old_css'] ?? null ) || hash( 'sha256', $snap['old_css'] ) !== ( $snap['old_css_sha256'] ?? '' ) || hash( 'sha256', $block ) !== ( $snap['block_sha256'] ?? '' ) || hash( 'sha256', $runtime ) !== ( $snap['runtime_sha256'] ?? '' ) || 'ready' !== $css_state( $snap['old_css'] ) || hash( 'sha256', $snap['old_css'] . "\n\n" . $block ) !== ( $snap['new_css_sha256'] ?? '' ) || ! $matches_snippet( $snap['created_snippet'] ?? null ) || hash( 'sha256', wp_json_encode( $snap['created_snippet'] ) ) !== ( $snap['created_snippet_sha256'] ?? '' ) ) { WP_CLI::error( 'Snapshot invalid, damaged or from another site/post/payload; nothing changed.' ); }
	if ( 'ready' === $state && $css === $snap['old_css'] && ! get_post( $snap['created_snippet']['id'] ) ) { WP_CLI::success( 'Exact original CSS and absent snippet already restored.' ); return; }
	if ( 'applied' !== $state || $css !== $snap['old_css'] . "\n\n" . $block || $snippet !== $snap['created_snippet'] ) { WP_CLI::error( 'CSS or complete owned snippet changed since apply; nothing changed.' ); }
	try { $set_css( $snap['old_css'] ); }
	catch ( \Throwable $e ) {
		try { if ( ! in_array( $read_css(), array( $css, $snap['old_css'] ), true ) ) { throw new \RuntimeException( 'CSS changed during rollback; refusing to overwrite unknown bytes.' ); } $set_css( $css ); } catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery failed: ' . $r->getMessage() . '. Stop; snapshot ' . $path ); }
		WP_CLI::error( $e->getMessage() . '; applied CSS restored and verified.' );
	}
	try { $delete_owned( $snap['created_snippet'] ); $refresh(); if ( $read_css() !== $snap['old_css'] || $find_owned() ) { throw new \RuntimeException( 'Final rollback read-back differs.' ); } }
	catch ( \Throwable $e ) {
		// A refused/failed deletion must not strand an active, unstyled runtime.
		// Only recover the applied state while the owned snippet is still exact.
		$recovered = false;
		try {
			if ( $capture_snippet( $snap['created_snippet']['id'] ) === $snap['created_snippet'] ) {
				if ( ! in_array( $read_css(), array( $css, $snap['old_css'] ), true ) ) { throw new \RuntimeException( 'CSS changed during rollback; refusing to overwrite unknown bytes.' ); }
				$set_css( $css ); $refresh();
				if ( $read_css() !== $css || $capture_snippet( $snap['created_snippet']['id'] ) !== $snap['created_snippet'] ) { throw new \RuntimeException( 'Rollback recovery read-back differs.' ); }
				$recovered = true;
			}
		} catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery also failed: ' . $r->getMessage() . '. Stop; snapshot ' . $path ); }
		WP_CLI::error( $e->getMessage() . ( $recovered ? '; applied CSS and owned snippet restored and verified.' : '; rollback partially completed: original CSS restored, snippet deletion/cache state requires inspection.' ) . ' Snapshot ' . $path );
	}
	WP_CLI::success( 'Exact rollback complete; only this run\'s unchanged snippet removed. Snapshot: ' . $path ); return;
}
$new_css = $css . "\n\n" . $block;
$file = $private . '/section-guide-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
$snap = array( 'bundle' => 'section-guide-v1', 'home' => $home, 'created' => gmdate( 'c' ), 'complete' => false, 'css_id' => $css_id, 'old_css' => $css, 'old_css_sha256' => hash( 'sha256', $css ), 'new_css_sha256' => hash( 'sha256', $new_css ), 'block_sha256' => hash( 'sha256', $block ), 'runtime_sha256' => hash( 'sha256', $runtime ), 'old_snippet' => null, 'expected_snippet' => array( 'title' => $title, 'code_sha256' => hash( 'sha256', $code ), 'meta' => $expected_meta, 'terms' => $expected_terms ), 'created_snippet' => null );
try { $save( $file, $snap ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
$created_id = 0;
if ( $read_css() !== $css || $find_owned() ) { WP_CLI::error( 'State changed after preflight; nothing changed.' ); }
try {
	$set_css( $new_css );
	$object = new WPCode_Snippet( array( 'title' => $title, 'code' => $code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true, 'note' => $note ) );
	$saved_id = $object->save();
	if ( is_wp_error( $saved_id ) || ! is_numeric( $saved_id ) || (int) $saved_id < 1 ) { throw new \RuntimeException( 'Could not create the section-guide snippet.' ); }
	$created_id = (int) $saved_id;
	if ( ! $created_id ) { throw new \RuntimeException( 'Could not create the section-guide snippet.' ); }
	$snap['created_snippet'] = $capture_snippet( $created_id ); $save( $file, $snap );
	// WPCode's initial save may unslash source; restore exact reviewed bytes and
	// immediately capture the owned identity even if that write reports failure.
	try {
		$result = wp_update_post( wp_slash( array( 'ID' => $created_id, 'post_content' => $code ) ), true );
		if ( is_wp_error( $result ) || ! $result || get_post_field( 'post_content', $created_id ) !== $code ) { throw new \RuntimeException( 'Snippet code save/read-back failed.' ); }
	} finally { $snap['created_snippet'] = $capture_snippet( $created_id ); }
	$save( $file, $snap ); $refresh();
	if ( $read_css() !== $new_css || $find_owned() !== array( $created_id ) || ! $matches_snippet( $capture_snippet( $created_id ) ) || $capture_snippet( $created_id ) !== $snap['created_snippet'] ) { throw new \RuntimeException( 'Applied CSS/snippet read-back differs.' ); }
	$snap['created_snippet_sha256'] = hash( 'sha256', wp_json_encode( $snap['created_snippet'] ) ); $snap['complete'] = true; $save( $file, $snap );
} catch ( \Throwable $e ) {
	try {
		if ( ! in_array( $read_css(), array( $css, $new_css ), true ) ) { throw new \RuntimeException( 'CSS changed during apply; refusing to overwrite unknown bytes.' ); }
		if ( $created_id ) { $delete_owned( $snap['created_snippet'] ); }
		if ( $read_css() !== $css ) { $set_css( $css ); }
		$refresh();
		if ( $read_css() !== $css || $find_owned() ) { throw new \RuntimeException( 'Recovery read-back differs.' ); }
	} catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery also failed: ' . $r->getMessage() . '. Stop; snapshot ' . $file ); }
	WP_CLI::error( $e->getMessage() . '; original CSS and absent snippet restored and verified.' );
}
WP_CLI::success( 'Section guide applied. Before-snapshot: ' . $file );
