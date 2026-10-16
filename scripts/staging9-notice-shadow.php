<?php
/** Add spacing and a soft shadow to the staging9 coming-soon notice. */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Run with WP-CLI.' );
}

$host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
if ( 'staging9.thaisolitude.com' !== $host ) {
	WP_CLI::error( 'Only staging9.thaisolitude.com may be changed; found ' . $host );
}

$css = wp_get_custom_css();
if ( ! hash_equals( 'e9376403e1944e308a5a06955a3aac5b72eb6adbb7ee4813750ba6f0f1c7e7a2', hash( 'sha256', $css ) ) ) {
	WP_CLI::error( 'Additional CSS changed since review; inspect it before applying.' );
}

$changes = array(
	'  margin: 24px auto 0 !important;' => '  margin: 24px auto 12px !important;',
	'  background: #eaf7eb;' => "  background: #eaf7eb;\n  box-shadow: 0 8px 22px rgba(24, 58, 31, 0.14);",
	'  .ts-coming-soon { margin: 16px 20px 0 !important; padding: 13px 16px; }' => '  .ts-coming-soon { margin: 16px 20px 12px !important; padding: 13px 16px; }',
);

$updated_css = $css;
foreach ( $changes as $before => $after ) {
	if ( 1 !== substr_count( $updated_css, $before ) ) {
		WP_CLI::error( 'Expected notice CSS rule missing or duplicated: ' . $before );
	}
	$updated_css = str_replace( $before, $after, $updated_css );
}

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
if ( ! in_array( 'apply-staging9', $cli_args, true ) ) {
	WP_CLI::success( 'Read-only preflight passed for notice shadow and spacing. No changes made.' );
	return;
}

$result = wp_update_custom_css_post( $updated_css );
if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result->get_error_message() );
}
WP_CLI::success( 'Staging9 notice shadow and spacing updated.' );
