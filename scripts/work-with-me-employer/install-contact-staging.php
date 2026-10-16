<?php
/**
 * Add the "Hiring a massage therapist?" section to the Contact page (ID 301) on a STAGING site. Never production.
 *
 * Why: the header's "Get in Touch" button points to /contact/ on every page, so an employer who follows it from Work With Me
 * used to land on a client-only page. This gives that page an employer route. Ryan approved pushing it (2026-10-16) once its
 * spacing was balanced; the spacing CSS travels with the employer CSS block (push-staging26.sh), so push that FIRST.
 *
 * Hosts: staging26.thaisolitude.com, staging27.thaisolitude.com and the throwaway rehearsal restores (8094, 8099) only.
 *
 * Modes (first WP-CLI argument; run with --user=1 so WordPress does not filter the saved page markup):
 *   (none) / preflight    read-only: is the page ready (map block present, no section yet) or already done?
 *   apply                 snapshot the page, insert the reviewed section just before the map block, verify the saved content
 *   rollback FILE         restore the snapshot if the page is still exactly what apply wrote
 *
 * Surgical by design: only the section is inserted; everything else on the page is untouched, so edits made on the staging
 * site by hand survive. A page that already has a DIFFERENT "employers" section, or lacks the map block, stops the run.
 * Remote hosts need WWM_BACKUP_DIR (existing, writable, outside the web root) for the snapshot.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'staging26.thaisolitude.com', 'staging27.thaisolitude.com', 'localhost:8094', 'localhost:8099' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed staging target.' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page markup.' );
}

$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$resume_path = '/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf';
$anchor = '<!-- wp:uagb/container {"block_id":"c6494f04"';   // the full-width map block; the section goes just before it
$mailto = 'mailto:butsabad@gmail.com?subject=LMT%20position%20in%20Portland&amp;body=Hi%20Butsaba%2C%0A%0APractice%3A%20%0ARole%20and%20hours%3A%20%0ABest%20time%20to%20talk%3A%20';

$page = get_post( 301 );
if ( ! $page || 'page' !== $page->post_type || 'contact' !== $page->post_name || 'publish' !== $page->post_status ) {
	WP_CLI::error( 'Contact (ID 301) was not found as a published page.' );
}

$raw = file_get_contents( __DIR__ . '/contact/contact-employer-section.html' );
if ( false === $raw ) {
	WP_CLI::error( 'The reviewed section file is missing from the package.' );
}
$section = rtrim( $raw, "\r\n" ) . "\n\n";

// Structure, link and wording checks on the reviewed section (same guards as the local installer).
if ( 1 !== substr_count( $section, 'id="employers"' ) || 1 !== preg_match_all( '/<h2[ >]/', $section ) || preg_match( '/<h1[ >]/', $section ) ) {
	WP_CLI::error( 'Section must have one anchor id, one h2 and no h1.' );
}
if ( 1 !== substr_count( $section, 'href="' . $resume_path . '"' ) || ! is_file( ABSPATH . ltrim( $resume_path, '/' ) ) ) {
	WP_CLI::error( 'Résumé link is missing, or the file is not on this site.' );
}
if ( 1 !== substr_count( $section, 'href="' . $mailto . '"' ) || 1 !== substr_count( $section, 'href="mailto:' ) ) {
	WP_CLI::error( 'Expected one email link with the reviewed recipient, subject and message body.' );
}
if ( false !== strpos( $section, 'aria-label' ) || false !== strpos( $section, 'width=' ) ) {
	WP_CLI::error( 'Section carries an attribute the block editor would flag as invalid.' );
}
preg_match_all( '/\bhref="([^"]+)"/', $section, $m );
foreach ( $m[1] as $href ) {
	if ( 0 === strpos( $href, '/' ) && ! in_array( $href, array( '/work-with-me/', $resume_path ), true ) ) {
		WP_CLI::error( 'Unexpected local link: ' . $href );
	}
}
$visible = wp_strip_all_tags( $section );
foreach ( array( '/\bheal/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve pain/i', '/pain management/i', '/\bsciatica\b/i',
	'/\bheadaches?\b/i', '/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore balance/i', '/nervous system/i', '/\bcirculation\b/i',
	'/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i', '/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i',
	'/real results/i', '/\bstudio\b/i', '/\b(we|our|us)\b/i', '/Portland[- ]area/i', '/Portland metro/i' ) as $pattern ) {
	if ( preg_match( $pattern, $visible, $hit ) ) {
		WP_CLI::error( 'Restricted wording in the reviewed copy: ' . $hit[0] );
	}
}

$content = $page->post_content;
if ( false !== strpos( $content, 'id="employers"' ) ) {
	$state = false !== strpos( $content, rtrim( $section ) ) ? 'already-applied' : 'CHANGED';   // a different employers section: someone edited it
} elseif ( 1 === substr_count( $content, $anchor ) ) {
	$state = 'ready';
} else {
	$state = 'CHANGED';   // the map block is missing or duplicated: not the page this installer was reviewed against
}
$new_content = 'ready' === $state ? str_replace( $anchor, $section . $anchor, $content ) : $content;
$report = sprintf( 'host=%s page=%d state=%s', $home, $page->ID, $state );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Contact is neither ready nor already carrying the reviewed section; stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Contact is not in the reviewed state; nothing was changed. ' . $report );
	}
	if ( 'already-applied' === $state ) {
		WP_CLI::success( 'Nothing to do: the section is already on Contact. ' . $report );
		return;
	}
	$after_hash = hash( 'sha256', $new_content );
	$file = rtrim( $backup_dir, '/' ) . '/contact-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'page_id' => $page->ID, 'before_content' => $content, 'before_hash' => hash( 'sha256', $content ), 'after_hash' => $after_hash );
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || ! filesize( $file ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	$r = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $new_content ) ), true );
	if ( is_wp_error( $r ) || get_post( $page->ID )->post_content !== $new_content ) {
		wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );   // put the page back exactly as it was
		WP_CLI::error( 'Contact did not save exactly as expected; the original content was restored.' );
	}
	update_post_meta( $page->ID, '_ts_contact_employer_installed_hash', $after_hash );
	WP_CLI::success( 'Added the employer section to Contact. Before-snapshot: ' . $file . ' | rollback: rollback ' . $file );
	return;
}

// rollback FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || empty( $snap['before_content'] ) || empty( $snap['after_hash'] ) || ( $snap['home'] ?? '' ) !== $home ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
if ( hash( 'sha256', $content ) !== $snap['after_hash'] ) {
	WP_CLI::error( 'Contact was edited after this installer wrote it; nothing was rolled back. Restore from the database export instead.' );
}
$r = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $snap['before_content'] ) ), true );
if ( is_wp_error( $r ) || get_post( $page->ID )->post_content !== $snap['before_content'] ) {
	WP_CLI::error( 'Could not restore Contact exactly; restore from the database export.' );
}
delete_post_meta( $page->ID, '_ts_contact_employer_installed_hash' );
WP_CLI::success( 'Rolled Contact back to the before-snapshot content.' );

// ref: ./contact/contact-employer-section.html — the reviewed block this inserts; ./contact/install-contact-local.php — the localhost version
// ref: ./push-contact-staging.sh — runs this on a staging site; ./employer-additions.css — carries the spacing CSS for this section
