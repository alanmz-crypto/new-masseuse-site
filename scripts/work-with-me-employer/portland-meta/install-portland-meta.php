<?php
/**
 * Home, Thai, Therapeutic and Deep Tissue pages: the SEO title / meta description (what a search engine shows) say "Portland" instead of "Mt. Tabor", for ONE site at a time (all four pages together).
 * Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." He accepted the exact wording below ("Accepted. All your changes").
 * This ends the hold of 2026-10-25 (contact-meta: the "in Mt. Tabor" titles and descriptions stayed "until the new location is known"). Individual appointments at a dedicated location are still only planned
 * for summer 2027, so the Home description now invites a corporate or group inquiry instead of "a session".
 *
 *   HOME         title  Thai Solitude | Thai & Therapeutic Massage, Mt. Tabor, Portland           ->  Thai Solitude | Thai & Therapeutic Massage in Portland, OR
 *                desc   ...deep tissue massage in Mt. Tabor, Portland. Ease tension and unwind. Ask about a session with Butsaba Dusrai, LMT.
 *                   ->  ...deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT.
 *   THAI         title  Thai Massage in Mt. Tabor | ...                                          ->  Thai Massage in Portland | Butsaba Dusrai, Oregon LMT
 *                desc   "...massage in Mt. Tabor, Portland with..."                              ->  "...massage in Portland with..."
 *   THERAPEUTIC  desc   "...massage in Mt. Tabor, Portland with..."                              ->  "...massage in Portland with..."    (title unchanged)
 *   DEEP TISSUE  desc   "...massage in Mt. Tabor, Portland with..."                              ->  "...massage in Portland with..."    (title unchanged)
 * Nothing else changes: only the location words, plus the one closing sentence of the Home description. Page content, images, menus and every other page are not touched (a hash of each page's content is compared before and after).
 * The Contact page's "Address: Mt. Tabor, Portland" line and map, and the on-page Mt. Tabor headings, are NOT part of this change (next items).
 *
 * What it writes: SureRank's post meta for these four pages (posts meta table), through the plugin's own saver (the Services, Thai and Contact precedent); the previews (Facebook-style, Twitter-style) follow.
 * The guard: ready = ALL four pages hold EXACTLY the "before" title and description above; applied = ALL four hold EXACTLY the "after" values; anything else (someone set a different one, or only some pages are done) stops.
 * Rollback is exact: the snapshot holds every SureRank row the four pages had (raw stored values); rollback puts exactly those back and removes any row the apply added.
 * The new wording is checked against the site's whole banned-word list (no research exemption applies to meta fields), the claim verbs and we/our/us, and the length limits.
 *
 * Allow-list: localhost:8090 and staging38 only. Live is added only after staging38 is applied, checked and proven AND Ryan says go; he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the four pages' SureRank rows, write the titles and descriptions, read them back; restore the snapshot if any read-back differs
 *   rollback     FILE = the before-snapshot: put the four pages' SureRank rows back exactly as they were
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging38 only).' );
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
	'home'                => array(
		'old_title' => 'Thai Solitude | Thai & Therapeutic Massage, Mt. Tabor, Portland',
		'old_desc'  => 'Personalized Thai, therapeutic and deep tissue massage in Mt. Tabor, Portland. Ease tension and unwind. Ask about a session with Butsaba Dusrai, LMT.',
		'title'     => 'Thai Solitude | Thai & Therapeutic Massage in Portland, OR',
		'desc'      => 'Personalized Thai, therapeutic and deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT.',
	),
	'thai-massage'        => array(
		'old_title' => 'Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT',
		'old_desc'  => 'Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.',
		'title'     => 'Thai Massage in Portland | Butsaba Dusrai, Oregon LMT',
		'desc'      => 'Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.',
	),
	'therapeutic-massage' => array(
		'old_title' => 'Therapeutic Massage | Butsaba Dusrai, Oregon LMT',
		'old_desc'  => 'Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.',
		'title'     => 'Therapeutic Massage | Butsaba Dusrai, Oregon LMT',
		'desc'      => 'Therapeutic massage in Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.',
	),
	'deep-tissue-massage' => array(
		'old_title' => 'Deep Tissue Massage | Butsaba Dusrai, Oregon LMT',
		'old_desc'  => 'Deep tissue massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.',
		'title'     => 'Deep Tissue Massage | Butsaba Dusrai, Oregon LMT',
		'desc'      => 'Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.',
	),
);

// ---- checks on the NEW wording ----------------------------------------------------------------------------------------------------------------------
$bad = array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i',
	'/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i', '/real results/i', '/\bstudio\b/i', '/\b(we|our|us|ours)\b/i', '/Portland[- ]area/i',
	'/Portland metro/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i', '/Mt\.? Tabor/i' );
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
	if ( 'home' === $slug && (int) get_option( 'page_on_front' ) !== (int) $pages[0]->ID ) {
		return $o;
	}
	$o['id']   = (int) $pages[0]->ID;
	$o['hash'] = hash( 'sha256', (string) $pages[0]->post_content );
	foreach ( get_post_meta( $pages[0]->ID ) as $k => $v ) {
		if ( 0 === strpos( $k, 'surerank' ) ) {
			$o['rows'][ $k ] = $v;
		}
	}
	$gen = get_post_meta( $pages[0]->ID, 'surerank_settings_general', true );
	if ( is_array( $gen ) && isset( $gen['page_title'], $gen['page_description'] ) ) {
		if ( $gen['page_title'] === $cfg['old_title'] && $gen['page_description'] === $cfg['old_desc'] ) {
			$o['state'] = 'ready';
		} elseif ( $gen['page_title'] === $cfg['title'] && $gen['page_description'] === $cfg['desc'] ) {
			$o['state'] = 'applied';
		}
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
		WP_CLI::error( 'The pages are not in a reviewed state (one is missing or unpublished, one holds a title/description that is not exactly the before or the after text, or only some pages are done); stop and review. ' . $report );
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
		WP_CLI::success( 'Nothing to do: all four pages already have the new titles and descriptions. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/portland-meta-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'pages' => array() );
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
	WP_CLI::success( 'SEO titles and meta descriptions are set on all four pages (Home, Thai, Therapeutic, Deep Tissue). Before-snapshot: ' . $file );
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
WP_CLI::success( 'Rollback done: all four pages have exactly the SureRank rows they had before.' );

// ref: ../services-meta/install-service-meta.php — the two-page installer this is extended from (same guard, snapshot and rollback); ../contact-meta/install-contact-meta.php — the Contact precedent and the "left until the location is known" note
// ref: ../availability/availability-content.php — the plan's wording (corporate and group massage now; individual appointments planned for summer 2027)
