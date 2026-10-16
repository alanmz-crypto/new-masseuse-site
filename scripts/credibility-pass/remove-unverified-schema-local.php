<?php
/** Remove unsupported structured-data claims from the localhost practice site. */

if ( 'http://localhost:8090' !== home_url() || 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'This script is restricted to localhost.' );
}

$snippet = get_post( 1023 );
if ( ! $snippet || 'wpcode' !== $snippet->post_type || 'LocalBusinessInfo' !== $snippet->post_title || false === strpos( $snippet->post_content, '97213' ) ) {
	WP_CLI::error( 'The expected LocalBusinessInfo snippet was not found.' );
}

$schema = get_post_meta( 296, 'surerank_settings_schemas', true );
$schema_id = '32eb489e-b9c3-4aba-be46-1968f1fc2588';
if ( ! is_array( $schema ) || ! isset( $schema['schemas'][ $schema_id ]['fields']['knowsLanguage'] ) || 'Hungarian' !== $schema['schemas'][ $schema_id ]['fields']['knowsLanguage'] ) {
	WP_CLI::error( 'The expected inaccurate language field was not found.' );
}

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
if ( ! in_array( 'apply-local', $cli_args, true ) ) {
	WP_CLI::success( 'Local schema preflight passed: the unverified business snippet and language field can be removed.' );
	return;
}

wp_update_post( array( 'ID' => 1023, 'post_status' => 'draft' ) );
unset( $schema['schemas'][ $schema_id ]['fields']['knowsLanguage'] );
update_post_meta( 296, 'surerank_settings_schemas', $schema );
wp_cache_flush();
WP_CLI::success( 'Disabled the unverified LocalBusiness snippet and removed the inaccurate language field on localhost.' );
