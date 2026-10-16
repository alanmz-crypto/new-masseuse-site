<?php
/**
 * One-site update for the reviewed Work With Me page on staging10.
 *
 * Keep staging10's existing coming-soon notice and replace only the Work With
 * Me block content and its marked Additional CSS. Without apply-staging10 this
 * is a read-only preflight. Run only after a staging10 database backup.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
if ( 'staging10.thaisolitude.com' !== $host ) {
	WP_CLI::error( 'Refusing host: ' . $host );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Expected the Astra theme.' );
}

$page = get_page_by_path( 'work-with-me', OBJECT, 'page' );
if ( ! $page || 1165 !== (int) $page->ID || 'publish' !== $page->post_status ) {
	WP_CLI::error( 'The reviewed staging10 Work With Me page was not found.' );
}
$expected_current_hash = '53a10527960a9a8522b67d51dc4cffda021445a21686a462f8e8394a8a90ed23';
if ( ! hash_equals( $expected_current_hash, hash( 'sha256', $page->post_content ) ) ) {
	WP_CLI::error( 'Staging10 page content changed since review; no update made.' );
}

$core_marker = '<!-- wp:group {"className":"ts-wwm"';
if ( 1 !== substr_count( $page->post_content, $core_marker ) ) {
	WP_CLI::error( 'Expected exactly one Work With Me block.' );
}
$core_position = strpos( $page->post_content, $core_marker );
$notice = substr( $page->post_content, 0, $core_position );
if ( false === strpos( $notice, 'className":"ts-coming-soon"' ) ) {
	WP_CLI::error( 'Staging10 coming-soon notice is missing.' );
}

$content = file_get_contents( __DIR__ . '/work-with-me-content.html' );
$css = file_get_contents( __DIR__ . '/work-with-me.css' );
if ( false === $content || false === $css ) {
	WP_CLI::error( 'Reviewed source files are missing.' );
}
if ( ! hash_equals( '19809117d62f05fd470be2744d997e5399a1a59be54a4ac367f25cc2a794c25c', hash( 'sha256', $content ) )
	|| ! hash_equals( '669173a4abd55e5fe04882a20b841f6a035dabd6d07c101aba429b996155184a', hash( 'sha256', $css ) ) ) {
	WP_CLI::error( 'Transferred page source does not match the reviewed local version.' );
}
if ( ! is_file( WP_CONTENT_DIR . '/uploads/2026/09/butsaba_homepage.webp' ) ) {
	WP_CLI::error( 'Butsaba portrait is missing.' );
}
foreach ( array( 'contact', 'services' ) as $route ) {
	$target = get_page_by_path( $route, OBJECT, 'page' );
	if ( ! $target || 'publish' !== $target->post_status
		|| untrailingslashit( wp_parse_url( get_permalink( $target ), PHP_URL_PATH ) ) !== '/' . $route ) {
		WP_CLI::error( 'Expected local link is missing: /' . $route . '/' );
	}
}

preg_match_all( '/\\bhref="([^"]+)"/', $content, $matches );
foreach ( $matches[1] as $href ) {
	if ( 0 === strpos( $href, '#' ) && 1 !== preg_match_all( '/\\bid="' . preg_quote( substr( $href, 1 ), '/' ) . '"/', $content ) ) {
		WP_CLI::error( 'Page anchor is missing: ' . $href );
	}
	if ( 0 === strpos( $href, '/' ) && ! in_array( $href, array( '/', '/contact/', '/services/' ), true ) ) {
		WP_CLI::error( 'Unexpected local link: ' . $href );
	}
}

$custom_css = wp_get_custom_css();
$start = '/* TS WORK WITH ME START */';
$end = '/* TS WORK WITH ME END */';
if ( 1 !== substr_count( $custom_css, $start ) || 1 !== substr_count( $custom_css, $end ) ) {
	WP_CLI::error( 'Expected one complete Work With Me CSS section.' );
}
$start_position = strpos( $custom_css, $start );
$end_position = strpos( $custom_css, $end );
if ( $end_position < $start_position ) {
	WP_CLI::error( 'Work With Me CSS markers are out of order.' );
}
$updated_css = substr( $custom_css, 0, $start_position )
	. trim( $css )
	. substr( $custom_css, $end_position + strlen( $end ) );
$updated_content = $notice . $content;

$args = isset( $args ) && is_array( $args ) ? $args : array();
if ( ! in_array( 'apply-staging10', $args, true ) ) {
	WP_CLI::success( 'Staging10 preflight passed; page=1165, notice=preserved, portrait=present, routes=present, CSS=marked. No changes made.' );
	return;
}

$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $updated_content ) ), true );
if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result->get_error_message() );
}
$css_result = wp_update_custom_css_post( $updated_css );
if ( is_wp_error( $css_result ) ) {
	WP_CLI::error( $css_result->get_error_message() );
}
update_post_meta( $page->ID, '_ts_wwm_installed_hash', hash( 'sha256', $updated_content ) );
WP_CLI::success( 'Updated staging10 Work With Me page at ' . get_permalink( $page->ID ) );
