<?php
/**
 * Staging43-only correction to the sitewide booking notice.
 *
 * What changes in WordPress storage:
 *   - WPCode snippet 1268: show on every published Page and use the relative
 *     corporate route, preserving the reviewed text, external booking link,
 *     mobile/desktop markup, and existing CSS.
 *   - blog_public: 1 -> 0 so WordPress emits its staging noindex directive.
 *
 * Modes: preflight (default), apply, rollback <snapshot path>.
 * Apply/rollback require WWM_BACKUP_DIR outside the web root. A full database
 * backup must also be taken and copied off-server before applying.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST );
if ( 'staging43.thaisolitude.com' !== $host ) {
	WP_CLI::error( 'Refusing to run: expected staging43.thaisolitude.com; found ' . $home );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
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
$old_sha    = 'ed11d048ce646214aa4427d6d98d14d0418b8802b01731326d0323d1981fb1be';
$first_sha  = '67571bf2e07cf64447f9e54d8e2ba22e71fbbd125e79ef49514841e3b4f5ec4f';
$second_sha = '5eabba03bab54d7be77843c0e85b5dc8e3e056c67e420906cc4eca99fbf74814';
$third_sha  = 'e1b6dd115fcda754995c22e455b7622baf830bf611a5eb96d94b351c3fa9ed44';
$source     = <<<'SNIPPET'
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

$post = get_post( $snippet_id );
if ( ! $post || 'wpcode' !== $post->post_type || 'CorporateBand' !== $post->post_title || 'publish' !== $post->post_status ) {
	WP_CLI::error( 'Snippet 1268 is not the expected published CorporateBand snippet.' );
}
$current_code = (string) $post->post_content;
$current_sha  = hash( 'sha256', $current_code );
$public       = (string) get_option( 'blog_public' );
$new_sha      = hash( 'sha256', $source );
$cache_blob   = maybe_serialize( get_option( 'wpcode_snippets' ) );
$cache_ready  = false !== strpos( $cache_blob, 'astra_content_before' );
$cache_stale  = $new_sha === $current_sha && '0' === $public && ! $cache_ready;
$state        = ( ( $old_sha === $current_sha && '1' === $public ) || ( in_array( $current_sha, array( $first_sha, $second_sha, $third_sha ), true ) && '0' === $public ) || $cache_stale ) ? 'ready' : ( $new_sha === $current_sha && '0' === $public && $cache_ready ? 'applied' : 'CHANGED' );
$report       = sprintf( 'host=%s snippet=#%d state=%s blog_public=%s old_sha=%s current_sha=%s new_sha=%s', $home, $snippet_id, $state, $public, $old_sha, $current_sha, $new_sha );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Staging43 content or visibility setting differs from the reviewed state; stop and inspect. ' . $report );
	}
	WP_CLI::success( $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be a writable absolute directory outside the web root.' );
}
$refresh_cache = function ( $expect_new ) {
	if ( ! function_exists( 'wpcode' ) || ! isset( wpcode()->cache ) || ! method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		return false;
	}
	wpcode()->cache->cache_all_loaded_snippets();
	$cached = maybe_serialize( get_option( 'wpcode_snippets' ) );
	return $expect_new === ( false !== strpos( $cached, 'astra_content_before' ) );
};

if ( 'rollback' === $mode ) {
	$snapshot_file = isset( $args[1] ) ? (string) $args[1] : '';
	$snapshot      = $snapshot_file && is_file( $snapshot_file ) ? json_decode( (string) file_get_contents( $snapshot_file ), true ) : null;
	if ( ! is_array( $snapshot ) || 'https://staging43.thaisolitude.com' !== ( $snapshot['home'] ?? '' ) || ( $snapshot['new_sha256'] ?? '' ) !== $current_sha || '0' !== $public ) {
		WP_CLI::error( 'Rollback requires a staging43 snapshot and the exact applied state; nothing changed.' );
	}
	$restored = wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $snapshot['old_code'] ) ), true );
	update_option( 'blog_public', $snapshot['old_blog_public'] );
	if ( is_wp_error( $restored ) || (string) get_post_field( 'post_content', $snippet_id ) !== $snapshot['old_code'] || (string) get_option( 'blog_public' ) !== (string) $snapshot['old_blog_public'] || ! $refresh_cache( false ) ) {
		WP_CLI::error( 'Rollback did not restore the exact previous values; stop and inspect.' );
	}
	WP_CLI::success( 'Rolled back exact snippet and blog_public values for staging43.' );
	return;
}

if ( 'applied' === $state ) {
	WP_CLI::success( 'Already applied. ' . $report );
	return;
}
if ( 'ready' !== $state ) {
	WP_CLI::error( 'Not in the reviewed pre-change state; nothing changed. ' . $report );
}
$snapshot = array(
	'created'         => gmdate( 'c' ),
	'home'            => 'https://staging43.thaisolitude.com',
	'snippet_id'      => $snippet_id,
	'old_sha256'      => $current_sha,
	'old_code'        => $current_code,
	'old_blog_public' => $public,
	'new_sha256'      => $new_sha,
);
$snapshot_file = rtrim( $backup_dir, '/' ) . '/corporate-band-staging43-before-' . gmdate( 'Ymd-His' ) . '.json';
if ( false === file_put_contents( $snapshot_file, wp_json_encode( $snapshot, JSON_PRETTY_PRINT ), LOCK_EX ) || filesize( $snapshot_file ) < 1000 ) {
	WP_CLI::error( 'Could not create the narrow rollback snapshot; nothing changed.' );
}
chmod( $snapshot_file, 0600 );

$restore = function () use ( $snippet_id, $current_code, $public ) {
	wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $current_code ) ), true );
	update_option( 'blog_public', $public );
};
$saved = wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $source ) ), true );
if ( is_wp_error( $saved ) || (string) get_post_field( 'post_content', $snippet_id ) !== $source ) {
	$restore();
	$refresh_cache( false );
	WP_CLI::error( 'Snippet save/read-back failed; previous values were restored.' );
}
update_option( 'blog_public', 0 );
if ( (string) get_option( 'blog_public' ) !== '0' || hash( 'sha256', (string) get_post_field( 'post_content', $snippet_id ) ) !== $new_sha || ! $refresh_cache( true ) ) {
	$restore();
	$refresh_cache( false );
	WP_CLI::error( 'Noindex setting or snippet read-back failed; previous values were restored.' );
}
WP_CLI::success( 'Applied the relative corporate route, all-published-pages scope, and staging noindex setting. Snapshot: ' . $snapshot_file );

// ref: ./push-corporate-band-staging43.sh — staging43-only upload, backup, apply, purge and verification; ../../ci/backup-now.sh — fresh off-server DB backup
