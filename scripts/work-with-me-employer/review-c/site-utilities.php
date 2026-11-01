<?php
// Bundle C utilities. The mailbox was explicitly approved by Ryan, 2026-11-01.
// Stored as a WPCode PHP snippet; no theme/plugin or web-root files are written.
add_action( 'init', function () {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
} );

add_filter( 'get_search_form', function ( $form ) {
	static $added = false;
	if ( is_admin() || ! is_404() || $added ) { return $form; }
	$added = true;
	$links = array( '/' => 'Home', '/services/' => 'Services', '/corporate-massage-portland/' => 'Corporate & Group Massage', '/contact/' => 'Contact' );
	$html = '<nav class="ts-review-404" aria-label="Browse Thai Solitude"><ul>';
	foreach ( $links as $path => $label ) { $html .= '<li><a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $label ) . '</a></li>'; }
	return $html . '</ul></nav>' . $form;
} );
add_action( 'loop_start', function ( $query ) {
	static $added = false;
	if ( is_admin() || ! is_search() || ! $query->is_main_query() || $added ) { return; }
	$added = true;
	echo '<div class="ts-review-search">' . get_search_form( false ) . '</div>';
} );
add_filter( 'astra_post_read_more', function ( $text ) {
	return is_search() ? 'View page &raquo;' : $text;
} );

// RFC 9116 endpoint, without a rewrite rule, a flush or a new server file.
// Fixed expiry: review this contact/expiry before 2027-10-31.
add_action( 'template_redirect', function () {
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	$expected = wp_parse_url( home_url( '/.well-known/security.txt' ), PHP_URL_PATH );
	if ( $path !== $expected ) { return; }
	$method = strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' );
	if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) { status_header( 405 ); header( 'Allow: GET, HEAD' ); exit; }
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	header( 'X-Robots-Tag: noindex' );
	if ( 'HEAD' !== $method ) {
		$scheme = 'localhost' === wp_parse_url( home_url(), PHP_URL_HOST ) ? 'http' : 'https';
		echo "Contact: mailto:butsabad@gmail.com\nExpires: 2027-10-31T23:59:59Z\nCanonical: " . set_url_scheme( home_url( '/.well-known/security.txt' ), $scheme ) . "\n";
	}
	exit;
}, -100 );
