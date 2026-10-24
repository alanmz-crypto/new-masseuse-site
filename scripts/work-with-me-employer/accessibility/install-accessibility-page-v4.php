<?php
/**
 * Accessibility page, VERSION 4: name the Deep Tissue Massage page among the pages with collapsible research summaries (in the "What has been checked" sentence and in the "no screen reader" limit) and update the date,
 * for ONE site at a time. Ryan, 2026-10-24: "Add the Deep Tissue line to the Accessibility page". The wording is accessibility-page.html next to this file (built by build-accessibility-page.py); every sentence is limited
 * to what was tested (see that file). accessibility-page-v3.html is the exact version-3 text (what is live before this update).
 *
 * What it writes: ONLY the page content of the published page with the slug "accessibility". Title, slug, the footer link and everything else stay as they are.
 * It only accepts a page that still holds EXACTLY version 3 (or already holds version 4); anything else stops the run. It also REFUSES unless the Thai Massage, Therapeutic Massage and Deep Tissue Massage pages
 * on that site really carry their research sections (the heading "What the Research Says About ... Massage"), so the page can never describe panels that are not there.
 *
 * Allow-list: localhost:8090 and staging36 ONLY. Live is deliberately not in it; it is added only on Ryan's own go.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: old = the page holds exactly version 3 (ready); new = already version 4; CHANGED = anything else (stop)
 *   apply        snapshot, then replace the page content with version 4 and read everything back; put version 3 back if any read-back differs
 *   rollback     FILE = the before-snapshot: put version 3 back, if the page still holds exactly version 4
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the old content.)
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
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$old = file_get_contents( __DIR__ . '/accessibility-page-v3.html' );
$new = file_get_contents( __DIR__ . '/accessibility-page.html' );
$old = false === $old ? '' : trim( $old );
$new = false === $new ? '' : trim( $new );
if ( '' === $old || '' === $new || $old === $new ) {
	WP_CLI::error( 'accessibility-page-v3.html and accessibility-page.html must both exist next to the installer and differ.' );
}
// The OBMT forbidden terms (list in AGENTS.md): substring checks, "heal" and "we/our/us" as whole words ("health" is allowed).
$text = html_entity_decode( wp_strip_all_tags( $new ), ENT_QUOTES, 'UTF-8' );
foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore balance', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio' ) as $bad ) {
	if ( false !== stripos( $text, $bad ) ) {
		WP_CLI::error( 'The page text contains a forbidden term: ' . $bad );
	}
}
if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $text ) || preg_match( '/\b(we|our|us|ours)\b/i', $text ) ) {
	WP_CLI::error( 'The page text contains heal/healing or we/our/us, which this site must not use.' );
}
if ( false !== stripos( $text, 'complian' ) || false !== stripos( $text, 'conform' ) || false !== stripos( $text, 'fully accessible' ) ) {
	WP_CLI::error( 'The page text makes a conformance claim, which this statement must not make.' );
}
if ( strlen( trim( $text ) ) < 900 ) {
	WP_CLI::error( 'The page text is far shorter than expected; the markup is probably broken.' );
}

$slug = 'accessibility';

/** Current state of the one page with this slug, and how many of the Thai Massage, Therapeutic Massage and Deep Tissue Massage pages carry their research sections. */
$read = function () use ( $slug, $old, $new ) {
	$pages  = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$panels = 0;
	foreach ( array( 'thai-massage' => 'What the Research Says About Thai Massage', 'therapeutic-massage' => 'What the Research Says About Therapeutic Massage', 'deep-tissue-massage' => 'What the Research Says About Deep Tissue Massage' ) as $page_slug => $needle ) {
		$p = get_posts( array( 'post_type' => 'page', 'name' => $page_slug, 'post_status' => 'publish', 'numberposts' => -1, 'suppress_filters' => true ) );
		if ( 1 === count( $p ) && 1 === substr_count( (string) $p[0]->post_content, $needle ) ) {
			++$panels;
		}
	}
	$state  = 'CHANGED';
	if ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) {
		$c     = trim( (string) $pages[0]->post_content );
		$state = $c === $old ? 'old' : ( $c === $new ? 'new' : 'CHANGED' );
	}
	return array( 'state' => $state, 'pages' => $pages, 'panels' => $panels );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d research_sections_present=%d_of_3', $home, $state, count( $now['pages'] ), $now['panels'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not exactly version 3 or version 4 (missing, hand-edited, or not published); stop and review. ' . $report );
	}
	if ( 'old' === $state && 3 !== $now['panels'] ) {
		WP_CLI::error( 'The Thai Massage, Therapeutic Massage and Deep Tissue Massage pages here do not all carry their research sections; the page would describe panels that are not there. Apply thai/, therapeutic/ and deep-tissue/ first. ' . $report );
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
		WP_CLI::success( 'Nothing to do: already version 4. ' . $report );
		return;
	}
	if ( 3 !== $now['panels'] ) {
		WP_CLI::error( 'The Thai Massage, Therapeutic Massage and Deep Tissue Massage pages here do not all carry their research sections; nothing was changed. ' . $report );
	}
	$id   = (int) $now['pages'][0]->ID;
	$file = rtrim( $backup_dir, '/' ) . '/accessibility-page-v4-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	$res   = $set_content( $id, $new );
	$after = $read();
	$path  = wp_parse_url( get_permalink( $id ), PHP_URL_PATH );
	if ( is_wp_error( $res ) || ! $res || 'new' !== $after['state'] || '/accessibility/' !== $path ) {
		$set_content( $id, $old );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; version 3 was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/accessibility/) now holds version 4 (the Deep Tissue page named in the research summaries sentence and the screen reader limit, and the new date). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || ( $snap['old_content_sha256'] ?? '' ) !== hash( 'sha256', $old ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete, or from another site or version.' );
}
if ( 'new' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold exactly version 4. ' . $report );
	return;
}
$set_content( (int) $snap['page_id'], $old );
$check = $read();
if ( 'old' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to version 3 (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the page holds version 3 again.' );

// ref: ./build-accessibility-page.py — builds accessibility-page.html (this version); ./accessibility-page-v3.html — the exact old text; ../deep-tissue/install-deep-tissue-evidence.php — must be applied first; ./install-accessibility-page-v3.php — the installer this follows
