<?php
/**
 * LOCALHOST TRIAL of the Home-page employer path. Ryan, 2026-10-16: "Try this on :8090" (a reviewer's notes on the Home page).
 * Never staging or production: the host allow-list is localhost:8090 and the throwaway rehearsal restore localhost:8099.
 *
 * What it changes (five stores, each snapshotted and reversible):
 *   1. Home page (ID 296) content: a licence line under the H1, the hero button text "Get in Touch" -> "Ask about a session"
 *      (same /contact/ link), and three sentences added to the "Learn About Thai Solitude" paragraph (the Chicago-to-Portland
 *      story, worded as on About, plus her language, from her résumé). A "Hiring a massage therapist?" band under the hero was tried and dropped at Ryan's request (2026-10-16).
 *   2. Additional CSS: one marked block, TS HOME TRIAL (home-trial.css).
 *   3. Home SEO title and description (SureRank grouped row surerank_settings_general). The old description said "restore balance"
 *      (restricted OBMT wording, also copied into the og/twitter descriptions); the new one drops it and matches the hero button.
 *   4. The "Work With Me" menu item label -> "For Practices" (same page and URL).
 *   5. Footer widget block-7 (OPTIONS table): "Portland, OR" plus an email and a "Résumé & experience" link.
 * Stores 4 and 5 are not carried by a posts+postmeta deploy in the same way: 5 lives in options (changed by hand on live).
 *
 * Modes (first WP-CLI argument; run with --user=1):
 *   (none) / preflight    read-only: report each store's state
 *   apply-local           snapshot, then apply all five (undoes the ones already done if one fails)
 *   revert-local FILE     restore every store from the snapshot, if each is still exactly what this installer wrote
 * apply-local and revert-local need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'localhost:8099' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not localhost. This trial has no staging or production path.' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved markup.' );
}

$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply-local', 'revert-local' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

// ---- The reviewed copy ---------------------------------------------------------------------------------------------------
$lookup = 'https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx';
$trust_block = "<!-- wp:paragraph {\"className\":\"ts-home-trust\"} -->\n<p class=\"ts-home-trust\">Oregon License LMT-29905 (<a href=\"" . $lookup . "\">verify</a>) · Mt. Tabor, Portland</p>\n<!-- /wp:paragraph -->";
$footer_links_block = "<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-links\"} -->\n<p class=\"has-text-align-center ts-footer-links\"><a href=\"mailto:butsabad@gmail.com\">butsabad@gmail.com</a> · <a href=\"/work-with-me/\">Résumé &amp; experience</a></p>\n<!-- /wp:paragraph -->";
$footer_old = "<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-location\"} -->\n<p class=\"has-text-align-center ts-footer-location\">Portland, OR</p>\n<!-- /wp:paragraph -->";
$footer_new = $footer_old . "\n\n" . $footer_links_block;
$old_title = 'Thai Solitude | Mt. Tabor, Portland Relaxation & Recovery';
$new_title = 'Thai Solitude | Thai & Therapeutic Massage, Mt. Tabor, Portland';
$old_desc = 'Relaxation, recovery, relief through personalized massage therapy in Mt. Tabor, Portland. Ease tension, restore balance. Book with Butsaba, today.';
$new_desc = 'Personalized Thai, therapeutic and deep tissue massage in Mt. Tabor, Portland. Ease tension and unwind. Ask about a session with Butsaba Dusrai, LMT.';
$story_old = 'I’m Butsaba Dusrai, an Oregon Licensed Massage Therapist, and Thai Solitude is my practice. I trained in traditional Thai bodywork and in massage therapy, and I draw on both.';
$story_add = ' I started Thai Solitude in Chicago in 2024, and I have worked in a busy Chicago massage practice where my clients included hotel guests and business travelers. Now I am bringing Thai Solitude to Portland. I am a native Thai speaker and fluent in English.';
$cta_old = 'Get in Touch';
$cta_new = 'Ask about a session';
$menu_old = 'Work With Me';
$menu_new = 'For Practices';
$widget_id = 7;
$page_id = 296;

// Regulated-advertising term scan over every new visible string (OBMT list in AGENTS.md).
$visible = wp_strip_all_tags( $trust_block . ' ' . $footer_links_block . ' ' . $story_add ) . ' ' . $new_title . ' ' . $new_desc . ' ' . $cta_new . ' ' . $menu_new;
foreach ( array( '/\bheal/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve pain/i', '/pain management/i', '/\bsciatica\b/i',
	'/\bheadaches?\b/i', '/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore balance/i', '/nervous system/i', '/\bcirculation\b/i',
	'/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i', '/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i',
	'/real results/i', '/\bstudio\b/i', '/\b(we|our|us)\b/i', '/Portland[- ]area/i', '/Portland metro/i' ) as $pattern ) {
	if ( preg_match( $pattern, $visible, $hit ) ) {
		WP_CLI::error( 'Restricted wording in the reviewed copy: ' . $hit[0] );
	}
}

// ---- CSS block -----------------------------------------------------------------------------------------------------------
$css_src = file_get_contents( __DIR__ . '/home-trial.css' );
$c_start = '/* TS HOME TRIAL START */';
$c_end = '/* TS HOME TRIAL END */';
$from = false === $css_src ? false : strpos( $css_src, $c_start );
$to = false === $css_src ? false : strpos( $css_src, $c_end );
if ( false === $from || false === $to || 1 !== substr_count( $css_src, $c_start ) || 1 !== substr_count( $css_src, $c_end ) ) {
	WP_CLI::error( 'The trial CSS block or its markers are missing from the package.' );
}
$css_block = substr( $css_src, $from, $to + strlen( $c_end ) - $from );
if ( false !== stripos( $css_block, '!important' ) || false !== stripos( $css_block, '@import' ) ) {
	WP_CLI::error( 'Trial CSS must not use !important or @import (Dark Reader).' );
}
$css_now = wp_get_custom_css();
$css_has = substr_count( $css_now, $c_start );
if ( $css_has > 1 || $css_has !== substr_count( $css_now, $c_end ) ) {
	WP_CLI::error( 'Trial CSS markers are unbalanced in Additional CSS.' );
}
$css_clean = $css_now;
if ( $css_has ) {
	$a = strpos( $css_now, $c_start );
	$b = strpos( $css_now, $c_end ) + strlen( $c_end );
	$css_clean = rtrim( substr( $css_now, 0, $a ) ) . substr( $css_now, $b );
}
$css_new = rtrim( $css_clean ) . "\n\n" . $css_block . "\n";

// ---- Page content --------------------------------------------------------------------------------------------------------
$page = get_post( $page_id );
if ( ! $page || 'page' !== $page->post_type || 'home' !== $page->post_name || 'publish' !== $page->post_status || (int) get_option( 'page_on_front' ) !== $page_id ) {
	WP_CLI::error( 'Home (ID 296) was not found as the published front page.' );
}
$content = $page->post_content;
$h1_text = '<h1 class="uagb-heading-text">Thai & Therapeutic Massage with Butsaba Dusrai, Oregon LMT</h1>';
$anchor_h1 = "<!-- /wp:uagb/advanced-heading -->\n\n<!-- wp:uagb/container {\"block_id\":\"083e68ed\"";
$applied_marks = array( substr_count( $content, 'ts-home-trust' ), substr_count( $content, 'ts-home-employers' ), substr_count( $content, $cta_new ), substr_count( $content, $story_add ) );
if ( array( 0, 0, 0, 0 ) === $applied_marks ) {
	$page_state = 'ready';
	$pos_h1 = strpos( $content, $h1_text );
	if ( 1 !== substr_count( $content, $h1_text ) || 1 !== substr_count( $content, $anchor_h1 )
		|| 1 !== substr_count( $content, '<p class="uagb-ifb-desc">' . $story_old . '</p>') || 2 !== substr_count( $content, $cta_old ) || 1 !== substr_count( $content, '"ctaText":"' . $cta_old . '"' ) || 1 !== substr_count( $content, '<span class="uagb-inline-editing">' . $cta_old . '</span>' )
		|| false === $pos_h1 || strpos( $content, $anchor_h1 ) < $pos_h1 ) {
		$page_state = 'CHANGED';   // not the page this trial was reviewed against
	}
} elseif ( array( 2, 0, 2, 1 ) === $applied_marks && 0 === substr_count( $content, $cta_old ) ) {
	$page_state = 'applied';
} else {
	$page_state = 'CHANGED';
}
$page_new = $content;
if ( 'ready' === $page_state ) {
	$page_new = str_replace( $anchor_h1, "<!-- /wp:uagb/advanced-heading -->\n\n" . $trust_block . "\n\n<!-- wp:uagb/container {\"block_id\":\"083e68ed\"", $page_new );
	$page_new = str_replace( '<p class="uagb-ifb-desc">' . $story_old . '</p>', '<p class="uagb-ifb-desc">' . $story_old . $story_add . '</p>', $page_new );
	$page_new = str_replace( array( '"ctaText":"' . $cta_old . '"', '<span class="uagb-inline-editing">' . $cta_old . '</span>' ),
		array( '"ctaText":"' . $cta_new . '"', '<span class="uagb-inline-editing">' . $cta_new . '</span>' ), $page_new );
}

// ---- SEO title and description (one grouped row; og and twitter descriptions follow the description) ----------------------
$seo_row = get_post_meta( $page_id, 'surerank_settings_general', true );
if ( ! is_array( $seo_row ) || ! isset( $seo_row['page_title'], $seo_row['page_description'] ) ) {
	WP_CLI::error( 'Home has no SureRank page title and description row to update.' );
}
if ( $old_title === $seo_row['page_title'] && $old_desc === $seo_row['page_description'] ) {
	$seo_state = 'ready';
} elseif ( $new_title === $seo_row['page_title'] && $new_desc === $seo_row['page_description'] ) {
	$seo_state = 'applied';
} else {
	$seo_state = 'CHANGED';
}

// ---- Menu item -----------------------------------------------------------------------------------------------------------
$menu_ids = array();
foreach ( (array) wp_get_nav_menu_items( 'primary-menu' ) as $item ) {
	if ( 'page' === $item->object && 1165 === (int) $item->object_id ) {
		$menu_ids[] = (int) $item->ID;
	}
}
if ( 1 !== count( $menu_ids ) ) {
	WP_CLI::error( 'Expected exactly one menu item pointing at Work With Me (1165).' );
}
$menu_id = $menu_ids[0];
// The item stores no title of its own (empty post_title), so the menu shows the linked page's title, "Work With Me".
// Renaming the label sets a title on the item; reverting clears it. The page's own title is not touched.
$menu_title = (string) get_post( $menu_id )->post_title;
$menu_state = ( '' === $menu_title && $menu_old === get_the_title( 1165 ) ) ? 'ready' : ( $menu_new === $menu_title ? 'applied' : 'CHANGED' );

// ---- Footer widget -------------------------------------------------------------------------------------------------------
$sidebars = get_option( 'sidebars_widgets' );
if ( ! isset( $sidebars['footer-widget-1'] ) || array( 'block-' . $widget_id ) !== $sidebars['footer-widget-1'] ) {
	WP_CLI::error( 'Footer widget area 1 does not hold exactly block-' . $widget_id . '.' );
}
$blocks = get_option( 'widget_block' );
$widget_now = isset( $blocks[ $widget_id ]['content'] ) ? (string) $blocks[ $widget_id ]['content'] : null;
if ( null === $widget_now ) {
	WP_CLI::error( 'Widget block ' . $widget_id . ' was not found.' );
}
$widget_state = $widget_now === $footer_old ? 'ready' : ( $widget_now === $footer_new ? 'applied' : 'CHANGED' );

$css_state = $css_has ? ( $css_now === $css_new ? 'applied' : 'differs' ) : 'ready';
$report = sprintf( 'host=%s page=%s css=%s seo=%s menu=%s widget=%s', $home, $page_state, $css_state, $seo_state, $menu_state, $widget_state );
$states = array( $page_state, $seo_state, $menu_state, $widget_state );

if ( 'preflight' === $mode ) {
	if ( in_array( 'CHANGED', $states, true ) ) {
		WP_CLI::error( 'A store is neither ready nor already applied; stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}

if ( 'apply-local' === $mode ) {
	if ( in_array( 'CHANGED', $states, true ) ) {
		WP_CLI::error( 'A store is not in the reviewed state; nothing was changed. ' . $report );
	}
	if ( 4 === count( array_filter( $states, function ( $s ) { return 'applied' === $s; } ) ) && 'applied' === $css_state ) {
		WP_CLI::success( 'Nothing to do: everything is already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/home-trial-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array(
		'created' => gmdate( 'c' ), 'home' => $home,
		'page'    => array( 'id' => $page_id, 'before' => $content, 'after_sha' => hash( 'sha256', $page_new ) ),
		'css'     => array( 'before' => $css_now, 'after_sha' => hash( 'sha256', $css_new ) ),
		'seo'     => array( 'before_row' => $seo_row, 'after_title' => $new_title, 'after_description' => $new_desc ),
		'menu'    => array( 'id' => $menu_id, 'before' => $menu_title, 'after' => $menu_new ),
		'widget'  => array( 'id' => $widget_id, 'before' => $widget_now, 'after_sha' => hash( 'sha256', $footer_new ) ),
	);
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || ! filesize( $file ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	$undo = array();
	$fail = function ( $why ) use ( &$undo ) {
		foreach ( array_reverse( $undo ) as $fn ) {
			$fn();
		}
		WP_CLI::error( $why . ' Everything already written was put back.' );
	};
	if ( 'ready' === $page_state ) {
		$r = wp_update_post( wp_slash( array( 'ID' => $page_id, 'post_content' => $page_new ) ), true );
		$undo[] = function () use ( $page_id, $content ) { wp_update_post( wp_slash( array( 'ID' => $page_id, 'post_content' => $content ) ), true ); };
		if ( is_wp_error( $r ) || get_post( $page_id )->post_content !== $page_new ) {
			$fail( 'The Home page did not save exactly as expected.' );
		}
	}
	if ( 'applied' !== $css_state ) {
		$r = wp_update_custom_css_post( $css_new );
		$undo[] = function () use ( $css_now ) { wp_update_custom_css_post( $css_now ); };
		if ( is_wp_error( $r ) || wp_get_custom_css() !== $css_new ) {
			$fail( 'Additional CSS did not save exactly as expected.' );
		}
	}
	if ( 'ready' === $seo_state ) {
		$row = $seo_row;
		$row['page_title'] = $new_title;
		$row['page_description'] = $new_desc;
		update_post_meta( $page_id, 'surerank_settings_general', $row );
		$undo[] = function () use ( $page_id, $seo_row ) { update_post_meta( $page_id, 'surerank_settings_general', $seo_row ); };
		$check = get_post_meta( $page_id, 'surerank_settings_general', true );
		if ( ! is_array( $check ) || $check['page_title'] !== $new_title || $check['page_description'] !== $new_desc ) {
			$fail( 'The Home SEO title and description did not save as expected.' );
		}
	}
	if ( 'ready' === $menu_state ) {
		$r = wp_update_post( wp_slash( array( 'ID' => $menu_id, 'post_title' => $menu_new ) ), true );
		$undo[] = function () use ( $menu_id, $menu_title ) { wp_update_post( wp_slash( array( 'ID' => $menu_id, 'post_title' => $menu_title ) ), true ); };
		if ( is_wp_error( $r ) || get_post( $menu_id )->post_title !== $menu_new ) {
			$fail( 'The menu label did not save as expected.' );
		}
	}
	if ( 'ready' === $widget_state ) {
		$blocks[ $widget_id ]['content'] = $footer_new;
		update_option( 'widget_block', $blocks );
		$undo[] = function () use ( $widget_id, $widget_now ) { $b = get_option( 'widget_block' ); $b[ $widget_id ]['content'] = $widget_now; update_option( 'widget_block', $b ); };
		$after = get_option( 'widget_block' );
		if ( ( $after[ $widget_id ]['content'] ?? null ) !== $footer_new ) {
			$fail( 'The footer widget did not save as expected.' );
		}
	}
	WP_CLI::success( 'Applied the Home employer-path trial to localhost. Before-snapshot: ' . $file );
	return;
}

// revert-local FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || empty( $snap['page']['before'] ) || ( $snap['home'] ?? '' ) !== $home ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
$problems = array();
if ( 'applied' === $page_state || 'ready' === $page_state ) {
	if ( 'applied' === $page_state && hash( 'sha256', $content ) !== $snap['page']['after_sha'] ) {
		$problems[] = 'Home page content was edited after the trial';
	}
}
if ( 'CHANGED' === $page_state ) {
	$problems[] = 'Home page content is not in a known state';
}
if ( $css_has && hash( 'sha256', $css_now ) !== $snap['css']['after_sha'] ) {
	$problems[] = 'Additional CSS was edited after the trial';
}
if ( 'applied' === $widget_state && hash( 'sha256', $widget_now ) !== $snap['widget']['after_sha'] ) {
	$problems[] = 'the footer widget was edited after the trial';
}
if ( $problems ) {
	WP_CLI::error( 'Nothing was reverted: ' . implode( '; ', $problems ) . '. Restore from the local backup instead.' );
}
wp_update_post( wp_slash( array( 'ID' => $page_id, 'post_content' => $snap['page']['before'] ) ), true );
wp_update_custom_css_post( $snap['css']['before'] );
update_post_meta( $page_id, 'surerank_settings_general', $snap['seo']['before_row'] );
wp_update_post( wp_slash( array( 'ID' => (int) $snap['menu']['id'], 'post_title' => $snap['menu']['before'] ) ), true );
$blocks = get_option( 'widget_block' );
$blocks[ (int) $snap['widget']['id'] ]['content'] = $snap['widget']['before'];
update_option( 'widget_block', $blocks );
$ok = get_post( $page_id )->post_content === $snap['page']['before'] && wp_get_custom_css() === $snap['css']['before']
	&& get_post( (int) $snap['menu']['id'] )->post_title === $snap['menu']['before'];
if ( ! $ok ) {
	WP_CLI::error( 'Reverted, but a store does not read back exactly; compare against the snapshot and the local backup.' );
}
WP_CLI::success( 'Reverted the Home employer-path trial from ' . $file );

// ref: ./home-trial.css — the CSS block applied to Additional CSS; ./verify-home.py — read-only rendered check
// ref: ../install-local.php — the Work With Me installer whose guards and CSS handling this follows
