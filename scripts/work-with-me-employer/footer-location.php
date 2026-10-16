<?php
/**
 * Footer: replace the "Coming Soon at Thai Solitude" footer panel (footer widget block-7, stored in the OPTIONS table, so no
 * posts+postmeta deploy can carry it) with a single line, "Portland, OR". Ryan, 2026-10-16: "I'm getting rid of 'Coming soon.'
 * ... Change the footer so it just says Portland, OR."
 * The license line and the medical-care disclaimer in the copyright area are NOT touched (the license number is required in
 * every footer).
 *
 * Hosts: localhost:8090, staging26.thaisolitude.com and the throwaway rehearsal restores (8094, 8099) ONLY. This script has no
 * production code path; the live footer is changed by hand (steps in the handoff to Ryan).
 *
 * Modes (first WP-CLI argument; run with --user=1):
 *   (none) / preflight    read-only: is the widget exactly the known panel (or already changed)?
 *   apply                 snapshot the old widget, then write the new one
 *   rollback FILE         put the old widget back if nothing was edited since
 * Remote hosts need WWM_BACKUP_DIR (existing, writable, outside the web root) for the snapshot.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging26.thaisolitude.com', 'staging27.thaisolitude.com', 'localhost:8094', 'localhost:8099' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target.' );
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

$widget_id = 7;
$old_sha = '3d9521bac8c82b1dac3f845f01f9f2d588ed37657347c6cafec7dd9270c47db3';   // the known "Coming Soon" Spectra info-box
$new_content = "<!-- wp:paragraph {\"align\":\"center\",\"className\":\"ts-footer-location\"} -->\n<p class=\"has-text-align-center ts-footer-location\">Portland, OR</p>\n<!-- /wp:paragraph -->";
$new_sha = hash( 'sha256', $new_content );

$sidebars = get_option( 'sidebars_widgets' );
if ( ! isset( $sidebars['footer-widget-1'] ) || array( 'block-' . $widget_id ) !== $sidebars['footer-widget-1'] ) {
	WP_CLI::error( 'Footer widget area 1 does not hold exactly block-' . $widget_id . '; stop and review.' );
}
$blocks = get_option( 'widget_block' );
$current = isset( $blocks[ $widget_id ]['content'] ) ? (string) $blocks[ $widget_id ]['content'] : null;
if ( null === $current ) {
	WP_CLI::error( 'Widget block ' . $widget_id . ' was not found.' );
}
$cur_sha = hash( 'sha256', $current );
$state = $cur_sha === $old_sha ? 'coming-soon-panel' : ( $cur_sha === $new_sha ? 'already-changed' : 'CHANGED' );
$report = sprintf( 'host=%s widget=%s', $home, $state );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The footer widget is neither the known panel nor the new text (someone edited it); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( $backup_dir ) {
	if ( '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
		WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
	}
} elseif ( 'localhost:8090' !== $host ) {
	WP_CLI::error( 'WWM_BACKUP_DIR is required for this host (rollback snapshot).' );
}

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The footer widget differs from the known panel; nothing was changed. ' . $report );
	}
	$file = '';
	if ( $backup_dir ) {
		$file = rtrim( $backup_dir, '/' ) . '/footer-before-' . gmdate( 'Ymd-His' ) . '.json';
		$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'widget_id' => $widget_id, 'old_content' => $current, 'old_sha' => $cur_sha, 'new_sha' => $new_sha );
		if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || ! filesize( $file ) ) {
			WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
		}
	}
	if ( 'coming-soon-panel' === $state ) {
		$blocks[ $widget_id ]['content'] = $new_content;
		update_option( 'widget_block', $blocks );
		$check = get_option( 'widget_block' );
		if ( ( $check[ $widget_id ]['content'] ?? null ) !== $new_content ) {
			WP_CLI::error( 'The widget did not save as expected' . ( $file ? '; run rollback ' . $file : '.' ) );
		}
	}
	WP_CLI::success( 'Footer panel now says "Portland, OR". ' . ( $file ? 'Rollback: rollback ' . $file : '' ) );
	return;
}

// rollback FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || empty( $snap['old_content'] ) || ( $snap['home'] ?? '' ) !== $home || hash( 'sha256', $snap['old_content'] ) !== $old_sha ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
if ( 'already-changed' !== $state ) {
	WP_CLI::error( 'The footer widget is not the text this script wrote; nothing was rolled back. ' . $report );
}
$blocks[ $widget_id ]['content'] = $snap['old_content'];
update_option( 'widget_block', $blocks );
WP_CLI::success( 'Footer widget rolled back to the Coming Soon panel.' );

// ref: ./employer-additions.css — carries the compact-band CSS for the footer through the normal posts deploy
// ref: ../../AGENTS.md — options-table settings are never pushed; widget changes on live are made by hand
