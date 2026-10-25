<?php
/**
 * Contact page: the SEO meta description (the line a search engine shows under the page title) says what is actually offered now, for ONE site at a time.
 * Ryan, 2026-10-25 (after the availability plan made individual appointments "planned for summer 2027", and I showed him the before/after): "Change to corporate wording".
 *
 *   BEFORE  Text (312) 539-4328 to book a massage with Butsaba Dusrai, Portland | butsabad@gmail.com
 *   AFTER   Text (312) 539-4328 or email butsabad@gmail.com to ask about corporate and group massage in Portland with Butsaba Dusrai, Oregon LMT.      (133 characters)
 *   The page title stays exactly as it is: Contact Butsaba Dusrai | Oregon LMT. The phone number and the email address are the ones the old description already published.
 *
 * What it writes: SureRank's post meta for this one page (written through SureRank's own saver so its grouped storage stays valid). The page content, images, menus and every other page are not touched
 * (a hash of the content is compared before and after). Same pattern as thai/install-thai-meta.php.
 * The guard: ready = the page's title and description are EXACTLY the "before" values above; applied = EXACTLY the "after" values; anything else (someone set a different one in the meantime) stops the run.
 * Rollback is exact: the snapshot holds every SureRank row the page had (raw stored values), and rollback puts exactly those back and removes any row the apply added.
 * The new wording is checked against the site's whole banned-word list (no research exemption applies to meta fields), the claim verbs and we/our/us, and the length limits.
 *
 * Allow-list: localhost:8090, staging38 and live. Live was added after staging38 was applied, checked and proven (2026-10-25: pixel-identical to live, restore proof moved only this page's SEO row); Ryan starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the page's SureRank rows, write the description, read it back; restore the snapshot if the read-back differs
 *   rollback     FILE = the before-snapshot: put the page's SureRank rows back exactly as they were
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging38 or live only).' );
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

$slug     = 'contact';
$title    = 'Contact Butsaba Dusrai | Oregon LMT';
$old_desc = 'Text (312) 539-4328 to book a massage with Butsaba Dusrai, Portland | butsabad@gmail.com';
$new_desc = 'Text (312) 539-4328 or email butsabad@gmail.com to ask about corporate and group massage in Portland with Butsaba Dusrai, Oregon LMT.';

// ---- checks on the NEW wording ----------------------------------------------------------------------------------------------------------------------
$bad = array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i',
	'/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i', '/real results/i', '/\bstudio\b/i', '/\b(we|our|us|ours)\b/i', '/Portland[- ]area/i',
	'/Portland metro/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i' );
foreach ( $bad as $re ) {
	if ( preg_match( $re, $new_desc, $m ) ) {
		WP_CLI::error( 'The new description contains a term this site must not use: "' . $m[0] . '"' );
	}
}
if ( strlen( $new_desc ) > 160 || strlen( $title ) > 60 || '' === $new_desc ) {
	WP_CLI::error( 'Internal error: the reviewed title/description are outside the length limits (title <= 60, description <= 160).' );
}

/** The page, its current SureRank rows and the state. */
$read = function () use ( $slug, $title, $old_desc, $new_desc ) {
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
		if ( is_array( $gen ) && isset( $gen['page_title'], $gen['page_description'] ) && $gen['page_title'] === $title ) {
			if ( $gen['page_description'] === $old_desc ) {
				$state = 'ready';
			} elseif ( $gen['page_description'] === $new_desc ) {
				$state = 'applied';
			}
		}
	}
	return array( 'state' => $state, 'pages' => $pages, 'rows' => $rows, 'hash' => $hash );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d', $home, $state, count( $now['pages'] ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The Contact page is not in a reviewed state (missing, not published, or its SEO title/description is not exactly the before or the after text); stop and review. ' . $report );
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
		WP_CLI::success( 'Nothing to do: the description is already there. ' . $report );
		return;
	}
	$id   = (int) $now['pages'][0]->ID;
	$file = rtrim( $backup_dir, '/' ) . '/contact-meta-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	\SureRank\Inc\API\Post::update_post_meta_common( $id, array( 'page_title' => $title, 'page_description' => $new_desc ) );
	$after = $read();
	if ( 'applied' !== $after['state'] || $after['hash'] !== $now['hash'] ) {
		$restore( $id, $now['rows'] );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written (state ' . $after['state'] . ', content unchanged: ' . ( $after['hash'] === $now['hash'] ? 'yes' : 'NO' ) . '); the old SureRank rows were put back (identical to before: ' . ( $check['rows'] === $now['rows'] ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/contact/): SEO description is set. Before-snapshot: ' . $file );
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

// ref: ../thai/install-thai-meta.php — the installer this is copied from (same guard, snapshot and rollback); ../client-pages/install-client-pages-staging.php — the Services precedent for SureRank title/description
// ref: ../availability/availability-content.php — the plan's wording (corporate and group massage now; individual appointments planned for summer 2027) this description follows
