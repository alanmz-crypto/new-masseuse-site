<?php
/**
 * Corporate & Group Massage: ADD a new page, a Services row, a menu item, an announcement strip and its CSS, for ONE site at a time.
 * Ryan, 2026-10-24: the three briefs in docs/inter-model/ (00_README..., 01_..._Full_Build_Brief.md, 02_..._Local_Agent_Handoff.md), then "Use staging37. Use staging first." and
 * "Please, please, please don't change anything already established on the website ... all you need to do is add." / "you can add, don't subtract without asking."
 *
 * EVERYTHING HERE IS ADDED. No existing page text, block, menu item, setting or CSS rule is edited, reworded or removed. What it writes (WordPress's own storage, so a SiteGround push carries it;
 * no files, no MU-plugin):
 *   1. a NEW page /corporate-massage-portland/ (posts table), with the theme's own title switched off like the other pages (site-post-title = disabled) and SureRank's title and description for it;
 *   2. ONE new row inserted into the Services page content immediately before the "Your Session" row (exact anchor: that row's block id). The text of every existing block is untouched. Its picture
 *      (images/corporate-group-massage.webp, supplied by Ryan 2026-10-24; sha256 checked) is imported into the media library first, as a new item, and shown whole (not cropped);
 *   3. ONE new menu item "Corporate Massage" in the Primary Menu, after Services. Placing it there moves the two items after it (For Practices, Contact) one position later (their menu_order +1);
 *      the order the visitor sees for the existing items does not change, and rollback puts the numbers back exactly;
 *   4. a WPCode PHP snippet "CorporateBand": a slim announcement strip under the header on the client-facing pages listed in the snippet (NOT Home, NOT Work With Me, NOT the new page, and not the
 *      unrelated pages on this install);
 *   5. ONE marked block "ts-corp" appended to Additional CSS (the strip, the page, the Services picture).
 * Source of every string: build-corporate-massage.py -> corporate-massage-content.php (generated; never edit by hand); the CSS block: corporate-massage.css.
 *
 * Allow-list: localhost:8090 and staging37. Live is NOT allowed: it is added only on Ryan's explicit go, after staging37 is reviewed, and he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = none of the six parts is there; applied = all six are there exactly as written; CHANGED = anything else (stop)
 *   apply        snapshot, create the page, SureRank fields, CSS block, snippet, import the picture, Services row, menu item; read everything back; put everything back (reverse order) if any read-back differs
 *   rollback     FILE = the before-snapshot: take all six parts out again (the page, snippet and media item this installer created are deleted; the Services content, the CSS and the menu order are restored)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the Services page.)
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging37.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging37 only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) || ! current_user_can( 'edit_theme_options' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved code.' );
}
if ( ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) {
	WP_CLI::error( 'WPCode (insert-headers-and-footers) is not loaded.' );
}
if ( ! class_exists( '\SureRank\Inc\API\Post' ) || ! method_exists( '\SureRank\Inc\API\Post', 'update_post_meta_common' ) ) {
	WP_CLI::error( 'SureRank page-level SEO API is not available.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

// ---- the reviewed content ------------------------------------------------------------------------------------------------------------------------
$data = is_file( __DIR__ . '/corporate-massage-content.php' ) ? require __DIR__ . '/corporate-massage-content.php' : null;
foreach ( array( 'slug', 'title', 'nav_label', 'nav_class', 'seo_title', 'seo_desc', 'ids', 'page_content', 'row', 'row_anchor', 'row_id', 'band_code', 'faq_text', 'band_text' ) as $k ) {
	if ( ! is_array( $data ) || empty( $data[ $k ] ) ) {
		WP_CLI::error( 'corporate-massage-content.php is missing or incomplete (run build-corporate-massage.py): ' . $k );
	}
}
$slug      = (string) $data['slug'];
$row_tpl   = (string) $data['row'];   // a template: {{IMG_ID}} and {{IMG_URL}} are filled in from the media item
$anchor    = (string) $data['row_anchor'];
$band_code = (string) $data['band_code'];
$start     = '/* ts-corp:start';
$end       = '/* ts-corp:end */';
$block     = rtrim( (string) file_get_contents( __DIR__ . '/corporate-massage.css' ) );
if ( 0 !== strpos( $block, $start ) || substr( $block, -strlen( $end ) ) !== $end || 1 !== substr_count( $block, 'ts-corp:start' ) || 1 !== substr_count( $block, 'ts-corp:end' ) || strlen( $block ) > 12000 ) {
	WP_CLI::error( 'corporate-massage.css is not the reviewed single marked block.' );
}
foreach ( array( '@import', 'url(', 'expression', '!important', 'javascript:', '<' ) as $bad ) {
	if ( false !== stripos( $block, $bad ) ) {
		WP_CLI::error( 'corporate-massage.css contains something the review did not cover: ' . $bad );
	}
}
if ( 0 !== substr_count( $row_tpl, $anchor ) || 1 !== substr_count( $row_tpl, '"block_id":"' . $data['row_id'] . '"' ) || 2 !== substr_count( $row_tpl, '{{IMG_ID}}' ) || 1 !== substr_count( $row_tpl, '{{IMG_URL}}' ) ) {
	WP_CLI::error( 'Internal error: the Services row is not what was reviewed.' );
}
$img      = isset( $data['image'] ) && is_array( $data['image'] ) ? $data['image'] : array();
$img_path = __DIR__ . '/images/' . ( isset( $img['name'] ) ? basename( (string) $img['name'] ) : '' );
if ( empty( $img['name'] ) || empty( $img['sha256'] ) || empty( $img['alt'] ) || empty( $img['title'] ) || ! is_file( $img_path ) || hash_file( 'sha256', $img_path ) !== $img['sha256'] ) {
	WP_CLI::error( 'The picture is missing or is not the reviewed file (images/' . ( $img['name'] ?? '?' ) . ').' );
}
/** The Services row for a given media item (the URL is made relative, so a staging-to-live push stays correct). */
$row_for = function ( $att_id ) use ( $row_tpl ) {
	$url = wp_make_link_relative( (string) wp_get_attachment_url( (int) $att_id ) );
	return str_replace( array( '{{IMG_ID}}', '{{IMG_URL}}' ), array( (string) (int) $att_id, $url ), $row_tpl );
};

// ---- wording checks on everything that becomes visible (the page has no research section, so the whole house list applies) ---------------------------------------
$visible = array(
	'page' => $data['page_content'], 'row' => $data['row'], 'picture alt text' => $img['alt'] . ' ' . $img['title'], 'faq' => $data['faq_text'], 'band' => $data['band_text'], 'seo' => $data['seo_title'] . ' ' . $data['seo_desc'], 'nav' => $data['nav_label'],
);
foreach ( $visible as $what => $raw ) {
	$text = html_entity_decode( wp_strip_all_tags( $raw ), ENT_QUOTES, 'UTF-8' );
	foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio', 'cure', 'treat', 'diagnos', 'inflammation', 'detox', 'guarantee', 'relieve', 'relief', 'portland-area', 'portland metro' ) as $bad ) {
		if ( false !== stripos( $text, $bad ) ) {
			WP_CLI::error( 'The ' . $what . ' text contains a term this site must not use: ' . $bad );
		}
	}
	if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $text ) || preg_match( '/\b(we|our|us|ours)\b/i', $text ) ) {
		WP_CLI::error( 'The ' . $what . ' text contains heal/healing or we/our/us, which this site must not use.' );
	}
}
if ( mb_strlen( $data['seo_title'] ) > 60 || mb_strlen( $data['seo_desc'] ) > 160 ) {
	WP_CLI::error( 'The SEO title or description is too long (60 / 160 characters).' );
}

// ---- the site as it is ------------------------------------------------------------------------------------------------------------------------------
$css_post = wp_get_custom_css_post( 'astra' );
if ( ! $css_post || strlen( (string) $css_post->post_content ) < 1000 || false === strpos( (string) $css_post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$services = get_page_by_path( 'services' );
if ( ! $services || 'publish' !== $services->post_status || 1 !== substr_count( (string) $services->post_content, $anchor ) ) {
	WP_CLI::error( 'The Services page was not found as a published page holding the "Your Session" row; nothing was changed.' );
}

$title_snippet = 'CorporateBand';

$find_menu = function () {
	$locs = get_nav_menu_locations();
	$id   = isset( $locs['primary'] ) ? (int) $locs['primary'] : 0;
	return $id ? $id : 0;
};

/** Current state of each of the five parts. */
$read = function () use ( $slug, $data, $row_tpl, $row_for, $img, $anchor, $block, $band_code, $title_snippet, $find_menu ) {
	$r = array();

	// 1. the page (and its SureRank fields)
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$r['pages'] = $pages;
	if ( 0 === count( $pages ) ) {
		$r['page'] = 'absent';
	} else {
		$pg  = $pages[0];
		$gen = 1 === count( $pages ) ? get_post_meta( $pg->ID, 'surerank_settings_general', true ) : null;
		$ok  = 1 === count( $pages ) && 'page' === $pg->post_type && 'publish' === $pg->post_status && trim( (string) $pg->post_content ) === trim( $data['page_content'] ) && $pg->post_title === $data['title']
			&& 'disabled' === get_post_meta( $pg->ID, 'site-post-title', true )
			&& is_array( $gen ) && ( $gen['page_title'] ?? '' ) === $data['seo_title'] && ( $gen['page_description'] ?? '' ) === $data['seo_desc'];
		$r['page'] = $ok ? 'exact' : 'CHANGED';
	}
	$page_id = ( 1 === count( $pages ) && 'exact' === $r['page'] ) ? (int) $pages[0]->ID : 0;

	// 2a. the picture in the media library
	$atts = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'meta_key' => '_ts_corp_src_sha256', 'meta_value' => $img['sha256'], 'suppress_filters' => true ) );
	$r['atts'] = $atts;
	$att_id    = 0;
	if ( 0 === count( $atts ) ) {
		$r['image'] = 'absent';
	} elseif ( 1 === count( $atts ) && is_file( (string) get_attached_file( $atts[0]->ID ) ) && hash_file( 'sha256', (string) get_attached_file( $atts[0]->ID ) ) === $img['sha256']
		&& get_post_meta( $atts[0]->ID, '_wp_attachment_image_alt', true ) === $img['alt'] ) {
		$r['image'] = 'exact';
		$att_id     = (int) $atts[0]->ID;
	} else {
		$r['image'] = 'CHANGED';
	}

	// 2b. the Services row
	$svc = get_page_by_path( 'services' );
	$c   = $svc ? (string) $svc->post_content : '';
	$r['services_content'] = $c;
	$row = $att_id ? $row_for( $att_id ) : null;
	$r['row_html'] = $row;
	$n_anchor = substr_count( $c, $anchor );
	$n_row    = $row ? substr_count( $c, $row . $anchor ) : 0;
	$n_id     = substr_count( $c, '"block_id":"' . $data['row_id'] . '"' );
	if ( 1 === $n_anchor && 0 === $n_id && 0 === substr_count( $c, 'Corporate &amp; Group Massage' ) ) {
		$r['row'] = 'absent';
	} elseif ( 1 === $n_anchor && 1 === $n_row && 1 === $n_id ) {
		$r['row'] = 'exact';
	} else {
		$r['row'] = 'CHANGED';
	}

	// 3. the menu item
	$menu_id = $find_menu();
	$r['menu_id'] = $menu_id;
	$items   = $menu_id ? wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) : array();
	$items   = is_array( $items ) ? $items : array();
	$marked  = array();
	$by_title = 0;
	$svc_item = null;
	foreach ( $items as $it ) {
		if ( '1' === (string) get_post_meta( $it->ID, '_ts_corp_nav', true ) ) {
			$marked[] = $it;
		}
		if ( strtolower( trim( wp_strip_all_tags( $it->title ) ) ) === strtolower( $data['nav_label'] ) ) {
			$by_title++;
		}
		if ( $svc && 'page' === $it->object && (int) $it->object_id === (int) $svc->ID && 0 === (int) $it->menu_item_parent ) {
			$svc_item = $it;
		}
	}
	$r['items'] = $items;
	$r['svc_item'] = $svc_item;
	$r['marked'] = $marked;
	if ( ! $menu_id || ! $svc_item ) {
		$r['menu'] = 'CHANGED';
	} elseif ( 0 === count( $marked ) && 0 === $by_title ) {
		$r['menu'] = 'absent';
	} elseif ( 1 === count( $marked ) && 1 === $by_title && $page_id && 'page' === $marked[0]->object && (int) $marked[0]->object_id === $page_id && 'publish' === $marked[0]->post_status
		&& 0 === (int) $marked[0]->menu_item_parent && (int) $marked[0]->menu_order === (int) $svc_item->menu_order + 1 && $marked[0]->title === $data['nav_label'] && in_array( $data['nav_class'], (array) $marked[0]->classes, true ) ) {
		$r['menu'] = 'exact';
	} else {
		$r['menu'] = 'CHANGED';
	}

	// 4. the snippet
	$found = get_posts( array( 'post_type' => 'wpcode', 'post_status' => 'any', 'title' => $title_snippet, 'numberposts' => -1, 'suppress_filters' => true ) );
	$r['found'] = $found;
	if ( 0 === count( $found ) ) {
		$r['snippet'] = 'absent';
	} elseif ( 1 === count( $found ) && 'publish' === $found[0]->post_status && trim( (string) $found[0]->post_content ) === trim( $band_code ) ) {
		$r['snippet'] = 'exact';
	} else {
		$r['snippet'] = 'CHANGED';
	}

	// 5. the CSS block
	$p  = wp_get_custom_css_post( 'astra' );
	$cs = $p ? (string) $p->post_content : '';
	$r['css'] = $cs;
	$n = substr_count( $cs, 'ts-corp:start' );
	if ( 0 === $n ) {
		$r['css_state'] = 'absent';
	} elseif ( 1 === $n && 1 === substr_count( $cs, $block ) && 1 === substr_count( $cs, 'ts-corp:end' ) ) {
		$r['css_state'] = 'exact';
	} else {
		$r['css_state'] = 'CHANGED';
	}

	$parts = array( $r['page'], $r['image'], $r['row'], $r['menu'], $r['snippet'], $r['css_state'] );
	if ( 6 === count( array_filter( $parts, function ( $s ) { return 'absent' === $s; } ) ) ) {
		$r['state'] = 'ready';
	} elseif ( 6 === count( array_filter( $parts, function ( $s ) { return 'exact' === $s; } ) ) ) {
		$r['state'] = 'applied';
	} else {
		$r['state'] = 'CHANGED';
	}
	$r['report'] = sprintf( 'page=%s picture=%s services_row=%s menu_item=%s snippet=%s css_block=%s', $r['page'], $r['image'], $r['row'], $r['menu'], $r['snippet'], $r['css_state'] );
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s %s', $home, $state, $now['report'] );

if ( 'preflight' === $mode ) {
	// In the ready state the reviewed block ids must be unused everywhere (a duplicate id collides in Spectra's CSS).
	if ( 'ready' === $state ) {
		global $wpdb;
		foreach ( $data['ids'] as $id ) {
			$used = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type <> 'revision' AND post_content LIKE %s", '%' . $wpdb->esc_like( '"block_id":"' . $id . '"' ) . '%' ) );
			if ( $used > 0 ) {
				WP_CLI::error( 'Block id ' . $id . ' is already used by another post; stop and pick new ids. ' . $report );
			}
		}
	}
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The six parts are not all absent or all exactly as written (or the Services page, the menu or a slug clash is not as reviewed); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$refresh = function () {
	if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
};
$set_content = function ( $post_id, $content ) {
	return wp_update_post( wp_slash( array( 'ID' => (int) $post_id, 'post_content' => $content ) ), true );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	global $wpdb;
	foreach ( $data['ids'] as $id ) {
		$used = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type <> 'revision' AND post_content LIKE %s", '%' . $wpdb->esc_like( '"block_id":"' . $id . '"' ) . '%' ) );
		if ( $used > 0 ) {
			WP_CLI::error( 'Block id ' . $id . ' is already used by another post; nothing was changed.' );
		}
	}

	$old_css      = (string) $now['css'];
	$old_services = (string) $now['services_content'];
	$svc_id       = (int) $services->ID;
	$menu_id      = (int) $now['menu_id'];
	$svc_order    = (int) $now['svc_item']->menu_order;
	$old_orders   = array();
	foreach ( $now['items'] as $it ) {
		$old_orders[ (string) $it->ID ] = (int) $it->menu_order;
	}
	$file = rtrim( $backup_dir, '/' ) . '/corporate-massage-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array(
		'created' => gmdate( 'c' ), 'home' => $home, 'services_id' => $svc_id, 'services_sha256' => hash( 'sha256', $old_services ), 'services_content' => $old_services,
		'css_sha256' => hash( 'sha256', $old_css ), 'css' => $old_css, 'menu_id' => $menu_id, 'menu_orders' => $old_orders, 'services_menu_order' => $svc_order,
		'page_id' => null, 'snippet_id' => null, 'menu_item_id' => null, 'attachment_id' => null,
	);
	$ok = false !== file_put_contents( $file, wp_json_encode( $snap ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$save_snap = function () use ( &$snap, $file ) {
		file_put_contents( $file, wp_json_encode( $snap ) );
	};

	// Undo, newest first. Each step is safe to run when its part was never created.
	$undo = function () use ( &$snap, $old_css, $old_services, $svc_id, $menu_id, $old_orders, $refresh, $set_content ) {
		if ( ! empty( $snap['menu_item_id'] ) ) {
			wp_delete_post( (int) $snap['menu_item_id'], true );
		}
		foreach ( $old_orders as $item_id => $order ) {
			if ( (int) get_post_field( 'menu_order', (int) $item_id ) !== (int) $order ) {
				wp_update_post( array( 'ID' => (int) $item_id, 'menu_order' => (int) $order ) );
			}
		}
		if ( (string) get_post_field( 'post_content', $svc_id ) !== $old_services ) {
			$set_content( $svc_id, $old_services );
		}
		if ( ! empty( $snap['attachment_id'] ) ) {
			wp_delete_attachment( (int) $snap['attachment_id'], true );   // after the Services content is back: nothing refers to it any more
		}
		if ( ! empty( $snap['snippet_id'] ) ) {
			wp_delete_post( (int) $snap['snippet_id'], true );
		}
		wp_update_custom_css_post( $old_css, array( 'stylesheet' => 'astra' ) );
		if ( ! empty( $snap['page_id'] ) ) {
			wp_delete_post( (int) $snap['page_id'], true );
		}
		$refresh();
	};
	$fail = function ( $why ) use ( $undo, $read ) {
		$undo();
		$check = $read();
		WP_CLI::error( $why . ' Everything was put back (state now: ' . $check['state'] . ').' );
	};

	// 1. the page
	$page_id = wp_insert_post( wp_slash( array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $data['title'], 'post_name' => $slug, 'post_content' => $data['page_content'], 'post_author' => get_current_user_id(),
		'comment_status' => 'closed', 'ping_status' => 'closed',
	) ), true );
	if ( is_wp_error( $page_id ) || ! $page_id ) {
		$fail( 'Could not create the page.' );
	}
	$snap['page_id'] = (int) $page_id;
	$save_snap();
	update_post_meta( $page_id, 'site-post-title', 'disabled' );
	\SureRank\Inc\API\Post::update_post_meta_common( $page_id, array( 'page_title' => $data['seo_title'], 'page_description' => $data['seo_desc'] ) );

	// 2. the CSS block
	$new_css = rtrim( $old_css ) . "\n\n" . $block . "\n";
	$res     = wp_update_custom_css_post( $new_css, array( 'stylesheet' => 'astra' ) );
	$chk     = wp_get_custom_css_post( 'astra' );
	if ( is_wp_error( $res ) || ! $chk || (string) $chk->post_content !== $new_css || 0 !== strpos( (string) $chk->post_content, rtrim( $old_css ) ) ) {
		$fail( 'The CSS did not save exactly as written.' );
	}

	// 3. the snippet (created from the plain code so WPCode's syntax check passes, then written back exactly: save() un-slashes the code)
	$snippet = new WPCode_Snippet( array(
		'title' => $title_snippet, 'code' => $band_code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true,
		'note'  => 'Created 2026-10-24 by scripts/work-with-me-employer/corporate/install-corporate-massage.php. The Corporate & Group Massage strip under the header comes from this snippet (and the ts-corp block in Additional CSS), not from page content or theme settings. Rollback: delete this snippet and the ts-corp CSS block.',
	) );
	$sid = $snippet->save();
	if ( $sid ) {
		$snap['snippet_id'] = (int) $sid;
		$save_snap();
		wp_update_post( wp_slash( array( 'ID' => (int) $sid, 'post_content' => $band_code ) ), true );
	}
	$sp  = $sid ? get_post( $sid ) : null;
	$sok = $sp && 'wpcode' === $sp->post_type && 'publish' === $sp->post_status && trim( (string) $sp->post_content ) === trim( $band_code )
		&& array( 'php' ) === wp_get_object_terms( $sid, 'wpcode_type', array( 'fields' => 'slugs' ) ) && array( 'everywhere' ) === wp_get_object_terms( $sid, 'wpcode_location', array( 'fields' => 'slugs' ) );
	if ( ! $sok ) {
		$fail( 'The snippet did not save exactly as expected.' );
	}
	$refresh();

	// 4. the picture (imported into the media library; its sha256 is kept on the item so a repeat run finds it; the Services row below points at it)
	$tmp = wp_tempnam( $img['name'] );
	if ( ! copy( $img_path, $tmp ) ) {
		$fail( 'Could not stage the picture.' );
	}
	$att = media_handle_sideload( array( 'name' => $img['name'], 'tmp_name' => $tmp ), 0, $img['title'] );
	if ( is_wp_error( $att ) ) {
		@unlink( $tmp );
		$fail( 'Import of the picture failed: ' . $att->get_error_message() . '.' );
	}
	$snap['attachment_id'] = (int) $att;
	$save_snap();
	update_post_meta( $att, '_ts_corp_src_sha256', $img['sha256'] );
	update_post_meta( $att, '_wp_attachment_image_alt', $img['alt'] );
	$row = $row_for( $att );

	// 5. the Services row (one exact insertion in front of the "Your Session" row)
	$new_services = str_replace( $anchor, $row . $anchor, $old_services, $count );
	if ( 1 !== $count || $new_services === $old_services ) {
		$fail( 'The Services anchor was not found exactly once.' );
	}
	$res = $set_content( $svc_id, $new_services );
	if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', $svc_id ) !== $new_services ) {
		$fail( 'The Services page did not save exactly as written.' );
	}

	// 6. the menu item (move the later items one place along, then add this one after Services)
	foreach ( $now['items'] as $it ) {
		if ( (int) $it->menu_order > $svc_order ) {
			wp_update_post( array( 'ID' => (int) $it->ID, 'menu_order' => (int) $it->menu_order + 1 ) );
		}
	}
	$item_id = wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title' => $data['nav_label'], 'menu-item-object' => 'page', 'menu-item-object-id' => (int) $page_id, 'menu-item-type' => 'post_type',
		'menu-item-status' => 'publish', 'menu-item-position' => $svc_order + 1, 'menu-item-parent-id' => 0, 'menu-item-classes' => $data['nav_class'],
	) );
	if ( is_wp_error( $item_id ) || ! $item_id ) {
		$fail( 'Could not add the menu item.' );
	}
	$snap['menu_item_id'] = (int) $item_id;
	$save_snap();
	update_post_meta( (int) $item_id, '_ts_corp_nav', '1' );
	wp_update_post( array( 'ID' => (int) $item_id, 'menu_order' => $svc_order + 1 ) );

	$after = $read();
	if ( 'applied' !== $after['state'] ) {
		$fail( 'Read-back differed from what was written (' . $after['report'] . ').' );
	}
	WP_CLI::success( 'Added page #' . $page_id . ' (/' . $slug . '/), picture #' . $att . ', the Services row, menu item #' . $item_id . ', snippet #' . $sid . ' "' . $title_snippet . '" and the ts-corp CSS block. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || empty( $snap['snippet_id'] ) || empty( $snap['menu_item_id' ] ) || empty( $snap['attachment_id'] ) || empty( $snap['css'] ) || ! isset( $snap['services_content'] )
	|| hash( 'sha256', (string) $snap['css'] ) !== ( $snap['css_sha256'] ?? '' ) || hash( 'sha256', (string) $snap['services_content'] ) !== ( $snap['services_sha256'] ?? '' ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, or from another site.' );
}
if ( 'applied' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] || (int) $now['found'][0]->ID !== (int) $snap['snippet_id'] || (int) $now['marked'][0]->ID !== (int) $snap['menu_item_id'] || (int) $now['atts'][0]->ID !== (int) $snap['attachment_id'] ) {
	WP_CLI::success( 'Nothing to roll back: what this installer wrote is not there in its original form. ' . $report );
	return;
}
// Services: take only the row out (so edits made elsewhere on the page since the apply survive); it is byte-identical to the snapshot if nothing else changed.
$cur_services = (string) $now['services_content'];
$row          = (string) $now['row_html'];
$restored_svc = ( $cur_services === str_replace( $anchor, $row . $anchor, (string) $snap['services_content'] ) ) ? (string) $snap['services_content'] : str_replace( $row . $anchor, $anchor, $cur_services );
// CSS: exact old CSS, or just the block out if other edits were made since.
$old_css      = (string) $snap['css'];
$restored_css = ( (string) $now['css'] === rtrim( $old_css ) . "\n\n" . $block . "\n" ) ? $old_css : str_replace( "\n\n" . $block . "\n", '', (string) $now['css'] );

wp_delete_post( (int) $snap['menu_item_id'], true );
foreach ( (array) $snap['menu_orders'] as $item_id => $order ) {
	if ( (int) get_post_field( 'menu_order', (int) $item_id ) !== (int) $order ) {
		wp_update_post( array( 'ID' => (int) $item_id, 'menu_order' => (int) $order ) );
	}
}
$set_content( (int) $snap['services_id'], $restored_svc );
wp_delete_attachment( (int) $snap['attachment_id'], true );   // after the Services content is back: nothing refers to it any more
wp_delete_post( (int) $snap['snippet_id'], true );
wp_update_custom_css_post( $restored_css, array( 'stylesheet' => 'astra' ) );
wp_delete_post( (int) $snap['page_id'], true );
$refresh();
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['report'] . '); stop and inspect.' );
}
$same_svc = hash( 'sha256', (string) $check['services_content'] ) === $snap['services_sha256'];
$same_css = hash( 'sha256', (string) $check['css'] ) === $snap['css_sha256'];
WP_CLI::success( 'Rollback done: the page, snippet, menu item and picture are deleted, the menu order is back, the Services row and the ts-corp block are out (Services content ' . ( $same_svc ? 'byte-identical to the snapshot' : 'differs from the snapshot: it was edited after the apply, and those edits were kept' ) . '; Additional CSS ' . ( $same_css ? 'byte-identical' : 'differs: edited after the apply, and those edits were kept' ) . ').' );

// ref: ./build-corporate-massage.py, ./corporate-massage.css — the reviewed sources; ../theme/install-theme-toggle.php — the snippet + CSS-block pattern; ../deep-tissue/install-deep-tissue-copy.php — the exact-replacement pattern
