<?php
/**
 * Add a Content-Security-Policy to WPCode snippet 1036 "HeaderSecurityProduction" (the PHP snippet that sets the site's security headers) for ONE site at a time.
 * Allow-list: staging31 only. Live is added only on Ryan's second, explicit go. Ryan, 2026-10-17: "go" (security headers, CSP for logged-out visitors only).
 *
 * What it changes (one store, snapshotted and reversible): the snippet gains a block that sends Content-Security-Policy ONLY when ! is_user_logged_in(), so a
 * logged-in admin (previewing pages, admin bar, Site Kit) never receives it; wp-admin and wp-login.php do not run the send_headers hook at all. The five headers the
 * snippet already sends are untouched. The policy is the one validated with tools/csp-test.mjs against all public pages (0 violations, 16/16 page-views pixel-identical).
 * Any new embed (a video, another map or font provider, analytics) must be added to the policy or it will be blocked.
 *
 * Modes (first WP-CLI argument; run with --user=1):
 *   (none) / preflight    read-only: report the snippet's state (ready, applied, or CHANGED = not the reviewed state)
 *   apply                 snapshot, then change the snippet and refresh WPCode's cache
 *   rollback FILE         put the snippet back from the snapshot, if it is still exactly what this installer wrote
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'staging31.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (staging31 only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved code.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$snippet_id = 1036;
// sha256 of the snippet's exact stored bytes (Windows line endings included). Identical on staging31 and live.
$snippet_old_sha = 'c5a98d4947b18c28802b2c4c240be440fe75facca3227867f9f8032550f37f64';
$snippet_new     = <<<'CODE'
add_action('send_headers', function () {

    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('Cross-Origin-Resource-Policy: same-site');

    // Content-Security-Policy for logged-out visitors only: a logged-in admin (preview, admin bar, Site Kit) never receives it.
    // Validated against every public page with scripts/work-with-me-employer/tools/csp-test.mjs. A new embed (video, another
    // map or font provider, analytics) must be added here or it will be blocked.
    if (!is_user_logged_in()) {
        header('Content-Security-Policy: ' . implode('; ', array(
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://maps.gstatic.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: https://*.google.com https://*.gstatic.com https://*.googleapis.com",
            "connect-src 'self' https://maps.googleapis.com https://places.googleapis.com https://www.google.com",
            "frame-src 'self' https://www.google.com https://maps.google.com",
            "worker-src 'self' blob:",
            "media-src 'self'",
            "object-src https://maps.google.com",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        )));
    }

}, 999);
CODE;
$snippet_new .= "\n";

$sn = get_post( $snippet_id );
if ( ! $sn || 'wpcode' !== $sn->post_type || 'publish' !== $sn->post_status || 'HeaderSecurityProduction' !== $sn->post_title ) {
	WP_CLI::error( 'WPCode snippet 1036 "HeaderSecurityProduction" was not found as a published snippet.' );
}
$sn_now = (string) $sn->post_content;
if ( hash( 'sha256', $sn_now ) === $snippet_old_sha ) {
	$sn_state = 'ready';
} elseif ( $sn_now === $snippet_new ) {
	$sn_state = 'applied';
} else {
	$sn_state = 'CHANGED';
}
$report = sprintf( 'host=%s snippet_1036=%s', $home, $sn_state );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $sn_state ) {
		WP_CLI::error( 'The snippet is neither the reviewed original nor already changed; stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
/** Rebuild WPCode's snippet cache; true if the cache now reflects $needle_present for the CSP header text. */
$refresh_cache = function ( $csp_expected ) {
	if ( function_exists( 'wpcode' ) && isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
	return $csp_expected === ( false !== strpos( maybe_serialize( get_option( 'wpcode_snippets' ) ), 'Content-Security-Policy' ) );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $sn_state ) {
		WP_CLI::error( 'The snippet is not in the reviewed state; nothing was changed. ' . $report );
	}
	if ( 'applied' === $sn_state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/headers-csp-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'snippet' => array( 'id' => $snippet_id, 'before' => $sn_now, 'after_sha' => hash( 'sha256', $snippet_new ) ) );
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || ! filesize( $file ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$r = wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $snippet_new ) ), true );
	if ( is_wp_error( $r ) || get_post( $snippet_id )->post_content !== $snippet_new ) {
		wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $sn_now ) ), true );
		$refresh_cache( false );
		WP_CLI::error( 'The snippet did not save exactly as expected; it was put back.' );
	}
	if ( ! $refresh_cache( true ) ) {
		wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $sn_now ) ), true );
		$refresh_cache( false );
		WP_CLI::error( 'WPCode\'s cached copy did not pick up the new code; the snippet was put back.' );
	}
	WP_CLI::success( 'Applied the Content-Security-Policy. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || ! isset( $snap['snippet']['before'] ) ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
if ( 'applied' !== $sn_state ) {
	WP_CLI::success( 'Nothing to roll back: the snippet is not in the state this installer wrote. ' . $report );
	return;
}
$r = wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => (string) $snap['snippet']['before'] ) ), true );
if ( is_wp_error( $r ) ) {
	WP_CLI::error( 'The snippet could not be restored: ' . $r->get_error_message() );
}
$refresh_cache( false );
WP_CLI::success( 'Rollback done: snippet 1036 restored to the snapshot.' );
