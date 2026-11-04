<?php
/**
 * Append the reviewed CSS-only reading indicator to Astra Additional CSS.
 * WP-CLI: --user=1 eval-file install-reading-progress.php preflight|apply|rollback [FILE]
 * Only localhost:8090 and staging38. Production is deliberately not allowed.
 * Remote mutations use push-reading-progress.sh for a verified off-site DB backup.
 * This installer saves exact CSS bytes and refuses rollback after any CSS drift.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}
$home = untrailingslashit( home_url() );
if ( ! in_array( $home, array( 'http://localhost:8090', 'https://staging38.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Only localhost:8090 and HTTPS staging38 are allowed; nothing changed.' );
}
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_css' ) ) {
	WP_CLI::error( 'Requires Astra and an administrator allowed to edit CSS (--user=1).' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || count( $args ) > ( 'rollback' === $mode ? 2 : 1 ) ) {
	WP_CLI::error( 'Use preflight, apply, or rollback FILE.' );
}
$start = '/* ts-reading-progress:start';
$end = '/* ts-reading-progress:end */';
$block = file_get_contents( __DIR__ . '/reading-progress.css' );
if ( false === $block || 0 !== strpos( $block, $start ) || substr( rtrim( $block ), -strlen( $end ) ) !== $end || 1 !== substr_count( $block, $start ) || 1 !== substr_count( $block, $end ) || strlen( $block ) > 4000 ) {
	WP_CLI::error( 'reading-progress.css must contain exactly one complete marked block.' );
}
foreach ( array( '@import', 'url(', 'expression', '!important', 'javascript:', '<' ) as $bad ) {
	if ( false !== stripos( $block, $bad ) ) {
		WP_CLI::error( 'Unexpected CSS content: ' . $bad );
	}
}
$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'Known site Additional CSS is missing; nothing changed.' );
}
$css = (string) $post->post_content;
$suffix = "\n\n" . $block;
$starts = substr_count( $css, $start );
$ends = substr_count( $css, $end );
$state = 0 === $starts && 0 === $ends ? 'ready' : ( 1 === $starts && 1 === $ends && 1 === substr_count( $css, $block ) ? 'applied' : 'CHANGED' );
if ( 'CHANGED' === $state ) {
	WP_CLI::error( 'Partial, changed or duplicate reading-progress block; nothing changed.' );
}
if ( 'preflight' === $mode ) {
	WP_CLI::success( 'Reading progress: ' . $state . '; CSS bytes=' . strlen( $css ) . '; nothing changed.' );
	return;
}
$backup_dir = getenv( 'WWM_BACKUP_DIR' );
$private = $backup_dir ? realpath( $backup_dir ) : false;
$webroot = realpath( ABSPATH );
if ( ! $private || '/' !== $backup_dir[0] || ! is_dir( $private ) || ! is_writable( $private ) || ! $webroot || $private === $webroot || 0 === strpos( $private . '/', rtrim( $webroot, '/' ) . '/' ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing writable absolute directory outside the web root.' );
}
if ( 'apply' === $mode ) {
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: reading progress already applied.' );
		return;
	}
	$new = $css . $suffix;
	$snapshot = array(
		'bundle' => 'reading-progress-v1', 'home' => $home, 'created' => gmdate( 'c' ),
		'css_id' => (int) $post->ID, 'css' => $css, 'css_sha256' => hash( 'sha256', $css ),
		'applied_sha256' => hash( 'sha256', $new ), 'block_sha256' => hash( 'sha256', $block ),
	);
	$file = $private . '/reading-progress-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
	$encoded = wp_json_encode( $snapshot );
	$mask = umask( 0077 );
	$written = false !== $encoded && false !== file_put_contents( $file, $encoded, LOCK_EX );
	umask( $mask );
	if ( ! $written || ! chmod( $file, 0600 ) || file_get_contents( $file ) !== $encoded ) {
		WP_CLI::error( 'Cannot write and verify private snapshot; nothing changed.' );
	}
} else {
	$file = isset( $args[1] ) ? (string) $args[1] : '';
	$path = $file ? realpath( $file ) : false;
	if ( ! $path || ! is_file( $path ) || 0 !== strpos( $path, $private . '/' ) ) {
		WP_CLI::error( 'Rollback requires a snapshot inside WWM_BACKUP_DIR.' );
	}
	$snapshot = json_decode( file_get_contents( $path ), true );
	if ( ! is_array( $snapshot ) || ( $snapshot['bundle'] ?? '' ) !== 'reading-progress-v1' || ( $snapshot['home'] ?? '' ) !== $home || ( $snapshot['css_id'] ?? 0 ) !== (int) $post->ID || ! is_string( $snapshot['css'] ?? null ) || hash( 'sha256', $snapshot['css'] ) !== ( $snapshot['css_sha256'] ?? '' ) || hash( 'sha256', $block ) !== ( $snapshot['block_sha256'] ?? '' ) || hash( 'sha256', $snapshot['css'] . $suffix ) !== ( $snapshot['applied_sha256'] ?? '' ) ) {
		WP_CLI::error( 'Snapshot is damaged, from another site/post, or from different CSS code.' );
	}
	$new = $snapshot['css'];
	if ( $css === $new && 'ready' === $state ) {
		WP_CLI::success( 'Nothing to do: exact original CSS is already restored.' );
		return;
	}
	if ( 'applied' !== $state || $css !== $new . $suffix ) {
		WP_CLI::error( 'Additional CSS changed since apply; refusing to overwrite later edits.' );
	}
}
$result = wp_update_custom_css_post( $new, array( 'stylesheet' => 'astra' ) );
$check = wp_get_custom_css_post( 'astra' );
if ( is_wp_error( $result ) || ! $check || (int) $check->ID !== (int) $post->ID || (string) $check->post_content !== $new ) {
	wp_update_custom_css_post( $css, array( 'stylesheet' => 'astra' ) );
	$restore = wp_get_custom_css_post( 'astra' );
	if ( ! $restore || (string) $restore->post_content !== $css ) {
		WP_CLI::error( 'Write and recovery both failed; inspect the private snapshot before continuing: ' . $file );
	}
	WP_CLI::error( 'Write verification failed; original CSS restored and verified.' );
}
WP_CLI::success( 'apply' === $mode ? 'Reading progress applied. Before-snapshot: ' . $file : 'Reading progress rolled back; exact original CSS restored.' );
