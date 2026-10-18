<?php
/**
 * Content-Security-Policy VERSION 2 (a FIX): let Google's map frame load in Firefox and Safari, in WPCode snippet 1036 "HeaderSecurityProduction", for ONE site at a time.
 * Found 2026-10-18 (Ryan: "the google map doesn't showup on the phone now. it's a white spot" and "i don't see the map on staging31"): the Contact page's map is an <embed> that loads
 * https://maps.google.com/maps?...&output=embed and is then REDIRECTED to https://www.google.com/maps/embed?... Chrome checks that frame under frame-src (which allows www.google.com),
 * but Firefox (and Safari) check an <embed>'s whole redirect chain under object-src, which listed only maps.google.com, so they BLOCKED it and showed a white box. Reproduced in
 * Firefox: "Content-Security-Policy: The page's settings blocked the loading of a resource (object-src) at https://www.google.com/maps/embed?...". The CSP was deployed on 2026-10-17
 * after a Chrome-only check (tools/csp-test.mjs), which is why it was missed; the check now also runs in Firefox.
 *
 * The ONLY change: the line "object-src https://maps.google.com" becomes "object-src https://maps.google.com https://www.google.com". Everything else in the snippet and the policy is
 * byte for byte what install-headers-csp.php wrote; this installer refuses any snippet that is not exactly that version (or already exactly the fixed one).
 *
 * Allow-list: staging31, staging32 and live (snippet 1036 exists on all three). Live only on Ryan's go.
 *
 * Modes (first WP-CLI argument; run with --user=1):
 *   (none) / preflight    read-only: report the snippet's state (ready = the version-1 text, applied = the fixed text, or CHANGED = anything else)
 *   apply                 snapshot, then change the snippet and refresh WPCode's cache
 *   rollback FILE         put the snippet back from the snapshot, if it is still exactly what this installer wrote
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'staging31.thaisolitude.com', 'staging32.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (staging31, staging32 or live only).' );
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
// Version 1 = exactly what install-headers-csp.php wrote (the snippet on staging31, staging32 and live today). Version 2 = the same with one object-src line changed.
$snippet_old = <<<'CODE'
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
$snippet_old .= "\n";
$snippet_new  = str_replace( '"object-src https://maps.google.com",', '"object-src https://maps.google.com https://www.google.com",', $snippet_old, $n_replaced );
if ( 1 !== $n_replaced || $snippet_new === $snippet_old ) {
	WP_CLI::error( 'Internal error: the object-src line could not be changed in the known snippet text.' );
}

$sn = get_post( $snippet_id );
if ( ! $sn || 'wpcode' !== $sn->post_type || 'publish' !== $sn->post_status || 'HeaderSecurityProduction' !== $sn->post_title ) {
	WP_CLI::error( 'WPCode snippet 1036 "HeaderSecurityProduction" was not found as a published snippet.' );
}
$sn_now = (string) $sn->post_content;
if ( $sn_now === $snippet_old ) {
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
/** Rebuild WPCode's snippet cache; true if the cache now reflects whether the FIXED object-src text is present. */
$refresh_cache = function ( $csp_expected ) {
	if ( function_exists( 'wpcode' ) && isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
	return $csp_expected === ( false !== strpos( maybe_serialize( get_option( 'wpcode_snippets' ) ), 'object-src https://maps.google.com https://www.google.com' ) );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $sn_state ) {
		WP_CLI::error( 'The snippet is not in the reviewed state; nothing was changed. ' . $report );
	}
	if ( 'applied' === $sn_state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/headers-csp-v2-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	WP_CLI::success( 'Applied the fixed Content-Security-Policy (object-src now also allows www.google.com). Before-snapshot: ' . $file );
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
