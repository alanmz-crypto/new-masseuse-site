<?php
/**
 * Add a very short Privacy Policy page (/privacy-policy/) and a "Privacy Policy" link in the footer, for ONE site at a time.
 * Ryan, 2026-10-17: "add a privacy policy page. keep it very very simple".
 *
 * The wording is privacy-policy.html next to this file. It is written in the first person ("I"), like the footer disclaimer, and avoids every term on the OBMT
 * forbidden list (we/our/us, studio, ...). Every statement in it was checked against the live site on 2026-10-17: 6 pages fetched, 0 forms, 0 iframes, no Set-Cookie
 * header, no analytics or ad host; the only outside loads are Google Fonts files, the contact route is an email link and a phone link.
 * If the site ever gains a form, analytics, a booking widget or a map embed, the page must be updated first.
 *
 * Allow-list: localhost:8090, staging31 and live. Live was added on Ryan's go (2026-10-17: "publish that. add the slug and go"), after the change was applied, checked
 * and rollback-proven on staging31 and localhost, and live's footer widget was proven byte-identical to the one the guard expects.
 *
 * What it writes (all in WordPress's own storage, so a SiteGround push carries it: posts table + options table; no files, no MU-plugin):
 *   1. a published page "Privacy Policy" (slug privacy-policy)
 *   2. the option wp_page_for_privacy_policy = that page's ID (WordPress then knows it as the site's privacy page)
 *   3. one link added to the centered footer row "email . Resume & experience" (footer widget block 7, stored in the options table as widget_block). The footer
 *      copyright text with the required license line is NOT touched. (A first try put the link under the copyright lines: it was nearly invisible on the dark
 *      footer and the scroll-to-top button sat on it, so it moved to this row, which already holds links in the right style.)
 *      The guard demands the known old widget text byte for byte; anything else stops the run.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = nothing there yet and the widget is the known text; applied = exactly this change is there; CHANGED = anything else (stop)
 *   apply        snapshot, then create the page, set the option, add the footer link, read everything back; undo itself if any read-back differs
 *   rollback     FILE = the before-snapshot: remove the page, reset the option and put the old widget text back, if everything is still exactly as written
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging31.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging31 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$content = file_get_contents( __DIR__ . '/privacy-policy.html' );
$content = false === $content ? '' : trim( $content );
if ( '' === $content ) {
	WP_CLI::error( 'privacy-policy.html is missing or empty next to the installer.' );
}
// The OBMT forbidden terms must never reach this page (the list is in AGENTS.md); "we/our/us" are whole-word checks.
foreach ( array( 'heal', 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore balance', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio' ) as $bad ) {
	if ( false !== stripos( $content, $bad ) ) {
		WP_CLI::error( 'The page text contains a forbidden term: ' . $bad );
	}
}
if ( preg_match( '/\b(we|our|us|ours)\b/i', wp_strip_all_tags( $content ) ) ) {
	WP_CLI::error( 'The page text uses we/our/us, which this site must not use.' );
}

$slug      = 'privacy-policy';
$title     = 'Privacy Policy';
$widget_id = 7;   // the footer widget block that holds "Portland, OR" and the email / Resume row (same on localhost, staging31 and live on 2026-10-17)
$old_widget = "<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-location\"} -->\n<p class=\"has-text-align-center ts-footer-location\">Portland, OR</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-links\"} -->\n<p class=\"has-text-align-center ts-footer-links\"><a href=\"mailto:butsabad@gmail.com\">butsabad@gmail.com</a> · <a href=\"/work-with-me/\">Résumé &amp; experience</a></p>\n<!-- /wp:paragraph -->";
// "Privacy&nbsp;Policy": a non-breaking space so the link never splits across two lines on a phone (seen on localhost 2026-10-17).
$new_widget = str_replace( 'Résumé &amp; experience</a></p>', 'Résumé &amp; experience</a> · <a href="/privacy-policy/">Privacy&nbsp;Policy</a></p>', $old_widget, $replaced );
if ( 1 !== $replaced || $new_widget === $old_widget ) {
	WP_CLI::error( 'Internal error: the footer link could not be placed in the known widget text.' );
}

/** The widget_block option without our block, to prove nothing else in it changed. */
$others = function ( $blocks ) use ( $widget_id ) {
	if ( is_array( $blocks ) ) {
		unset( $blocks[ $widget_id ] );
	}
	return $blocks;
};
/** Current state of the three things this installer touches. */
$read = function () use ( $slug, $content, $widget_id, $old_widget, $new_widget ) {
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'widget_block', 'options' );
	wp_cache_delete( 'wp_page_for_privacy_policy', 'options' );
	$blocks = get_option( 'widget_block' );
	$widget = is_array( $blocks ) && isset( $blocks[ $widget_id ]['content'] ) ? (string) $blocks[ $widget_id ]['content'] : null;
	$pages  = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$opt    = (int) get_option( 'wp_page_for_privacy_policy', 0 );
	if ( 0 === count( $pages ) && $widget === $old_widget && 0 === $opt ) {
		$state = 'ready';
	} elseif ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status && trim( (string) $pages[0]->post_content ) === $content
		&& $widget === $new_widget && (int) $pages[0]->ID === $opt ) {
		$state = 'applied';
	} else {
		$state = 'CHANGED';
	}
	return array( 'state' => $state, 'pages' => $pages, 'opt' => $opt, 'widget' => $widget, 'blocks' => $blocks );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d privacy_option=%d footer_widget_is=%s', $home, $state, count( $now['pages'] ), $now['opt'],
	$now['widget'] === $old_widget ? 'known-old' : ( $now['widget'] === $new_widget ? 'old+link' : 'UNKNOWN' ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Something differs from the reviewed state (a page with this slug, another privacy page, or a different footer widget); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$write_snap = function ( $file, $created_id ) use ( $home, $now, $old_widget, $widget_id, $content ) {
	$ok = false !== file_put_contents( $file, wp_json_encode( array(
		'created'            => gmdate( 'c' ),
		'home'               => $home,
		'created_id'         => $created_id,
		'widget_id'          => $widget_id,
		'old_widget'         => $old_widget,
		'old_privacy_option' => $now['opt'],
		'content_sha256'     => hash( 'sha256', $content ),
	) ) );
	if ( $ok ) {
		chmod( $file, 0600 );
	}
	return $ok && filesize( $file ) > 0;
};
/** Put the footer widget text and the privacy option back to the given values and delete the page (used by rollback and by apply's self-undo). */
$undo = function ( $page_id, $widget_text, $option_value ) use ( $widget_id ) {
	$blocks = get_option( 'widget_block' );
	if ( is_array( $blocks ) && isset( $blocks[ $widget_id ] ) ) {
		$blocks[ $widget_id ]['content'] = $widget_text;
		update_option( 'widget_block', $blocks );
	}
	update_option( 'wp_page_for_privacy_policy', (int) $option_value );
	if ( $page_id ) {
		wp_delete_post( (int) $page_id, true );
	}
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/privacy-page-before-' . gmdate( 'Ymd-His' ) . '.json';
	if ( ! $write_snap( $file, null ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	$id = wp_insert_post( wp_slash( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_title'     => $title,
		'post_name'      => $slug,
		'post_content'   => $content,
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_author'    => get_current_user_id(),
	) ), true );
	if ( is_wp_error( $id ) || ! $id ) {
		WP_CLI::error( 'Could not create the page; nothing else was changed.' );
	}
	$write_snap( $file, (int) $id );
	update_option( 'wp_page_for_privacy_policy', (int) $id );
	$blocks = $now['blocks'];
	$blocks[ $widget_id ]['content'] = $new_widget;
	update_option( 'widget_block', $blocks );

	// Read everything back: state, permalink path, and no other footer widget touched.
	$after    = $read();
	$path     = wp_parse_url( get_permalink( (int) $id ), PHP_URL_PATH );
	$other_ok = $others( $after['blocks'] ) == $others( $now['blocks'] );   // loose compare on purpose: same keys and values, order may differ
	$ok       = 'applied' === $after['state'] && '/privacy-policy/' === $path && $other_ok;
	if ( ! $ok ) {
		$undo( (int) $id, $old_widget, $now['opt'] );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; everything was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Created page #' . $id . ' /privacy-policy/, set it as the privacy page, added the footer link. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['created_id'] ) || ( $snap['old_widget'] ?? '' ) !== $old_widget ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['created_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the change this installer wrote is not there in its original form. ' . $report );
	return;
}
$undo( (int) $snap['created_id'], (string) $snap['old_widget'], (int) $snap['old_privacy_option'] );
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: page deleted, privacy option reset, footer widget text restored.' );
