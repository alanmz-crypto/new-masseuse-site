<?php
/**
 * Add the Accessibility page (/accessibility/) and an "Accessibility" link in the footer row, right after "Privacy Policy", for ONE site at a time.
 * Ryan, 2026-10-17: "work on an accessibility page" ... "This should go at the bottom after 'Privacy Policy' right?"
 *
 * The wording is accessibility-page.html next to this file (built by build-accessibility-page.py; every sentence is limited to what was actually tested, see that file).
 * W3C WAI advises linking an accessibility statement from the footer and using one consistent link name: the link is called "Accessibility".
 *
 * Allow-list: localhost:8090 and staging32 ONLY; live is added only on Ryan's go (or the page arrives through a SiteGround Custom Deploy from staging32).
 *
 * What it writes (WordPress's own storage: posts table + options table; no files, no MU-plugin):
 *   1. a published page "Accessibility" (slug accessibility)
 *   2. the footer row (footer widget block 7, options table widget_block): " . Accessibility" added after the Privacy Policy link. The guard demands the row exactly as
 *      the Privacy Policy installer leaves it, so run that first; anything else stops the run. The copyright/license lines are not touched.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = no such page and the footer row is the known text; applied = exactly this change is there; CHANGED = anything else (stop)
 *   apply        snapshot, create the page, add the footer link, read everything back; undo itself if any read-back differs
 *   rollback     FILE = the before-snapshot: delete the page and put the old footer row back, if everything is still exactly as written
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
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$content = file_get_contents( __DIR__ . '/accessibility-page.html' );
$content = false === $content ? '' : trim( $content );
if ( '' === $content ) {
	WP_CLI::error( 'accessibility-page.html is missing or empty next to the installer.' );
}
// The OBMT forbidden terms (list in AGENTS.md): substring checks, "heal" and "we/our/us" as whole words ("health" is allowed).
$text = html_entity_decode( wp_strip_all_tags( $content ), ENT_QUOTES, 'UTF-8' );
foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore balance', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio' ) as $bad ) {
	if ( false !== stripos( $text, $bad ) ) {
		WP_CLI::error( 'The page text contains a forbidden term: ' . $bad );
	}
}
if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $text ) || preg_match( '/\b(we|our|us|ours)\b/i', $text ) ) {
	WP_CLI::error( 'The page text contains heal/healing or we/our/us, which this site must not use.' );
}
if ( strlen( trim( $text ) ) < 900 ) {
	WP_CLI::error( 'The page text is far shorter than expected; the markup is probably broken.' );
}

$slug      = 'accessibility';
$title     = 'Accessibility';
$widget_id = 7;
// The footer row as install-privacy-page.php leaves it (email, Resume, Privacy Policy), byte for byte.
$old_widget = "<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-location\"} -->\n<p class=\"has-text-align-center ts-footer-location\">Portland, OR</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-links\"} -->\n<p class=\"has-text-align-center ts-footer-links\"><a href=\"mailto:butsabad@gmail.com\">butsabad@gmail.com</a> · <a href=\"/work-with-me/\">Résumé &amp; experience</a> · <a href=\"/privacy-policy/\">Privacy&nbsp;Policy</a></p>\n<!-- /wp:paragraph -->";
$new_widget = str_replace( 'Privacy&nbsp;Policy</a></p>', 'Privacy&nbsp;Policy</a> · <a href="/accessibility/">Accessibility</a></p>', $old_widget, $replaced );
if ( 1 !== $replaced || $new_widget === $old_widget ) {
	WP_CLI::error( 'Internal error: the footer link could not be placed in the known footer row.' );
}

/** The widget_block option without our block, to prove nothing else in it changed. */
$others = function ( $blocks ) use ( $widget_id ) {
	if ( is_array( $blocks ) ) {
		unset( $blocks[ $widget_id ] );
	}
	return $blocks;
};
$read = function () use ( $slug, $content, $widget_id, $old_widget, $new_widget ) {
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'widget_block', 'options' );
	$blocks = get_option( 'widget_block' );
	$widget = is_array( $blocks ) && isset( $blocks[ $widget_id ]['content'] ) ? (string) $blocks[ $widget_id ]['content'] : null;
	$pages  = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	if ( 0 === count( $pages ) && $widget === $old_widget ) {
		$state = 'ready';
	} elseif ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status && trim( (string) $pages[0]->post_content ) === $content && $widget === $new_widget ) {
		$state = 'applied';
	} else {
		$state = 'CHANGED';
	}
	return array( 'state' => $state, 'pages' => $pages, 'widget' => $widget, 'blocks' => $blocks );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d footer_row_is=%s', $home, $state, count( $now['pages'] ),
	$now['widget'] === $old_widget ? 'known-old' : ( $now['widget'] === $new_widget ? 'old+link' : 'UNKNOWN' ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Something differs from the reviewed state (a page with this slug, or a different footer row: run the Privacy Policy installer first); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$write_snap = function ( $file, $created_id ) use ( $home, $old_widget, $widget_id, $content ) {
	$ok = false !== file_put_contents( $file, wp_json_encode( array(
		'created' => gmdate( 'c' ), 'home' => $home, 'created_id' => $created_id, 'widget_id' => $widget_id, 'old_widget' => $old_widget, 'content_sha256' => hash( 'sha256', $content ),
	) ) );
	if ( $ok ) {
		chmod( $file, 0600 );
	}
	return $ok && filesize( $file ) > 0;
};
$undo = function ( $page_id, $widget_text ) use ( $widget_id ) {
	$blocks = get_option( 'widget_block' );
	if ( is_array( $blocks ) && isset( $blocks[ $widget_id ] ) ) {
		$blocks[ $widget_id ]['content'] = $widget_text;
		update_option( 'widget_block', $blocks );
	}
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
	$file = rtrim( $backup_dir, '/' ) . '/accessibility-page-before-' . gmdate( 'Ymd-His' ) . '.json';
	if ( ! $write_snap( $file, null ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	$id = wp_insert_post( wp_slash( array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content,
		'comment_status' => 'closed', 'ping_status' => 'closed', 'post_author' => get_current_user_id(),
	) ), true );
	if ( is_wp_error( $id ) || ! $id ) {
		WP_CLI::error( 'Could not create the page; nothing else was changed.' );
	}
	$write_snap( $file, (int) $id );
	$blocks = $now['blocks'];
	$blocks[ $widget_id ]['content'] = $new_widget;
	update_option( 'widget_block', $blocks );
	$after = $read();
	$path  = wp_parse_url( get_permalink( (int) $id ), PHP_URL_PATH );
	if ( 'applied' !== $after['state'] || '/accessibility/' !== $path || $others( $after['blocks'] ) != $others( $now['blocks'] ) ) {
		$undo( (int) $id, $old_widget );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; everything was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Created page #' . $id . ' /accessibility/ and added the footer link after Privacy Policy. Before-snapshot: ' . $file );
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
$undo( (int) $snap['created_id'], (string) $snap['old_widget'] );
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: page deleted, footer row restored.' );
