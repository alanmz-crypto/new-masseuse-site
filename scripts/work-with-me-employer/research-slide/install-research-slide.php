<?php
/**
 * Research panels: slide open and shut, like the FAQ and About accordions (Additional CSS, ONE marked block). Applies to the "What the Research Says" panels on the Thai, Therapeutic and Deep Tissue pages.
 * Ryan, 2026-10-24: "I wanted you to keep the 'what research says' area [like the] other slide open sections on all these pages. Put them back and keep your other new work." (He confirmed: the slide-open animation.)
 *
 * Why CSS and not an accordion block: the panels contain numbered links, and a link inside a Spectra FAQ answer is an axe nested-interactive failure (Spectra wraps question and answer in role="tab"), which is why they
 * are native <details> sections. The block animates the content of those sections: height from 0 to its natural size and back, 0.35 s, through ::details-content and interpolate-size. A browser without that support
 * opens and closes at once, exactly as today; prefers-reduced-motion switches the animation off. Closed panels look the same as before, so the page screenshots do not change.
 *
 * Why Additional CSS: styles must live in WordPress's own storage so a SiteGround push carries them (no MU-plugin; see AGENTS.md). The block is delimited by marker comments and appended at the end.
 * The guard: the known site CSS is present, the Thai and Therapeutic pages hold their research panels, and the block is the reviewed single marked block with no url(), @import or !important.
 *
 * Allow-list: localhost:8090 and staging36 ONLY. Live is deliberately not in it; it is added only on Ryan's own go.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = the block is absent; applied = the block is there exactly once, exactly as reviewed; CHANGED = anything else (stop)
 *   apply        snapshot the whole Additional CSS, append the block, read back; restore the old CSS if the read-back differs
 *   rollback     FILE = the before-snapshot: take the block out again (restores the exact old CSS if nothing else was edited since)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging36.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging36 only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_css' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator who may edit CSS).' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$start = '/* ts-research-slide:start';
$end   = '/* ts-research-slide:end */';
$block = rtrim( (string) file_get_contents( __DIR__ . '/research-slide.css' ) );
if ( 0 !== strpos( $block, $start ) || substr( $block, -strlen( $end ) ) !== $end || 1 !== substr_count( $block, 'ts-research-slide:start' ) || 1 !== substr_count( $block, 'ts-research-slide:end' ) || strlen( $block ) > 3000 ) {
	WP_CLI::error( 'research-slide.css is not the reviewed single marked block.' );
}
// Everything in the block must be CSS the review covered: only these selectors, no @import, no url(), no expression, no !important.
foreach ( array( '@import', 'url(', 'expression', '!important', 'javascript:', '<' ) as $bad ) {
	if ( false !== stripos( $block, $bad ) ) {
		WP_CLI::error( 'research-slide.css contains something the review did not cover: ' . $bad );
	}
}

$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$css = (string) $post->post_content;
foreach ( array( 'thai-massage', 'therapeutic-massage' ) as $slug ) {
	$page = get_page_by_path( $slug );
	if ( ! $page || 'publish' !== $page->post_status || false === strpos( (string) $page->post_content, '<details class="wp-block-details' ) ) {
		WP_CLI::error( 'The ' . $slug . ' page was not found as a published page with its research panels; this CSS is for the pages as built; nothing was changed.' );
	}
}
$cnt = substr_count( $css, 'ts-research-slide:start' );
if ( 0 === $cnt ) {
	$state = 'ready';
} elseif ( 1 === $cnt && 1 === substr_count( $css, $block ) && 1 === substr_count( $css, 'ts-research-slide:end' ) ) {
	$state = 'applied';
} else {
	$state = 'CHANGED';
}
$report = sprintf( 'host=%s state=%s css_bytes=%d', $home, $state, strlen( $css ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The Additional CSS holds a different or duplicate ts-research-slide block; stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/research-slide-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array( 'created' => gmdate( 'c' ), 'home' => $home, 'css_sha256' => hash( 'sha256', $css ), 'css' => $css ) ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$new = rtrim( $css ) . "\n\n" . $block . "\n";
	$res = wp_update_custom_css_post( $new, array( 'stylesheet' => 'astra' ) );
	$chk = wp_get_custom_css_post( 'astra' );
	$ok  = ! is_wp_error( $res ) && $chk && (string) $chk->post_content === $new && 0 === strpos( (string) $chk->post_content, rtrim( $css ) );
	if ( ! $ok ) {
		wp_update_custom_css_post( $css, array( 'stylesheet' => 'astra' ) );
		WP_CLI::error( 'Read-back differed from what was written; the old CSS was put back.' );
	}
	WP_CLI::success( 'Added the ts-research-slide block to the Additional CSS (' . strlen( $css ) . ' -> ' . strlen( $new ) . ' bytes). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['css'] ) || hash( 'sha256', (string) $snap['css'] ) !== ( $snap['css_sha256'] ?? '' ) ) {
	WP_CLI::error( 'Snapshot is missing, damaged, or from another site.' );
}
if ( 'applied' !== $state ) {
	WP_CLI::success( 'Nothing to roll back: the block is not there in its original form. ' . $report );
	return;
}
$old      = (string) $snap['css'];
$restored = ( $css === rtrim( $old ) . "\n\n" . $block . "\n" ) ? $old : str_replace( "\n\n" . $block . "\n", '', $css );   // exact old CSS, or just the block out if other edits were made since
wp_update_custom_css_post( $restored, array( 'stylesheet' => 'astra' ) );
$chk = wp_get_custom_css_post( 'astra' );
if ( ! $chk || false !== strpos( (string) $chk->post_content, 'ts-research-slide' ) ) {
	WP_CLI::error( 'Rollback did not remove the block; stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the ts-research-slide block is out of the Additional CSS' . ( (string) $chk->post_content === $old ? ' (exact old CSS restored).' : ' (other edits since were kept).' ) );

// ref: ./research-slide.css — the reviewed block; ../thai/install-image-crop.php — the marked-CSS-block pattern this follows
