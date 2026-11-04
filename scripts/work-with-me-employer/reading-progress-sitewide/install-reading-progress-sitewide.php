<?php
/**
 * Extend the reviewed CSS-only reading indicator to every public theme page.
 * WP-CLI: --user=1 eval-file install-reading-progress-sitewide.php preflight|apply|rollback [FILE]
 * Only localhost:8090, staging38 and the exact observed live origin.
 * Remote mutations use push-reading-progress-sitewide.sh for a verified off-site DB backup.
 * This installer saves exact CSS bytes and refuses rollback after any CSS drift.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST );
$port = wp_parse_url( $home, PHP_URL_PORT );
$host .= $port ? ':' . $port : '';
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Target host is not allowed; nothing changed.' );
}
// Staging38 and live store HTTP home URLs (live inspected 2026-11-04), although
// visitors use HTTPS. Keep exact origins; no www, paths, ports or other sites.
if ( ! in_array( $home, array( 'http://localhost:8090', 'http://staging38.thaisolitude.com', 'https://staging38.thaisolitude.com', 'http://thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Only localhost:8090, exact staging38 origins and the observed live origin are allowed; nothing changed.' );
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
$legacy = file_get_contents( __DIR__ . '/reading-progress-v1.css' );
$block = file_get_contents( __DIR__ . '/reading-progress.css' );
// Freeze both reviewed payloads. In particular, never derive legacy bytes from
// the mutable installer of a future release: they are this migration's contract.
if ( false === $legacy || hash( 'sha256', $legacy ) !== 'b6b7556107abe24eb8ee9a53154a801ae59f4356e44d4a797c93021b78850ba1' || false === $block || hash( 'sha256', $block ) !== 'f7cbdd8c307ca1fb270b3109d1b705847dba835926b2f7cca2f4df88a3dc0072' ) {
	WP_CLI::error( 'The reviewed old/new CSS payload hash does not match; nothing changed.' );
}
$css_state = static function ( $value ) use ( $start, $end, $legacy, $block ) {
	$starts = substr_count( $value, $start );
	$ends = substr_count( $value, $end );
	if ( 0 === $starts && 0 === $ends ) {
		return 'ready-empty';
	}
	if ( 1 !== $starts || 1 !== $ends ) {
		return 'CHANGED';
	}
	if ( 1 === substr_count( $value, $block ) ) {
		return 'applied';
	}
	return 1 === substr_count( $value, $legacy ) ? 'ready-v1' : 'CHANGED';
};
$upgrade = static function ( $value ) use ( $css_state, $legacy, $block ) {
	$state = $css_state( $value );
	if ( 'ready-empty' === $state ) {
		return $value . "\n\n" . $block;
	}
	if ( 'ready-v1' === $state ) {
		return str_replace( $legacy, $block, $value );
	}
	return false;
};
$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'Known site Additional CSS is missing; nothing changed.' );
}
$css = (string) $post->post_content;
$state = $css_state( $css );
if ( 'CHANGED' === $state ) {
	WP_CLI::error( 'Partial, changed or duplicate reading-progress block; nothing changed.' );
}
if ( 'preflight' === $mode ) {
	WP_CLI::success( 'Sitewide reading progress: ' . $state . '; CSS bytes=' . strlen( $css ) . '; nothing changed.' );
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
		WP_CLI::success( 'Nothing to do: sitewide reading progress already applied.' );
		return;
	}
	$new = $upgrade( $css );
	if ( false === $new ) {
		WP_CLI::error( 'The CSS cannot be upgraded from its current state.' );
	}
	$snapshot = array(
		'bundle' => 'reading-progress-v2', 'home' => $home, 'created' => gmdate( 'c' ),
		'css_id' => (int) $post->ID, 'css' => $css, 'css_sha256' => hash( 'sha256', $css ),
		'applied_sha256' => hash( 'sha256', $new ), 'block_sha256' => hash( 'sha256', $block ), 'legacy_sha256' => hash( 'sha256', $legacy ),
	);
	$file = $private . '/reading-progress-sitewide-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
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
	if ( ! is_array( $snapshot ) || ( $snapshot['bundle'] ?? '' ) !== 'reading-progress-v2' || ( $snapshot['home'] ?? '' ) !== $home || ( $snapshot['css_id'] ?? 0 ) !== (int) $post->ID || ! is_string( $snapshot['css'] ?? null ) || hash( 'sha256', $snapshot['css'] ) !== ( $snapshot['css_sha256'] ?? '' ) || hash( 'sha256', $block ) !== ( $snapshot['block_sha256'] ?? '' ) || hash( 'sha256', $legacy ) !== ( $snapshot['legacy_sha256'] ?? '' ) || false === $upgrade( $snapshot['css'] ) || hash( 'sha256', $upgrade( $snapshot['css'] ) ) !== ( $snapshot['applied_sha256'] ?? '' ) ) {
		WP_CLI::error( 'Snapshot is damaged, from another site/post, or from different CSS code.' );
	}
	$new = $snapshot['css'];
	if ( $css === $new && in_array( $state, array( 'ready-empty', 'ready-v1' ), true ) ) {
		WP_CLI::success( 'Nothing to do: exact original CSS is already restored.' );
		return;
	}
	if ( 'applied' !== $state || $css !== $upgrade( $new ) ) {
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
WP_CLI::success( 'apply' === $mode ? 'Sitewide reading progress applied. Before-snapshot: ' . $file : 'Sitewide reading progress rolled back; exact original CSS restored.' );
