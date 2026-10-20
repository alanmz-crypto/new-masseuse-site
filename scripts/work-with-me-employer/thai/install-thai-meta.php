<?php
/**
 * Thai Massage page: SEO title and meta description, for ONE site at a time.
 * Source: Ryan, 2026-10-19 ("I like yours. Go with that meta.") after he asked what a meta description is and saw the before/after:
 *
 *   title        Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT                                       (browser tab, search-result title)
 *   description  Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.   (150 characters)
 *
 * The page had none: its description was auto-built by SureRank from the first words of the page text ("Thai Massage in Mt. Tabor What Is Thai Massage? Thai massage brings movement and massage
 * together through rhythmic compression, gentle rocking, and assisted"), cut off mid-sentence, and the same text went into the Facebook-style and Twitter-style previews. Same pattern as the Services page
 * (client-pages/install-client-pages-staging.php): written through SureRank's own saver so its grouped storage stays valid; the previews follow the title and description.
 *
 * What it writes: SureRank's post meta for this one page (posts meta table). The page content, images, menus and every other page are not touched (a hash of the content is compared before and after).
 * The guard: ready = the page has no SEO title/description of its own; applied = it holds exactly the two values above; anything else (someone set a different one) stops the run.
 * Rollback is exact: the snapshot holds every SureRank row the page had (raw stored values), and rollback puts exactly those back and removes any row the apply added.
 * The new wording is checked against the site's whole banned-word list (no research exemption applies to meta fields), the claim verbs and we/our/us, and the length limits.
 *
 * Allow-list: localhost:8090, staging33 and live. Live was added by Ryan's go ("push it live", 2026-10-19) after the change was applied, checked and rollback-proven on localhost and staging33; he applies it himself (the auto-mode classifier refuses an agent adding a live host).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the page's SureRank rows, write the title and description, read them back; restore the snapshot if the read-back differs
 *   rollback     FILE = the before-snapshot: put the page's SureRank rows back exactly as they were
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging33.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging33 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator).' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}
if ( ! class_exists( '\SureRank\Inc\API\Post' ) || ! method_exists( '\SureRank\Inc\API\Post', 'update_post_meta_common' ) ) {
	WP_CLI::error( 'SureRank page-level SEO API is not available.' );
}

$slug  = 'thai-massage';
$title = 'Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT';
$desc  = 'Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.';

// ---- checks on the NEW wording ----------------------------------------------------------------------------------------------------------------------
$bad = array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i',
	'/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i', '/real results/i', '/\bstudio\b/i', '/\b(we|our|us|ours)\b/i', '/Portland[- ]area/i',
	'/Portland metro/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i' );
foreach ( $bad as $re ) {
	if ( preg_match( $re, $title . ' ' . $desc, $m ) ) {
		WP_CLI::error( 'The new title or description contains a term this site must not use: "' . $m[0] . '"' );
	}
}
if ( strlen( $desc ) > 160 || strlen( $title ) > 60 || '' === $title || '' === $desc ) {
	WP_CLI::error( 'Internal error: the reviewed title/description are outside the length limits (title <= 60, description <= 160).' );
}

/** The page, its current SureRank rows and the state. */
$read = function () use ( $slug, $title, $desc ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$state = 'CHANGED';
	$rows  = array();
	$hash  = '';
	if ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) {
		$hash = hash( 'sha256', (string) $pages[0]->post_content );
		foreach ( get_post_meta( $pages[0]->ID ) as $k => $v ) {
			if ( 0 === strpos( $k, 'surerank' ) ) {
				$rows[ $k ] = $v;
			}
		}
		$gen = get_post_meta( $pages[0]->ID, 'surerank_settings_general', true );
		$has_t = is_array( $gen ) && isset( $gen['page_title'] ) && '' !== $gen['page_title'];
		$has_d = is_array( $gen ) && isset( $gen['page_description'] ) && '' !== $gen['page_description'];
		if ( ! $has_t && ! $has_d ) {
			$state = 'ready';
		} elseif ( $has_t && $has_d && $gen['page_title'] === $title && $gen['page_description'] === $desc ) {
			$state = 'applied';
		}
	}
	return array( 'state' => $state, 'pages' => $pages, 'rows' => $rows, 'hash' => $hash );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d', $home, $state, count( $now['pages'] ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not in a reviewed state (missing, not published, or it already has a different SEO title/description); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
/** Put exactly these SureRank rows on the page (raw stored values): rows not listed are removed. */
$restore = function ( $page_id, array $rows ) {
	foreach ( get_post_meta( $page_id ) as $k => $v ) {
		if ( 0 === strpos( $k, 'surerank' ) ) {
			delete_post_meta( $page_id, $k );
		}
	}
	foreach ( $rows as $k => $vals ) {
		foreach ( $vals as $raw ) {
			add_post_meta( $page_id, $k, wp_slash( maybe_unserialize( $raw ) ) );
		}
	}
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: the title and description are already there. ' . $report );
		return;
	}
	$id   = (int) $now['pages'][0]->ID;
	$file = rtrim( $backup_dir, '/' ) . '/thai-meta-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array(
		'created'        => gmdate( 'c' ),
		'home'           => $home,
		'page_id'        => $id,
		'content_sha256' => $now['hash'],
		'rows'           => $now['rows'],
	) ) );
	if ( ! $ok || filesize( $file ) < 40 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	\SureRank\Inc\API\Post::update_post_meta_common( $id, array( 'page_title' => $title, 'page_description' => $desc ) );
	$after = $read();
	if ( 'applied' !== $after['state'] || $after['hash'] !== $now['hash'] ) {
		$restore( $id, $now['rows'] );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written (state ' . $after['state'] . ', content unchanged: ' . ( $after['hash'] === $now['hash'] ? 'yes' : 'NO' ) . '); the old SureRank rows were put back (identical to before: ' . ( $check['rows'] === $now['rows'] ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/thai-massage/): SEO title and meta description are set. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || ! isset( $snap['rows'] ) || ! is_array( $snap['rows'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold exactly the applied title and description. ' . $report );
	return;
}
$restore( (int) $snap['page_id'], $snap['rows'] );
$check = $read();
if ( 'ready' !== $check['state'] || $check['rows'] !== $snap['rows'] ) {
	WP_CLI::error( 'Rollback did not return to the old SEO rows (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the page has exactly the SureRank rows it had before (content hash ' . ( $check['hash'] === $snap['content_sha256'] ? 'unchanged' : 'differs: the page was edited since' ) . ').' );

// ref: ../client-pages/install-client-pages-staging.php — the Services precedent for SureRank title/description; ./install-thai-evidence.php — the sibling installer for the same page
// ref: ../client-pages/verify-client-pages.py — the rendered-page gate (title, description, og/twitter, length, wording) for this change
