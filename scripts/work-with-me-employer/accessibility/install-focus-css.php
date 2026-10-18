<?php
/**
 * Two keyboard-focus fixes found by the accessibility audit (2026-10-17, tools/a11y-audit.mjs), appended to Additional CSS as ONE marked block, for ONE site at a time.
 * Ryan, 2026-10-17: "work on an accessibility page ... you can use web research tools to confirm anything we missed" (the audit is what makes the page's statements true).
 *
 *   1. #ast-scroll-top (the round "scroll to top" button, a focusable div): Astra turns its outline off, so a keyboard user could not see where focus was (WCAG 2.4.7 Focus Visible).
 *      Fix: a double ring (white outline + dark halo) on :focus-visible only; mouse users see no change.
 *   2. The sticky in-page menu on the For Practices page (.ts-wwm__sticky) is fixed to the BOTTOM of a phone screen (61 px high); tabbing scrolled links underneath it (16 of 59 stops
 *      on a 390 px screen; WCAG 2.2 2.4.11 Focus Not Obscured, AA). Fix: scroll-padding-bottom on the page so focused items stop above the bar. No change on other pages
 *      (the :has() selector matches only pages that contain that menu) or for mouse and touch use.
 *
 * Why Additional CSS: styles must live in WordPress's own storage so a SiteGround push carries them (no MU-plugin; see AGENTS.md). The block is delimited by marker comments.
 *
 * Allow-list: localhost:8090 and staging32 ONLY; live is added only on Ryan's go (or the CSS arrives through a SiteGround Custom Deploy of the posts table from staging32).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = the block is absent; applied = the block is there exactly once; CHANGED = anything else (stop)
 *   apply        snapshot the whole Additional CSS, append the block, read back; restore the old CSS if the read-back differs
 *   rollback     FILE = the before-snapshot: take the block out again (restores the exact old CSS if nothing else was edited since)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging32.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging32 only).' );
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

$start = '/* ts-a11y-focus:start (2026-10-17, accessibility audit) */';
$end   = '/* ts-a11y-focus:end */';
$block = $start . "\n"
	. "#ast-scroll-top:focus-visible{outline:3px solid #fff;outline-offset:2px;box-shadow:0 0 0 6px #1f2a37}\n"
	. "html:has(.ts-wwm__sticky){scroll-padding-bottom:5.5rem}\n"
	. $end;

$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$css = (string) $post->post_content;
$cnt = substr_count( $css, 'ts-a11y-focus:start' );
if ( 0 === $cnt ) {
	$state = 'ready';
} elseif ( 1 === $cnt && 1 === substr_count( $css, $block ) && 1 === substr_count( $css, 'ts-a11y-focus:end' ) ) {
	$state = 'applied';
} else {
	$state = 'CHANGED';
}
$report = sprintf( 'host=%s state=%s css_bytes=%d', $home, $state, strlen( $css ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The Additional CSS holds a different or duplicate ts-a11y-focus block; stop and review. ' . $report );
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
	$file = rtrim( $backup_dir, '/' ) . '/focus-css-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	WP_CLI::success( 'Added the ts-a11y-focus block to the Additional CSS (' . strlen( $css ) . ' -> ' . strlen( $new ) . ' bytes). Before-snapshot: ' . $file );
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
if ( ! $chk || false !== strpos( (string) $chk->post_content, 'ts-a11y-focus' ) ) {
	WP_CLI::error( 'Rollback did not remove the block; stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the ts-a11y-focus block is out of the Additional CSS' . ( (string) $chk->post_content === $old ? ' (exact old CSS restored).' : ' (other edits since were kept).' ) );
