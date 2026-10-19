<?php
/**
 * Phone header fix: the dark/light button made the header wrap "Thai Solitude" onto two lines at 375 px (iPhone SE) and split the word "Solitude" at 320 px.
 * Ryan, 2026-10-19: "it was probably the dark light button and i didn't notice till now" / "ideally the text should be small enough to fit on an iphone SE without needing wrapping in the header".
 *
 * Diagnosis (measured on live with tools/header-fit-test.mjs): the mobile header is a two-column grid with a 20 px gap; the button added 44 px + ~28 px of padding and margin to the right column, leaving
 * 189 px at 375 px for a logo and title that need 196 (it failed at every width of 380 px and below, also common Android phones at 360 px). With the button hidden the title fit on one line at every width.
 * Fix (theme/header-fit.css, one marked block in Additional CSS, phones only): at 389 px and below tighten the spacing around the button (column gap 8 px, button margin 8 px, no extra padding), keep every tap
 * target (button 44 px, menu 46 px) and stop the name wrapping; below 350 px the name shrinks smoothly (it is exactly its current 23.712 px at 350 px, so no jump). From 390 px up nothing changes.
 * Prototyped in the browser first (15 widths x light and dark: one line, no split word, no overlap, tap targets kept, 8 px between the buttons, no side-scroll).
 *
 * Why Additional CSS: styles must live in WordPress's own storage so a SiteGround push carries them (no MU-plugin; see AGENTS.md). The block is delimited by marker comments and appended AFTER the ts-theme block.
 * The guard refuses unless the known site CSS is there AND the dark-mode block (ts-theme) is there (this fix only makes sense around it).
 *
 * Allow-list: localhost:8090 and staging34. Live is added only after Ryan's explicit go (an agent adding a live host is refused by the auto-mode classifier: hand him a tested patch).
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
if ( ! in_array( $host, array( 'localhost:8090', 'staging34.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging34 only).' );
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

$start = '/* ts-header-fit:start';
$end   = '/* ts-header-fit:end */';
$block = rtrim( (string) file_get_contents( __DIR__ . '/header-fit.css' ) );
if ( 0 !== strpos( $block, $start ) || substr( $block, -strlen( $end ) ) !== $end || 1 !== substr_count( $block, 'ts-header-fit:start' ) || 1 !== substr_count( $block, 'ts-header-fit:end' ) || strlen( $block ) > 3000 ) {
	WP_CLI::error( 'header-fit.css is not the reviewed single marked block.' );
}
// Everything in the block must be CSS the review covered: only these selectors, no @import, no url(), no expression, no !important.
foreach ( array( '@import', 'url(', 'expression', '!important', 'javascript:', '<' ) as $bad ) {
	if ( false !== stripos( $block, $bad ) ) {
		WP_CLI::error( 'header-fit.css contains something the review did not cover: ' . $bad );
	}
}

$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$css = (string) $post->post_content;
if ( 1 !== substr_count( $css, 'ts-theme:start' ) || 1 !== substr_count( $css, 'ts-theme:end' ) ) {
	WP_CLI::error( 'The dark/light button block (ts-theme) is not in the Additional CSS exactly once; this fix is for the page with that button; nothing was changed.' );
}
$cnt = substr_count( $css, 'ts-header-fit:start' );
if ( 0 === $cnt ) {
	$state = 'ready';
} elseif ( 1 === $cnt && 1 === substr_count( $css, $block ) && 1 === substr_count( $css, 'ts-header-fit:end' ) ) {
	$state = 'applied';
} else {
	$state = 'CHANGED';
}
$report = sprintf( 'host=%s state=%s css_bytes=%d', $home, $state, strlen( $css ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The Additional CSS holds a different or duplicate ts-header-fit block; stop and review. ' . $report );
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
	$file = rtrim( $backup_dir, '/' ) . '/header-fit-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	WP_CLI::success( 'Added the ts-header-fit block to the Additional CSS (' . strlen( $css ) . ' -> ' . strlen( $new ) . ' bytes). Before-snapshot: ' . $file );
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
if ( ! $chk || false !== strpos( (string) $chk->post_content, 'ts-header-fit' ) ) {
	WP_CLI::error( 'Rollback did not remove the block; stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the ts-header-fit block is out of the Additional CSS' . ( (string) $chk->post_content === $old ? ' (exact old CSS restored).' : ' (other edits since were kept).' ) );

// ref: ./header-fit.css — the reviewed block; ./header-fit-test.mjs — the width/theme test; ../accessibility/install-focus-css.php — the marked-CSS-block pattern this follows; ./install-theme-toggle.php — the button this fixes around
