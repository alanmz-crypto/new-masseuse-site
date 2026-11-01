<?php
/**
 * Approved review Bundle E on staging38 / isolated localhost only.
 * Two exact page-image replacements, prepared WebP and two WPCode snippets.
 * All ten original images remain unchanged. No CSS, prose, SEO or options edit.
 * Private complete snapshots and verified off-site DB backup precede writes.
 * Rollback refuses drift, restores old stores, then removes only resources
 * created by this snapshot whose full posts/meta/terms/files still match.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) ) { WP_CLI::error( 'staging38 / isolated localhost only. Live disabled.' ); }
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'upload_files' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) { WP_CLI::error( 'Requires Astra, WPCode and administrator --user=1.' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) { WP_CLI::error( 'Unknown mode.' ); }
$data = require __DIR__ . '/bundle-e-content.php';
global $wp_version, $wpdb;
if ( $wp_version !== $data['wp_version'] ) { WP_CLI::error( 'Installed core changed since emoji/source review; nothing changed.' ); }
$media = $data['media']; $payload = __DIR__ . '/assets/' . $media['name'];
$dim = is_file( $payload ) ? getimagesize( $payload ) : false;
if ( ! $dim || $dim[0] !== $media['width'] || $dim[1] !== $media['height'] || $dim['mime'] !== $media['mime'] || filesize( $payload ) !== $media['filesize'] || hash_file( 'sha256', $payload ) !== $media['sha256'] ) { WP_CLI::error( 'Prepared WebP missing/changed.' ); }
foreach ( $data['new_snippets'] as $cfg ) {
	if ( hash( 'sha256', $cfg['code'] ) !== $cfg['sha256'] ) { WP_CLI::error( 'Frozen runtime hash differs.' ); }
	try { token_get_all( "<?php\n" . $cfg['code'], TOKEN_PARSE ); } catch ( \ParseError $e ) { WP_CLI::error( 'Runtime syntax: ' . $e->getMessage() ); }
}
$hydrate = function ( $value ) use ( &$hydrate, $host ) {
	if ( is_string( $value ) ) { return str_replace( array( '{{HTTP}}', '{{HTTPS}}' ), array( 'http://' . $host, ( 'localhost:8090' === $host ? 'http://' : 'https://' ) . $host ), $value ); }
	if ( is_array( $value ) ) { foreach ( $value as $key => $item ) { $value[ $key ] = $hydrate( $item ); } }
	return $value;
};
$data = $hydrate( $data );
$upload = wp_upload_dir( null, false, false );
$upload_root = empty( $upload['error'] ) ? realpath( $upload['basedir'] ) : false;
if ( ! $upload_root || '/wp-content/uploads' !== rtrim( (string) wp_parse_url( $upload['baseurl'], PHP_URL_PATH ), '/' ) ) { WP_CLI::error( 'Unexpected uploads location.' ); }
if ( _wp_relative_upload_path( $upload['basedir'] . '/' . $media['relative_file'] ) !== $media['relative_file'] ) { WP_CLI::error( 'WordPress logical upload path does not map to the expected relative file.' ); }
$file_info = function ( $relative ) use ( $upload_root ) {
	$path = realpath( $upload_root . '/' . $relative );
	if ( ! $path || 0 !== strpos( $path, $upload_root . DIRECTORY_SEPARATOR ) || ! is_file( $path ) || ! is_readable( $path ) ) { throw new \RuntimeException( 'Image unavailable: ' . $relative ); }
	$d = getimagesize( $path ); if ( ! $d ) { throw new \RuntimeException( 'Image dimensions unavailable: ' . $relative ); }
	return array( 'width' => $d[0], 'height' => $d[1], 'mime' => $d['mime'], 'filesize' => filesize( $path ), 'sha256' => hash_file( 'sha256', $path ) );
};
$original_files = function () use ( $data, $file_info, $wpdb, $upload_root ) {
	$out = array();
	foreach ( $data['originals'] as $cfg ) {
		$rel = $cfg['relative_file']; $got = $file_info( $rel );
		foreach ( array( 'width', 'height', 'mime', 'filesize', 'sha256' ) as $key ) { if ( $got[ $key ] !== $cfg[ $key ] ) { throw new \RuntimeException( 'Original image differs: ' . $rel ); } }
		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s", '_wp_attached_file', $rel ) );
		if ( count( $ids ) !== ( $cfg['attachment_expected'] ? 1 : 0 ) || ( $ids && ( 'attachment' !== get_post_type( $ids[0] ) || realpath( get_attached_file( $ids[0] ) ) !== realpath( $upload_root . '/' . $rel ) ) ) ) { throw new \RuntimeException( 'Original attachment lookup differs: ' . $rel ); }
		$out[ $rel ] = $got;
		foreach ( $cfg['sizes'] as $size ) {
			$got = $file_info( $size['relative_file'] );
			foreach ( array( 'width', 'height', 'mime', 'filesize' ) as $key ) { if ( $got[ $key ] !== $size[ $key ] ) { throw new \RuntimeException( 'Existing responsive image differs.' ); } }
			$out[ $size['relative_file'] ] = $got;
		}
	}
	ksort( $out ); return $out;
};
$all_meta = function ( $id ) { $out = get_post_meta( $id ); ksort( $out ); return $out; };
$terms = function ( $id ) { $out = array(); foreach ( array( 'wpcode_type', 'wpcode_location' ) as $tax ) { $v = wp_get_object_terms( $id, $tax, array( 'fields' => 'slugs' ) ); if ( is_wp_error( $v ) ) { throw new \RuntimeException( 'Snippet terms unavailable.' ); } sort( $v ); $out[ $tax ] = $v; } return $out; };
$rows = function ( $id ) { $out = array(); foreach ( get_post_meta( $id ) as $key => $values ) { if ( 0 === strpos( $key, 'surerank_settings_' ) ) { $out[ $key ] = $values; } } ksort( $out ); return $out; };
$pages = $new_pages = $snippets = $states = array();
$report = function ( $label, $state ) use ( &$states ) { WP_CLI::log( $label . '=' . $state ); $states[] = $state; };
try {
	$original_files();
	foreach ( $data['pages'] as $slug => $cfg ) {
		$found = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
		if ( count( $found ) !== 1 || 'publish' !== $found[0]->post_status || ( 'home' === $slug && (int) get_option( 'page_on_front' ) !== (int) $found[0]->ID ) ) { throw new \RuntimeException( 'Page missing, ambiguous or wrong front page: ' . $slug ); }
		$p = $found[0]; $pages[ $slug ] = $p; $hash = hash( 'sha256', $p->post_content ); $hashes = $cfg['hashes'][ $host ];
		$state = $hash === $hashes['new'] ? 'applied' : ( $hash === $hashes['old'] ? 'ready' : 'CHANGED' );
		if ( 'ready' === $state ) {
			$content = $p->post_content;
			foreach ( $cfg['pairs'] as $pair ) { if ( substr_count( $content, $pair['old'] ) !== $pair['count'] ) { throw new \RuntimeException( 'Fragment drift: ' . $pair['label'] ); } $content = str_replace( $pair['old'], $pair['new'], $content ); }
			if ( hash( 'sha256', $content ) !== $hashes['new'] ) { throw new \RuntimeException( 'Packaged image replacement hash differs.' ); }
			$new_pages[ $slug ] = $content;
		}
		$report( 'page ' . $slug, $state );
	}
	foreach ( $data['new_snippets'] as $title => $cfg ) {
		$found = get_posts( array( 'post_type' => 'wpcode', 'title' => $title, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
		$state = 'CHANGED';
		if ( ! $found ) { $state = 'ready'; }
		elseif ( count( $found ) === 1 ) {
			$p = $found[0]; $meta = $cfg['meta']; ksort( $meta );
			if ( 'publish' === $p->post_status && $p->post_content === $cfg['code'] && $all_meta( $p->ID ) === $meta && $terms( $p->ID ) === array( 'wpcode_type' => array( 'php' ), 'wpcode_location' => array( 'everywhere' ) ) ) { $state = 'applied'; $snippets[ $title ] = (int) $p->ID; }
		}
		$report( 'new snippet ' . $title, $state );
	}
	$found_media = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'suppress_filters' => true, 'meta_query' => array( 'relation' => 'OR', array( 'key' => '_wp_attached_file', 'value' => $media['relative_file'] ), array( 'key' => '_ts_review_e_sha256', 'value' => $media['sha256'] ) ) ) );
	if ( count( $found_media ) > 1 ) { throw new \RuntimeException( 'Duplicate prepared WebP attachments.' ); }
	$media_id = $found_media ? (int) $found_media[0]->ID : 0;
	if ( $media_id && ( get_post_meta( $media_id, '_wp_attached_file', true ) !== $media['relative_file'] || get_post_mime_type( $media_id ) !== $media['mime'] || $file_info( $media['relative_file'] ) !== array( 'width' => $media['width'], 'height' => $media['height'], 'mime' => $media['mime'], 'filesize' => $media['filesize'], 'sha256' => $media['sha256'] ) ) ) { throw new \RuntimeException( 'Prepared WebP bytes, URL or dimensions differ.' ); }
	if ( ! $media_id && file_exists( $upload_root . '/' . $media['relative_file'] ) ) { throw new \RuntimeException( 'Unregistered target file exists; refuse overwrite.' ); }
	if ( ! $media_id && glob( dirname( $upload_root . '/' . $media['relative_file'] ) . '/' . pathinfo( $media['name'], PATHINFO_FILENAME ) . '*' ) ) { throw new \RuntimeException( 'Unregistered derived-name collision; refuse overwrite.' ); }
} catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
$unique = array_values( array_unique( $states ) ); $state = count( $unique ) === 1 ? $unique[0] : 'CHANGED';
if ( 'applied' === $state && ! $media_id ) { $state = 'CHANGED'; }
WP_CLI::log( 'prepared WebP=' . ( $media_id ? 'exact existing asset' : 'ready to import once' ) . '; originals=10 preserved' );
WP_CLI::log( 'host=' . $home . ' state=' . $state );
if ( 'preflight' === $mode ) { if ( 'CHANGED' === $state ) { WP_CLI::error( 'Drift/partial state; nothing changed.' ); } WP_CLI::success( 'Preflight ' . $state . '; nothing changed.' ); return; }
$dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $dir || '/' !== $dir[0] || ! is_dir( $dir ) || ! is_writable( $dir ) || 0 === strpos( realpath( $dir ), realpath( ABSPATH ) ) ) { WP_CLI::error( 'Private backup directory outside ABSPATH required.' ); }
$capture = function () use ( $pages, $rows, $original_files ) {
	$out = array( 'pages' => array(), 'original_files' => $original_files() );
	foreach ( $pages as $slug => $p ) { $p = get_post( $p->ID ); $out['pages'][ $slug ] = array( 'id' => (int) $p->ID, 'type' => $p->post_type, 'status' => $p->post_status, 'title' => $p->post_title, 'content' => $p->post_content, 'rows' => $rows( $p->ID ) ); }
	return $out;
};
$capture_snippets = function ( $ids ) use ( $all_meta, $terms ) { $out = array(); foreach ( $ids as $title => $id ) { $out[ $title ] = array( 'id' => $id, 'post' => get_post( $id, ARRAY_A, 'raw' ), 'meta' => $all_meta( $id ), 'terms' => $terms( $id ) ); } return $out; };
$capture_media = function ( $id ) use ( $all_meta, $upload_root ) {
	$path = (string) get_attached_file( $id ); $metadata = wp_get_attachment_metadata( $id ); $paths = array( $path );
	foreach ( (array) ( $metadata['sizes'] ?? array() ) as $size ) { $paths[] = dirname( $path ) . '/' . $size['file']; }
	if ( ! empty( $metadata['original_image'] ) ) { $paths[] = dirname( $path ) . '/' . $metadata['original_image']; }
	$files = array(); foreach ( array_unique( $paths ) as $file ) { $real = realpath( $file ); if ( ! $real || 0 !== strpos( $real, $upload_root . DIRECTORY_SEPARATOR ) || ! is_file( $real ) ) { throw new \RuntimeException( 'Prepared media file unavailable/outside uploads.' ); } $files[ $real ] = hash_file( 'sha256', $real ); } ksort( $files );
	return array( 'id' => (int) $id, 'post' => get_post( $id, ARRAY_A, 'raw' ), 'meta' => $all_meta( $id ), 'files' => $files );
};
$refresh = function () { if ( class_exists( '\SureRank\Inc\Functions\Settings' ) ) { \SureRank\Inc\Functions\Settings::clear_cache(); } if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { wpcode()->cache->cache_all_loaded_snippets(); } };
$set = function ( $id, $content ) { $r = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $content ) ), true ); if ( is_wp_error( $r ) || ! $r || get_post_field( 'post_content', $id ) !== $content ) { throw new \RuntimeException( 'Post save/read-back failed.' ); } };
$restore = function ( $old ) use ( $rows, $refresh ) {
	foreach ( $old['pages'] as $p ) {
		$r = wp_update_post( wp_slash( array( 'ID' => $p['id'], 'post_content' => $p['content'], 'post_title' => $p['title'], 'post_status' => $p['status'], 'post_type' => $p['type'] ) ), true );
		if ( is_wp_error( $r ) || ! $r ) { throw new \RuntimeException( 'Page restore failed.' ); }
	}
	foreach ( $old['pages'] as $p ) { foreach ( $rows( $p['id'] ) as $key => $values ) { delete_post_meta( $p['id'], $key ); } foreach ( $p['rows'] as $key => $values ) { foreach ( $values as $value ) { add_post_meta( $p['id'], $key, wp_slash( maybe_unserialize( $value ) ) ); } } }
	$refresh();
};
$save = function ( $file, $snap ) { $json = wp_json_encode( $snap ); if ( false === $json || false === file_put_contents( $file, $json, LOCK_EX ) || file_get_contents( $file ) !== $json || ! chmod( $file, 0600 ) ) { throw new \RuntimeException( 'Snapshot save/verification failed.' ); } };
$upload_files = function () use ( $upload_root, $media ) {
	$out = array(); $prefix = dirname( $upload_root . '/' . $media['relative_file'] ) . '/' . pathinfo( $media['name'], PATHINFO_FILENAME );
	foreach ( glob( $prefix . '*' ) as $path ) { $real = realpath( $path ); if ( ! $real || ! is_file( $real ) || 0 !== strpos( $real, $upload_root . DIRECTORY_SEPARATOR ) ) { throw new \RuntimeException( 'Unexpected owned upload path.' ); } $out[ $real ] = hash_file( 'sha256', $real ); }
	ksort( $out ); return $out;
};
$cleanup = function ( $snap ) use ( $capture_snippets, $capture_media, $refresh ) {
	if ( $capture_snippets( $snap['created_snippet_ids'] ) !== $snap['created_snippets'] || ( $snap['created_media_id'] && $capture_media( $snap['created_media_id'] ) !== $snap['created_media'] ) ) { throw new \RuntimeException( 'Owned resources changed; refuse removal.' ); }
	foreach ( $snap['created_upload_files'] as $path => $hash ) { if ( ! is_file( $path ) || hash_file( 'sha256', $path ) !== $hash ) { throw new \RuntimeException( 'Owned upload changed; refuse removal.' ); } }
	foreach ( $snap['created_snippet_ids'] as $id ) { wp_delete_post( $id, true ); if ( get_post( $id ) ) { throw new \RuntimeException( 'Owned snippet removal failed.' ); } }
	if ( $snap['created_media_id'] ) { wp_delete_attachment( $snap['created_media_id'], true ); if ( get_post( $snap['created_media_id'] ) ) { throw new \RuntimeException( 'Owned attachment removal failed.' ); } foreach ( $snap['created_media']['files'] as $path => $hash ) { if ( is_file( $path ) ) { throw new \RuntimeException( 'Owned media file removal failed.' ); } } }
	foreach ( $snap['created_upload_files'] as $path => $hash ) { if ( is_file( $path ) && ( hash_file( 'sha256', $path ) !== $hash || ! unlink( $path ) ) ) { throw new \RuntimeException( 'Owned upload cleanup failed.' ); } if ( is_file( $path ) ) { throw new \RuntimeException( 'Owned upload remains after cleanup.' ); } }
	$refresh();
};
if ( 'rollback' === $mode ) {
	$file = $args[1] ?? ''; $snap = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
	try {
		if ( ! is_array( $snap ) || ( $snap['bundle'] ?? '' ) !== 'review-e-v1' || ( $snap['home'] ?? '' ) !== $home || empty( $snap['complete'] ) || hash( 'sha256', wp_json_encode( $snap['old'] ) ) !== ( $snap['old_sha256'] ?? '' ) || hash( 'sha256', wp_json_encode( $snap['new'] ) ) !== ( $snap['new_sha256'] ?? '' ) || $capture() !== $snap['new'] || $snippets !== $snap['created_snippet_ids'] || $capture_snippets( $snippets ) !== $snap['created_snippets'] || $capture_media( $media_id ) !== $snap['final_media'] ) { throw new \RuntimeException( 'Snapshot invalid or a store/media/snippet changed.' ); }
		foreach ( $snap['created_upload_files'] as $path => $hash ) { if ( ! is_file( $path ) || hash_file( 'sha256', $path ) !== $hash ) { throw new \RuntimeException( 'Owned upload changed.' ); } }
	} catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
	try { $restore( $snap['old'] ); if ( $capture() !== $snap['old'] ) { throw new \RuntimeException( 'Exact rollback read-back differs.' ); } }
	catch ( \Throwable $e ) { try { $restore( $snap['new'] ); if ( $capture() !== $snap['new'] ) { throw new \RuntimeException( 'Recovery read-back differs.' ); } } catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery failed: ' . $r->getMessage() . '; snapshot ' . $file ); } WP_CLI::error( $e->getMessage() . '; applied pages restored and verified.' ); }
	try { $cleanup( $snap ); if ( $capture() !== $snap['old'] ) { throw new \RuntimeException( 'Final old-store read-back differs.' ); } }
	catch ( \Throwable $e ) { WP_CLI::error( 'Old pages restored; cleanup failed: ' . $e->getMessage() . '; snapshot ' . $file ); }
	WP_CLI::success( 'Exact rollback complete from ' . $file ); return;
}
if ( 'applied' === $state ) { WP_CLI::success( 'Already applied; nothing changed.' ); return; }
if ( 'ready' !== $state ) { WP_CLI::error( 'Not reviewed state; nothing changed.' ); }
try { $old = $capture(); $previous_media = $media_id ? $capture_media( $media_id ) : null; }
catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
foreach ( $old['pages'] as $slug => $p ) { if ( hash( 'sha256', $p['content'] ) !== $data['pages'][ $slug ]['hashes'][ $host ]['old'] || 'publish' !== $p['status'] || 'page' !== $p['type'] ) { WP_CLI::error( 'Page drift after preflight; nothing changed.' ); } }
$file = $dir . '/bundle-e-before-' . gmdate( 'Ymd-His' ) . '.json';
$snap = array( 'bundle' => 'review-e-v1', 'home' => $home, 'created' => gmdate( 'c' ), 'complete' => false, 'created_media_id' => 0, 'created_media' => null, 'created_upload_files' => array(), 'previous_media' => $previous_media, 'created_snippet_ids' => array(), 'created_snippets' => array(), 'old' => $old, 'old_sha256' => hash( 'sha256', wp_json_encode( $old ) ) );
try { $save( $file, $snap ); if ( $capture() !== $old || ( $media_id && $capture_media( $media_id ) !== $previous_media ) ) { throw new \RuntimeException( 'Store drift before writing.' ); } }
catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
try {
	if ( ! $media_id ) {
		require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php'; require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp = tempnam( $dir, 'review-e-import-' ); if ( ! $tmp || ! copy( $payload, $tmp ) || ! chmod( $tmp, 0600 ) ) { throw new \RuntimeException( 'Private import staging failed.' ); }
		$fixed_uploads = function ( $value ) use ( $media ) { $value['subdir'] = '/' . dirname( $media['relative_file'] ); $value['path'] = $value['basedir'] . $value['subdir']; $value['url'] = $value['baseurl'] . $value['subdir']; return $value; };
		add_filter( 'upload_dir', $fixed_uploads );
		$sideload = array( 'name' => $media['name'], 'tmp_name' => $tmp, 'type' => $media['mime'], 'size' => $media['filesize'], 'error' => 0 );
		try { $handled = wp_handle_sideload( $sideload, array( 'test_form' => false, 'mimes' => array( 'webp' => 'image/webp' ) ) ); }
		finally { remove_filter( 'upload_dir', $fixed_uploads ); if ( is_file( $tmp ) ) { unlink( $tmp ); } }
		if ( ! empty( $handled['error'] ) ) { throw new \RuntimeException( 'WebP upload: ' . $handled['error'] ); }
		$wordpress_file = (string) $handled['file']; $created_path = realpath( $wordpress_file );
		if ( ! $created_path || 0 !== strpos( $created_path, $upload_root . DIRECTORY_SEPARATOR ) || hash_file( 'sha256', $created_path ) !== $media['sha256'] ) { throw new \RuntimeException( 'Uploaded file unavailable/outside uploads or bytes differ.' ); }
		$snap['created_upload_files'] = array( $created_path => $media['sha256'] ); $save( $file, $snap );
		if ( $created_path !== realpath( $upload_root . '/' . $media['relative_file'] ) || $handled['type'] !== $media['mime'] ) { throw new \RuntimeException( 'Unexpected upload filename/MIME.' ); }
		// WordPress derives relative metadata by its configured logical basedir.
		// Keep realpath only for byte/path guards; SiteGround uses symlinked roots.
		$id = wp_insert_attachment( array( 'post_mime_type' => $media['mime'], 'post_title' => $media['title'], 'post_content' => '', 'post_status' => 'inherit', 'guid' => $handled['url'] ), $wordpress_file, 0, true );
		if ( is_wp_error( $id ) ) { throw new \RuntimeException( 'WebP import: ' . $id->get_error_message() ); }
		$media_id = (int) $id; $snap['created_media_id'] = $media_id; $save( $file, $snap ); $snap['created_media'] = $capture_media( $media_id ); $save( $file, $snap );
		try {
			$metadata = wp_generate_attachment_metadata( $media_id, $wordpress_file );
			if ( ! is_array( $metadata ) || ( $metadata['width'] ?? null ) !== $media['width'] || ( $metadata['height'] ?? null ) !== $media['height'] || ( $metadata['file'] ?? null ) !== $media['relative_file'] ) { throw new \RuntimeException( 'Generated media dimensions/file metadata incomplete.' ); }
			wp_update_attachment_metadata( $media_id, $metadata );
			if ( wp_get_attachment_metadata( $media_id ) !== $metadata ) { throw new \RuntimeException( 'Saved attachment metadata differs.' ); }
			update_post_meta( $media_id, '_ts_review_e_sha256', $media['sha256'] ); update_post_meta( $media_id, '_ts_review_e_source_sha256', $media['source_sha256'] ); update_post_meta( $media_id, '_wp_attachment_image_alt', $media['alt'] );
		}
		finally { $snap['created_upload_files'] = $upload_files(); $snap['created_media'] = $capture_media( $media_id ); $save( $file, $snap ); }
		if ( get_post_meta( $media_id, '_wp_attached_file', true ) !== $media['relative_file'] || hash_file( 'sha256', get_attached_file( $media_id ) ) !== $media['sha256'] || get_post_mime_type( $media_id ) !== $media['mime'] ) { throw new \RuntimeException( 'Imported WebP URL/bytes/MIME differ.' ); }
	}
	foreach ( $data['new_snippets'] as $title => $cfg ) {
		$snippet = new WPCode_Snippet( array( 'title' => $title, 'code' => $cfg['code'], 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true, 'note' => $cfg['note'] ) );
		$id = $snippet->save(); if ( ! $id ) { throw new \RuntimeException( 'Snippet creation failed: ' . $title ); }
		$snippets[ $title ] = (int) $id; $snap['created_snippet_ids'] = $snippets; $save( $file, $snap ); $snap['created_snippets'] = $capture_snippets( $snippets ); $save( $file, $snap );
		try { $set( (int) $id, $cfg['code'] ); }
		finally { $snap['created_snippets'] = $capture_snippets( $snippets ); $save( $file, $snap ); }
	}
	foreach ( $pages as $slug => $p ) { $set( $p->ID, $new_pages[ $slug ] ); }
	foreach ( $old['pages'] as $p ) { foreach ( $rows( $p['id'] ) as $key => $values ) { delete_post_meta( $p['id'], $key ); } foreach ( $p['rows'] as $key => $values ) { foreach ( $values as $value ) { add_post_meta( $p['id'], $key, wp_slash( maybe_unserialize( $value ) ) ); } } }
	$refresh(); $new = $capture(); $expected = $old; foreach ( $new_pages as $slug => $content ) { $expected['pages'][ $slug ]['content'] = $content; }
	if ( $new !== $expected || ( $previous_media && $capture_media( $media_id ) !== $previous_media ) ) { throw new \RuntimeException( 'Saved stores or unowned asset differ.' ); }
	foreach ( $snippets as $title => $id ) { $cfg = $data['new_snippets'][ $title ]; $meta = $cfg['meta']; ksort( $meta ); if ( get_post_field( 'post_content', $id ) !== $cfg['code'] || 'publish' !== get_post_status( $id ) || $all_meta( $id ) !== $meta || $terms( $id ) !== array( 'wpcode_type' => array( 'php' ), 'wpcode_location' => array( 'everywhere' ) ) ) { throw new \RuntimeException( 'Saved snippet code/settings differ.' ); } }
	$snap['created_snippets'] = $capture_snippets( $snippets ); $snap['final_media'] = $capture_media( $media_id ); if ( $snap['created_media_id'] && $snap['final_media'] !== $snap['created_media'] ) { throw new \RuntimeException( 'Created media changed during apply.' ); }
	$snap['new'] = $new; $snap['new_sha256'] = hash( 'sha256', wp_json_encode( $new ) ); $snap['complete'] = true; $save( $file, $snap );
} catch ( \Throwable $e ) {
	try { $restore( $old ); if ( $capture() !== $old ) { throw new \RuntimeException( 'Old-store recovery differs.' ); } $cleanup( $snap ); }
	catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery/cleanup failed: ' . $r->getMessage() . '; snapshot ' . $file ); }
	WP_CLI::error( $e->getMessage() . '; old stores restored and only owned resources removed. Snapshot: ' . $file );
}
WP_CLI::success( 'Applied Bundle E. Before-snapshot: ' . $file );
