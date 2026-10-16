<?php
/**
 * Add a "Hiring a massage therapist?" section to the Contact page (ID 301), localhost only.
 *
 * Why: the header's "Get in Touch" button points to /contact/ on every page, so an employer who follows it from
 * Work With Me used to land on a client-only page. This gives that page an employer route.
 *
 * Modes (first WP-CLI argument):
 *   (none)          read-only preflight; changes nothing
 *   apply-local     insert the reviewed section before the map block
 *   rollback-local  put back the captured before-content (only if the page is still what this installer wrote)
 *
 * Run from the repo root after ./scripts/backup-site.sh --note <why>:
 *   docker compose run --rm -v "$PWD/scripts/work-with-me-employer:/wwm:ro" wpcli wp eval-file /wwm/contact/install-contact-local.php [mode]
 *
 * Storage: post_content only (posts table), so a SiteGround Custom Deploy of posts + postmeta carries it. No MU plugin,
 * no options-table change. The section reuses the .ts-wwm styles already in Additional CSS.
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

// Captured 2026-10-15 before the edit (contact/contact-301-before.html).
$baseline_hash = '5130007f68a1aefbbeb0a8ff17695551327dc62058acc131dd1d6f549fe165c6';
$resume_path = '/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf';
$anchor = '<!-- wp:uagb/container {"block_id":"c6494f04"';   // the full-width map block; the section goes just before it

$page = get_post( 301 );
if ( ! $page || 'page' !== $page->post_type || 'contact' !== $page->post_name || 'publish' !== $page->post_status ) {
	WP_CLI::error( 'Contact (ID 301) was not found as a published page.' );
}

$section = file_get_contents( $root . '/contact-employer-section.html' );
$before = file_get_contents( $root . '/contact-301-before.html' );
if ( false === $section || false === $before ) {
	WP_CLI::error( 'Reviewed source files are missing.' );
}
if ( ! hash_equals( $baseline_hash, hash( 'sha256', $before ) ) ) {
	WP_CLI::error( 'Captured before-content no longer matches its recorded hash.' );
}
$section = rtrim( $section, "\r\n" ) . "\n\n";
if ( 1 !== substr_count( $before, $anchor ) ) {
	WP_CLI::error( 'Expected exactly one map-block anchor in the captured Contact content.' );
}
$new_content = str_replace( $anchor, $section . $anchor, $before );
$new_hash = hash( 'sha256', $new_content );

// Structure and link checks on the new section.
if ( 1 !== substr_count( $section, 'id="employers"' ) || 1 !== preg_match_all( '/<h2[ >]/', $section ) || preg_match( '/<h1[ >]/', $section ) ) {
	WP_CLI::error( 'Section must have one anchor id, one h2 and no h1.' );
}
if ( 1 !== substr_count( $section, 'href="' . $resume_path . '"' ) || ! is_file( ABSPATH . ltrim( $resume_path, '/' ) ) ) {
	WP_CLI::error( 'Résumé link is missing or the file is not in local uploads.' );
}
$mailto = 'mailto:butsabad@gmail.com?subject=LMT%20position%20in%20Portland&amp;body=Hi%20Butsaba%2C%0A%0APractice%3A%20%0ARole%20and%20hours%3A%20%0ABest%20time%20to%20talk%3A%20';
if ( 1 !== substr_count( $section, 'href="' . $mailto . '"' ) || 1 !== substr_count( $section, 'href="mailto:' ) ) {
	WP_CLI::error( 'Expected one email link with the reviewed recipient, subject and message body.' );
}
preg_match_all( '/\bhref="([^"]+)"/', $section, $m );
foreach ( $m[1] as $href ) {
	if ( 0 === strpos( $href, '/' ) && ! in_array( $href, array( '/work-with-me/', $resume_path ), true ) ) {
		WP_CLI::error( 'Unexpected local link: ' . $href );
	}
}

// Regulated-advertising term scan (OBMT list in AGENTS.md) over the new visible copy.
$visible = wp_strip_all_tags( $section );
$forbidden = array(
	'/\bheal/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve pain/i',
	'/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i', '/\bchronic\b/i', '/\badhesions?\b/i',
	'/\bcorrective\b/i', '/root of the restriction/i', '/\baccelerat/i', '/restore balance/i',
	'/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i',
	'/real results/i', '/\bstudio\b/i', '/\b(we|our|us)\b/i', '/Portland[- ]area/i', '/Portland metro/i',
);
foreach ( $forbidden as $pattern ) {
	if ( preg_match( $pattern, $visible, $hit ) ) {
		WP_CLI::error( 'Restricted wording in the reviewed copy: ' . $hit[0] );
	}
}

$current_hash = hash( 'sha256', $page->post_content );
$installed_hash = (string) get_post_meta( $page->ID, '_ts_contact_employer_installed_hash', true );
$is_ours = $current_hash === $new_hash || ( '' !== $installed_hash && $current_hash === $installed_hash );
$report = sprintf( 'page=%s current=%s new=%s ours=%s', $page->ID, substr( $current_hash, 0, 12 ), substr( $new_hash, 0, 12 ), $is_ours ? 'yes' : 'no' );

if ( 'preflight' === $mode ) {
	if ( $current_hash !== $baseline_hash && ! $is_ours ) {
		WP_CLI::error( 'Contact content matches neither the captured baseline nor content this installer wrote (someone changed it). ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

if ( 'apply-local' === $mode ) {
	if ( $current_hash !== $baseline_hash && ! $is_ours ) {
		WP_CLI::error( 'Contact content changed since the captured baseline; stop and review. ' . $report );
	}
	if ( $current_hash !== $new_hash ) {
		$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $new_content ) ), true );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
	}
	update_post_meta( $page->ID, '_ts_contact_employer_installed_hash', $new_hash );
	WP_CLI::success( 'Added the employer section to Contact on localhost: ' . get_permalink( $page->ID ) . '#employers' );
	return;
}

// rollback-local
if ( $current_hash === $baseline_hash ) {
	WP_CLI::error( 'Contact already has the captured before-content.' );
}
if ( ! $is_ours ) {
	WP_CLI::error( 'Contact was changed after this installer wrote it; refusing to overwrite. ' . $report );
}
$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $before ) ), true );
if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result->get_error_message() );
}
delete_post_meta( $page->ID, '_ts_contact_employer_installed_hash' );
WP_CLI::success( 'Rolled Contact back to the captured before-content.' );

// ref: ./contact-employer-section.html — the reviewed block this inserts; ./contact-301-before.html — rollback source
// ref: ../install-local.php — the Work With Me installer this mirrors; ../page-content.html — source of the invitation block it copies
