<?php
/**
 * Employer-focused Work With Me update, localhost only (page 1165).
 *
 * Modes (pass as the first WP-CLI argument):
 *   (none)          read-only preflight; changes nothing
 *   apply-local     write the reviewed page content, Additional CSS block and SEO fields
 *   rollback-local  put back the captured before-content and remove the CSS block and SEO fields
 *
 * Run from the repo root after ./scripts/backup-site.sh --note <why>:
 *   docker compose run --rm -v "$PWD/scripts/work-with-me-employer:/wwm:ro" wpcli wp eval-file /wwm/install-local.php [mode]
 *
 * Storage rule: everything lands in WordPress's own tables (post_content, custom_css,
 * SureRank post meta) so a SiteGround stage-to-live push carries it. No MU plugin.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}
if ( 'http://localhost:8090' !== home_url() || 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'This installer is for the localhost Thai Solitude practice site only.' );
}

$root = __DIR__;
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply-local', 'rollback-local' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

// Values captured on 2026-10-15 before the edit (see baseline/).
$baseline_hash = '59df9c7f2456f1844c112eb3dba20715f9fb39535eb8ae80cdb7cf62a59a8912';
$baseline_installed_hash = '1db39c2972100d85887c812378a2ed2b77bd092ec30afb47c3f81cde5ba53eea';
$resume_path = '/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf';

// SureRank per-page SEO fields. Written through the plugin's own saver so its grouped storage
// (surerank_settings_general / _social) stays valid; Twitter follows Facebook by default.
$seo_title = 'Butsaba Dusrai, LMT | Thai Bodywork | Portland, OR';
$seo_description = 'Butsaba Dusrai, Oregon LMT-29905, is based in Portland and open to full-time or part-time roles. Explore her Thai and deep tissue massage experience.';
$meta = array(
	'page_title'           => $seo_title,
	'page_description'     => $seo_description,
	'facebook_title'       => $seo_title,
	'facebook_description' => $seo_description,
);
// The page had none of these before the edit, so rollback deletes them. The first four
// are flat keys an earlier draft of this installer wrote by mistake; they are cleaned up too.
$seo_meta_keys = array(
	'surerank_settings_general', 'surerank_settings_social', 'surerank_settings_schemas',
	'surerank_settings_post_no_index', 'surerank_settings_post_no_follow', 'surerank_settings_post_no_archive',
	'surerank_settings_page_title', 'surerank_settings_page_description',
	'surerank_settings_facebook_title', 'surerank_settings_facebook_description',
);
if ( ! class_exists( '\SureRank\Inc\API\Post' ) || ! method_exists( '\SureRank\Inc\API\Post', 'update_post_meta_common' ) ) {
	WP_CLI::error( 'SureRank page-level SEO API is not available.' );
}

$page = get_post( 1165 );
if ( ! $page || 'page' !== $page->post_type || 'work-with-me' !== $page->post_name || 'publish' !== $page->post_status ) {
	WP_CLI::error( 'Work With Me (ID 1165) was not found as a published page.' );
}

$new_content = file_get_contents( $root . '/page-content.html' );
$before_content = file_get_contents( $root . '/baseline/page-1165-before.html' );
$additions = file_get_contents( $root . '/employer-additions.css' );
if ( false === $new_content || false === $before_content || false === $additions ) {
	WP_CLI::error( 'Reviewed source files are missing.' );
}
$new_content = rtrim( $new_content, "\r\n" );
if ( ! hash_equals( $baseline_hash, hash( 'sha256', $before_content ) ) ) {
	WP_CLI::error( 'Captured before-content no longer matches its recorded hash.' );
}
$new_hash = hash( 'sha256', $new_content );

// Ryan approved removing the top coming-soon banner from this page only (2026-10-15, asked twice).
// The banner is the first block of the captured before-content; rollback-local restores it.
// The reviewed content must start at the Work With Me group and contain no banner.
if ( 0 !== strpos( $before_content, '<!-- wp:paragraph {"className":"ts-coming-soon"} -->' ) ) {
	WP_CLI::error( 'Captured before-content no longer starts with the banner block.' );
}
if ( false !== strpos( $new_content, 'ts-coming-soon' ) || 0 !== strpos( $new_content, '<!-- wp:group {"className":"ts-wwm"' ) ) {
	WP_CLI::error( 'Reviewed content must start at the Work With Me group with no coming-soon banner.' );
}

// Structure and link checks on the reviewed content.
// Three résumé links: hero button, closing button, mobile bar. Five email links: hero button, hero address, closing
// button, closing address, mobile bar. Every email link carries the same prefilled message body.
$mailto = 'mailto:butsabad@gmail.com?subject=LMT%20position%20in%20Portland&amp;body=Hi%20Butsaba%2C%0A%0APractice%3A%20%0ARole%20and%20hours%3A%20%0ABest%20time%20to%20talk%3A%20';
if ( 3 !== substr_count( $new_content, 'href="' . $resume_path . '"' )
	|| 1 !== substr_count( $new_content, 'License No. LMT-29905' ) ) {
	WP_CLI::error( 'Résumé link or license number is missing from the reviewed page.' );
}
if ( 5 !== substr_count( $new_content, 'href="' . $mailto . '"' ) || substr_count( $new_content, 'href="mailto:' ) !== 5 ) {
	WP_CLI::error( 'Expected five email links, all with the reviewed recipient, subject and message body.' );
}
if ( 1 !== preg_match_all( '/<h1[ >]/', $new_content ) ) {
	WP_CLI::error( 'Expected exactly one h1.' );
}
preg_match_all( '/\bhref="([^"]+)"/', $new_content, $matches );
foreach ( $matches[1] as $href ) {
	if ( 0 === strpos( $href, '#' ) && 1 !== substr_count( $new_content, 'id="' . substr( $href, 1 ) . '"' ) ) {
		WP_CLI::error( 'Page anchor is missing: ' . $href );
	}
	if ( 0 === strpos( $href, '/' ) && ! in_array( $href, array( '/', '/contact/', '/services/', $resume_path ), true ) ) {
		WP_CLI::error( 'Unexpected local link: ' . $href );
	}
}
if ( ! is_file( WP_CONTENT_DIR . '/uploads/2026/09/butsaba_homepage.webp' ) || ! is_file( ABSPATH . ltrim( $resume_path, '/' ) ) ) {
	WP_CLI::error( 'Portrait or résumé file is missing from local uploads.' );
}

// Thai signature section image: Ryan's supplied WebP, copied unaltered into uploads (checksum-pinned, staged before any DB write).
$thai_img_rel = '/wp-content/uploads/2026/10/thai-massage-therapy-session-cutout.webp';
$thai_img_sha = '2f2fc1b345e6bfbf5bfec7006422af17841d6b905e16489318341c188c46d545';
$thai_img_src = $root . '/images/thai-massage-therapy-session-cutout.webp';
$thai_img_dst = ABSPATH . ltrim( $thai_img_rel, '/' );
if ( 1 !== substr_count( $new_content, 'src="' . $thai_img_rel . '"' ) ) {
	WP_CLI::error( 'Expected exactly one Thai section image in the reviewed content.' );
}
if ( ! is_file( $thai_img_src ) || ! hash_equals( $thai_img_sha, hash_file( 'sha256', $thai_img_src ) ) ) {
	WP_CLI::error( 'Packaged Thai section image is missing or is not the reviewed file.' );
}
if ( is_file( $thai_img_dst ) && ! hash_equals( $thai_img_sha, hash_file( 'sha256', $thai_img_dst ) ) ) {
	WP_CLI::error( 'A different file already exists at the Thai section image path; stop and review.' );
}

// Regulated-advertising term scan (OBMT list in AGENTS.md) across visible copy and SEO strings.
$visible = wp_strip_all_tags( $new_content ) . ' ' . $seo_title . ' ' . $seo_description;
$forbidden = array(
	'/\bheal/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve pain/i',
	'/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i', '/\bchronic\b/i', '/\badhesions?\b/i',
	'/\bcorrective\b/i', '/root of the restriction/i', '/\baccelerat/i', '/restore balance/i',
	'/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i',
	'/real results/i', '/\bstudio\b/i', '/\b(we|our|us)\b/i', '/Thai Lotus/i', '/October 28/i',
	// The search is Portland city only. "Treatment planning" stays as Estelle education (Ryan, Oct 15).
	'/Portland[- ]area/i', '/Portland metro/i',
);
foreach ( $forbidden as $pattern ) {
	if ( preg_match( $pattern, $visible, $hit ) ) {
		WP_CLI::error( 'Restricted wording in the reviewed copy: ' . $hit[0] );
	}
}

// Additional CSS: one Work With Me section, one optional employer block inside it.
$custom_css = wp_get_custom_css();
$sec_start = '/* TS WORK WITH ME START */';
$sec_end = '/* TS WORK WITH ME END */';
$emp_start = '/* TS WWM EMPLOYER START */';
$emp_end = '/* TS WWM EMPLOYER END */';
if ( 1 !== substr_count( $custom_css, $sec_start ) || 1 !== substr_count( $custom_css, $sec_end )
	|| strpos( $custom_css, $sec_end ) < strpos( $custom_css, $sec_start ) ) {
	WP_CLI::error( 'Expected one complete Work With Me CSS section.' );
}
$has_block = substr_count( $custom_css, $emp_start );
if ( $has_block > 1 || $has_block !== substr_count( $custom_css, $emp_end ) ) {
	WP_CLI::error( 'Employer CSS block markers are unbalanced.' );
}
$block_from = strpos( $additions, $emp_start );
$block_to = strpos( $additions, $emp_end );
if ( false === $block_from || false === $block_to || 1 !== substr_count( $additions, $emp_start ) ) {
	WP_CLI::error( 'Employer CSS source markers are missing.' );
}
$block = substr( $additions, $block_from, $block_to + strlen( $emp_end ) - $block_from );
// WordPress core forces margin-left/right:auto !important on constrained-group children, so the left-aligned sections
// need these two margin declarations (and only these) to use !important. Colours and @import stay banned (Dark Reader).
$block_check = str_replace( array( 'margin-left:0!important', 'margin-right:auto!important' ), '', $block );
if ( false !== stripos( $block_check, '!important' ) || false !== stripos( $block_check, '@import' ) ) {
	WP_CLI::error( 'Employer CSS must not use !important (except the two margin overrides) or @import (Dark Reader).' );
}
$css_without_block = $custom_css;
if ( $has_block ) {
	$from = strpos( $custom_css, $emp_start );
	$to = strpos( $custom_css, $emp_end ) + strlen( $emp_end );
	$css_without_block = rtrim( substr( $custom_css, 0, $from ), " \t" ) . ltrim( substr( $custom_css, $to ), "\r\n" );
}

$current_hash = hash( 'sha256', $page->post_content );
// "Ours" = the content is exactly what an earlier apply of this installer wrote (its recorded
// hash), so replacing or reverting it cannot overwrite someone else's edit.
$installed_hash = (string) get_post_meta( $page->ID, '_ts_wwm_installed_hash', true );
$is_ours = $current_hash === $new_hash
	|| ( $current_hash === $installed_hash && $installed_hash !== $baseline_installed_hash && $current_hash !== $baseline_hash );
$report = sprintf( 'page=%s current=%s new=%s ours=%s css_block=%s', $page->ID, substr( $current_hash, 0, 12 ), substr( $new_hash, 0, 12 ), $is_ours ? 'yes' : 'no', $has_block ? 'present' : 'absent' );

if ( 'preflight' === $mode ) {
	if ( $current_hash !== $baseline_hash && ! $is_ours ) {
		WP_CLI::error( 'Page content matches neither the captured baseline nor content this installer wrote (someone changed it). ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

if ( 'apply-local' === $mode ) {
	if ( $current_hash !== $baseline_hash && ! $is_ours ) {
		WP_CLI::error( 'Page content changed since the captured baseline; stop and review. ' . $report );
	}
	// Stage the image first so a failure leaves the page untouched.
	if ( ! is_file( $thai_img_dst ) ) {
		if ( ! wp_mkdir_p( dirname( $thai_img_dst ) ) || ! copy( $thai_img_src, $thai_img_dst ) || ! hash_equals( $thai_img_sha, hash_file( 'sha256', $thai_img_dst ) ) ) {
			WP_CLI::error( 'Could not stage the Thai section image; nothing else was changed.' );
		}
	}
	$updated_css = $css_without_block;
	$updated_css = str_replace( $sec_end, $block . "\n" . $sec_end, $updated_css );
	if ( $current_hash !== $new_hash ) {
		$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $new_content ) ), true );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
	}
	$css_result = wp_update_custom_css_post( $updated_css );
	if ( is_wp_error( $css_result ) ) {
		WP_CLI::error( $css_result->get_error_message() );
	}
	foreach ( $seo_meta_keys as $key ) {
		delete_post_meta( $page->ID, $key );
	}
	\SureRank\Inc\API\Post::update_post_meta_common( $page->ID, $meta );
	update_post_meta( $page->ID, '_ts_wwm_installed_hash', $new_hash );
	WP_CLI::success( 'Applied employer-focused Work With Me to localhost: ' . get_permalink( $page->ID ) );
	return;
}

// rollback-local
if ( $current_hash === $baseline_hash ) {
	WP_CLI::error( 'Page already has the captured before-content.' );
}
if ( ! $is_ours ) {
	WP_CLI::error( 'Page was edited after this installer wrote it; roll back by hand from the backup snapshot instead. ' . $report );
}
$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $before_content ) ), true );
if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result->get_error_message() );
}
$css_result = wp_update_custom_css_post( $css_without_block );
if ( is_wp_error( $css_result ) ) {
	WP_CLI::error( $css_result->get_error_message() );
}
foreach ( $seo_meta_keys as $key ) {
	delete_post_meta( $page->ID, $key );
}
update_post_meta( $page->ID, '_ts_wwm_installed_hash', $baseline_installed_hash );
WP_CLI::success( 'Rolled Work With Me back to the captured before-content.' );

// ref: ./page-content.html, ./employer-additions.css, ./baseline/page-1165-before.html — sources this installer applies and reverses
// ref: ../../AGENTS.md — host guard, no-MU-plugin storage rule, restricted-wording list
// src: ../install-work-with-me-staging12.php — drift-guard and CSS-section pattern reused here
