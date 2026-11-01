<?php
/**
 * Bundle C, authorized by the 2026-11-01 handoff and Ryan's A–E live-release go.
 * Exact page fragments, two active snippets, marked CSS, Astra blog-meta and two
 * new WPCode snippets. No existing posts/files/media are removed. security.txt
 * uses Ryan's approved public mailbox and a virtual endpoint, not a server file.
 *
 * preflight / apply / rollback FILE. Backup/off-site verification is in push.
 * Snapshot all affected content, SureRank rows, active snippet settings and the
 * whole Astra option. Rollback refuses drift BEFORE restoring anything; it then
 * removes only this snapshot's unchanged newly created snippets (house pattern).
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com', 'thaisolitude.com' ), true ) ) { WP_CLI::error( 'Refusing target: isolated localhost, staging38 or thaisolitude.com only.' ); }
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) { WP_CLI::error( 'Requires Astra, WPCode and administrator --user=1.' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) { WP_CLI::error( 'Unknown mode.' ); }
$data = require __DIR__ . '/bundle-c-content.php';
foreach ( $data['snippets'] as $title => $cfg ) {
	foreach ( array( 'old', 'new' ) as $kind ) { if ( hash( 'sha256', $cfg[ $kind ] ) !== $cfg[ $kind . '_sha256' ] ) { WP_CLI::error( 'Frozen snippet hash differs: ' . $title ); } }
}
$syntax = array(); foreach ( $data['snippets'] as $cfg ) { $syntax[] = $cfg['new']; }
foreach ( $data['new_snippets'] as $cfg ) { if ( hash( 'sha256', $cfg['code'] ) !== $cfg['sha256'] ) { WP_CLI::error( 'New snippet hash differs.' ); } $syntax[] = $cfg['code']; }
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
$css = wp_get_custom_css_post( 'astra' ); if ( ! $css || strlen( $css->post_content ) < 1000 ) { WP_CLI::error( 'Additional CSS missing.' ); }
$hash = hash( 'sha256', $css->post_content ); $want = $data['css']['hashes'][ $host ];
$state = $hash === $want['new'] ? 'applied' : ( $hash === $want['old'] ? 'ready' : 'CHANGED' );
$new = null;
if ( 'ready' === $state ) { try { $new = $transform( $css->post_content, $data['css']['pairs'] ) . "\n\n" . $data['css']['append'] . "\n"; } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() ); } if ( hash( 'sha256', $new ) !== $want['new'] ) { WP_CLI::error( 'CSS result hash differs.' ); } }
$resources['css'] = array( 'id' => (int) $css->ID, 'type' => 'custom_css', 'new' => $new ); $report( 'Additional CSS', $state );
$terms = function ( $id ) { $out = array(); foreach ( array( 'wpcode_type', 'wpcode_location' ) as $tax ) { $v = wp_get_object_terms( $id, $tax, array( 'fields' => 'slugs' ) ); if ( is_wp_error( $v ) ) { throw new \RuntimeException( 'WPCode terms unreadable.' ); } sort( $v ); $out[ $tax ] = $v; } return $out; };
$all_meta = function ( $id ) { $v = get_post_meta( $id ); ksort( $v ); return $v; };
foreach ( $data['snippets'] as $title => $cfg ) {
	$p = $find( 'wpcode', 'title', $title ); $now = $all_meta( $p->ID ); $expected = $cfg['meta']; ksort( $expected );
	if ( $now !== $expected || $terms( $p->ID ) !== array( 'wpcode_type' => array( 'php' ), 'wpcode_location' => array( 'everywhere' ) ) ) { WP_CLI::error( 'Active snippet settings changed: ' . $title ); }
	$state = $p->post_content === $cfg['new'] ? 'applied' : ( $p->post_content === $cfg['old'] ? 'ready' : 'CHANGED' );
	$resources[ 'snippet:' . $title ] = array( 'id' => (int) $p->ID, 'type' => 'wpcode', 'new' => $cfg['new'] ); $report( 'snippet ' . $title, $state );
}
$astra = get_option( 'astra-settings' ); if ( ! is_array( $astra ) ) { WP_CLI::error( 'Astra option missing.' ); }
$v = $astra['blog-meta'] ?? null; $report( 'Astra blog-meta', $v === $data['astra_blog_meta_new'] ? 'applied' : ( $v === $data['astra_blog_meta_old'] ? 'ready' : 'CHANGED' ) );
$created = array();
foreach ( $data['new_snippets'] as $title => $cfg ) {
	$found = get_posts( array( 'post_type' => 'wpcode', 'title' => $title, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$state = 'CHANGED';
	if ( ! $found ) { $state = 'ready'; }
	elseif ( count( $found ) === 1 ) {
		$p = $found[0]; $id = (int) $p->ID;
		$expected_meta = $cfg['meta']; ksort( $expected_meta );
		if ( $all_meta( $id ) === $expected_meta && 'publish' === $p->post_status && $p->post_content === $cfg['code'] && $terms( $id ) === array( 'wpcode_type' => array( 'php' ), 'wpcode_location' => array( 'everywhere' ) ) && '1' === (string) get_post_meta( $id, '_wpcode_auto_insert', true ) && '10' === (string) get_post_meta( $id, '_wpcode_priority', true ) ) { $state = 'applied'; $created[ $title ] = $id; }
	}
	$report( 'new snippet ' . $title, $state );
}
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
$capture_created = function ( $ids ) use ( $all_meta, $terms ) { $out = array(); foreach ( $ids as $title => $id ) { $out[ $title ] = array( 'id' => (int) $id, 'post' => get_post( $id, ARRAY_A, 'raw' ), 'meta' => $all_meta( $id ), 'terms' => $terms( $id ) ); } return $out; };
$refresh = function () { if ( class_exists( '\SureRank\Inc\Functions\Settings' ) ) { \SureRank\Inc\Functions\Settings::clear_cache(); } if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { wpcode()->cache->cache_all_loaded_snippets(); } };
$set = function ( $id, $content ) { $r = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true ); if ( is_wp_error( $r ) || ! $r || (string) get_post_field( 'post_content', $id ) !== $content ) { throw new \RuntimeException( 'Content save/read-back failed: ' . $id ); } };
$restore_rows = function ( $wanted ) use ( $pages, $rows ) { foreach ( $wanted as $slug => $rowset ) { $id = $pages[ $slug ]->ID; foreach ( $rows( $id ) as $k => $v ) { delete_post_meta( $id, $k ); } foreach ( $rowset as $k => $values ) { foreach ( $values as $v ) { add_post_meta( $id, $k, wp_slash( maybe_unserialize( $v ) ) ); } } } };
$restore = function ( $old ) use ( $set, $restore_rows, $refresh, $all_meta ) { foreach ( $old['resources'] as $r ) { if ( get_post_field( 'post_content', $r['id'] ) !== $r['content'] ) { $set( $r['id'], $r['content'] ); } } update_option( 'astra-settings', $old['astra'] );
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
$delete_created = function ( $ids, $expected ) use ( $capture_created ) { if ( $capture_created( $ids ) !== $expected ) { throw new \RuntimeException( 'New snippet details changed; refuse removal.' ); } foreach ( $ids as $id ) { wp_delete_post( $id, true ); if ( get_post( $id ) ) { throw new \RuntimeException( 'Removal of this run\'s new snippet failed.' ); } } };
if ( 'rollback' === $mode ) {
	$file = $args[1] ?? ''; $snap = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
	if ( ! is_array( $snap ) || ( $snap['bundle'] ?? '' ) !== 'review-c-v1' || ( $snap['home'] ?? '' ) !== $home || empty( $snap['complete'] ) || hash( 'sha256', wp_json_encode( $snap['old'] ) ) !== ( $snap['old_sha256'] ?? '' ) || hash( 'sha256', wp_json_encode( $snap['new'] ) ) !== ( $snap['new_sha256'] ?? '' ) || $capture() !== $snap['new'] || $created !== $snap['created_ids'] || $capture_created( $created ) !== $snap['created_posts'] ) { WP_CLI::error( 'Snapshot invalid or a store/new snippet changed. Nothing changed.' ); }
	try { $restore( $snap['old'] ); if ( $capture() !== $snap['old'] ) { throw new \RuntimeException( 'Rollback read-back differs.' ); } }
	catch ( \Throwable $e ) { try { $restore( $snap['new'] ); if ( $capture() !== $snap['new'] ) { throw new \RuntimeException( 'Recovery read-back differs.' ); } } catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery also failed: ' . $r->getMessage() . '. Snapshot ' . $file ); } WP_CLI::error( $e->getMessage() . '; applied stores restored and verified.' ); }
	try { $delete_created( $created, $snap['created_posts'] ); $refresh(); if ( $capture() !== $snap['old'] ) { throw new \RuntimeException( 'Final rollback read-back differs.' ); } }
	catch ( \Throwable $e ) { WP_CLI::error( 'Old stores restored; ' . $e->getMessage() . ' Stop; snapshot ' . $file ); }
	WP_CLI::success( 'Exact rollback complete from ' . $file ); return;
}
if ( 'applied' === $state ) { WP_CLI::success( 'Already applied; nothing changed.' ); return; }
if ( 'ready' !== $state ) { WP_CLI::error( 'Not reviewed state; nothing changed.' ); }
$old = $capture(); $file = $dir . '/bundle-c-before-' . gmdate( 'Ymd-His' ) . '.json';
$snap = array( 'bundle' => 'review-c-v1', 'home' => $home, 'complete' => false, 'created' => gmdate( 'c' ), 'created_ids' => array(), 'created_posts' => array(), 'old' => $old, 'old_sha256' => hash( 'sha256', wp_json_encode( $old ) ) );
try { $save( $file, $snap ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
try {
	foreach ( $resources as $r ) { $set( $r['id'], $r['new'] ); }
	$astra['blog-meta'] = $data['astra_blog_meta_new']; update_option( 'astra-settings', $astra );
	$restore_rows( $old['page_rows'] );
	foreach ( $data['new_snippets'] as $title => $cfg ) {
		$snippet = new WPCode_Snippet( array( 'title' => $title, 'code' => $cfg['code'], 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true, 'note' => $cfg['note'] ) );
		$id = $snippet->save(); if ( ! $id ) { throw new \RuntimeException( 'Could not create snippet: ' . $title ); }
		$created[ $title ] = (int) $id;
		$snap['created_ids'] = $created; $snap['created_posts'] = $capture_created( $created ); $save( $file, $snap );
		try { $set( (int) $id, $cfg['code'] ); }
		finally { $snap['created_posts'] = $capture_created( $created ); }
		$save( $file, $snap );
	}
	$refresh(); $new = $capture();
	foreach ( $new['resources'] as $key => $r ) { if ( $r['content'] !== $resources[ $key ]['new'] ) { throw new \RuntimeException( 'Unexpected saved content: ' . $key ); } }
	if ( $new['page_rows'] !== $old['page_rows'] || $new['snippet_settings'] !== $old['snippet_settings'] || $new['astra'] !== $astra ) { throw new \RuntimeException( 'Metadata or option read-back differs.' ); }
	foreach ( $created as $title => $id ) { $expected_meta = $data['new_snippets'][ $title ]['meta']; ksort( $expected_meta ); if ( $all_meta( $id ) !== $expected_meta || 'publish' !== get_post_status( $id ) || get_post_field( 'post_content', $id ) !== $data['new_snippets'][ $title ]['code'] || $terms( $id ) !== array( 'wpcode_type' => array( 'php' ), 'wpcode_location' => array( 'everywhere' ) ) || '1' !== (string) get_post_meta( $id, '_wpcode_auto_insert', true ) || '10' !== (string) get_post_meta( $id, '_wpcode_priority', true ) ) { throw new \RuntimeException( 'New snippet settings/read-back differ.' ); } }
	$snap['created_posts'] = $capture_created( $created ); $snap['new'] = $new; $snap['new_sha256'] = hash( 'sha256', wp_json_encode( $new ) ); $snap['complete'] = true; $save( $file, $snap );
} catch ( \Throwable $e ) {
	try { $restore( $old ); if ( $capture() !== $old ) { throw new \RuntimeException( 'Restored stores differ.' ); } if ( $created ) { $delete_created( $created, $snap['created_posts'] ); } $refresh(); }
	catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery also failed: ' . $r->getMessage() . '. Stop; snapshot ' . $file ); }
	WP_CLI::error( $e->getMessage() . '; old stores restored exactly.' );
}
WP_CLI::success( 'Applied Bundle C. Before-snapshot: ' . $file );
