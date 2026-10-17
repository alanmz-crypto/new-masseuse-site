<?php
/**
 * Privacy Policy page, VERSION 2: replace the short page (version 1) with an accordion of two panels, "Online privacy" and "Client privacy", for ONE site at a time.
 * Ryan, 2026-10-17: "return to the privacy policy directed at clients i gave you earlier. we'll add that. make that a second section. possibly have an accordion for both
 * online privacy and client based privacy ... a slide open accordion type thing for both like we do for the About page, My Commitment to Your Wellness."
 *
 * The content is privacy-policy.html next to this file, produced by build-privacy-page.py (one Spectra FAQ accordion, the same block and settings as the About page).
 * It only accepts a page that still holds EXACTLY version 1 (privacy-policy-v1.html, what install-privacy-page.php wrote), with the footer link and the privacy option in
 * place; a hand-edited page, a missing page or any other footer text stops the run. Nothing else is written: the page title, slug, footer link and option stay as they are.
 *
 * FACTS (Ryan, 2026-10-17: "include what we can from this list while being completely true"): the Online privacy panel was verified against the live site on 2026-10-17;
 * the Client privacy panel keeps only what the site itself says (service pages: tell me about health conditions, medications, pregnancy, injury or surgery before a session;
 * About: goals, tailored sessions; footer: not a substitute for medical care) or what is plainly true, plus one commitment ("I do not sell ... or use it for marketing") that
 * matches the live page and that Butsaba should be comfortable with. Payments, record keeping, retention, providers and promotional messages are left out until confirmed.
 * Allow-list: localhost:8090 and staging31 ONLY; live is added only on Ryan's go.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: v1 = the page holds exactly version 1 (ready to update); v2 = already version 2; CHANGED = anything else (stop)
 *   apply        snapshot, then replace the page content with version 2 and read everything back; put version 1 back if any read-back differs
 *   rollback     FILE = the before-snapshot: put version 1 back, if the page still holds exactly version 2
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 * (WordPress also keeps its own revision of the old content, a second way back.)
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging31.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging31 only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page.' );
}
if ( ! class_exists( 'UAGB_Faq' ) || ! WP_Block_Type_Registry::get_instance()->is_registered( 'uagb/faq-child' ) ) {
	WP_CLI::error( 'Spectra (ultimate-addons-for-gutenberg) with the FAQ block is not loaded; the accordion would not render.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$v1 = file_get_contents( __DIR__ . '/privacy-policy-v1.html' );
$v2 = file_get_contents( __DIR__ . '/privacy-policy.html' );
$v1 = false === $v1 ? '' : trim( $v1 );
$v2 = false === $v2 ? '' : trim( $v2 );
if ( '' === $v1 || '' === $v2 || $v1 === $v2 ) {
	WP_CLI::error( 'privacy-policy-v1.html and privacy-policy.html must both exist next to the installer and differ.' );
}

/** All visible text of a block list: innerHTML of every block plus the question/answer attributes of the accordion items. */
$visible_text = function ( $blocks ) use ( &$visible_text ) {
	$out = '';
	foreach ( $blocks as $b ) {
		$out .= ' ' . ( $b['innerHTML'] ?? '' );
		foreach ( array( 'question', 'answer' ) as $k ) {
			if ( ! empty( $b['attrs'][ $k ] ) ) {
				$out .= ' ' . $b['attrs'][ $k ];
			}
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			$out .= $visible_text( $b['innerBlocks'] );
		}
	}
	return $out;
};
$text = html_entity_decode( wp_strip_all_tags( str_replace( array( '<br>', '<br />' ), ' ', $visible_text( parse_blocks( $v2 ) ) ) ), ENT_QUOTES, 'UTF-8' );
if ( strlen( trim( $text ) ) < 1500 ) {
	WP_CLI::error( 'Version 2 parsed to far less text than expected (' . strlen( trim( $text ) ) . ' characters); the block markup is probably broken.' );
}
// The OBMT forbidden terms (list in AGENTS.md). "heal" is matched as a whole word (the page legitimately says "health"); "we/our/us" are whole-word checks.
foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore balance', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio' ) as $bad ) {
	if ( false !== stripos( $text, $bad ) ) {
		WP_CLI::error( 'The page text contains a forbidden term: ' . $bad );
	}
}
if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $text ) ) {
	WP_CLI::error( 'The page text contains the forbidden term heal/healing.' );
}
if ( preg_match( '/\b(we|our|us|ours)\b/i', $text ) ) {
	WP_CLI::error( 'The page text uses we/our/us, which this site must not use.' );
}

$slug       = 'privacy-policy';
$widget_id  = 7;
// The footer widget as install-privacy-page.php leaves it (the link row with Privacy Policy); the guard demands it byte for byte so a changed footer stops the run.
$post_widget = "<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-location\"} -->\n<p class=\"has-text-align-center ts-footer-location\">Portland, OR</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-links\"} -->\n<p class=\"has-text-align-center ts-footer-links\"><a href=\"mailto:butsabad@gmail.com\">butsabad@gmail.com</a> · <a href=\"/work-with-me/\">Résumé &amp; experience</a> · <a href=\"/privacy-policy/\">Privacy&nbsp;Policy</a></p>\n<!-- /wp:paragraph -->";

/** Current state. */
$read = function () use ( $slug, $v1, $v2, $widget_id, $post_widget ) {
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'widget_block', 'options' );
	wp_cache_delete( 'wp_page_for_privacy_policy', 'options' );
	$blocks = get_option( 'widget_block' );
	$widget = is_array( $blocks ) && isset( $blocks[ $widget_id ]['content'] ) ? (string) $blocks[ $widget_id ]['content'] : null;
	$pages  = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$opt    = (int) get_option( 'wp_page_for_privacy_policy', 0 );
	$state  = 'CHANGED';
	if ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status && $widget === $post_widget && (int) $pages[0]->ID === $opt ) {
		$c = trim( (string) $pages[0]->post_content );
		$state = $c === $v1 ? 'v1' : ( $c === $v2 ? 'v2' : 'CHANGED' );
	}
	return array( 'state' => $state, 'pages' => $pages, 'opt' => $opt, 'widget' => $widget );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d privacy_option=%d', $home, $state, count( $now['pages'] ), $now['opt'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not exactly version 1 or version 2 (missing, hand-edited, or the footer link/option differ); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$set_content = function ( $page_id, $content ) {
	return wp_update_post( wp_slash( array( 'ID' => (int) $page_id, 'post_content' => $content ) ), true );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'v2' === $state ) {
		WP_CLI::success( 'Nothing to do: already version 2. ' . $report );
		return;
	}
	$id   = (int) $now['pages'][0]->ID;
	$file = rtrim( $backup_dir, '/' ) . '/privacy-page-v2-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array(
		'created'            => gmdate( 'c' ),
		'home'               => $home,
		'page_id'            => $id,
		'old_content_sha256' => hash( 'sha256', $v1 ),
		'new_content_sha256' => hash( 'sha256', $v2 ),
		'old_content'        => $v1,
	) ) );
	if ( ! $ok || filesize( $file ) < 100 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$res = $set_content( $id, $v2 );
	$after = $read();
	$path  = wp_parse_url( get_permalink( $id ), PHP_URL_PATH );
	if ( is_wp_error( $res ) || ! $res || 'v2' !== $after['state'] || '/privacy-policy/' !== $path ) {
		$set_content( $id, $v1 );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; version 1 was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' now holds version 2 (accordion with Online privacy and Client privacy). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || ( $snap['old_content_sha256'] ?? '' ) !== hash( 'sha256', $v1 ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete, or from another site or version.' );
}
if ( 'v2' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold exactly version 2. ' . $report );
	return;
}
$set_content( (int) $snap['page_id'], $v1 );
$check = $read();
if ( 'v1' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to version 1 (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the page holds version 1 again.' );
