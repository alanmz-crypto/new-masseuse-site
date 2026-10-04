<?php
/** Disable the unsupported WPCode snippet and clear its compiled local copy. */

if ( 'http://localhost:8090' !== home_url() ) {
	WP_CLI::error( 'This script is restricted to localhost.' );
}
$snippet = get_post( 1023 );
if ( ! $snippet || 'publish' !== $snippet->post_status || 'LocalBusinessInfo' !== $snippet->post_title || false === strpos( $snippet->post_content, '97213' ) ) {
	WP_CLI::error( 'The expected active snippet was not found.' );
}
$compiled = get_option( 'wpcode_snippets' );
if ( ! is_array( $compiled ) || ! isset( $compiled['everywhere'] ) || ! is_array( $compiled['everywhere'] ) ) {
	WP_CLI::error( 'WPCode compiled snippets could not be inspected.' );
}
$filtered = array_values( array_filter( $compiled['everywhere'], static function ( $item ) {
	return 1023 !== (int) ( $item['id'] ?? 0 );
} ) );
if ( count( $filtered ) !== count( $compiled['everywhere'] ) - 1 ) {
	WP_CLI::error( 'Expected exactly one stale compiled LocalBusiness snippet.' );
}
$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
if ( ! in_array( 'apply-local', $cli_args, true ) ) {
	WP_CLI::success( 'Local preflight passed: one stale compiled snippet is ready to clear.' );
	return;
}
$compiled['everywhere'] = $filtered;
global $wpdb;
$changed = $wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => 1023, 'post_status' => 'publish' ), array( '%s' ), array( '%d', '%s' ) );
if ( 1 !== $changed ) {
	WP_CLI::error( 'Could not deactivate the WPCode post.' );
}
clean_post_cache( 1023 );
update_option( 'wpcode_snippets', $compiled );
wp_cache_flush();
WP_CLI::success( 'Cleared the stale compiled LocalBusiness snippet on localhost.' );
