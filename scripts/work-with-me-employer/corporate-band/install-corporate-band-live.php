<?php
/**
 * Ryan-run, live-only update for the reviewed sitewide booking notice.
 *
 * Updates WPCode snippet 1268 and Astra Additional CSS record 29, then
 * regenerates WPCode's wpcode_snippets cache option. It never writes options
 * blog_public and refuses unless its current value is 1.
 *
 * Modes: preflight (default), apply, rollback <snapshot path>.
 * Apply/rollback require WWM_BACKUP_DIR outside the web root. Take and verify
 * a fresh production database backup before running apply.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = strtolower( (string) wp_parse_url( $home, PHP_URL_HOST ) );
if ( 'https://thaisolitude.com' !== $home || 'thaisolitude.com' !== $host ) {
	WP_CLI::error( 'Refusing to run: expected exact live URL https://thaisolitude.com; found ' . $home );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( '1' !== (string) get_option( 'blog_public' ) ) {
	WP_CLI::error( 'Refusing to run: production blog_public must be 1.' );
}
if ( ! function_exists( 'wpcode' ) || ! isset( wpcode()->cache ) || ! method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
	WP_CLI::error( 'WPCode cache refresh is unavailable; nothing changed.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 as an administrator.' );
}

$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$snippet_id = 1268;
$css_id     = 29;
$old_code_sha = '537c9a7be28cac1574c9b597f850008a66a7c7be4bb818cf1b69620f9d678363';
$new_code_sha = '7298fc1977c11e46a5aa2d9ad4d70f4dc59d0b7049b1b10eda88b2e04f026c7c';
$old_css_sha  = '7a14b1ff2fff98be296f4d9a4ab34a8669cb95a58eee7248fce4427ec97f064e';
$new_css_sha  = '2870307aa8248d06166b2bd7dfc8652cc3dae2cad859bdc718e530b593a1d4f5';

$source = <<<'SNIPPET'
// Corporate & Group Massage announcement strip under the header (Ryan 2026-10-24; wording from the Work With Me and availability plan, 2026-10-25). Shown on every published WordPress Page.
// Remove this snippet and the ts-corp block in Additional CSS to take it away.
// Some published pages use a header template that omits astra_header_after; the content-top fallback covers those pages.
$rendered = false;
$render = function () use ( &$rendered ) {
	if ( $rendered || is_admin() ) {
		return;
	}
	$target = get_page_by_path( 'corporate-massage-portland' );
	if ( ! $target || 'publish' !== $target->post_status ) {
		return;
	}
	$rendered = true;
	$booking_url = 'https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt';
	$head = 'Now booking appointments at <a href="/winding-willow/">Winding Willow Acupuncture</a> and for Solitude' . "'s " . '<a href="' . esc_url( '/corporate-massage-portland/' ) . '">corporate &amp; group massage</a> in Portland.';
	$text = ' <a href="' . esc_url( $booking_url ) . '">Book an appointment with Butsaba</a> at Winding Willow, or arrange a corporate or group massage at a suitable Portland venue with Thai Solitude.';
	$mobile_text = '<strong>' . $head . '</strong>' . $text;
	echo '<div class="ts-corp-band" role="region" aria-label="Booking notice"><div class="ts-corp-band__inner">'
		. '<p class="ts-corp-band__label">Winding Willow Acupuncture &middot; Corporate &amp; Group Massage &middot; Portland</p>'
		. '<p class="ts-corp-band__text"><span class="ts-corp-band__desktop"><strong>' . $head . '</strong>' . $text . '</span><span class="ts-corp-band__mobile">' . $mobile_text . '</span></p>'
		. '</div></div>';
};
add_action( 'astra_header_after', $render );
add_action( 'astra_content_before', $render, 0 );

SNIPPET;

$css_rule = ".ts-corp-band__text a {\n  text-decoration-line: underline;\n  text-decoration-style: solid;\n  text-decoration-color: var(--ts-accent);\n  text-decoration-thickness: 1.25px;\n  text-underline-offset: 0.2em;\n}\n";
$css_anchor = ".ts-corp-band__link {";

$snippet = get_post( $snippet_id );
$css     = get_post( $css_id );
if ( ! $snippet || 'wpcode' !== $snippet->post_type || 'CorporateBand' !== $snippet->post_title || 'publish' !== $snippet->post_status ) {
	WP_CLI::error( 'Live snippet 1268 is not the expected published CorporateBand snippet.' );
}
if ( ! $css || 'custom_css' !== $css->post_type || 'astra' !== $css->post_name || 'publish' !== $css->post_status ) {
	WP_CLI::error( 'Live Additional CSS record 29 is not the expected published Astra CSS.' );
}

$current_code     = (string) $snippet->post_content;
$current_css      = (string) $css->post_content;
$current_code_sha = hash( 'sha256', $current_code );
$current_css_sha  = hash( 'sha256', $current_css );
$cache_blob       = maybe_serialize( get_option( 'wpcode_snippets' ) );
$cache_has_fallback = false !== strpos( $cache_blob, 'astra_content_before' );
$new_css          = str_replace( $css_anchor, $css_rule . $css_anchor, $current_css, $css_anchor_count );
$new_css_sha_actual = hash( 'sha256', $new_css );

$ready   = $old_code_sha === $current_code_sha && $old_css_sha === $current_css_sha && 1 === $css_anchor_count && ! $cache_has_fallback;
$applied = $new_code_sha === $current_code_sha && $new_css_sha === $current_css_sha && 1 === substr_count( $current_css, $css_rule ) && $cache_has_fallback;
$state   = $ready ? 'ready' : ( $applied ? 'applied' : 'CHANGED' );
$report  = sprintf(
	'host=%s state=%s blog_public=%s snippet=#%d code_sha=%s css=#%d css_sha=%s cache_fallback=%s expected_new_code_sha=%s expected_new_css_sha=%s',
	$home,
	$state,
	(string) get_option( 'blog_public' ),
	$snippet_id,
	$current_code_sha,
	$css_id,
	$current_css_sha,
	$cache_has_fallback ? 'yes' : 'no',
	$new_code_sha,
	$new_css_sha
);

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Live snippet, CSS, or visibility differs from the reviewed state; stop and inspect. ' . $report );
	}
	WP_CLI::success( $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be a writable absolute directory outside the web root.' );
}
$refresh_cache = function ( $expect_new ) {
	wpcode()->cache->cache_all_loaded_snippets();
	$cached = maybe_serialize( get_option( 'wpcode_snippets' ) );
	return $expect_new === ( false !== strpos( $cached, 'astra_content_before' ) );
};
$set = function ( $post_id, $content ) {
	return wp_update_post( wp_slash( array( 'ID' => (int) $post_id, 'post_content' => $content ) ), true );
};

if ( 'rollback' === $mode ) {
	$snapshot_file = isset( $args[1] ) ? (string) $args[1] : '';
	$snapshot      = $snapshot_file && is_file( $snapshot_file ) ? json_decode( (string) file_get_contents( $snapshot_file ), true ) : null;
	if ( ! is_array( $snapshot ) || 'https://thaisolitude.com' !== ( $snapshot['home'] ?? '' ) || (int) ( $snapshot['snippet_id'] ?? 0 ) !== $snippet_id || (int) ( $snapshot['css_id'] ?? 0 ) !== $css_id || ! isset( $snapshot['old_code'], $snapshot['old_css'] ) || hash( 'sha256', (string) $snapshot['old_code'] ) !== ( $snapshot['old_code_sha256'] ?? '' ) || hash( 'sha256', (string) $snapshot['old_css'] ) !== ( $snapshot['old_css_sha256'] ?? '' ) || '1' !== ( $snapshot['blog_public'] ?? '' ) || ( $snapshot['new_code_sha256'] ?? '' ) !== $current_code_sha || ( $snapshot['new_css_sha256'] ?? '' ) !== $current_css_sha || '1' !== (string) get_option( 'blog_public' ) ) {
		WP_CLI::error( 'Rollback requires the exact applied production state and blog_public=1; nothing changed.' );
	}
	$code_result = $set( $snippet_id, $snapshot['old_code'] );
	$css_result  = $set( $css_id, $snapshot['old_css'] );
	$rolled_back = ! is_wp_error( $code_result ) && ! is_wp_error( $css_result )
		&& (string) get_post_field( 'post_content', $snippet_id ) === $snapshot['old_code']
		&& (string) get_post_field( 'post_content', $css_id ) === $snapshot['old_css']
		&& $refresh_cache( false )
		&& '1' === (string) get_option( 'blog_public' );
	if ( ! $rolled_back ) {
		$reapplied_code = $set( $snippet_id, $current_code );
		$reapplied_css  = $set( $css_id, $current_css );
		$reapplied      = ! is_wp_error( $reapplied_code ) && ! is_wp_error( $reapplied_css )
			&& (string) get_post_field( 'post_content', $snippet_id ) === $current_code
			&& (string) get_post_field( 'post_content', $css_id ) === $current_css
			&& $refresh_cache( true )
			&& '1' === (string) get_option( 'blog_public' );
		WP_CLI::error( $reapplied ? 'Rollback failed; the exact new approved state was restored. blog_public remains 1.' : 'Rollback and recovery verification failed; stop and inspect production immediately.' );
	}
	WP_CLI::success( 'Rolled back the exact live notice snippet and Additional CSS; blog_public remains 1.' );
	return;
}

if ( 'applied' === $state ) {
	WP_CLI::success( 'Already applied. ' . $report );
	return;
}
if ( 'ready' !== $state || $new_css_sha_actual !== $new_css_sha ) {
	WP_CLI::error( 'Production does not match the reviewed before-state; nothing changed. ' . $report );
}

$snapshot = array(
	'created'          => gmdate( 'c' ),
	'home'             => 'https://thaisolitude.com',
	'snippet_id'       => $snippet_id,
	'css_id'           => $css_id,
	'old_code_sha256'  => $current_code_sha,
	'old_code'         => $current_code,
	'old_css_sha256'   => $current_css_sha,
	'old_css'          => $current_css,
	'new_code_sha256'  => $new_code_sha,
	'new_css_sha256'   => $new_css_sha,
	'blog_public'      => '1',
);
$snapshot_file = rtrim( $backup_dir, '/' ) . '/corporate-band-live-before-' . gmdate( 'Ymd-His' ) . '.json';
if ( false === file_put_contents( $snapshot_file, wp_json_encode( $snapshot, JSON_PRETTY_PRINT ), LOCK_EX ) || filesize( $snapshot_file ) < 1000 ) {
	WP_CLI::error( 'Could not create the narrow production rollback snapshot; nothing changed.' );
}
chmod( $snapshot_file, 0600 );

$saved_code = $set( $snippet_id, $source );
$saved_css  = $set( $css_id, $new_css );
$ok         = ! is_wp_error( $saved_code ) && ! is_wp_error( $saved_css )
	&& hash( 'sha256', (string) get_post_field( 'post_content', $snippet_id ) ) === $new_code_sha
	&& hash( 'sha256', (string) get_post_field( 'post_content', $css_id ) ) === $new_css_sha
	&& '1' === (string) get_option( 'blog_public' )
	&& $refresh_cache( true )
	&& '1' === (string) get_option( 'blog_public' );

if ( ! $ok ) {
	$restore_code = $set( $snippet_id, $current_code );
	$restore_css  = $set( $css_id, $current_css );
	$restored     = ! is_wp_error( $restore_code ) && ! is_wp_error( $restore_css )
		&& (string) get_post_field( 'post_content', $snippet_id ) === $current_code
		&& (string) get_post_field( 'post_content', $css_id ) === $current_css
		&& $refresh_cache( false )
		&& '1' === (string) get_option( 'blog_public' );
	WP_CLI::error( $restored ? 'Apply verification failed; exact old values were restored and blog_public remains 1.' : 'Apply and automatic restore verification failed; stop and inspect production immediately.' );
}

WP_CLI::success( 'Applied the reviewed booking notice, solid link underline, and refreshed WPCode cache. blog_public remains 1. Rollback snapshot: ' . $snapshot_file );

// src: ./install-corporate-band-staging43.php — exact reviewed copy and responsive markup, approved on staging43
// ref: ./verify-corporate-band-live.py — post-apply public checks, including live indexing visibility
