<?php
/**
 * Winding Willow page: ADD the page /winding-willow/ ("Massage at Winding Willow"; H1 "Massage with Sa at Winding Willow Acupuncture & Wellness"), exactly as it was
 * built and reviewed on staging39 (page #1385, last edited 2026-11-13), on ONE site at a time.
 * Ryan, 2026-11-17: "get the 'winding-willow' page on Thai Solitude to the point where publishing to live is one step ... Follow the pattern of
 * corporate/install-corporate-massage.php + push-corporate-massage.sh: allow-list, dry run by default, --apply with DB export + off-site copy, --rollback,
 * idempotent re-apply."
 *
 * EVERYTHING HERE IS ADDED. What it writes (WordPress's own storage; no files, no MU-plugin):
 *   the page (posts table): slug winding-willow, the exact content bytes of winding-willow-page.html (sha256 in winding-willow.json), published, comments closed;
 *   its meta site-post-title = disabled (the theme's own title off, like the other pages) and SureRank's title + description (through SureRank's own saver).
 * It does NOT add a menu item, a link from any other page, CSS or a snippet: on staging39 the page has none of these (capture ww-capture-s39-1), and where it is
 * linked from is Ryan's decision. Spectra rebuilds the page's own block CSS by itself on the first view (it did the same for the corporate page).
 *
 * Allow-list: localhost:8090, staging40 (the fresh copy of live) and live. staging39 (the reference copy) for the READ-ONLY preflight only. Live was added on
 * Ryan's go (docs/inter-model/2026-11-18-winding-willow-enable-live.patch) after the rehearsal on a copy of live and the staging40 apply; he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = no page with this slug or title exists; applied = the page is there exactly as written; CHANGED = anything else (stop)
 *   apply        snapshot, create the page and its two meta fields, read everything back; delete what it made if any read-back differs
 *   rollback     FILE = the before-snapshot apply wrote: delete exactly that page again (only when it is still exactly as applied)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
$writable  = array( 'localhost:8090', 'staging40.thaisolitude.com', 'thaisolitude.com' );
$read_only = array( 'staging39.thaisolitude.com' );
if ( ! in_array( $host, $writable, true ) && ! ( 'preflight' === $mode && in_array( $host, $read_only, true ) ) ) {
	WP_CLI::error( 'Refusing to run ' . $mode . ': ' . $home . ' is not an allowed target (write: ' . implode( ', ', $writable ) . '; preflight only: ' . implode( ', ', $read_only ) . ').' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved content.' );
}
if ( ! class_exists( '\SureRank\Inc\API\Post' ) || ! method_exists( '\SureRank\Inc\API\Post', 'update_post_meta_common' ) ) {
	WP_CLI::error( 'SureRank page-level SEO API is not available.' );
}

// ---- the reviewed package -----------------------------------------------------------------------------------------------------------------------------
$spec = json_decode( (string) @file_get_contents( __DIR__ . '/winding-willow.json' ), true );
foreach ( array( 'slug', 'title', 'content_file', 'content_sha256', 'seo_title', 'seo_desc', 'research_heading', 'block_ids' ) as $k ) {
	if ( ! is_array( $spec ) || empty( $spec[ $k ] ) ) {
		WP_CLI::error( 'winding-willow.json is missing or incomplete: ' . $k );
	}
}
$content = (string) @file_get_contents( __DIR__ . '/' . basename( (string) $spec['content_file'] ) );
if ( hash( 'sha256', $content ) !== $spec['content_sha256'] ) {
	WP_CLI::error( 'winding-willow-page.html is not the reviewed content (sha256 differs).' );
}
$slug = (string) $spec['slug'];

// ---- house wording (the three tiers of client-pages/wording_tiers.py): voice + claim words never; the evidence words only from the research heading on;
// verbatim titles in <cite class="ts-source"> are exempt from the claim and evidence words. The SEO fields get the whole list.
$voice    = array( '/\b(we|our|us|ours)\b/i', '/\bstudio\b/i', '/Portland[- ]area/i', '/Portland metro/i' );
$claims   = array( '/\bheal(s|ed|ers?|ing)?\b/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i' );
$evidence = array( '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i',
	'/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i',
	'/\bneurological\b/i', '/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i', '/real results/i' );
$plain = function ( $h ) {
	$h = preg_replace( '#<(script|style)[^>]*>.*?</\1>#s', '', $h );
	return trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( preg_replace( '/<[^>]+>/', ' ', $h ) ), ENT_QUOTES, 'UTF-8' ) ) );
};
$lint = function ( $what, $text, $lists ) {
	foreach ( $lists as $list ) {
		foreach ( $list as $re ) {
			if ( preg_match( $re, $text, $m ) ) {
				WP_CLI::error( 'The ' . $what . ' contains a word this site must not use there: "' . $m[0] . '"; nothing was changed.' );
			}
		}
	}
};
$at = strpos( $content, (string) $spec['research_heading'] );
if ( false === $at || 1 !== substr_count( $content, (string) $spec['research_heading'] ) ) {
	WP_CLI::error( 'The research heading must occur exactly once in the content.' );
}
$cite_re  = '#<cite class="ts-source">.*?</cite>#s';
$before   = substr( $content, 0, $at );
$research = substr( $content, $at );
if ( preg_match( $cite_re, $before ) ) {
	WP_CLI::error( 'A source title appears before the research heading.' );
}
$lint( 'page text before the research section', $plain( $before ), array( $voice, $claims, $evidence ) );
$lint( 'research section', $plain( preg_replace( $cite_re, ' ', $research ) ), array( $voice, $claims ) );
preg_match_all( $cite_re, $research, $cites );
$lint( 'list of source titles', $plain( implode( ' ', $cites[0] ) ), array( $voice ) );
$lint( 'SEO title or description', $spec['seo_title'] . ' ' . $spec['seo_desc'], array( $voice, $claims, $evidence ) );
if ( mb_strlen( $spec['seo_title'] ) > 60 || mb_strlen( $spec['seo_desc'] ) > 160 ) {
	WP_CLI::error( 'The SEO title or description is too long (60 / 160 characters).' );
}

// ---- the site as it is ---------------------------------------------------------------------------------------------------------------------------------
$read = function () use ( $slug, $spec, $content ) {
	global $wpdb;
	$by_slug  = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type NOT IN ('revision','nav_menu_item','attachment')", $slug ) );
	$by_title = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'page' AND post_status <> 'trash'", $spec['title'] ) );
	$ids      = array_values( array_unique( array_map( 'intval', array_merge( $by_slug, $by_title ) ) ) );
	$r        = array( 'ids' => $ids, 'page_id' => 0 );
	if ( 0 === count( $ids ) ) {
		$r['state'] = 'ready';
	} else {
		$p   = 1 === count( $ids ) ? get_post( $ids[0] ) : null;
		$gen = $p ? get_post_meta( $p->ID, 'surerank_settings_general', true ) : null;
		$ok  = $p && 'page' === $p->post_type && 'publish' === $p->post_status && $p->post_name === $slug && $p->post_title === $spec['title'] && (string) $p->post_content === $content
			&& 0 === (int) $p->post_parent && 'disabled' === get_post_meta( $p->ID, 'site-post-title', true )
			&& is_array( $gen ) && ( $gen['page_title'] ?? '' ) === $spec['seo_title'] && ( $gen['page_description'] ?? '' ) === $spec['seo_desc'];
		$r['state']   = $ok ? 'applied' : 'CHANGED';
		$r['page_id'] = $ok ? (int) $p->ID : 0;
	}
	$r['report'] = 'state=' . $r['state'] . ' matching_posts=' . ( $ids ? implode( ',', $ids ) : 'none' );
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = 'host=' . $home . ' ' . $now['report'];

$ids_free = function () use ( $spec ) {
	global $wpdb;
	foreach ( $spec['block_ids'] as $id ) {
		$used = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type NOT IN ('revision') AND post_status <> 'trash' AND post_content LIKE %s", '%' . $wpdb->esc_like( '"block_id":"' . $id . '"' ) . '%' ) );
		if ( $used > 0 ) {
			return $id;
		}
	}
	return '';
};

if ( 'preflight' === $mode ) {
	if ( 'ready' === $state && '' !== ( $clash = $ids_free() ) ) {
		WP_CLI::error( 'Block id ' . $clash . ' is already used by another post (a duplicate id collides in Spectra\'s CSS); stop. ' . $report );
	}
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'A page with this slug or title exists but is not exactly the reviewed page; stop and compare. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report . ' (package sha256 ' . $spec['content_sha256'] . ')' );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	if ( '' !== ( $clash = $ids_free() ) ) {
		WP_CLI::error( 'Block id ' . $clash . ' is already used by another post; nothing was changed.' );
	}
	$file = rtrim( $backup_dir, '/' ) . '/winding-willow-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'slug' => $slug, 'content_sha256' => $spec['content_sha256'], 'page_id' => null );
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$page_id = wp_insert_post( wp_slash( array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $spec['title'], 'post_name' => $slug, 'post_content' => $content,
		'post_author' => get_current_user_id(), 'comment_status' => 'closed', 'ping_status' => 'closed',
	) ), true );
	if ( is_wp_error( $page_id ) || ! $page_id ) {
		WP_CLI::error( 'Could not create the page; nothing was changed.' );
	}
	$snap['page_id'] = (int) $page_id;
	file_put_contents( $file, wp_json_encode( $snap ) );
	update_post_meta( $page_id, 'site-post-title', 'disabled' );
	\SureRank\Inc\API\Post::update_post_meta_common( $page_id, array( 'page_title' => $spec['seo_title'], 'page_description' => $spec['seo_desc'] ) );
	$after = $read();
	if ( 'applied' !== $after['state'] || (int) $after['page_id'] !== (int) $page_id ) {
		wp_delete_post( (int) $page_id, true );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written (' . $after['report'] . '); the new page was deleted again (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Added page #' . $page_id . ' (/' . $slug . '/, ' . strlen( $content ) . ' bytes, sha256 ' . $spec['content_sha256'] . ') with its SureRank title and description. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE -------------------------------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || ( $snap['slug'] ?? '' ) !== $slug ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, or from another site.' );
}
if ( 'applied' !== $state || (int) $now['page_id'] !== (int) $snap['page_id'] ) {
	WP_CLI::error( 'Nothing rolled back: the page this installer wrote is not there exactly as written (it may have been edited in the editor; remove it by hand after a look). ' . $report );
}
wp_delete_post( (int) $snap['page_id'], true );
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['report'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: page #' . (int) $snap['page_id'] . ' and its meta are deleted; no page with this slug or title remains.' );

// ref: ./winding-willow.json, ./winding-willow-page.html — the package (captured by ./capture-winding-willow.sh); ../corporate/install-corporate-massage.php — the pattern; ../client-pages/wording_tiers.py — the word tiers mirrored here
