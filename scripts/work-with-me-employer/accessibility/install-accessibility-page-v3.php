<?php
/**
 * Accessibility page, VERSION 3: add the sentence about the new collapsible research summaries (Thai Massage and Therapeutic Massage), the "no screen reader was used" limit and the new date, for ONE site at a time.
 * Ryan, 2026-10-19: "go on this: Accessibility page: its date and limits haven't been updated for the new panels". The wording is accessibility-page.html next to this file (built by build-accessibility-page.py);
 * every sentence is limited to what was tested (see that file). accessibility-page-v2.html is the exact version-2 text (what is live before this update).
 *
 * What it writes: ONLY the page content of the published page with the slug "accessibility". Title, slug, the footer link and everything else stay as they are.
 * It only accepts a page that still holds EXACTLY version 2 (or already holds version 3); anything else stops the run. It also REFUSES unless the Thai Massage and Therapeutic Massage pages on that site
 * really carry their research sections (the heading "What the Research Says About ... Massage"), so the page can never describe panels that are not there.
 *
 * Allow-list: localhost:8090 and staging34. Live is added only after Ryan's explicit go (an agent adding a live host is refused by the auto-mode classifier: hand him a tested patch).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: old = the page holds exactly version 2 (ready); new = already version 3; CHANGED = anything else (stop)
 *   apply        snapshot, then replace the page content with version 3 and read everything back; put version 2 back if any read-back differs
 *   rollback     FILE = the before-snapshot: put version 2 back, if the page still holds exactly version 3
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the old content.)
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
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$old = file_get_contents( __DIR__ . '/accessibility-page-v2.html' );
$new = file_get_contents( __DIR__ . '/accessibility-page.html' );
$old = false === $old ? '' : trim( $old );
$new = false === $new ? '' : trim( $new );
if ( '' === $old || '' === $new || $old === $new ) {
	WP_CLI::error( 'accessibility-page-v2.html and accessibility-page.html must both exist next to the installer and differ.' );
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

/** Current state of the one page with this slug, and how many of the Thai Massage and Therapeutic Massage pages carry their research sections. */
$read = function () use ( $slug, $old, $new ) {
	$pages  = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$panels = 0;
	foreach ( array( 'thai-massage' => 'What the Research Says About Thai Massage', 'therapeutic-massage' => 'What the Research Says About Therapeutic Massage' ) as $page_slug => $needle ) {
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
$report = sprintf( 'host=%s state=%s pages_with_slug=%d research_sections_present=%d_of_2', $home, $state, count( $now['pages'] ), $now['panels'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not exactly version 2 or version 3 (missing, hand-edited, or not published); stop and review. ' . $report );
	}
	if ( 'old' === $state && 2 !== $now['panels'] ) {
		WP_CLI::error( 'The Thai Massage and Therapeutic Massage pages here do not both carry their research sections; the page would describe panels that are not there. Apply thai/ and therapeutic/ first. ' . $report );
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
	if ( 2 !== $now['panels'] ) {
		WP_CLI::error( 'The Thai Massage and Therapeutic Massage pages here do not both carry their research sections; nothing was changed. ' . $report );
	}
	$id   = (int) $now['pages'][0]->ID;
	$file = rtrim( $backup_dir, '/' ) . '/accessibility-page-v3-before-' . gmdate( 'Ymd-His' ) . '.json';
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
		WP_CLI::error( 'Read-back differed from what was written; version 2 was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/accessibility/) now holds version 3 (the research summaries sentence, the screen reader limit and the new date). Before-snapshot: ' . $file );
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

// ref: ./build-accessibility-page.py — builds accessibility-page.html (this version); ./accessibility-page-v2.html — the exact old text; ../thai/install-thai-evidence.php and ../therapeutic/install-therapeutic-evidence.php — must be applied first; ./install-accessibility-page-v2.php — the version this follows
