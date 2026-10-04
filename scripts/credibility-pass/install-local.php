<?php
/** Apply the reviewed staging12-based credibility edits to localhost only. */

if ( 'http://localhost:8090' !== home_url() || 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'This installer is for the localhost Thai Solitude practice site only.' );
}

$root = __DIR__;
$page_ids = array( 298, 296, 299, 1091, 1107, 301 );
$updates = array();
foreach ( $page_ids as $id ) {
	$page = get_post( $id );
	$file = $root . '/page-' . $id . '.html';
	if ( ! $page || 'page' !== $page->post_type || ! is_file( $file ) ) {
		WP_CLI::error( 'Expected page and edited source missing for ID ' . $id );
	}
	$new_content = rtrim( file_get_contents( $file ), "\r\n" );
	if ( false === strpos( $new_content, 'ts-coming-soon' ) || false === strpos( $new_content, 'uagb-block-' ) ) {
		WP_CLI::error( 'Expected page structure missing for ID ' . $id );
	}
	$current_content = rtrim( $page->post_content, "\r\n" );
	$current_content = preg_replace( '/,"nonces":\{[^{}]*\}/', '', $current_content );
	if ( 296 === $id ) {
		$current_content = preg_replace( '/,"authorName":"[^"]+","authorLink":"http:\/\/localhost:8090\/wp-admin\/profile\.php"/', '', $current_content );
	}
	if ( in_array( $id, array( 1091, 1107 ), true ) ) {
		$current_content = preg_replace( '/[ \t]+$/m', '', $current_content );
	}
	if ( $current_content !== $new_content && $current_content !== stripslashes( $new_content ) ) {
		WP_CLI::error( 'Page ID ' . $id . ' has changed since the reviewed local edit.' );
	}
	$updates[ $id ] = $new_content;
}

$work_page = get_post( 1165 );
if ( ! $work_page || 2 !== substr_count( $work_page->post_content, '>View Résumé</a>' ) || false === strpos( $work_page->post_content, 'LMT-29905' ) ) {
	WP_CLI::error( 'Work With Me résumé links or license number are missing.' );
}
$work_hash = hash( 'sha256', $work_page->post_content );
$settings = get_option( 'astra-settings' );
if ( ! is_array( $settings ) || empty( $settings['footer-copyright-editor'] ) ) {
	WP_CLI::error( 'Astra footer settings are missing.' );
}

$seo = array(
	296 => array(
		'page_description' => 'Thai, therapeutic, and relaxation massage shaped around your goals and comfort in Mt. Tabor, Portland. Meet Butsaba Dusrai, Oregon Licensed Massage Therapist.',
	),
	298 => array(
		'page_title' => 'About Butsaba Dusrai | Oregon Licensed Massage Therapist in Portland',
		'page_description' => 'Meet Butsaba Dusrai, Oregon Licensed Massage Therapist in Portland. Her Thai bodywork, therapeutic massage training, and approach to listening shape Thai Solitude.',
	),
	299 => array(
		'page_title' => 'Massage Services in Mt. Tabor, Portland | Thai Solitude',
		'page_description' => 'Explore Thai, therapeutic, and deep tissue massage with Butsaba Dusrai, Oregon Licensed Massage Therapist. Sessions are shaped around your goals and comfort.',
	),
	301 => array(
		'page_title' => 'Contact Butsaba Dusrai | Oregon Licensed Massage Therapist, Portland',
		'page_description' => 'Contact Butsaba Dusrai, Oregon Licensed Massage Therapist in Portland, to ask about Thai Solitude and upcoming massage sessions. Email butsabad@gmail.com.',
	),
);

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
if ( ! in_array( 'apply-local', $cli_args, true ) ) {
	WP_CLI::success( 'Local preflight passed: 6 page sources, 4 SEO entries, and the license footer were checked.' );
	return;
}

foreach ( $updates as $id => $content ) {
	if ( rtrim( get_post( $id )->post_content, "\r\n" ) === $content ) {
		continue;
	}
	$result = wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $content ) ), true );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( 'Could not update page ID ' . $id . ': ' . $result->get_error_message() );
	}
}

foreach ( $seo as $id => $replacement ) {
	$current = get_post_meta( $id, 'surerank_settings_general', true );
	if ( ! is_array( $current ) ) {
		WP_CLI::error( 'SureRank metadata missing for page ID ' . $id );
	}
	update_post_meta( $id, 'surerank_settings_general', array_merge( $current, $replacement ) );
}

$settings['footer-copyright-editor'] = 'Copyright [copyright] [current_year] [site_title]<br>Butsaba Dusrai · Oregon Licensed Massage Therapist · License No. LMT-29905';
update_option( 'astra-settings', $settings );

if ( $work_hash !== hash( 'sha256', get_post( 1165 )->post_content ) ) {
	WP_CLI::error( 'Work With Me changed unexpectedly.' );
}
wp_cache_flush();
WP_CLI::success( 'Updated only the localhost credibility copy, metadata, and license footer.' );
