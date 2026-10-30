<?php
/**
 * Two small SEO fixes found by the pre-indexing scan of 2026-10-30 (seo-scan/scan-seo.sh), for ONE site at a time. Ryan, 2026-10-30: "fix Services" and "For the corporate/group page use the image already
 * associated with the insight link. Only once."
 *
 *   1. Services page, SEO description (SureRank, title unchanged):
 *        BEFORE  Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Mt. Tabor, Portland. Every session starts with your goals.
 *        AFTER   Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Portland. Every session starts with your goals.
 *      (the same change item 1 of the Portland reconcile made on Home, Thai, Therapeutic and Deep Tissue; the social tags follow the description.)
 *   2. Corporate & Group Massage page: it has no share image (Open Graph / Twitter image). It gets the picture the Home page already uses for the link to it, corporate-group-massage.webp, which is ALREADY in the
 *      media library: found by file name, its bytes checked against the reviewed sha256, exactly one match or stop; NO second copy is imported. It is set as the page's SHARE picture in SureRank's own social
 *      settings (facebook_image_url / facebook_image_id; Twitter follows Facebook), once. It is NOT made the page's featured image, because that would also draw it on the page itself. Nothing in the page's content changes.
 *   3. (Ryan, 2026-10-30, "Yes, add it": to make the picture an SEO fix, not only a link-preview image) the SAME picture is also placed ONCE on the Corporate page itself, between the hero and the three at-a-glance
 *      cards, as a core image block with descriptive alt text (the wording already approved for this picture on the Home card and the Services row), its real width and height, and the picture's own file (no copy).
 *      The page's build notes had left it picture-free on purpose (the illustration shows a client on a massage table); Ryan chose to add it anyway. One exact insertion; every other byte of the page stays.
 * NOTHING ELSE changes. ready = Services holds exactly the old description and the corporate page has no share picture and no featured image; applied = the new description and that picture as the share picture; anything else stops.
 * The Services page content is hashed before and after and must be identical; the Corporate page content must equal the old content with that one insertion.
 *
 * Allow-list: localhost:8090 and staging38 only. Live is added only after staging38 is applied, checked and proven AND Ryan says go; he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot, write, read back; put everything back if any read-back differs
 *   rollback     FILE = the before-snapshot: put the SureRank rows of both pages back exactly and take the inserted picture block out again (other edits made since are kept)
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

$svc_slug  = 'services';
$corp_slug = 'corporate-massage-portland';
$svc_title = 'Massage Services | Butsaba Dusrai, Oregon LMT';
$svc_old   = 'Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Mt. Tabor, Portland. Every session starts with your goals.';
$svc_new   = 'Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Portland. Every session starts with your goals.';
$img_name  = 'corporate-group-massage.webp';
$img_sha   = '764a7ef428cbd2985156b584116f8bc196dd5c3a900e2426ff4f49f8582a747e';   // the same reviewed file the Home corporate card uses (home-card/build-home-corporate-card.py)
$img_alt   = 'Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.';   // the approved alt text of this picture (Home card, Services row)
$anchor_old = "<!-- /wp:group -->\n\n<!-- wp:columns {\"className\":\"ts-corp__glance\"} -->";   // the end of the hero group and the start of the at-a-glance cards: occurs exactly once

// ---- checks on the NEW wording (the whole house list applies) --------------------------------------------------------------------------------------------
foreach ( array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i',
	'/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i', '/real results/i', '/\bstudio\b/i', '/\b(we|our|us|ours)\b/i', '/Portland[- ]area/i',
	'/Portland metro/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i', '/Mt\.? Tabor/i' ) as $re ) {
	if ( preg_match( $re, $svc_title . ' ' . $svc_new, $m ) ) {
		WP_CLI::error( 'The new Services description contains a term this site must not use: "' . $m[0] . '"' );
	}
}
if ( preg_match( '/\b(we|our|us|ours)\b|\bheal|\bstudio\b|\brelief\b|\bcure\b|\btreat/i', $img_alt, $m ) ) {
	WP_CLI::error( 'The picture alt text contains a term this site must not use: "' . $m[0] . '"' );
}
if ( strlen( $svc_new ) > 160 ) {
	WP_CLI::error( 'Internal error: the reviewed description is longer than 160 characters.' );
}

$get_page = function ( $slug ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	return ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) ? $pages[0] : null;
};
/** The corporate illustration that is already in the media library: found by file name, its bytes checked against the reviewed sha256. Exactly one match, or null. */
$find_picture = function () use ( $img_name, $img_sha ) {
	$cands = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'suppress_filters' => true,
		'meta_query' => array( array( 'key' => '_wp_attached_file', 'value' => $img_name, 'compare' => 'LIKE' ) ) ) );
	$ok = array();
	foreach ( $cands as $c ) {
		$f = get_attached_file( $c->ID );
		if ( $f && is_file( $f ) && hash_file( 'sha256', $f ) === $img_sha ) {
			$ok[] = $c;
		}
	}
	return 1 === count( $ok ) ? $ok[0] : null;
};
$pic = $find_picture();
if ( ! $pic ) {
	WP_CLI::error( 'The corporate illustration (' . $img_name . ', reviewed sha256) is not in this site\'s media library exactly once; nothing was changed.' );
}

$meta = wp_get_attachment_metadata( $pic->ID );
$pw   = is_array( $meta ) ? (int) ( $meta['width'] ?? 0 ) : 0;
$ph   = is_array( $meta ) ? (int) ( $meta['height'] ?? 0 ) : 0;
if ( $pw < 100 || $ph < 100 ) {
	WP_CLI::error( 'The picture has no readable width and height in the media library; nothing was changed.' );
}
$img_url    = wp_make_link_relative( (string) wp_get_attachment_url( $pic->ID ) );   // relative, so a staging-to-live push stays correct
$esc_alt    = esc_attr( $img_alt );
$figure     = '<!-- wp:image {"width":"520px","sizeSlug":"full","linkDestination":"none","align":"center","style":{"border":{"radius":"16px"}},"className":"ts-corp__pic"} -->' . "\n"
	. '<figure class="wp-block-image aligncenter size-full is-resized has-custom-border ts-corp__pic"><img src="' . esc_url( $img_url ) . '" alt="' . $esc_alt . '" title="' . $esc_alt . '" width="' . $pw . '" height="' . $ph . '" style="border-radius:16px;width:520px;max-width:100%;height:auto"/></figure>' . "\n"
	. '<!-- /wp:image -->';
$anchor_new = "<!-- /wp:group -->\n\n" . $figure . "\n\n<!-- wp:columns {\"className\":\"ts-corp__glance\"} -->";
if ( $anchor_old === $anchor_new || false !== strpos( $anchor_new, $anchor_old ) ) {
	WP_CLI::error( 'Internal error: the insertion is not what was reviewed.' );
}

$read = function () use ( $get_page, $anchor_old, $anchor_new, $svc_slug, $corp_slug, $svc_title, $svc_old, $svc_new, $pic ) {
	$r   = array( 'svc' => 'CHANGED', 'corp' => 'CHANGED', 'corp_c' => 'CHANGED', 'svc_page' => null, 'corp_page' => null, 'svc_rows' => array(), 'svc_hash' => '', 'corp_hash' => '', 'corp_rows' => array() );
	$svc = $get_page( $svc_slug );
	if ( $svc ) {
		$r['svc_page'] = $svc;
		$r['svc_hash'] = hash( 'sha256', (string) $svc->post_content );
		foreach ( get_post_meta( $svc->ID ) as $k => $v ) {
			if ( 0 === strpos( $k, 'surerank' ) ) {
				$r['svc_rows'][ $k ] = $v;
			}
		}
		$g = get_post_meta( $svc->ID, 'surerank_settings_general', true );
		if ( is_array( $g ) && isset( $g['page_title'], $g['page_description'] ) && $g['page_title'] === $svc_title ) {
			$r['svc'] = ( $g['page_description'] === $svc_old ) ? 'ready' : ( ( $g['page_description'] === $svc_new ) ? 'applied' : 'CHANGED' );
		}
	}
	$corp = $get_page( $corp_slug );
	if ( $corp ) {
		$r['corp_page'] = $corp;
		$r['corp_hash'] = hash( 'sha256', (string) $corp->post_content );
		foreach ( get_post_meta( $corp->ID ) as $k => $v ) {
			if ( 0 === strpos( $k, 'surerank' ) ) {
				$r['corp_rows'][ $k ] = $v;
			}
		}
		$cc            = (string) $corp->post_content;
		$r['corp_c'] = ( 1 === substr_count( $cc, $anchor_old ) && 0 === substr_count( $cc, $anchor_new ) ) ? 'ready' : ( ( 0 === substr_count( $cc, $anchor_old ) && 1 === substr_count( $cc, $anchor_new ) ) ? 'applied' : 'CHANGED' );
		$thumb = get_post_meta( $corp->ID, '_thumbnail_id' );
		$soc   = get_post_meta( $corp->ID, 'surerank_settings_social', true );
		$url   = is_array( $soc ) ? (string) ( $soc['facebook_image_url'] ?? '' ) : '';
		$id    = is_array( $soc ) ? (int) ( $soc['facebook_image_id'] ?? 0 ) : 0;
		if ( 0 === count( $thumb ) ) {   // the picture must never become the featured image (it would be drawn on the page)
			if ( '' === $url && 0 === $id ) {
				$r['corp'] = 'ready';
			} elseif ( $url === (string) wp_get_attachment_url( $pic->ID ) && $id === (int) $pic->ID ) {
				$r['corp'] = 'applied';
			}
		}
	}
	// three parts, each ready / applied / CHANGED. Staging38 already holds the first two from the earlier run of this installer, so a mix of ready and applied is allowed ("partial"): apply does only the parts still ready.
	$parts      = array( $r['svc'], $r['corp'], $r['corp_c'] );
	$r['state'] = in_array( 'CHANGED', $parts, true ) ? 'CHANGED' : ( 3 === count( array_keys( $parts, 'ready', true ) ) ? 'ready' : ( 3 === count( array_keys( $parts, 'applied', true ) ) ? 'applied' : 'partial' ) );
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s services=%s corporate-share=%s corporate-page-picture=%s picture=media#%d', $home, $state, $now['svc'], $now['corp'], $now['corp_c'], $pic->ID );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Not in a reviewed state (a page is missing or unpublished; Services holds neither exactly the old nor the new description; the corporate page already has some other featured image, or its hero/cards joint is not as reviewed); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
/** Put exactly these SureRank rows on the page (raw stored values): rows not listed are removed. */
$restore_rows = function ( $page_id, array $rows ) {
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
		WP_CLI::success( 'Nothing to do: all three fixes are already there. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/seo-fixes-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'svc_id' => (int) $now['svc_page']->ID, 'svc_rows' => $now['svc_rows'], 'svc_content_sha256' => $now['svc_hash'],
		'corp_id' => (int) $now['corp_page']->ID, 'corp_rows' => $now['corp_rows'], 'corp_content_sha256' => $now['corp_hash'], 'pic_id' => (int) $pic->ID, 'before' => array( 'svc' => $now['svc'], 'corp' => $now['corp'], 'corp_c' => $now['corp_c'] ) );
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || filesize( $file ) < 40 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	\SureRank\Inc\API\Post::update_post_meta_common( (int) $now['svc_page']->ID, array( 'page_title' => $svc_title, 'page_description' => $svc_new ) );
	$old_content = (string) $now['corp_page']->post_content;
	$new_content = ( 'ready' === $now['corp_c'] ) ? str_replace( $anchor_old, $anchor_new, $old_content ) : $old_content;
	$upd         = ( 'ready' === $now['corp_c'] ) ? wp_update_post( array( 'ID' => (int) $now['corp_page']->ID, 'post_content' => wp_slash( $new_content ) ), true ) : 1;
	if ( is_wp_error( $upd ) ) {
		$restore_rows( (int) $snap['svc_id'], $now['svc_rows'] );
		WP_CLI::error( 'Could not write the corporate page: ' . $upd->get_error_message() . '; Services put back.' );
	}
	$cg = get_post_meta( (int) $now['corp_page']->ID, 'surerank_settings_general', true );
	\SureRank\Inc\API\Post::update_post_meta_common( (int) $now['corp_page']->ID, array( 'page_title' => (string) ( $cg['page_title'] ?? '' ), 'page_description' => (string) ( $cg['page_description'] ?? '' ),
		'facebook_image_url' => (string) wp_get_attachment_url( $pic->ID ), 'facebook_image_id' => (int) $pic->ID ) );
	$after = $read();
	if ( 'applied' !== $after['state'] || $after['svc_hash'] !== $now['svc_hash'] || $after['corp_hash'] !== hash( 'sha256', $new_content ) ) {
		wp_update_post( array( 'ID' => (int) $snap['corp_id'], 'post_content' => wp_slash( $old_content ) ) );
		$restore_rows( (int) $snap['svc_id'], $now['svc_rows'] );
		$restore_rows( (int) $snap['corp_id'], $now['corp_rows'] );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written (state ' . $after['state'] . '); everything was put back (state now: ' . $check['state'] . ', Services rows identical to before: ' . ( $check['svc_rows'] === $now['svc_rows'] ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Services description now says "in Portland"; the corporate page has its share picture (media #' . $pic->ID . ', set once in SureRank, no copy made, and the same picture is on the page once, with alt text). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['svc_id'] ) || ! isset( $snap['svc_rows'] ) || ! isset( $snap['corp_rows'] ) || empty( $snap['corp_id'] )
	|| (int) $snap['svc_id'] !== (int) $now['svc_page']->ID || (int) $snap['corp_id'] !== (int) $now['corp_page']->ID ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), from another site, or does not match these pages.' );
}
if ( 'applied' !== $state ) {
	WP_CLI::success( 'Nothing to roll back: the pages do not hold exactly the applied state. ' . $report );
	return;
}
$restore_rows( (int) $snap['svc_id'], $snap['svc_rows'] );
$restore_rows( (int) $snap['corp_id'], $snap['corp_rows'] );
$bf0  = isset( $snap['before']['corp_c'] ) ? $snap['before']['corp_c'] : 'ready';
$back = ( 'ready' === $bf0 ) ? str_replace( $anchor_new, $anchor_old, (string) $now['corp_page']->post_content ) : (string) $now['corp_page']->post_content;   // take out only the inserted picture block; other edits stay
wp_update_post( array( 'ID' => (int) $snap['corp_id'], 'post_content' => wp_slash( $back ) ) );
$check = $read();
$bf = isset( $snap['before'] ) && is_array( $snap['before'] ) ? $snap['before'] : array( 'svc' => 'ready', 'corp' => 'ready', 'corp_c' => 'ready' );   // what each part was before that apply
if ( $check['svc'] !== $bf['svc'] || $check['corp'] !== $bf['corp'] || $check['corp_c'] !== $bf['corp_c'] || $check['corp_hash'] !== ( $snap['corp_content_sha256'] ?? '' ) || $check['svc_rows'] !== $snap['svc_rows'] || $check['corp_rows'] !== $snap['corp_rows'] ) {
	WP_CLI::error( 'Rollback did not return to the old state (now: ' . $check['state'] . '; parts now ' . $check['svc'] . '/' . $check['corp'] . '/' . $check['corp_c'] . ' vs before ' . $bf['svc'] . '/' . $bf['corp'] . '/' . $bf['corp_c'] . '; content same as before: ' . ( $check['corp_hash'] === ( $snap['corp_content_sha256'] ?? '' ) ? 'yes' : 'NO' ) . '; Services rows same: ' . ( $check['svc_rows'] === $snap['svc_rows'] ? 'yes' : 'NO' ) . '; Corporate rows same: ' . ( $check['corp_rows'] === $snap['corp_rows'] ? 'yes' : 'NO' ) . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: both pages have exactly their old SureRank rows again and the corporate page content is byte-identical to before.' );

// ref: ../portland-meta/install-portland-meta.php — the SureRank title/description installer this is reduced from (same guard, snapshot and rollback); ../home-card/install-home-corporate-card.php — the "already in the media library" finder for the corporate picture
