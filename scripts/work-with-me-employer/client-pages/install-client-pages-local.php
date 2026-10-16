<?php
/**
 * LOCALHOST wrap-up of the client-page copy that still carried restricted OBMT wording (Ryan, 2026-10-16: "finish with all human
 * facing elements"). Never staging or production: the host allow-list is localhost:8090 and the throwaway rehearsal restore :8099.
 * The wording was shown to Ryan first (meta descriptions in the 2026-10-16 compliance scan; the rest printed with the result).
 *
 * What it changes (two stores, each snapshotted and reversible):
 *   1. Services (ID 299) SEO title and description (SureRank grouped row surerank_settings_general). The old description said
 *      "relieve pain"; the title said "Relief, Recovery, Relaxation". og/twitter descriptions follow the description.
 *   2. Therapeutic Massage (ID 1107) page content, three exact-string edits:
 *        a. research sentence 1: names no conditions ("several adult groups, including family caregivers"), citations [2][6][7][8] kept;
 *        b. research sentence 2: "stress, anxiety, and quality-of-life" -> "stress and quality-of-life", citations [9][10][5] kept;
 *        c. the tension-headache FAQ is reworded as a neck-and-shoulders question (same place, same block).
 *
 * Modes (first WP-CLI argument; run with --user=1):
 *   (none) / preflight    read-only: report each store's state
 *   apply-local           snapshot, then apply both (undoes the first if the second fails)
 *   revert-local FILE     restore both from the snapshot, if each is still exactly what this installer wrote
 * apply-local and revert-local need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'localhost:8099' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not localhost. This package has no staging or production path.' );
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
$svc_id = 299;
$svc_old_title = 'Services Butsaba Dusrai | Relief, Recovery, Relaxation';
$svc_new_title = 'Massage Services | Butsaba Dusrai, Oregon LMT';
$svc_old_desc = 'Discover therapeutic massage, deep tissue massage, and deep tissue therapy. Treatments to relieve pain and stress and to support recovery.';
$svc_new_desc = 'Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Mt. Tabor, Portland. Every session starts with your goals.';

$ther_id = 1107;
$pm = 'https://pubmed.ncbi.nlm.nih.gov/';
$edits = array(
	'research sentence 1' => array(
		'Research has explored massage among people living with chronic pain, people recovering from stroke with related disability, people with cancer-related symptoms, and family caregivers.<a href="' . $pm . '27165971/">[2]</a><a href="' . $pm . '33919371/">[6]</a><a href="' . $pm . '39558520/">[7]</a><a href="' . $pm . '31003683/">[8]</a>',
		'Research has explored massage in several adult groups, including family caregivers.<a href="' . $pm . '27165971/">[2]</a><a href="' . $pm . '33919371/">[6]</a><a href="' . $pm . '39558520/">[7]</a><a href="' . $pm . '31003683/">[8]</a>',
	),
	'research sentence 2' => array(
		'Research has also examined massage in relation to stress, anxiety, and quality-of-life measures in specific adult groups.<a href="' . $pm . '27901219/">[9]</a>',
		'Research has also examined massage in relation to stress and quality-of-life measures in specific adult groups.<a href="' . $pm . '27901219/">[9]</a>',
	),
	'neck and shoulders FAQ' => array(
		'"question":"Is Massage Okay if I Get Tension Headaches?","answer":"If headaches are part of why you are coming in, mention them at the start. I can work on neck and shoulder tension within the scope of massage. For new, severe, or worrying headaches, please see a doctor first."',
		'"question":"Can I Ask You to Focus on My Neck and Shoulders?","answer":"Yes. Tell me at the start where you would like the focus, and you can change it at any point during the session. For anything new, severe, or worrying, please see a doctor first."',
	),
);

// Regulated-advertising term scan over every new visible string (OBMT list in AGENTS.md, plus "relieve" and "relief").
$visible = $svc_new_title . ' ' . $svc_new_desc;
foreach ( $edits as $pair ) {
	$visible .= ' ' . wp_strip_all_tags( $pair[1] );
}
foreach ( array( '/\bheal/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i',
	'/\bheadaches?\b/i', '/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i',
	'/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i', '/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i',
	'/real results/i', '/\bstudio\b/i', '/\b(we|our|us)\b/i', '/Portland[- ]area/i', '/Portland metro/i' ) as $pattern ) {
	if ( preg_match( $pattern, $visible, $hit ) ) {
		WP_CLI::error( 'Restricted wording in the reviewed copy: ' . $hit[0] );
	}
}

// ---- Services SEO row ----------------------------------------------------------------------------------------------------
$svc = get_post( $svc_id );
if ( ! $svc || 'page' !== $svc->post_type || 'services' !== $svc->post_name || 'publish' !== $svc->post_status ) {
	WP_CLI::error( 'Services (ID 299) was not found as a published page.' );
}
$seo_row = get_post_meta( $svc_id, 'surerank_settings_general', true );
if ( ! is_array( $seo_row ) || ! isset( $seo_row['page_title'], $seo_row['page_description'] ) ) {
	WP_CLI::error( 'Services has no SureRank page title and description row to update.' );
}
if ( $svc_old_title === $seo_row['page_title'] && $svc_old_desc === $seo_row['page_description'] ) {
	$seo_state = 'ready';
} elseif ( $svc_new_title === $seo_row['page_title'] && $svc_new_desc === $seo_row['page_description'] ) {
	$seo_state = 'applied';
} else {
	$seo_state = 'CHANGED';
}

// ---- Therapeutic Massage content -----------------------------------------------------------------------------------------
$ther = get_post( $ther_id );
if ( ! $ther || 'page' !== $ther->post_type || 'therapeutic-massage' !== $ther->post_name || 'publish' !== $ther->post_status ) {
	WP_CLI::error( 'Therapeutic Massage (ID 1107) was not found as a published page.' );
}
$content = $ther->post_content;
$old_hits = 0;
$new_hits = 0;
foreach ( $edits as $pair ) {
	$old_hits += substr_count( $content, $pair[0] );
	$new_hits += substr_count( $content, $pair[1] );
}
if ( count( $edits ) === $old_hits && 0 === $new_hits ) {
	$ther_state = 'ready';
} elseif ( 0 === $old_hits && count( $edits ) === $new_hits ) {
	$ther_state = 'applied';
} else {
	$ther_state = 'CHANGED';   // not the page this was reviewed against, or only partly edited by hand
}
if ( 'ready' === $ther_state ) {
	foreach ( $edits as $pair ) {
		if ( 1 !== substr_count( $content, $pair[0] ) ) {
			$ther_state = 'CHANGED';
		}
	}
}
$ther_new = $content;
if ( 'ready' === $ther_state ) {
	foreach ( $edits as $pair ) {
		$ther_new = str_replace( $pair[0], $pair[1], $ther_new );
	}
}

$report = sprintf( 'host=%s services=%s therapeutic=%s', $home, $seo_state, $ther_state );
$states = array( $seo_state, $ther_state );

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
	if ( 'applied' === $seo_state && 'applied' === $ther_state ) {
		WP_CLI::success( 'Nothing to do: everything is already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/client-pages-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array(
		'created' => gmdate( 'c' ), 'home' => $home,
		'services'    => array( 'id' => $svc_id, 'before_row' => $seo_row, 'after_title' => $svc_new_title, 'after_description' => $svc_new_desc ),
		'therapeutic' => array( 'id' => $ther_id, 'before' => $content, 'after_sha' => hash( 'sha256', $ther_new ) ),
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
	if ( 'ready' === $ther_state ) {
		$r = wp_update_post( wp_slash( array( 'ID' => $ther_id, 'post_content' => $ther_new ) ), true );
		$undo[] = function () use ( $ther_id, $content ) { wp_update_post( wp_slash( array( 'ID' => $ther_id, 'post_content' => $content ) ), true ); };
		if ( is_wp_error( $r ) || get_post( $ther_id )->post_content !== $ther_new ) {
			$fail( 'Therapeutic Massage did not save exactly as expected.' );
		}
	}
	if ( 'ready' === $seo_state ) {
		$row = $seo_row;
		$row['page_title'] = $svc_new_title;
		$row['page_description'] = $svc_new_desc;
		update_post_meta( $svc_id, 'surerank_settings_general', $row );
		$undo[] = function () use ( $svc_id, $seo_row ) { update_post_meta( $svc_id, 'surerank_settings_general', $seo_row ); };
		$check = get_post_meta( $svc_id, 'surerank_settings_general', true );
		if ( ! is_array( $check ) || $check['page_title'] !== $svc_new_title || $check['page_description'] !== $svc_new_desc ) {
			$fail( 'The Services SEO title and description did not save as expected.' );
		}
	}
	WP_CLI::success( 'Applied the client-page wording to localhost. Before-snapshot: ' . $file );
	return;
}

// revert-local FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || empty( $snap['therapeutic']['before'] ) || empty( $snap['services']['before_row'] ) || ( $snap['home'] ?? '' ) !== $home ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
$problems = array();
if ( 'applied' === $ther_state && hash( 'sha256', $content ) !== $snap['therapeutic']['after_sha'] ) {
	$problems[] = 'Therapeutic Massage was edited after this package wrote it';
}
if ( 'CHANGED' === $ther_state ) {
	$problems[] = 'Therapeutic Massage is not in a known state';
}
if ( 'CHANGED' === $seo_state ) {
	$problems[] = 'the Services SEO row is not in a known state';
}
if ( $problems ) {
	WP_CLI::error( 'Nothing was reverted: ' . implode( '; ', $problems ) . '. Restore from the local backup instead.' );
}
wp_update_post( wp_slash( array( 'ID' => $ther_id, 'post_content' => $snap['therapeutic']['before'] ) ), true );
update_post_meta( $svc_id, 'surerank_settings_general', $snap['services']['before_row'] );
if ( get_post( $ther_id )->post_content !== $snap['therapeutic']['before'] || get_post_meta( $svc_id, 'surerank_settings_general', true ) !== $snap['services']['before_row'] ) {
	WP_CLI::error( 'Reverted, but a store does not read back exactly; compare against the snapshot and the local backup.' );
}
WP_CLI::success( 'Reverted the client-page wording from ' . $file );

// ref: ./verify-client-pages.py — read-only rendered check; ../home/install-home-local.php — the Home package this one is modelled on
// ref: ../../credibility-pass/ — the earlier reviewed research-sentence work whose citations these edits keep
