<?php
/**
 * Home page: a Corporate & Group Massage card at the TOP of "The Massage I Offer", for ONE site at a time. Ryan, 2026-10-25: "put Corporate and Group at the top of 'Massage I Offer' on the home page
 * now. Make it harmonize with the other services on the page." (after approving the availability update on staging38).
 *
 * What it changes (WordPress's own storage; no files, no MU-plugin): TWO additions, nothing edited or removed.
 *   1. ONE insertion into the Home page's content. The new card is a clone of the existing first card (same grid row, title, description, outlined link button, 16 px-rounded picture, rule underneath; see
 *      build-home-corporate-card.py and home-card-content.php, generated) and goes immediately BEFORE the Therapeutic Massage card, as the first child of the section's card list. Its picture is the corporate
 *      illustration ALREADY in the media library since the corporate release (found by file name, its sha256 checked, referred to by relative URL only; no second copy is imported).
 *   2. ONE marked block at the end of Additional CSS ("ts-home-corp", home-corporate-card.css, generated): the picture-frame rules the site already holds for the three existing cards (6:5 frame, rounded, compact and
 *      centred when the rows stack), repeated for the new card's block id only. Without them the picture would not match its neighbours.
 * The old anchor (list wrapper + the Therapeutic card's opening) must occur EXACTLY ONCE (ready) or the whole new card in front of it exactly once (applied); anything else stops the run, so edits made to the
 * page in the meantime survive and are never overwritten. The CSS block is added only if the site's own frame rules that it mirrors are there.
 *
 * Allow-list: localhost:8090 and staging38. Live is NOT allowed: it is added only on Ryan's explicit go after staging38 is reviewed, and he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = the anchor is there once, the card and the CSS block are not; applied = the card is there once in front of the anchor and the CSS block is there once; CHANGED = anything else (stop)
 *   apply        snapshot the Home page and the Additional CSS, insert the card, add the CSS block, read both back; put both back if any read-back differs
 *   rollback     FILE = the before-snapshot: remove exactly this card and exactly this CSS block (other edits made since are kept)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the page and of the CSS.)
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
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) || ! current_user_can( 'edit_theme_options' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved blocks or the CSS.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

// ---- the reviewed content ------------------------------------------------------------------------------------------------------------------------
$data = is_file( __DIR__ . '/home-card-content.php' ) ? require __DIR__ . '/home-card-content.php' : null;
foreach ( array( 'card', 'anchor', 'wrapper', 'ids', 'title', 'desc', 'label', 'link', 'alt', 'img_name', 'img_sha256' ) as $k ) {
	if ( ! is_array( $data ) || empty( $data[ $k ] ) ) {
		WP_CLI::error( 'home-card-content.php is missing or incomplete (run build-home-corporate-card.py): ' . $k );
	}
}
$card_tpl = (string) $data['card'];
$anchor   = (string) $data['anchor'];
$wrapper  = (string) $data['wrapper'];
$ids      = (array) $data['ids'];
$link     = (string) $data['link'];
$first_id = '"block_id":"' . $ids[0] . '"';
if ( 1 !== substr_count( $card_tpl, '{{IMG_URL}}' ) || 0 !== substr_count( $card_tpl, $anchor ) || 6 !== count( $ids ) || 1 !== substr_count( $card_tpl, $first_id ) || "\n\n" !== substr( $card_tpl, -2 )
	|| 1 !== substr_count( $card_tpl, 'href="' . $link . '"' ) || false !== strpos( $card_tpl, 'staging' ) || false !== strpos( $card_tpl, 'http' ) ) {
	WP_CLI::error( 'Internal error: the card is not what was reviewed.' );
}
foreach ( $ids as $id ) {
	if ( ! preg_match( '/^[0-9a-f]{8}$/', $id ) || 0 === substr_count( $card_tpl, $id ) ) {
		WP_CLI::error( 'Internal error: a block id is not what was reviewed: ' . $id );
	}
}
$start = '/* ts-home-corp:start';
$end   = '/* ts-home-corp:end */';
$block = rtrim( is_file( __DIR__ . '/home-corporate-card.css' ) ? (string) file_get_contents( __DIR__ . '/home-corporate-card.css' ) : '' );
if ( 0 !== strpos( $block, $start ) || substr( $block, -strlen( $end ) ) !== $end || 1 !== substr_count( $block, 'ts-home-corp:start' ) || 1 !== substr_count( $block, 'ts-home-corp:end' ) || strlen( $block ) > 4000
	|| 3 !== substr_count( $block, 'uagb-block-' . $ids[0] ) || 0 !== preg_match( '/uagb-block-(?!' . preg_quote( $ids[0], '/' ) . ')/', $block ) ) {
	WP_CLI::error( 'home-corporate-card.css is not the reviewed single marked block (three rules, new card only).' );
}
foreach ( array( '@import', 'url(', 'expression', '!important', 'javascript:', '<' ) as $bad ) {
	if ( false !== stripos( $block, $bad ) ) {
		WP_CLI::error( 'home-corporate-card.css contains something the review did not cover: ' . $bad );
	}
}

// ---- wording checks on everything that becomes visible (the whole house list applies; "studio" is on it) ------------------------------------------------------
$visible = html_entity_decode( wp_strip_all_tags( $data['title'] . ' ' . $data['desc'] . ' ' . $data['label'] . ' ' . $data['alt'] ), ENT_QUOTES, 'UTF-8' );
foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio', 'cure', 'treat', 'diagnos', 'inflammation', 'detox', 'guarantee', 'relieve', 'relief', 'portland-area', 'portland metro' ) as $bad ) {
	if ( false !== stripos( $visible, $bad ) ) {
		WP_CLI::error( 'The card text contains a term this site must not use: ' . $bad );
	}
}
if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $visible ) || preg_match( '/\b(we|our|us|ours)\b/i', $visible ) ) {
	WP_CLI::error( 'The card text contains heal/healing or we/our/us, which this site must not use.' );
}
// the card must be exactly one well-formed top-level block (a container) and survive WordPress's own parse and re-serialise unchanged
$probe  = str_replace( '{{IMG_URL}}', '/wp-content/uploads/probe.webp', $card_tpl );
$blocks = array_values( array_filter( parse_blocks( $probe ), function ( $b ) { return null !== $b['blockName']; } ) );
if ( 1 !== count( $blocks ) || 'uagb/container' !== $blocks[0]['blockName'] || trim( serialize_blocks( parse_blocks( $probe ) ) ) !== trim( $probe ) ) {
	WP_CLI::error( 'The card is not exactly one block that WordPress reads back unchanged; nothing was changed.' );
}

// ---- the site as it is ------------------------------------------------------------------------------------------------------------------------------
$page = get_page_by_path( 'home' );
if ( ! $page || 'publish' !== $page->post_status || ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) !== (int) $page->ID ) ) {
	WP_CLI::error( 'The Home page was not found as the published front page; nothing was changed.' );
}
$target = get_page_by_path( trim( $link, '/' ) );
if ( ! $target || 'publish' !== $target->post_status ) {
	WP_CLI::error( 'The Corporate & Group Massage page (' . $link . ') is not published on this site; the card would link to nothing, so nothing was changed.' );
}
$css_post = wp_get_custom_css_post( 'astra' );
$css_now  = $css_post ? (string) $css_post->post_content : '';
if ( strlen( $css_now ) < 1000 || false === strpos( $css_now, 'ts-footer-links' ) || 1 !== substr_count( $css_now, 'Thai Solitude homepage service illustration frames: 6:5' ) || 1 !== substr_count( $css_now, 'TS SERVICE IMAGE RESPONSIVE START' )
	|| 1 !== substr_count( $css_now, "\nbody.home .wp-block-uagb-container.uagb-block-db0b5e62 > figure.wp-block-image,\n" ) ) {
	WP_CLI::error( 'The Additional CSS does not hold the picture-frame rules this block mirrors (or is not the known site CSS); nothing was changed.' );
}
/** The corporate illustration that is already in the media library: found by file name, its bytes checked against the reviewed sha256. Exactly one match, or stop. */
$find_picture = function () use ( $data ) {
	$cands = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'suppress_filters' => true,
		'meta_query' => array( array( 'key' => '_wp_attached_file', 'value' => $data['img_name'], 'compare' => 'LIKE' ) ) ) );
	$ok = array();
	foreach ( $cands as $c ) {
		$f = get_attached_file( $c->ID );
		if ( $f && is_file( $f ) && hash_file( 'sha256', $f ) === $data['img_sha256'] ) {
			$ok[] = $c;
		}
	}
	return 1 === count( $ok ) ? $ok[0] : null;
};
$pic = $find_picture();
if ( ! $pic ) {
	WP_CLI::error( 'The corporate illustration (' . $data['img_name'] . ', reviewed sha256) is not in this site\'s media library exactly once; the corporate release must be on this site first. Nothing was changed.' );
}
$card = str_replace( '{{IMG_URL}}', wp_make_link_relative( (string) wp_get_attachment_url( $pic->ID ) ), $card_tpl );

$read = function () use ( $card, $anchor, $wrapper, $first_id, $block ) {
	$pg = get_page_by_path( 'home' );
	$c  = (string) $pg->post_content;
	$r  = array( 'page' => $pg, 'anchors' => substr_count( $c, $anchor ), 'pinned_old' => substr_count( $c, $wrapper . $anchor ), 'pinned_new' => substr_count( $c, $wrapper . $card . $anchor ), 'first_id' => substr_count( $c, $first_id ), 'heading' => substr_count( $c, 'The Massage I Offer' ) );
	if ( 1 === $r['anchors'] && 1 === $r['pinned_old'] && 0 === $r['pinned_new'] && 0 === $r['first_id'] && 1 === $r['heading'] ) {
		$r['card_state'] = 'ready';
	} elseif ( 1 === $r['anchors'] && 0 === $r['pinned_old'] && 1 === $r['pinned_new'] && 1 === $r['first_id'] && 1 === $r['heading'] ) {
		$r['card_state'] = 'applied';
	} else {
		$r['card_state'] = 'CHANGED';
	}
	$p        = wp_get_custom_css_post( 'astra' );
	$r['css'] = $p ? (string) $p->post_content : '';
	$n        = substr_count( $r['css'], 'ts-home-corp:start' );
	if ( 0 === $n && 0 === substr_count( $r['css'], 'ts-home-corp:end' ) ) {
		$r['css_state'] = 'absent';
	} elseif ( 1 === $n && 1 === substr_count( $r['css'], $block ) && 1 === substr_count( $r['css'], 'ts-home-corp:end' ) ) {
		$r['css_state'] = 'exact';
	} else {
		$r['css_state'] = 'CHANGED';
	}
	if ( 'ready' === $r['card_state'] && 'absent' === $r['css_state'] ) {
		$r['state'] = 'ready';
	} elseif ( 'applied' === $r['card_state'] && 'exact' === $r['css_state'] ) {
		$r['state'] = 'applied';
	} else {
		$r['state'] = 'CHANGED';
	}
	$r['report'] = sprintf( 'card=%s (anchor=%d old-pinned=%d card-in-front=%d card-id=%d heading=%d) css_block=%s', $r['card_state'], $r['anchors'], $r['pinned_old'], $r['pinned_new'], $r['first_id'], $r['heading'], $r['css_state'] );
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s page=#%d %s', $home, $state, $now['page']->ID, $now['report'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The Home page or the Additional CSS is not as reviewed (the Therapeutic card is not the first card of "The Massage I Offer" exactly once, or something is half there); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report . ' picture=media#' . $pic->ID );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
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
	$old     = (string) $now['page']->post_content;
	$old_css = (string) $now['css'];
	$snap    = array( 'created' => gmdate( 'c' ), 'home' => $home, 'page_id' => (int) $now['page']->ID, 'old_sha256' => hash( 'sha256', $old ), 'old_content' => $old, 'css_sha256' => hash( 'sha256', $old_css ), 'css' => $old_css,
		'card' => $card, 'block' => $block, 'wrapper' => $wrapper, 'anchor' => $anchor );
	$file = rtrim( $backup_dir, '/' ) . '/home-corporate-card-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( $snap ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );

	$fail = function ( $why ) use ( $snap, $set_content, $read, $old_css ) {
		if ( (string) get_post_field( 'post_content', $snap['page_id'] ) !== $snap['old_content'] ) {
			$set_content( $snap['page_id'], $snap['old_content'] );
		}
		$p = wp_get_custom_css_post( 'astra' );
		if ( ! $p || (string) $p->post_content !== $old_css ) {
			wp_update_custom_css_post( $old_css, array( 'stylesheet' => 'astra' ) );
		}
		$check = $read();
		WP_CLI::error( $why . ' Everything was put back (state now: ' . $check['state'] . ').' );
	};
	// 1. the card
	$new = str_replace( $wrapper . $anchor, $wrapper . $card . $anchor, $old, $count );
	if ( 1 !== $count || strlen( $new ) !== strlen( $old ) + strlen( $card ) ) {
		$fail( 'The insertion did not match exactly once.' );
	}
	$res = $set_content( $snap['page_id'], $new );
	if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', $snap['page_id'] ) !== $new ) {
		$fail( 'The Home page did not save exactly as written.' );
	}
	// 2. the CSS block (appended; the old CSS stays byte for byte in front of it)
	$new_css = rtrim( $old_css ) . "\n\n" . $block . "\n";
	$res     = wp_update_custom_css_post( $new_css, array( 'stylesheet' => 'astra' ) );
	$chk     = wp_get_custom_css_post( 'astra' );
	if ( is_wp_error( $res ) || ! $chk || (string) $chk->post_content !== $new_css || 0 !== strpos( (string) $chk->post_content, rtrim( $old_css ) ) ) {
		$fail( 'The Additional CSS did not save exactly as written.' );
	}
	$after = $read();
	if ( 'applied' !== $after['state'] ) {
		$fail( 'Read-back differed from what was written (' . $after['report'] . ').' );
	}
	WP_CLI::success( 'Inserted the card at the top of "The Massage I Offer" on the Home page #' . $snap['page_id'] . ' and added the ts-home-corp CSS block. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || empty( $snap['card'] ) || empty( $snap['block'] ) || empty( $snap['old_content'] ) || ! isset( $snap['css'] )
	|| hash( 'sha256', (string) $snap['old_content'] ) !== ( $snap['old_sha256'] ?? '' ) || hash( 'sha256', (string) $snap['css'] ) !== ( $snap['css_sha256'] ?? '' ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, or from another site.' );
}
if ( (int) $snap['page_id'] !== (int) $now['page']->ID ) {
	WP_CLI::error( 'The snapshot belongs to another page; nothing was changed.' );
}
$cur       = (string) $now['page']->post_content;
$cur_css   = (string) $now['css'];
$has_card  = 1 === substr_count( $cur, (string) $snap['card'] );
$has_block = 1 === substr_count( $cur_css, (string) $snap['block'] ) && 1 === substr_count( $cur_css, 'ts-home-corp:start' );
if ( ! $has_card && ! $has_block ) {
	WP_CLI::success( 'Nothing to roll back: neither the card nor the CSS block is there exactly once. ' . $report );
	return;
}
$restored     = $has_card ? str_replace( (string) $snap['card'], '', $cur ) : $cur;
$old_css      = (string) $snap['css'];
$restored_css = $has_block ? ( ( $cur_css === rtrim( $old_css ) . "\n\n" . $snap['block'] . "\n" ) ? $old_css : str_replace( "\n\n" . $snap['block'] . "\n", '', $cur_css ) ) : $cur_css;
if ( $has_card ) {
	$res = $set_content( (int) $snap['page_id'], $restored );
	if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', (int) $snap['page_id'] ) !== $restored ) {
		$set_content( (int) $snap['page_id'], $cur );
		WP_CLI::error( 'The Home page did not save exactly as written; the page was put back as it was.' );
	}
}
if ( $has_block ) {
	wp_update_custom_css_post( $restored_css, array( 'stylesheet' => 'astra' ) );
	$chk = wp_get_custom_css_post( 'astra' );
	if ( ! $chk || (string) $chk->post_content !== $restored_css ) {
		if ( $has_card ) {
			$set_content( (int) $snap['page_id'], $cur );
		}
		wp_update_custom_css_post( $cur_css, array( 'stylesheet' => 'astra' ) );
		WP_CLI::error( 'The Additional CSS did not save exactly as written; the page and the CSS were put back as they were.' );
	}
}
$check = $read();
WP_CLI::success( sprintf( 'Rollback done: card %s, CSS block %s. Home page %s the before-snapshot; Additional CSS %s the before-snapshot.', $has_card ? 'removed' : 'was not there', $has_block ? 'removed' : 'was not there',
	hash( 'sha256', (string) $check['page']->post_content ) === $snap['old_sha256'] ? 'is byte-identical to' : 'differs from (other edits since the apply were kept)', hash( 'sha256', (string) $check['css'] ) === $snap['css_sha256'] ? 'is byte-identical to' : 'differs from (other edits since the apply were kept)' ) );

// ref: ./build-home-corporate-card.py — generates home-card-content.php and home-corporate-card.css, which this loads; ../corporate/install-corporate-massage.php — the CSS-block pattern
