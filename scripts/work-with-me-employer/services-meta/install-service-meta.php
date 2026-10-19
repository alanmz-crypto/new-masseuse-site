<?php
/**
 * Therapeutic Massage and Deep Tissue Massage pages: SEO title and meta description, for ONE site at a time (both pages together).
 * Ryan, 2026-10-19: "finish one and two then go to three" (my list: three = "Butsaba's three FAQ lines, and the meta descriptions on Therapeutic and Deep Tissue"). Same fix as the Thai page
 * (thai/install-thai-meta.php, approved by Ryan on 2026-10-19: "I like yours. Go with that meta."): both pages have NO title/description of their own, so SureRank auto-builds the description from the first
 * words of the page text and cuts it off mid-sentence:
 *   Therapeutic: "Therapeutic Massage in Portland What Is Therapeutic Massage? Therapeutic massage combines focused work with time to relax. I start with what brought you in."
 *   Deep Tissue: "Deep Tissue Massage in Mt. Tabor What Is Deep Tissue Massage? Deep tissue massage uses slow, deliberate pressure on muscles that stay tight. I begin by asking"
 * The proposed wording only restates what each page itself says (checked against the page text on 2026-10-19):
 *   Therapeutic  title        Therapeutic Massage | Butsaba Dusrai, Oregon LMT                                                  (48 characters)
 *                description  Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.   (146)
 *   Deep Tissue  title        Deep Tissue Massage | Butsaba Dusrai, Oregon LMT                                                  (48)
 *                description  Deep tissue massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.   (140)
 * (The page says "You can ask me to stay with one area ... or to work more broadly", "adapting pressure and pace to your comfort", "slow, deliberate pressure on muscles that stay tight" and
 * "Deep pressure is never required: you can ask me to lighten up at any time, and I will." Titles follow the Services page pattern; the location is in the description.)
 *
 * What it writes: SureRank's post meta for these two pages (posts meta table), through the plugin's own saver (the Services and Thai precedent); the previews (Facebook-style, Twitter-style) follow.
 * Page content, images, menus and every other page are not touched (a hash of each page's content is compared before and after).
 * The guard: ready = BOTH pages have no SEO title/description of their own; applied = BOTH hold exactly the values above; anything else (someone set a different one, or only one page is done) stops.
 * Rollback is exact: the snapshot holds every SureRank row both pages had (raw stored values); rollback puts exactly those back and removes any row the apply added.
 * The new wording is checked against the site's whole banned-word list (no research exemption applies to meta fields), the claim verbs and we/our/us, and the length limits.
 *
 * Allow-list: localhost:8090 and staging34. Live is added only after Ryan's explicit go (an agent adding a live host is refused by the auto-mode classifier: hand him a tested patch).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot both pages' SureRank rows, write the titles and descriptions, read them back; restore the snapshot if any read-back differs
 *   rollback     FILE = the before-snapshot: put both pages' SureRank rows back exactly as they were
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
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

$pages_cfg = array(
	'therapeutic-massage' => array(
		'title' => 'Therapeutic Massage | Butsaba Dusrai, Oregon LMT',
		'desc'  => 'Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.',
	),
	'deep-tissue-massage' => array(
		'title' => 'Deep Tissue Massage | Butsaba Dusrai, Oregon LMT',
		'desc'  => 'Deep tissue massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.',
	),
);

// ---- checks on the NEW wording ----------------------------------------------------------------------------------------------------------------------
$bad = array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i',
	'/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i', '/real results/i', '/\bstudio\b/i', '/\b(we|our|us|ours)\b/i', '/Portland[- ]area/i',
	'/Portland metro/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i' );
foreach ( $pages_cfg as $slug => $cfg ) {
	foreach ( $bad as $re ) {
		if ( preg_match( $re, $cfg['title'] . ' ' . $cfg['desc'], $m ) ) {
			WP_CLI::error( 'The new title or description for ' . $slug . ' contains a term this site must not use: "' . $m[0] . '"' );
		}
	}
	if ( strlen( $cfg['desc'] ) > 160 || strlen( $cfg['title'] ) > 60 || '' === $cfg['title'] || '' === $cfg['desc'] ) {
		WP_CLI::error( 'Internal error: the reviewed title/description of ' . $slug . ' are outside the length limits (title <= 60, description <= 160).' );
	}
}

/** One page: its SureRank rows, a hash of its content, and its own state. */
$read_page = function ( $slug, $cfg ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$o     = array( 'state' => 'CHANGED', 'id' => 0, 'rows' => array(), 'hash' => '' );
	if ( 1 !== count( $pages ) || 'page' !== $pages[0]->post_type || 'publish' !== $pages[0]->post_status ) {
		return $o;
	}
	$o['id']   = (int) $pages[0]->ID;
	$o['hash'] = hash( 'sha256', (string) $pages[0]->post_content );
	foreach ( get_post_meta( $pages[0]->ID ) as $k => $v ) {
		if ( 0 === strpos( $k, 'surerank' ) ) {
			$o['rows'][ $k ] = $v;
		}
	}
	$gen   = get_post_meta( $pages[0]->ID, 'surerank_settings_general', true );
	$has_t = is_array( $gen ) && isset( $gen['page_title'] ) && '' !== $gen['page_title'];
	$has_d = is_array( $gen ) && isset( $gen['page_description'] ) && '' !== $gen['page_description'];
	if ( ! $has_t && ! $has_d ) {
		$o['state'] = 'ready';
	} elseif ( $has_t && $has_d && $gen['page_title'] === $cfg['title'] && $gen['page_description'] === $cfg['desc'] ) {
		$o['state'] = 'applied';
	}
	return $o;
};
$read = function () use ( $pages_cfg, $read_page ) {
	$all = array();
	foreach ( $pages_cfg as $slug => $cfg ) {
		$all[ $slug ] = $read_page( $slug, $cfg );
	}
	$states = array_unique( array_column( $all, 'state' ) );
	return array( 'state' => ( 1 === count( $states ) && in_array( $states[0], array( 'ready', 'applied' ), true ) ) ? $states[0] : 'CHANGED', 'pages' => $all );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages=%s', $home, $state, wp_json_encode( array_map( function ( $p ) {
	return $p['state'];
}, $now['pages'] ) ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The pages are not in a reviewed state (one is missing or unpublished, one already has a different SEO title/description, or only one of the two is done); stop and review. ' . $report );
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
		WP_CLI::success( 'Nothing to do: both pages already have the titles and descriptions. ' . $report );
		return;
	}
	$file  = rtrim( $backup_dir, '/' ) . '/service-meta-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap  = array( 'created' => gmdate( 'c' ), 'home' => $home, 'pages' => array() );
	foreach ( $now['pages'] as $slug => $p ) {
		$snap['pages'][ $slug ] = array( 'page_id' => $p['id'], 'content_sha256' => $p['hash'], 'rows' => $p['rows'] );
	}
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || filesize( $file ) < 40 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	foreach ( $pages_cfg as $slug => $cfg ) {
		\SureRank\Inc\API\Post::update_post_meta_common( $now['pages'][ $slug ]['id'], array( 'page_title' => $cfg['title'], 'page_description' => $cfg['desc'] ) );
	}
	$after = $read();
	$ok    = 'applied' === $after['state'];
	foreach ( $after['pages'] as $slug => $p ) {
		$ok = $ok && $p['hash'] === $now['pages'][ $slug ]['hash'];
	}
	if ( ! $ok ) {
		foreach ( $now['pages'] as $slug => $p ) {
			$restore( $p['id'], $p['rows'] );
		}
		$check = $read();
		$same  = true;
		foreach ( $check['pages'] as $slug => $p ) {
			$same = $same && $p['rows'] === $now['pages'][ $slug ]['rows'];
		}
		WP_CLI::error( 'Read-back differed from what was written (state ' . $after['state'] . '); the old SureRank rows were put back (identical to before: ' . ( $same ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'SEO title and meta description are set on both pages (Therapeutic Massage, Deep Tissue Massage). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['pages'] ) || ! is_array( $snap['pages'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
foreach ( $pages_cfg as $slug => $cfg ) {
	if ( empty( $snap['pages'][ $slug ]['page_id'] ) || ! isset( $snap['pages'][ $slug ]['rows'] ) || (int) $snap['pages'][ $slug ]['page_id'] !== (int) $now['pages'][ $slug ]['id'] ) {
		WP_CLI::error( 'The snapshot does not match these pages; stop and inspect.' );
	}
}
if ( 'applied' !== $state ) {
	WP_CLI::success( 'Nothing to roll back: the pages do not hold exactly the applied titles and descriptions. ' . $report );
	return;
}
foreach ( $snap['pages'] as $slug => $s ) {
	$restore( (int) $s['page_id'], $s['rows'] );
}
$check = $read();
$exact = 'ready' === $check['state'];
foreach ( $snap['pages'] as $slug => $s ) {
	$exact = $exact && $check['pages'][ $slug ]['rows'] === $s['rows'];
}
if ( ! $exact ) {
	WP_CLI::error( 'Rollback did not return to the old SEO rows (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: both pages have exactly the SureRank rows they had before.' );

// ref: ../thai/install-thai-meta.php — the single-page installer this extends to two pages; ../client-pages/install-client-pages-staging.php — the Services precedent for SureRank title/description
// ref: ../client-pages/verify-client-pages.py — the rendered-page gate (title, description, og/twitter, length, wording) for this change
