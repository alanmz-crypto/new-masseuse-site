<?php
/**
 * Update only staging12's existing Work With Me page and install its résumé.
 * Run a read-only preflight without arguments. Pass apply-staging12 only after
 * taking and checking a staging12 database backup.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
if ( 'staging12.thaisolitude.com' !== $host || 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Unexpected site or theme: ' . $host . ', ' . get_stylesheet() );
}

$page = get_page_by_path( 'work-with-me', OBJECT, 'page' );
if ( ! $page || 1165 !== (int) $page->ID || 'publish' !== $page->post_status ) {
	WP_CLI::error( 'The reviewed staging12 Work With Me page was not found.' );
}
if ( ! hash_equals( 'c0d07aa60cfda4df1673a17e6ae8761d894c6bafacb195562ac51477fbf9d3f7', hash( 'sha256', $page->post_content ) ) ) {
	WP_CLI::error( 'Staging12 Work With Me content changed since review.' );
}

$marker = '<!-- wp:group {"className":"ts-wwm"';
if ( 1 !== substr_count( $page->post_content, $marker ) ) {
	WP_CLI::error( 'Expected exactly one Work With Me block.' );
}
$notice = substr( $page->post_content, 0, strpos( $page->post_content, $marker ) );
if ( false === strpos( $notice, 'className":"ts-coming-soon"' ) ) {
	WP_CLI::error( 'The staging12 coming-soon notice is missing.' );
}

$content = file_get_contents( __DIR__ . '/work-with-me-content.html' );
$css = file_get_contents( __DIR__ . '/work-with-me.css' );
$resume = file_get_contents( __DIR__ . '/assets/Butsaba_Dusrai_LMT_Resume_License.pdf' );
if ( false === $content || false === $css || false === $resume
	|| ! hash_equals( '704c54b909e7bcbffa6cfd0c530bb448f2ceb478cffe2e2804a378ba5240de17', hash( 'sha256', $content ) )
	|| ! hash_equals( '6cb1886db58b7deaeec3cd0567fc4b87b3f846c58c6eadceff74e9888802f6f4', hash( 'sha256', $css ) )
	|| ! hash_equals( '6d0dea8e7b19c3577b25c905ebcaae750a97ee7fbe83b12db49920ba446d7d85', hash( 'sha256', $resume ) ) ) {
	WP_CLI::error( 'Transferred Work With Me source or résumé differs from the reviewed local files.' );
}

$resume_path = '/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf';
if ( 2 !== substr_count( $content, 'href="' . $resume_path . '"' )
	|| false === strpos( $content, 'License No. LMT-29905' )
	|| false === strpos( $content, 'href="mailto:butsabad@gmail.com' ) ) {
	WP_CLI::error( 'Résumé, license, or contact link is missing from the reviewed page.' );
}
preg_match_all( '/\bhref="([^"]+)"/', $content, $matches );
foreach ( $matches[1] as $href ) {
	if ( 0 === strpos( $href, '#' ) && 1 !== substr_count( $content, 'id="' . substr( $href, 1 ) . '"' ) ) {
		WP_CLI::error( 'Page anchor is missing: ' . $href );
	}
	if ( 0 === strpos( $href, '/' ) && ! in_array( $href, array( '/', '/contact/', '/services/', $resume_path ), true ) ) {
		WP_CLI::error( 'Unexpected local link: ' . $href );
	}
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

$destination = ABSPATH . ltrim( $resume_path, '/' );
if ( is_file( $destination ) && ! hash_equals( hash( 'sha256', $resume ), hash_file( 'sha256', $destination ) ) ) {
	WP_CLI::error( 'An unrelated PDF already occupies the résumé path.' );
}

$args = isset( $args ) && is_array( $args ) ? $args : array();
if ( ! in_array( 'apply-staging12', $args, true ) ) {
	WP_CLI::success( 'Staging12 preflight passed; page and notice preserved, PDF verified, routes and CSS checked. No changes made.' );
	return;
}

if ( ! wp_mkdir_p( dirname( $destination ) ) ) {
	WP_CLI::error( 'Could not create the staging12 résumé uploads directory.' );
}
if ( ! is_file( $destination ) ) {
	$temp = $destination . '.uploading';
	if ( strlen( $resume ) !== file_put_contents( $temp, $resume )
		|| ! hash_equals( hash( 'sha256', $resume ), hash_file( 'sha256', $temp ) )
		|| ! rename( $temp, $destination ) ) {
		WP_CLI::error( 'Could not install the staging12 résumé PDF.' );
	}
}

$updated_content = $notice . $content;
$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $updated_content ) ), true );
if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result->get_error_message() );
}
$css_result = wp_update_custom_css_post( $updated_css );
if ( is_wp_error( $css_result ) ) {
	WP_CLI::error( $css_result->get_error_message() );
}
update_post_meta( $page->ID, '_ts_wwm_installed_hash', hash( 'sha256', $updated_content ) );
WP_CLI::success( 'Updated staging12 Work With Me and installed the résumé at ' . get_permalink( $page->ID ) );
