<?php
/**
 * Privacy Policy page, VERSION 3 (a CORRECTION): replace version 2 with the same two-panel accordion with corrected wording, for ONE site at a time.
 * Found 2026-10-17 while testing the site for accessibility: the Contact page shows an embedded Google Map (inserted by script, so an earlier raw-HTML check had missed it),
 * which loads from Google's servers. Version 2 said Google Fonts was the only outside load, that Google Maps was only a link, and that the site "does not set cookies for
 * visitors". Version 3 says the fonts and the Contact page's embedded map come from Google (which handles that information under its own privacy policy), that the SITE ITSELF
 * sets no cookies, and that other pages link to the Oregon license lookup and the schools and organizations named on the For Practices page.
 *
 * Content: privacy-policy.html (built by build-privacy-page.py). It only accepts a page that still holds EXACTLY version 2 (privacy-policy-v2.html), with the footer link and
 * the privacy option in place; anything else stops the run. Nothing else is written: title, slug, footer link and option stay as they are.
 *
 * Allow-list: localhost:8090, staging32 and live. Live was added on Ryan's go (2026-10-17: "fix the privacy correction on live now"), after version 3 was applied, checked and rollback-proven on localhost and staging32.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: old = the page holds exactly version 2 (ready to correct); new = already version 3; CHANGED = anything else (stop)
 *   apply        snapshot, then replace the page content with version 3 and read everything back; put version 2 back if any read-back differs
 *   rollback     FILE = the before-snapshot: put version 2 back, if the page still holds exactly version 3
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the old content.)
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging32.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging32 or live only).' );
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

$old = file_get_contents( __DIR__ . '/privacy-policy-v2.html' );
$new = file_get_contents( __DIR__ . '/privacy-policy.html' );
$old = false === $old ? '' : trim( $old );
$new = false === $new ? '' : trim( $new );
if ( '' === $old || '' === $new || $old === $new ) {
	WP_CLI::error( 'privacy-policy-v2.html and privacy-policy.html must both exist next to the installer and differ.' );
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
$text = html_entity_decode( wp_strip_all_tags( str_replace( array( '<br>', '<br />' ), ' ', $visible_text( parse_blocks( $new ) ) ) ), ENT_QUOTES, 'UTF-8' );
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
$read = function () use ( $slug, $old, $new, $widget_id, $post_widget ) {
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
		$state = $c === $old ? 'old' : ( $c === $new ? 'new' : 'CHANGED' );
	}
	return array( 'state' => $state, 'pages' => $pages, 'opt' => $opt, 'widget' => $widget );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d privacy_option=%d', $home, $state, count( $now['pages'] ), $now['opt'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not exactly version 2 or version 3 (missing, hand-edited, or the footer link/option differ); stop and review. ' . $report );
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
	if ( 'new' === $state ) {
		WP_CLI::success( 'Nothing to do: already version 3. ' . $report );
		return;
	}
	$id   = (int) $now['pages'][0]->ID;
	$file = rtrim( $backup_dir, '/' ) . '/privacy-page-v3-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array(
		'created'            => gmdate( 'c' ),
		'home'               => $home,
		'page_id'            => $id,
		'old_content_sha256' => hash( 'sha256', $old ),
		'new_content_sha256' => hash( 'sha256', $new ),
		'old_content'        => $old,
	) ) );
	if ( ! $ok || filesize( $file ) < 100 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$res = $set_content( $id, $new );
	$after = $read();
	$path  = wp_parse_url( get_permalink( $id ), PHP_URL_PATH );
	if ( is_wp_error( $res ) || ! $res || 'new' !== $after['state'] || '/privacy-policy/' !== $path ) {
		$set_content( $id, $old );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; version 2 was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' now holds version 3 (the corrected accordion). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || ( $snap['old_content_sha256'] ?? '' ) !== hash( 'sha256', $old ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete, or from another site or version.' );
}
if ( 'new' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold exactly version 3. ' . $report );
	return;
}
$set_content( (int) $snap['page_id'], $old );
$check = $read();
if ( 'old' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to version 2 (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the page holds version 2 again.' );
