<?php
/** Guarded title and footer correction for the reviewed Thai Solitude copies. */

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $cli_args[0] ?? 'check';
if ( ! in_array( $mode, array( 'check', 'apply-local', 'apply-staging12' ), true ) ) {
	WP_CLI::error( 'Use check, apply-local, or apply-staging12.' );
}

$home = untrailingslashit( home_url() );
$allowed = array( 'http://localhost:8090', 'https://staging12.thaisolitude.com' );
if ( ! in_array( $home, $allowed, true ) || 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'This script is only for the reviewed localhost or staging12 Astra copy.' );
}
if ( 'apply-local' === $mode && 'http://localhost:8090' !== $home ) {
	WP_CLI::error( 'apply-local requires localhost:8090.' );
}
if ( 'apply-staging12' === $mode && 'https://staging12.thaisolitude.com' !== $home ) {
	WP_CLI::error( 'apply-staging12 requires staging12.' );
}

$titles = array(
	296 => array(
		'page' => 'Home',
		'old' => 'Thai Solitude | Mt. Tabor, Portland Relaxation & Recovery',
		'new' => 'Thai Solitude | Butsaba Dusrai, Oregon Licensed Massage Therapist',
	),
	298 => array(
		'page' => 'About',
		'old' => 'About Butsaba Dusrai | Registered Therapist in Mt. Tabor, Portland',
		'new' => 'About Butsaba Dusrai | Oregon Licensed Massage Therapist in Portland',
	),
	299 => array(
		'page' => 'Services',
		'old' => 'Services Butsaba Dusrai | Pain Relief, Recovery, Relaxation',
		'new' => 'Massage Services in Mt. Tabor, Portland | Thai Solitude',
	),
	301 => array(
		'page' => 'Contact',
		'old' => 'Contact Butsaba Dusrai | Registered Therapist, Mt. Tabor, Portland',
		'new' => 'Contact Butsaba Dusrai | Oregon Licensed Massage Therapist, Portland',
	),
);

$changes = array();
foreach ( $titles as $id => $target ) {
	$post = get_post( $id );
	if ( ! $post || 'page' !== $post->post_type || $target['page'] !== $post->post_title ) {
		WP_CLI::error( 'Unexpected page at ID ' . $id . '; refusing all changes.' );
	}
	$general = get_post_meta( $id, 'surerank_settings_general', true );
	if ( ! is_array( $general ) || ! isset( $general['page_title'] ) ) {
		WP_CLI::error( 'Missing SureRank title for page ' . $id . '; refusing all changes.' );
	}
	$current = $general['page_title'];
	if ( $target['new'] === $current ) {
		WP_CLI::log( $target['page'] . ': title already current.' );
		continue;
	}
	if ( $target['old'] !== $current ) {
		WP_CLI::error( $target['page'] . ': title changed independently; refusing all changes.' );
	}
	$general['page_title'] = $target['new'];
	$changes[] = array( 'type' => 'meta', 'id' => $id, 'value' => $general );
	WP_CLI::log( $target['page'] . ': title -> ' . $target['new'] );
}

$settings = get_option( 'astra-settings' );
$base_footer = 'Copyright [copyright] [current_year] [site_title]';
$license_line = 'Butsaba Dusrai · Oregon Licensed Massage Therapist · License No. LMT-29905';
$desired_footer = $base_footer . '<br>' . $license_line;
if ( ! is_array( $settings ) || ! isset( $settings['footer-copyright-editor'] ) ) {
	WP_CLI::error( 'Missing Astra copyright footer; refusing all changes.' );
}
$current_footer = $settings['footer-copyright-editor'];
if ( $desired_footer === $current_footer ) {
	WP_CLI::log( 'Astra footer already displays the license.' );
} elseif ( $base_footer === $current_footer ) {
	$settings['footer-copyright-editor'] = $desired_footer;
	$changes[] = array( 'type' => 'footer', 'value' => $settings );
	WP_CLI::log( 'Astra footer -> copyright plus Oregon license number.' );
} else {
	WP_CLI::error( 'Astra footer changed independently; refusing all changes.' );
}

if ( 'check' === $mode ) {
	WP_CLI::success( count( $changes ) . ' reviewed change(s) ready; no database writes.' );
	return;
}
foreach ( $changes as $change ) {
	if ( 'meta' === $change['type'] ) {
		if ( false === update_post_meta( $change['id'], 'surerank_settings_general', $change['value'] ) ) {
			WP_CLI::error( 'Could not update SureRank title for page ' . $change['id'] . '.' );
		}
	} else {
		if ( false === update_option( 'astra-settings', $change['value'] ) ) {
			WP_CLI::error( 'Could not update Astra footer.' );
		}
	}
}
WP_CLI::success( count( $changes ) . ' reviewed change(s) applied to ' . $home . '.' );
