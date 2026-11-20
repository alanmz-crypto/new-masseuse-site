<?php
/**
 * Winding Willow page: ADD the page /winding-willow/ ("Massage at Winding Willow"; H1 "Massage with Sa at Winding Willow Acupuncture & Wellness"), exactly as it was
 * built and reviewed on staging39 (page #1385, last edited 2026-11-13), on ONE site at a time.
 * Ryan, 2026-11-17: "get the 'winding-willow' page on Thai Solitude to the point where publishing to live is one step ... Follow the pattern of
 * corporate/install-corporate-massage.php + push-corporate-massage.sh: allow-list, dry run by default, --apply with DB export + off-site copy, --rollback,
 * idempotent re-apply."
 *
 * What it writes (WordPress's own storage; no files, no MU-plugin):
 *   1. the page (posts table): slug winding-willow, the exact content bytes of winding-willow-page.html (sha256 in winding-willow.json), published, comments closed;
 *      its meta site-post-title = disabled and SureRank's title + description (through SureRank's own saver). That file = the staging39 capture + Ryan's approved
 *      wording (2026-11-18 and 2026-11-20; build-winding-willow.py), so staging39 itself preflights as CHANGED.
 *      UPGRADE: where the page is exactly the previous version (previous_content_sha256) and the menu item + CSS are absent, its content is replaced and they are added.
 *      UPDATE: where the page is exactly the previous version and the menu item + CSS are already exactly as written (the site carries the previous package), ONLY
 *      the page content is replaced (Ryan 2026-11-20: "just keep names up to date", for Winding Willow's practitioner names); its rollback puts that content back.
 *   2. ONE menu item "Winding Willow" (class ts-ww-nav, meta _ts_ww_nav) in the Primary Menu, right after Corporate Massage (the ts-corp-nav item; after Services
 *      where there is none). The items after it move one position later (menu_order +1); Corporate Massage keeps its reviewed position. Ryan 2026-11-20.
 *   3. the CSS block ts-ww (appended to the Additional CSS): hides the header's "Get in Touch" button (Ryan 2026-11-20: Contact is already in the menu),
 *      which leaves room for the seven items on one line from 1120 px; keeps the item out of the footer menu (seven never fit there).
 * Nothing else is edited. Spectra rebuilds the page's own block CSS by itself on the first view.
 *
 * Allow-list: localhost:8090, staging40 (the fresh copy of live) and live. staging39 (the reference copy) for the READ-ONLY preflight only. Live was added on
 * Ryan's go (docs/inter-model/2026-11-18-winding-willow-enable-live.patch) after the rehearsals on copies of live and staging40 and the staging40 apply; he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = none of the three parts exists; upgrade = the page is the previous version and the menu item + CSS are absent;
 *                update = the page is the previous version and the menu item + CSS are exactly as written;
 *                applied = all three exactly as written; CHANGED = anything else (stop)
 *   apply        snapshot, write the parts, read everything back; put everything back (reverse order) if any read-back differs
 *   rollback     FILE = the before-snapshot apply wrote: take the menu item and the CSS block out, put the menu order back, and delete the page (from ready) or
 *                put its previous content back (from upgrade); only when everything is still exactly as applied
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
foreach ( array( 'slug', 'title', 'content_file', 'content_sha256', 'previous_content_sha256', 'seo_title', 'seo_desc', 'research_heading', 'block_ids', 'nav_label', 'nav_class', 'nav_after_class', 'nav_css' ) as $k ) {
	if ( ! is_array( $spec ) || empty( $spec[ $k ] ) ) {
		WP_CLI::error( 'winding-willow.json is missing or incomplete: ' . $k );
	}
}
$content = (string) @file_get_contents( __DIR__ . '/' . basename( (string) $spec['content_file'] ) );
if ( hash( 'sha256', $content ) !== $spec['content_sha256'] ) {
	WP_CLI::error( 'winding-willow-page.html is not the reviewed content (sha256 differs).' );
}
$slug  = (string) $spec['slug'];
$block = (string) $spec['nav_css'];
if ( 0 !== strpos( $block, '/* ts-ww:start' ) || '/* ts-ww:end */' !== substr( $block, -15 ) ) {
	WP_CLI::error( 'The ts-ww CSS block in winding-willow.json is malformed.' );
}

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
$css_post = wp_get_custom_css_post( 'astra' );
if ( ! $css_post || strlen( (string) $css_post->post_content ) < 1000 || false === strpos( (string) $css_post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$find_menu = function () {
	$locs = get_nav_menu_locations();
	return isset( $locs['primary'] ) ? (int) $locs['primary'] : 0;
};
$read = function () use ( $slug, $spec, $content, $block, $find_menu ) {
	global $wpdb;
	$r = array();
	// 1. the page
	$by_slug  = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type NOT IN ('revision','nav_menu_item','attachment')", $slug ) );
	$by_title = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'page' AND post_status <> 'trash'", $spec['title'] ) );
	$ids      = array_values( array_unique( array_map( 'intval', array_merge( $by_slug, $by_title ) ) ) );
	$r['ids'] = $ids;
	$r['page_id'] = 0;
	$r['page_content'] = '';
	if ( 0 === count( $ids ) ) {
		$r['page'] = 'absent';
	} else {
		$p    = 1 === count( $ids ) ? get_post( $ids[0] ) : null;
		$gen  = $p ? get_post_meta( $p->ID, 'surerank_settings_general', true ) : null;
		$rest = $p && 'page' === $p->post_type && 'publish' === $p->post_status && $p->post_name === $slug && $p->post_title === $spec['title']
			&& 0 === (int) $p->post_parent && 'disabled' === get_post_meta( $p->ID, 'site-post-title', true )
			&& is_array( $gen ) && ( $gen['page_title'] ?? '' ) === $spec['seo_title'] && ( $gen['page_description'] ?? '' ) === $spec['seo_desc'];
		$cur  = $p ? (string) $p->post_content : '';
		if ( $rest && $cur === $content ) {
			$r['page'] = 'exact';
		} elseif ( $rest && hash( 'sha256', $cur ) === $spec['previous_content_sha256'] ) {
			$r['page'] = 'previous';
		} else {
			$r['page'] = 'CHANGED';
		}
		if ( 'CHANGED' !== $r['page'] ) {
			$r['page_id']      = (int) $p->ID;
			$r['page_content'] = $cur;
		}
	}
	// 2. the menu item
	$menu_id = $find_menu();
	$items   = $menu_id ? wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) : array();
	$items   = is_array( $items ) ? $items : array();
	$svc     = get_page_by_path( 'services' );
	$marked  = array();
	$by_name = 0;
	$svc_item = null;
	$after_items = array();
	foreach ( $items as $it ) {
		if ( '1' === (string) get_post_meta( $it->ID, '_ts_ww_nav', true ) ) {
			$marked[] = $it;
		}
		if ( strtolower( trim( wp_strip_all_tags( $it->title ) ) ) === strtolower( $spec['nav_label'] ) ) {
			$by_name++;
		}
		if ( $svc && 'page' === $it->object && (int) $it->object_id === (int) $svc->ID && 0 === (int) $it->menu_item_parent ) {
			$svc_item = $it;
		}
		if ( in_array( $spec['nav_after_class'], (array) $it->classes, true ) && 0 === (int) $it->menu_item_parent ) {
			$after_items[] = $it;
		}
	}
	$anchor = 1 === count( $after_items ) ? $after_items[0] : ( 0 === count( $after_items ) ? $svc_item : null );
	$r['menu_id'] = $menu_id;
	$r['items']   = $items;
	$r['anchor']  = $anchor;
	$r['marked']  = $marked;
	if ( ! $menu_id || ! $anchor ) {
		$r['menu'] = 'CHANGED';
	} elseif ( 0 === count( $marked ) && 0 === $by_name ) {
		$r['menu'] = 'absent';
	} elseif ( 1 === count( $marked ) && 1 === $by_name && $r['page_id'] && 'page' === $marked[0]->object && (int) $marked[0]->object_id === $r['page_id']
		&& 'publish' === $marked[0]->post_status && 0 === (int) $marked[0]->menu_item_parent && (int) $marked[0]->menu_order === (int) $anchor->menu_order + 1
		&& $marked[0]->title === $spec['nav_label'] && in_array( $spec['nav_class'], (array) $marked[0]->classes, true ) ) {
		$r['menu'] = 'exact';
	} else {
		$r['menu'] = 'CHANGED';
	}
	// 3. the CSS block
	$cp = wp_get_custom_css_post( 'astra' );
	$cs = $cp ? (string) $cp->post_content : '';
	$r['css'] = $cs;
	$n = substr_count( $cs, 'ts-ww:start' );
	if ( 0 === $n ) {
		$r['css_state'] = 'absent';
	} elseif ( 1 === $n && 1 === substr_count( $cs, $block ) && 1 === substr_count( $cs, 'ts-ww:end' ) ) {
		$r['css_state'] = 'exact';
	} else {
		$r['css_state'] = 'CHANGED';
	}
	if ( 'absent' === $r['page'] && 'absent' === $r['menu'] && 'absent' === $r['css_state'] ) {
		$r['state'] = 'ready';
	} elseif ( 'previous' === $r['page'] && 'absent' === $r['menu'] && 'absent' === $r['css_state'] ) {
		$r['state'] = 'upgrade';
	} elseif ( 'previous' === $r['page'] && 'exact' === $r['menu'] && 'exact' === $r['css_state'] ) {
		$r['state'] = 'update';
	} elseif ( 'exact' === $r['page'] && 'exact' === $r['menu'] && 'exact' === $r['css_state'] ) {
		$r['state'] = 'applied';
	} else {
		$r['state'] = 'CHANGED';
	}
	$r['report'] = sprintf( 'state=%s page=%s menu_item=%s css_block=%s matching_posts=%s', $r['state'], $r['page'], $r['menu'], $r['css_state'], $ids ? implode( ',', $ids ) : 'none' );
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = 'host=' . $home . ' ' . $now['report'];

$ids_free = function ( $own = 0 ) use ( $spec ) {   // $own = the page itself (upgrade), whose blocks carry these ids already
	global $wpdb;
	foreach ( $spec['block_ids'] as $id ) {
		$used = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type NOT IN ('revision') AND post_status <> 'trash' AND ID <> %d AND post_content LIKE %s", (int) $own, '%' . $wpdb->esc_like( '"block_id":"' . $id . '"' ) . '%' ) );
		if ( $used > 0 ) {
			return $id;
		}
	}
	return '';
};

if ( 'preflight' === $mode ) {
	if ( in_array( $state, array( 'ready', 'upgrade', 'update' ), true ) && '' !== ( $clash = $ids_free( $now['page_id'] ) ) ) {
		WP_CLI::error( 'Block id ' . $clash . ' is already used by another post (a duplicate id collides in Spectra\'s CSS); stop. ' . $report );
	}
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page, the menu item or the ts-ww CSS block is not in a reviewed state (or the menu has no Services / Corporate Massage item to place it after); stop and compare. ' . $report );
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
	if ( '' !== ( $clash = $ids_free( $now['page_id'] ) ) ) {
		WP_CLI::error( 'Block id ' . $clash . ' is already used by another post; nothing was changed.' );
	}
	if ( 'update' === $state ) {   // content only: the menu item and the CSS block stay exactly as they are
		$page_id = (int) $now['page_id'];
		$file    = rtrim( $backup_dir, '/' ) . '/winding-willow-before-' . gmdate( 'Ymd-His' ) . '.json';
		$snap    = array(
			'created' => gmdate( 'c' ), 'home' => $home, 'slug' => $slug, 'content_sha256' => $spec['content_sha256'], 'from' => 'update',
			'page_id' => $page_id, 'old_content' => (string) $now['page_content'], 'menu_item_id' => (int) $now['marked'][0]->ID,
		);
		if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || filesize( $file ) < 1000 ) {
			WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
		}
		chmod( $file, 0600 );
		$res = wp_update_post( wp_slash( array( 'ID' => $page_id, 'post_content' => $content ) ), true );
		$after = $read();
		if ( is_wp_error( $res ) || ! $res || 'applied' !== $after['state'] || (int) $after['page_id'] !== $page_id ) {
			wp_update_post( wp_slash( array( 'ID' => $page_id, 'post_content' => (string) $snap['old_content'] ) ) );
			$check = $read();
			WP_CLI::error( 'The page content did not save exactly as written; the previous content was put back (state now: ' . $check['report'] . ').' );
		}
		WP_CLI::success( 'Updated the content of page #' . $page_id . ' (/' . $slug . '/, ' . strlen( $content ) . ' bytes, sha256 ' . $spec['content_sha256'] . '); menu item #'
			. (int) $snap['menu_item_id'] . ' and the ts-ww CSS block unchanged. Before-snapshot: ' . $file );
		return;
	}
	$from       = $state;   // ready | upgrade
	$old_css    = (string) $now['css'];
	$menu_id    = (int) $now['menu_id'];
	$pos        = (int) $now['anchor']->menu_order;
	$old_orders = array();
	foreach ( $now['items'] as $it ) {
		$old_orders[ (string) $it->ID ] = (int) $it->menu_order;
	}
	$file = rtrim( $backup_dir, '/' ) . '/winding-willow-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array(
		'created' => gmdate( 'c' ), 'home' => $home, 'slug' => $slug, 'content_sha256' => $spec['content_sha256'], 'from' => $from,
		'page_id' => 'upgrade' === $from ? (int) $now['page_id'] : null, 'old_content' => 'upgrade' === $from ? (string) $now['page_content'] : null,
		'css_sha256' => hash( 'sha256', $old_css ), 'css' => $old_css, 'menu_id' => $menu_id, 'menu_orders' => $old_orders, 'menu_item_id' => null,
	);
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$save_snap = function () use ( &$snap, $file ) {
		file_put_contents( $file, wp_json_encode( $snap ) );
	};
	$set_content = function ( $post_id, $c ) {
		return wp_update_post( wp_slash( array( 'ID' => (int) $post_id, 'post_content' => $c ) ), true );
	};
	// Undo, newest first; each step is safe when its part was never written.
	$undo = function () use ( &$snap, $old_css, $old_orders, $from, $set_content ) {
		if ( ! empty( $snap['menu_item_id'] ) ) {
			wp_delete_post( (int) $snap['menu_item_id'], true );
		}
		foreach ( $old_orders as $item_id => $order ) {
			if ( (int) get_post_field( 'menu_order', (int) $item_id ) !== (int) $order ) {
				wp_update_post( array( 'ID' => (int) $item_id, 'menu_order' => (int) $order ) );
			}
		}
		$cp = wp_get_custom_css_post( 'astra' );
		if ( ! $cp || (string) $cp->post_content !== $old_css ) {
			wp_update_custom_css_post( $old_css, array( 'stylesheet' => 'astra' ) );
		}
		if ( 'upgrade' === $from && ! empty( $snap['page_id'] ) ) {
			if ( (string) get_post_field( 'post_content', (int) $snap['page_id'] ) !== (string) $snap['old_content'] ) {
				$set_content( (int) $snap['page_id'], (string) $snap['old_content'] );
			}
		} elseif ( ! empty( $snap['page_id'] ) ) {
			wp_delete_post( (int) $snap['page_id'], true );
		}
	};
	$fail = function ( $why ) use ( $undo, $read ) {
		$undo();
		$check = $read();
		WP_CLI::error( $why . ' Everything written was put back (state now: ' . $check['report'] . ').' );
	};

	// 1. the page
	if ( 'upgrade' === $from ) {
		$page_id = (int) $now['page_id'];
		$res     = $set_content( $page_id, $content );
		if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', $page_id ) !== $content ) {
			$fail( 'The page content did not save exactly as written.' );
		}
	} else {
		$page_id = wp_insert_post( wp_slash( array(
			'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $spec['title'], 'post_name' => $slug, 'post_content' => $content,
			'post_author' => get_current_user_id(), 'comment_status' => 'closed', 'ping_status' => 'closed',
		) ), true );
		if ( is_wp_error( $page_id ) || ! $page_id ) {
			$fail( 'Could not create the page.' );
		}
		$snap['page_id'] = (int) $page_id;
		$save_snap();
		update_post_meta( $page_id, 'site-post-title', 'disabled' );
		\SureRank\Inc\API\Post::update_post_meta_common( $page_id, array( 'page_title' => $spec['seo_title'], 'page_description' => $spec['seo_desc'] ) );
	}
	// 2. the CSS block
	$new_css = rtrim( $old_css ) . "\n\n" . $block . "\n";
	$res     = wp_update_custom_css_post( $new_css, array( 'stylesheet' => 'astra' ) );
	$cp      = wp_get_custom_css_post( 'astra' );
	if ( is_wp_error( $res ) || ! $cp || (string) $cp->post_content !== $new_css ) {
		$fail( 'The Additional CSS did not save exactly as written.' );
	}
	// 3. the menu item (move the later items one place along, then add this one after the anchor)
	foreach ( $now['items'] as $it ) {
		if ( (int) $it->menu_order > $pos ) {
			wp_update_post( array( 'ID' => (int) $it->ID, 'menu_order' => (int) $it->menu_order + 1 ) );
		}
	}
	$item_id = wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title' => $spec['nav_label'], 'menu-item-object' => 'page', 'menu-item-object-id' => (int) $page_id, 'menu-item-type' => 'post_type',
		'menu-item-status' => 'publish', 'menu-item-position' => $pos + 1, 'menu-item-parent-id' => 0, 'menu-item-classes' => $spec['nav_class'],
	) );
	if ( is_wp_error( $item_id ) || ! $item_id ) {
		$fail( 'Could not add the menu item.' );
	}
	$snap['menu_item_id'] = (int) $item_id;
	$save_snap();
	update_post_meta( (int) $item_id, '_ts_ww_nav', '1' );
	wp_update_post( array( 'ID' => (int) $item_id, 'menu_order' => $pos + 1 ) );

	$after = $read();
	if ( 'applied' !== $after['state'] || (int) $after['page_id'] !== (int) $page_id ) {
		$fail( 'Read-back differed from what was written (' . $after['report'] . ').' );
	}
	WP_CLI::success( ( 'upgrade' === $from ? 'Updated page #' : 'Added page #' ) . $page_id . ' (/' . $slug . '/, ' . strlen( $content ) . ' bytes, sha256 ' . $spec['content_sha256'] . ')'
		. ( 'upgrade' === $from ? '' : ' with its SureRank title and description' ) . ', menu item #' . $item_id . ' "' . $spec['nav_label'] . '" and the ts-ww CSS block. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE -------------------------------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( is_array( $snap ) && 'update' === ( $snap['from'] ?? '' ) ) {   // a content-only update: put the previous content back, nothing else
	if ( ( $snap['home'] ?? '' ) !== $home || ( $snap['slug'] ?? '' ) !== $slug || empty( $snap['page_id'] )
		|| hash( 'sha256', (string) ( $snap['old_content'] ?? '' ) ) !== $spec['previous_content_sha256'] ) {
		WP_CLI::error( 'Snapshot is damaged, from another site, or from another version of this package.' );
	}
	if ( 'applied' !== $state || (int) $now['page_id'] !== (int) $snap['page_id'] ) {
		WP_CLI::error( 'Nothing rolled back: the page is not exactly as the update wrote it (it may have been edited; look first). ' . $report );
	}
	wp_update_post( wp_slash( array( 'ID' => (int) $snap['page_id'], 'post_content' => (string) $snap['old_content'] ) ) );
	$check = $read();
	if ( 'update' !== $check['state'] ) {
		WP_CLI::error( 'Rollback did not return to the state before the update (now: ' . $check['report'] . '); stop and inspect.' );
	}
	WP_CLI::success( 'Rollback done: page #' . (int) $snap['page_id'] . ' has its previous content back; the menu item and the ts-ww CSS block were not touched.' );
	return;
}
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || ( $snap['slug'] ?? '' ) !== $slug || empty( $snap['menu_item_id'] )
	|| ! in_array( $snap['from'] ?? '', array( 'ready', 'upgrade' ), true ) || empty( $snap['css'] ) || hash( 'sha256', (string) $snap['css'] ) !== ( $snap['css_sha256'] ?? '' )
	|| ( 'upgrade' === $snap['from'] && hash( 'sha256', (string) ( $snap['old_content'] ?? '' ) ) !== $spec['previous_content_sha256'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, from another site, or from the older page-only installer (use that version to roll it back).' );
}
if ( 'applied' !== $state || (int) $now['page_id'] !== (int) $snap['page_id'] || (int) $now['marked'][0]->ID !== (int) $snap['menu_item_id'] ) {
	WP_CLI::error( 'Nothing rolled back: what this installer wrote is not there exactly as written (it may have been edited; look before removing anything by hand). ' . $report );
}
// CSS: exactly the old CSS, or only the block out if the CSS was edited since.
$old_css      = (string) $snap['css'];
$restored_css = ( (string) $now['css'] === rtrim( $old_css ) . "\n\n" . $block . "\n" ) ? $old_css : str_replace( "\n\n" . $block . "\n", '', (string) $now['css'] );
wp_delete_post( (int) $snap['menu_item_id'], true );
foreach ( (array) $snap['menu_orders'] as $item_id => $order ) {
	if ( get_post( (int) $item_id ) && (int) get_post_field( 'menu_order', (int) $item_id ) !== (int) $order ) {
		wp_update_post( array( 'ID' => (int) $item_id, 'menu_order' => (int) $order ) );
	}
}
wp_update_custom_css_post( $restored_css, array( 'stylesheet' => 'astra' ) );
if ( 'upgrade' === $snap['from'] ) {
	wp_update_post( wp_slash( array( 'ID' => (int) $snap['page_id'], 'post_content' => (string) $snap['old_content'] ) ) );
} else {
	wp_delete_post( (int) $snap['page_id'], true );
}
$check = $read();
if ( $snap['from'] !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the state before the apply (' . $snap['from'] . '; now: ' . $check['report'] . '); stop and inspect.' );
}
$same_css = hash( 'sha256', (string) $check['css'] ) === $snap['css_sha256'];
WP_CLI::success( 'Rollback done: the menu item is deleted and the menu order is back, the ts-ww CSS block is out (Additional CSS ' . ( $same_css ? 'byte-identical to the snapshot' : 'differs: edited after the apply, and those edits were kept' ) . '), and '
	. ( 'upgrade' === $snap['from'] ? 'page #' . (int) $snap['page_id'] . ' has its previous content back' : 'page #' . (int) $snap['page_id'] . ' and its meta are deleted' ) . '.' );

// ref: ./winding-willow.json, ./winding-willow-page.html — the package (captured by ./capture-winding-willow.sh); ../corporate/install-corporate-massage.php — the pattern (menu item, CSS block, undo); ../client-pages/wording_tiers.py — the word tiers mirrored here
