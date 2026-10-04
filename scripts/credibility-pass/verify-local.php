<?php
/** Read-only comparison of the packaged page content with localhost WordPress. */

if ( 'http://localhost:8090' !== home_url() || 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'This verifier is for the localhost Thai Solitude practice site only.' );
}

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
$allow_legacy_content = in_array( 'allow-legacy-content', $cli_args, true );
foreach ( array( 298, 296, 299, 1091, 1107, 301 ) as $id ) {
	$page = get_post( $id );
	$source = __DIR__ . '/page-' . $id . '.html';
	if ( ! $page || 'page' !== $page->post_type || ! is_file( $source ) ) {
		WP_CLI::error( 'Page or packaged output missing for ID ' . $id );
	}
	$current = rtrim( $page->post_content, "\r\n" );
	if ( $allow_legacy_content ) {
		$current = preg_replace( '/,"nonces":\{[^{}]*\}/', '', $current );
		if ( 296 === $id ) {
			$current = preg_replace( '/,"authorName":"[^"]+","authorLink":"http:\/\/localhost:8090\/wp-admin\/profile\.php"/', '', $current );
		}
		if ( in_array( $id, array( 1091, 1107 ), true ) ) {
			$current = preg_replace( '/[ \t]+$/m', '', $current );
		}
	}
	$expected = rtrim( file_get_contents( $source ), "\r\n" );
	if ( $current !== $expected && ( ! $allow_legacy_content || $current !== stripslashes( $expected ) ) ) {
		WP_CLI::error( 'Packaged output differs from localhost page ID ' . $id );
	}
	WP_CLI::log( 'Page ' . $id . ': ' . ( $allow_legacy_content ? 'legacy-normalized' : 'exact' ) . ' content match' );
}

WP_CLI::success( 'All six packaged pages match localhost.' );
