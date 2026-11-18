<?php
/**
 * READ-ONLY capture of the Winding Willow page and everything it depends on, from one site (Ryan, 2026-11-17: "export the page exactly:
 * content, title, slug, status, template, meta, any CSS/snippets it depends on, and its images. Also check whether any menu/Services/Contact
 * link to it exists there."). Writes nothing: every call below is a read.
 * Run: wp --user=1 eval-file capture-winding-willow.php
 * Output: a plain summary, then the whole bundle as JSON (gzip + base64) between BEGIN/END lines, so the exact bytes survive the log.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}
global $wpdb;
$out = array( 'home' => home_url(), 'captured' => gmdate( 'c' ), 'theme' => get_stylesheet() );

// 1. the page: by the known slugs first, then by title
$cands = array();
foreach ( array( 'winding-willow', 'winding-willow-acupuncture', 'massage-with-sa', 'winding-willow-acupuncture-wellness' ) as $s ) {
	foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type NOT IN ('revision','nav_menu_item')", $s ) ) as $id ) {
		$cands[ (int) $id ] = true;
	}
}
foreach ( $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE '%Winding Willow%' AND post_type NOT IN ('revision','nav_menu_item')" ) as $id ) {
	$cands[ (int) $id ] = true;
}
$out['candidates'] = array();
foreach ( array_keys( $cands ) as $id ) {
	$p = get_post( $id, ARRAY_A );
	$meta = array();
	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d ORDER BY meta_id", $id ), ARRAY_A ) as $m ) {
		$meta[] = $m;
	}
	$revs = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_modified_gmt, LENGTH(post_content) AS len, SHA2(post_content,256) AS sha FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'revision' ORDER BY ID", $id ), ARRAY_A );
	$p['content_sha256'] = hash( 'sha256', (string) $p['post_content'] );
	$p['content_length'] = strlen( (string) $p['post_content'] );
	$out['candidates'][] = array( 'post' => $p, 'meta' => $meta, 'template' => get_page_template_slug( $id ), 'permalink' => get_permalink( $id ), 'revisions' => $revs );
	WP_CLI::log( sprintf( 'PAGE #%d type=%s status=%s slug=%s title="%s" modified=%s UTC len=%d sha256=%s template="%s" meta_rows=%d revisions=%d', $id, $p['post_type'], $p['post_status'], $p['post_name'], $p['post_title'], $p['post_modified_gmt'], $p['content_length'], $p['content_sha256'], get_page_template_slug( $id ), count( $meta ), count( $revs ) ) );
	foreach ( $meta as $m ) {
		WP_CLI::log( '  meta ' . $m['meta_key'] . ' = ' . substr( preg_replace( '/\s+/', ' ', (string) $m['meta_value'] ), 0, 300 ) );
	}
}
if ( ! $out['candidates'] ) {
	WP_CLI::log( 'NO PAGE found by slug or title.' );
}
$ids = array_keys( $cands );

// 2. images and attachments the page refers to (wp-image-N, "id":N in blocks, /wp-content/uploads/ URLs)
$out['attachments'] = array();
foreach ( $out['candidates'] as $c ) {
	$html = (string) $c['post']['post_content'];
	preg_match_all( '#/wp-content/uploads/[^"\'\s)<>]+#', $html, $u );
	preg_match_all( '/wp-image-(\d+)/', $html, $w );
	$att_ids = array_unique( array_map( 'intval', $w[1] ) );
	foreach ( $c['meta'] as $m ) {
		if ( '_thumbnail_id' === $m['meta_key'] ) {
			$att_ids[] = (int) $m['meta_value'];
		}
	}
	foreach ( array_unique( $u[0] ) as $url ) {
		$f = WP_CONTENT_DIR . substr( (string) wp_parse_url( $url, PHP_URL_PATH ), strlen( '/wp-content' ) );
		$ok = is_file( $f );
		$out['attachments'][] = array( 'url' => $url, 'exists' => $ok, 'size' => $ok ? filesize( $f ) : null, 'sha256' => $ok ? hash_file( 'sha256', $f ) : null, 'b64' => ( $ok && filesize( $f ) < 3000000 ) ? base64_encode( (string) file_get_contents( $f ) ) : null );
		WP_CLI::log( 'UPLOAD ' . $url . ' exists=' . ( $ok ? 'yes size=' . filesize( $f ) . ' sha256=' . hash_file( 'sha256', $f ) : 'NO' ) );
	}
	foreach ( array_unique( $att_ids ) as $aid ) {
		$f = get_attached_file( $aid );
		$out['attachments'][] = array( 'attachment_id' => $aid, 'file' => $f, 'alt' => get_post_meta( $aid, '_wp_attachment_image_alt', true ), 'title' => get_the_title( $aid ) );
		WP_CLI::log( 'ATTACHMENT #' . $aid . ' ' . $f );
	}
}
if ( ! $out['attachments'] ) {
	WP_CLI::log( 'No uploads or attachments referenced by the page.' );
}

// 3. CSS: the whole Additional CSS, and every marked block or rule that names the page (ww/winding/page-id-N)
$css = wp_get_custom_css_post( get_stylesheet() );
$cs  = $css ? (string) $css->post_content : '';
$out['css'] = array( 'id' => $css ? $css->ID : null, 'sha256' => hash( 'sha256', $cs ), 'length' => strlen( $cs ), 'content' => $cs );
WP_CLI::log( 'ADDITIONAL CSS #' . ( $css ? $css->ID : '-' ) . ' length=' . strlen( $cs ) . ' sha256=' . hash( 'sha256', $cs ) );
preg_match_all( '#/\*\s*([a-z0-9-]+):start#i', $cs, $mk );
WP_CLI::log( '  marked blocks: ' . implode( ' ', $mk[1] ) );
$needles = array( 'winding', 'ts-ww', '.ww-', 'willow' );
foreach ( $ids as $id ) {
	$needles[] = 'page-id-' . $id;
	$needles[] = 'postid-' . $id;
}
foreach ( $needles as $n ) {
	$k = substr_count( strtolower( $cs ), strtolower( $n ) );
	if ( $k ) {
		WP_CLI::log( '  CSS mentions "' . $n . '" ' . $k . ' time(s)' );
	}
}
// classes the page uses, and whether the CSS defines them
$out['classes'] = array();
foreach ( $out['candidates'] as $c ) {
	preg_match_all( '/class="([^"]+)"/', (string) $c['post']['post_content'], $cl );
	$all = array();
	foreach ( $cl[1] as $s ) {
		foreach ( preg_split( '/\s+/', $s ) as $one ) {
			if ( '' !== $one && 0 !== strpos( $one, 'wp-block' ) && 0 !== strpos( $one, 'has-' ) ) {
				$all[ $one ] = true;
			}
		}
	}
	foreach ( array_keys( $all ) as $one ) {
		$in = false !== strpos( $cs, '.' . $one );
		$out['classes'][ $one ] = $in;
		WP_CLI::log( '  class ' . $one . ( $in ? ' (styled in Additional CSS)' : '' ) );
	}
}

// 4. snippets (WPCode) that name the page, its slug or its id
$out['snippets'] = array();
foreach ( get_posts( array( 'post_type' => 'wpcode', 'post_status' => 'any', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $s ) {
	$hit = stripos( $s->post_content, 'winding' ) !== false || stripos( $s->post_content, 'willow' ) !== false;
	foreach ( $ids as $id ) {
		$hit = $hit || preg_match( '/\b' . $id . '\b/', $s->post_content );
	}
	if ( $hit ) {
		$out['snippets'][] = array( 'ID' => $s->ID, 'status' => $s->post_status, 'title' => $s->post_title, 'code' => $s->post_content, 'sha256' => hash( 'sha256', $s->post_content ) );
		WP_CLI::log( 'SNIPPET #' . $s->ID . ' ' . $s->post_status . ' "' . $s->post_title . '" mentions the page (len ' . strlen( $s->post_content ) . ')' );
	}
}
if ( ! $out['snippets'] ) {
	WP_CLI::log( 'No WPCode snippet mentions the page.' );
}

// 5. links to it: menu items, other posts' content, options, widgets
$out['menu_items'] = array();
foreach ( wp_get_nav_menus() as $menu ) {
	foreach ( (array) wp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'any' ) ) as $it ) {
		$to = ( 'page' === $it->object && in_array( (int) $it->object_id, $ids, true ) ) || stripos( (string) $it->url, 'winding' ) !== false;
		if ( $to ) {
			$out['menu_items'][] = array( 'menu' => $menu->name, 'ID' => $it->ID, 'title' => $it->title, 'url' => $it->url, 'order' => $it->menu_order, 'parent' => $it->menu_item_parent );
			WP_CLI::log( 'MENU "' . $menu->name . '" item #' . $it->ID . ' "' . $it->title . '" -> ' . $it->url );
		}
	}
}
WP_CLI::log( 'menu items linking to it: ' . count( $out['menu_items'] ) . ' (menus: ' . implode( ', ', wp_list_pluck( wp_get_nav_menus(), 'name' ) ) . ')' );
$out['linked_from'] = array();
$q = "SELECT ID, post_type, post_status, post_name FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','nav_menu_item') AND post_content LIKE '%winding%'";
foreach ( $wpdb->get_results( $q, ARRAY_A ) as $r ) {
	if ( in_array( (int) $r['ID'], $ids, true ) ) {
		continue;
	}
	$out['linked_from'][] = $r;
	WP_CLI::log( 'MENTIONED IN post #' . $r['ID'] . ' ' . $r['post_type'] . '/' . $r['post_status'] . ' ' . $r['post_name'] );
}
foreach ( array( 'services', 'contact', 'about' ) as $s ) {
	$p = get_page_by_path( $s );
	WP_CLI::log( '  /' . $s . '/ links to it: ' . ( $p && stripos( $p->post_content, 'winding' ) !== false ? 'YES' : 'no' ) );
}
$opts = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_value LIKE '%winding%' AND option_name NOT LIKE '\\_transient%' AND option_name NOT LIKE '\\_site\\_transient%'" );
$out['options_mentioning'] = $opts;
WP_CLI::log( 'options mentioning "winding": ' . ( $opts ? implode( ', ', $opts ) : 'none' ) );

// 6. what else exists on this install (for a later choice of a fresh staging copy)
$out['latest_pages'] = $wpdb->get_results( "SELECT ID, post_name, post_status, post_modified_gmt FROM {$wpdb->posts} WHERE post_type='page' ORDER BY post_modified_gmt DESC LIMIT 12", ARRAY_A );
foreach ( $out['latest_pages'] as $r ) {
	WP_CLI::log( '  recent page #' . $r['ID'] . ' ' . $r['post_status'] . ' ' . $r['post_name'] . ' ' . $r['post_modified_gmt'] );
}

$json = wp_json_encode( $out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
WP_CLI::log( 'BUNDLE sha256 ' . hash( 'sha256', $json ) . ' (' . strlen( $json ) . ' bytes JSON)' );
WP_CLI::log( '----- BEGIN bundle (json.gz base64) -----' );
foreach ( str_split( base64_encode( gzencode( $json, 9 ) ), 76 ) as $line ) {
	WP_CLI::log( $line );
}
WP_CLI::log( '----- END bundle -----' );

// ref: ./capture-winding-willow.sh — runs this read-only on the server; ../../ci/inspect.sh — the read-only inspect pattern
