<?php
/**
 * Approved Bundle D copy corrections on staging38 / isolated localhost only.
 * Exact frozen page fragments, A11yHeartLinks, one Astra footer HTML field.
 * No new posts, files, snippets or metadata changes. No live target.
 * All-or-nothing preflight; private exact snapshots; rollback refuses drift.
 * Push verifies a fresh database export off-site before apply or rollback.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) ) { WP_CLI::error( 'Refusing target: staging38/isolated localhost only. Live disabled.' ); }
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) { WP_CLI::error( 'Requires Astra, WPCode and administrator --user=1.' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) { WP_CLI::error( 'Unknown mode.' ); }
$data = require __DIR__ . '/bundle-d-content.php';
foreach ( $data['snippets'] as $title => $cfg ) {
	foreach ( array( 'old', 'new' ) as $kind ) { if ( hash( 'sha256', $cfg[ $kind ] ) !== $cfg[ $kind . '_sha256' ] ) { WP_CLI::error( 'Frozen snippet hash differs: ' . $title ); } }
}
$syntax = array(); foreach ( $data['snippets'] as $cfg ) { $syntax[] = $cfg['new']; }
foreach ( $syntax as $code ) { try { token_get_all( "<?php\n" . $code, TOKEN_PARSE ); } catch ( \ParseError $e ) { WP_CLI::error( 'Snippet syntax: ' . $e->getMessage() ); } }
$hydrate = function ( $v ) use ( &$hydrate, $host ) {
	if ( is_string( $v ) ) { return str_replace( array( '{{HTTP}}', '{{HTTPS}}' ), array( 'http://' . $host, ( 'localhost:8090' === $host ? 'http://' : 'https://' ) . $host ), $v ); }
	if ( is_array( $v ) ) { foreach ( $v as $k => $item ) { $v[ $k ] = $hydrate( $item ); } }
	return $v;
};
$data = $hydrate( $data );
$find = function ( $type, $key, $value ) {
	$posts = get_posts( array( 'post_type' => $type, $key => $value, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	if ( count( $posts ) !== 1 || 'publish' !== $posts[0]->post_status ) { WP_CLI::error( 'Need exactly one published ' . $type . ': ' . $value ); }
	return $posts[0];
};
$transform = function ( $content, $pairs ) {
	foreach ( $pairs as $pair ) { if ( substr_count( $content, $pair['old'] ) !== $pair['count'] ) { throw new \RuntimeException( 'Fragment drift: ' . $pair['label'] ); } $content = str_replace( $pair['old'], $pair['new'], $content ); }
	return $content;
};
$resources = $pages = $states = array();
$report = function ( $label, $state ) use ( &$states ) { WP_CLI::log( $label . '=' . $state ); $states[] = $state; };
foreach ( $data['pages'] as $slug => $cfg ) {
	$p = $find( 'page', 'name', $slug ); $pages[ $slug ] = $p;
	if ( 'home' === $slug && (int) get_option( 'page_on_front' ) !== (int) $p->ID ) { WP_CLI::error( 'Wrong front page.' ); }
	$hash = hash( 'sha256', (string) $p->post_content ); $want = $cfg['hashes'][ $host ];
	$state = $hash === $want['new'] ? 'applied' : ( $hash === $want['old'] ? 'ready' : 'CHANGED' );
	$new = null;
	if ( 'ready' === $state ) { try { $new = $transform( (string) $p->post_content, $cfg['pairs'] ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() ); } if ( hash( 'sha256', $new ) !== $want['new'] ) { WP_CLI::error( 'Packaged page result hash differs: ' . $slug ); } }
	$resources[ 'page:' . $slug ] = array( 'id' => (int) $p->ID, 'type' => 'page', 'new' => $new ); $report( 'page ' . $slug, $state );
}
$terms = function ( $id ) { $out = array(); foreach ( array( 'wpcode_type', 'wpcode_location' ) as $tax ) { $v = wp_get_object_terms( $id, $tax, array( 'fields' => 'slugs' ) ); if ( is_wp_error( $v ) ) { throw new \RuntimeException( 'WPCode terms unreadable.' ); } sort( $v ); $out[ $tax ] = $v; } return $out; };
$all_meta = function ( $id ) { $v = get_post_meta( $id ); ksort( $v ); return $v; };
foreach ( $data['snippets'] as $title => $cfg ) {
	$p = $find( 'wpcode', 'title', $title ); $now = $all_meta( $p->ID ); $expected = $cfg['meta']; ksort( $expected );
	if ( $now !== $expected || $terms( $p->ID ) !== array( 'wpcode_type' => array( 'php' ), 'wpcode_location' => array( 'everywhere' ) ) ) { WP_CLI::error( 'Active snippet settings changed: ' . $title ); }
	$state = $p->post_content === $cfg['new'] ? 'applied' : ( $p->post_content === $cfg['old'] ? 'ready' : 'CHANGED' );
	$resources[ 'snippet:' . $title ] = array( 'id' => (int) $p->ID, 'type' => 'wpcode', 'new' => $cfg['new'] ); $report( 'snippet ' . $title, $state );
}
$astra = get_option( 'astra-settings' ); if ( ! is_array( $astra ) ) { WP_CLI::error( 'Astra option missing.' ); }
$v = $astra[ $data['astra_footer_key'] ] ?? null;
$report( 'Astra footer HTML', $v === $data['astra_footer_new'] ? 'applied' : ( $v === $data['astra_footer_old'] ? 'ready' : 'CHANGED' ) );
$unique = array_values( array_unique( $states ) ); $state = count( $unique ) === 1 ? $unique[0] : 'CHANGED'; WP_CLI::log( 'host=' . $home . ' state=' . $state );
if ( 'preflight' === $mode ) { if ( 'CHANGED' === $state ) { WP_CLI::error( 'Drift/partial state; nothing changed.' ); } WP_CLI::success( 'Preflight ' . $state . '; nothing changed.' ); return; }
$dir = getenv( 'WWM_BACKUP_DIR' ); if ( ! $dir || '/' !== $dir[0] || ! is_dir( $dir ) || ! is_writable( $dir ) || 0 === strpos( realpath( $dir ), realpath( ABSPATH ) ) ) { WP_CLI::error( 'Private writable backup directory required outside ABSPATH.' ); }
$rows = function ( $id ) { $v = array(); foreach ( get_post_meta( $id ) as $k => $values ) { if ( 0 === strpos( $k, 'surerank_settings_' ) ) { $v[ $k ] = $values; } } ksort( $v ); return $v; };
$capture = function () use ( $resources, $pages, $rows, $all_meta, $terms ) {
	$out = array( 'resources' => array(), 'page_rows' => array(), 'snippet_settings' => array(), 'astra' => get_option( 'astra-settings' ) );
	foreach ( $resources as $key => $r ) { $p = get_post( $r['id'] ); $out['resources'][ $key ] = array( 'id' => $r['id'], 'type' => $p->post_type, 'status' => $p->post_status, 'title' => $p->post_title, 'content' => (string) $p->post_content ); if ( 'wpcode' === $r['type'] ) { $out['snippet_settings'][ $key ] = array( 'meta' => $all_meta( $r['id'] ), 'terms' => $terms( $r['id'] ) ); } }
	foreach ( $pages as $slug => $p ) { $out['page_rows'][ $slug ] = $rows( $p->ID ); }
	return $out;
};
$refresh = function () { if ( class_exists( '\SureRank\Inc\Functions\Settings' ) ) { \SureRank\Inc\Functions\Settings::clear_cache(); } if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { wpcode()->cache->cache_all_loaded_snippets(); } };
$set = function ( $id, $content ) { $r = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true ); if ( is_wp_error( $r ) || ! $r || (string) get_post_field( 'post_content', $id ) !== $content ) { throw new \RuntimeException( 'Content save/read-back failed: ' . $id ); } };
$restore_rows = function ( $wanted ) use ( $pages, $rows ) { foreach ( $wanted as $slug => $rowset ) { $id = $pages[ $slug ]->ID; foreach ( $rows( $id ) as $k => $v ) { delete_post_meta( $id, $k ); } foreach ( $rowset as $k => $values ) { foreach ( $values as $v ) { add_post_meta( $id, $k, wp_slash( maybe_unserialize( $v ) ) ); } } } };
$restore = function ( $old ) use ( $restore_rows, $refresh, $all_meta ) {
	foreach ( $old['resources'] as $r ) {
		$p = get_post( $r['id'] );
		if ( ! $p || $p->post_content !== $r['content'] || $p->post_title !== $r['title'] || $p->post_status !== $r['status'] || $p->post_type !== $r['type'] ) {
			$result = wp_update_post( wp_slash( array( 'ID' => $r['id'], 'post_content' => $r['content'], 'post_title' => $r['title'], 'post_status' => $r['status'], 'post_type' => $r['type'] ) ), true );
			if ( is_wp_error( $result ) || ! $result ) { throw new \RuntimeException( 'Post restore failed: ' . $r['id'] ); }
		}
	}
	update_option( 'astra-settings', $old['astra'] );
	foreach ( $old['snippet_settings'] as $key => $settings ) {
		$id = $old['resources'][ $key ]['id'];
		if ( $all_meta( $id ) !== $settings['meta'] ) {
			foreach ( $all_meta( $id ) as $name => $values ) { delete_post_meta( $id, $name ); }
			foreach ( $settings['meta'] as $name => $values ) { foreach ( $values as $value ) { add_post_meta( $id, $name, wp_slash( maybe_unserialize( $value ) ) ); } }
		}
		foreach ( $settings['terms'] as $tax => $slugs ) { $r = wp_set_object_terms( $id, $slugs, $tax, false ); if ( is_wp_error( $r ) ) { throw new \RuntimeException( 'Snippet term restore failed.' ); } }
	}
	$restore_rows( $old['page_rows'] ); $refresh(); };
$save = function ( $file, $snap ) { $json = wp_json_encode( $snap ); if ( false === $json || false === file_put_contents( $file, $json, LOCK_EX ) || file_get_contents( $file ) !== $json || ! chmod( $file, 0600 ) ) { throw new \RuntimeException( 'Snapshot save/verification failed.' ); } };
if ( 'rollback' === $mode ) {
	$file = $args[1] ?? ''; $snap = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
	if ( ! is_array( $snap ) || ( $snap['bundle'] ?? '' ) !== 'review-d-v1' || ( $snap['home'] ?? '' ) !== $home || empty( $snap['complete'] ) || hash( 'sha256', wp_json_encode( $snap['old'] ) ) !== ( $snap['old_sha256'] ?? '' ) || hash( 'sha256', wp_json_encode( $snap['new'] ) ) !== ( $snap['new_sha256'] ?? '' ) || $capture() !== $snap['new'] ) { WP_CLI::error( 'Snapshot invalid or a store changed. Nothing changed.' ); }
	try { $restore( $snap['old'] ); if ( $capture() !== $snap['old'] ) { throw new \RuntimeException( 'Rollback read-back differs.' ); } }
	catch ( \Throwable $e ) { try { $restore( $snap['new'] ); if ( $capture() !== $snap['new'] ) { throw new \RuntimeException( 'Recovery read-back differs.' ); } } catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery also failed: ' . $r->getMessage() . '. Snapshot ' . $file ); } WP_CLI::error( $e->getMessage() . '; applied stores restored and verified.' ); }
	WP_CLI::success( 'Exact rollback complete from ' . $file ); return;
}
if ( 'applied' === $state ) { WP_CLI::success( 'Already applied; nothing changed.' ); return; }
if ( 'ready' !== $state ) { WP_CLI::error( 'Not reviewed state; nothing changed.' ); }
$old = $capture(); $file = $dir . '/bundle-d-before-' . gmdate( 'Ymd-His' ) . '.json';
foreach ( $data['pages'] as $slug => $cfg ) {
	$r = $old['resources'][ 'page:' . $slug ];
	if ( 'page' !== $r['type'] || 'publish' !== $r['status'] || hash( 'sha256', $r['content'] ) !== $cfg['hashes'][ $host ]['old'] ) { WP_CLI::error( 'Page drift after preflight; nothing changed.' ); }
}
foreach ( $data['snippets'] as $title => $cfg ) {
	$r = $old['resources'][ 'snippet:' . $title ];
	$expected_meta = $cfg['meta']; ksort( $expected_meta );
	if ( 'wpcode' !== $r['type'] || 'publish' !== $r['status'] || $r['title'] !== $title || $r['content'] !== $cfg['old'] || $old['snippet_settings'][ 'snippet:' . $title ] !== array( 'meta' => $expected_meta, 'terms' => array( 'wpcode_type' => array( 'php' ), 'wpcode_location' => array( 'everywhere' ) ) ) ) { WP_CLI::error( 'Snippet drift after preflight; nothing changed.' ); }
}
$astra = $old['astra'];
if ( ( $astra[ $data['astra_footer_key'] ] ?? null ) !== $data['astra_footer_old'] ) { WP_CLI::error( 'Footer drift after preflight; nothing changed.' ); }
$snap = array( 'bundle' => 'review-d-v1', 'home' => $home, 'complete' => false, 'created' => gmdate( 'c' ), 'old' => $old, 'old_sha256' => hash( 'sha256', wp_json_encode( $old ) ) );
try { $save( $file, $snap ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
if ( $capture() !== $old ) { WP_CLI::error( 'Store drift before writing; nothing changed.' ); }
try {
	foreach ( $resources as $r ) { $set( $r['id'], $r['new'] ); }
	$astra[ $data['astra_footer_key'] ] = $data['astra_footer_new']; update_option( 'astra-settings', $astra );
	$restore_rows( $old['page_rows'] );
	$refresh(); $new = $capture();
	$expected = $old; $expected['astra'] = $astra;
	foreach ( $resources as $key => $r ) { $expected['resources'][ $key ]['content'] = $r['new']; }
	if ( $new !== $expected ) { throw new \RuntimeException( 'Content, post fields, metadata or option read-back differs.' ); }
	$snap['new'] = $new; $snap['new_sha256'] = hash( 'sha256', wp_json_encode( $new ) ); $snap['complete'] = true; $save( $file, $snap );
} catch ( \Throwable $e ) {
	try { $restore( $old ); if ( $capture() !== $old ) { throw new \RuntimeException( 'Restored stores differ.' ); } $refresh(); }
	catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery also failed: ' . $r->getMessage() . '. Stop; snapshot ' . $file ); }
	WP_CLI::error( $e->getMessage() . '; old stores restored exactly.' );
}
WP_CLI::success( 'Applied Bundle D. Before-snapshot: ' . $file );
