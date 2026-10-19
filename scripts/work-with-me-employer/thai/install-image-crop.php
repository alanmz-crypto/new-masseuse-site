<?php
/**
 * Thai Massage page: let the three illustrations show in their own proportions (512 x 460), instead of fixed-height frames that cut off up to 50% of the picture. Additional CSS, ONE marked block.
 * Ryan, 2026-10-19: "the pictures need to be cropped closer to their original square shape" (after the image swap went live).
 *
 * Diagnosis (measured on live with thai/crop-probe numbers): Spectra writes `.uagb-block-<id>.wp-block-uagb-image .wp-block-uagb-image__figure img { object-fit: cover; width: Wpx; height: Hpx }` for desktop,
 * tablet and phone. Width shrinks with the screen (max-width 100%) but the height stays fixed, so the frame drifts from the picture's shape: hero 15% cut off at 1280 px, 32-35% at 977-1024 px, 31% at 320 px;
 * the two inline pictures 10% on desktop, 19-27% on phones, 23% on tablets, 50% at 600 px. Fix (thai/image-crop.css): `height: auto; aspect-ratio: 512 / 460` on the three block ids, so the height follows the
 * width in the picture's own proportions: 0% cut off at every width (prototyped in the browser first; measured at ten widths; the hero keeps its overlap of the card). The page gets slightly shorter on desktop
 * (about 9 px at 1280) and slightly taller on phones (about 86 px at 390); nothing else on the page is touched.
 *
 * Why Additional CSS: styles must live in WordPress's own storage so a SiteGround push carries them (no MU-plugin; see AGENTS.md). The block is delimited by marker comments and appended at the end.
 * The guard: the known site CSS is present, the Thai page holds all three image blocks, and the block is the reviewed single marked block with no url(), @import or !important.
 *
 * Allow-list: localhost:8090, staging34 and live. Live was added by Ryan's go (2026-10-19) after the block was applied, checked and rollback-proven on localhost and staging34;
 * he applies it himself (the auto-mode classifier refuses an agent adding a live host).
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
if ( ! in_array( $host, array( 'localhost:8090', 'staging34.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging34 or live only).' );
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

$start = '/* ts-thai-image-crop:start';
$end   = '/* ts-thai-image-crop:end */';
$block = rtrim( (string) file_get_contents( __DIR__ . '/image-crop.css' ) );
if ( 0 !== strpos( $block, $start ) || substr( $block, -strlen( $end ) ) !== $end || 1 !== substr_count( $block, 'ts-thai-image-crop:start' ) || 1 !== substr_count( $block, 'ts-thai-image-crop:end' ) || strlen( $block ) > 3000 ) {
	WP_CLI::error( 'image-crop.css is not the reviewed single marked block.' );
}
// Everything in the block must be CSS the review covered: only these selectors, no @import, no url(), no expression, no !important.
foreach ( array( '@import', 'url(', 'expression', '!important', 'javascript:', '<' ) as $bad ) {
	if ( false !== stripos( $block, $bad ) ) {
		WP_CLI::error( 'image-crop.css contains something the review did not cover: ' . $bad );
	}
}

$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$css = (string) $post->post_content;
$page = get_page_by_path( 'thai-massage' );
if ( ! $page || 'publish' !== $page->post_status ) {
	WP_CLI::error( 'The Thai Massage page was not found as a published page; nothing was changed.' );
}
foreach ( array( '5b6bb4f3', '04d0e681', 'f48b2ed7' ) as $bid ) {
	if ( 1 !== substr_count( (string) $page->post_content, 'uagb-block-' . $bid ) ) {
		WP_CLI::error( 'The Thai Massage page does not hold image block ' . $bid . ' exactly once; this CSS is for that page as built; nothing was changed.' );
	}
}
$cnt = substr_count( $css, 'ts-thai-image-crop:start' );
if ( 0 === $cnt ) {
	$state = 'ready';
} elseif ( 1 === $cnt && 1 === substr_count( $css, $block ) && 1 === substr_count( $css, 'ts-thai-image-crop:end' ) ) {
	$state = 'applied';
} else {
	$state = 'CHANGED';
}
$report = sprintf( 'host=%s state=%s css_bytes=%d', $home, $state, strlen( $css ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The Additional CSS holds a different or duplicate ts-thai-image-crop block; stop and review. ' . $report );
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
	$file = rtrim( $backup_dir, '/' ) . '/thai-image-crop-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	WP_CLI::success( 'Added the ts-thai-image-crop block to the Additional CSS (' . strlen( $css ) . ' -> ' . strlen( $new ) . ' bytes). Before-snapshot: ' . $file );
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
if ( ! $chk || false !== strpos( (string) $chk->post_content, 'ts-thai-image-crop' ) ) {
	WP_CLI::error( 'Rollback did not remove the block; stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the ts-thai-image-crop block is out of the Additional CSS' . ( (string) $chk->post_content === $old ? ' (exact old CSS restored).' : ' (other edits since were kept).' ) );

// ref: ./image-crop.css — the reviewed block; ./install-thai-images.php — the image swap this refines; ../theme/install-header-fit.php — the marked-CSS-block pattern this follows
