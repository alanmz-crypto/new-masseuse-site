<?php
/**
 * One-time availability copy update for staging9.thaisolitude.com.
 *
 * After backing up staging9's database, run with WP-CLI. No argument performs
 * a read-only preflight; apply-staging9 writes the reviewed changes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Run with WP-CLI.' );
}

$host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
if ( 'staging9.thaisolitude.com' !== $host ) {
	WP_CLI::error( 'This update is restricted to staging9.thaisolitude.com; found ' . $host );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Expected Astra theme.' );
}

$expected_hashes = array(
	'home'                => '50d56996d162ee033f279fdd7df543263e6fd1e10959e2fc95c38c76fc12f36e',
	'about'               => '9475901ed33083eecc1b9009d7a5a34ee9d8449f970191976ef35b596efbb77a',
	'services'            => '7b002797a14645b8812c5d18b89ae2505d3cbd34bc5ab708f37d9061d446a936',
	'contact'             => '45e1f29b536a6b78a17be6b41e8dc821e39728f775e1805fd5c1435f5a9f3f1a',
	'therapeutic-massage' => '4be7e15b89750ac6f4e2ceef55dfbd60ddbef7f32bf5019375a12e00a4d6d50d',
	'deep-tissue-massage' => 'a704eefed3243e70a86567daa076c6de55c50883adf8a0e9f1fcdfbfbb2eddf8',
	'thai-massage'        => '54670f87dc6a30fb4b0631ed8f6bb9d26f74b9fc00411242ffd9858b2a5c4295',
	'work-with-me'        => 'a2791f2e0daa63ca705b7e40cfded92d963e20c4ba564e2b993c8b1567225e50',
);

$notice = '<!-- wp:paragraph {"className":"ts-coming-soon"} -->' . "\n"
	. '<p class="ts-coming-soon"><strong>Coming soon.</strong> Thai Solitude plans to begin massage sessions in November. Questions are welcome now—<a href="/contact/">get in touch with Butsaba</a>.</p>' . "\n"
	. '<!-- /wp:paragraph -->' . "\n\n";

function ts_staging9_replace_once( $content, $old, $new, $where ) {
	$count = substr_count( $content, $old );
	if ( 1 !== $count ) {
		WP_CLI::error( $where . ': expected one copy of ' . $old . ', found ' . $count );
	}
	return str_replace( $old, $new, $content );
}

$updates = array();
foreach ( $expected_hashes as $slug => $expected_hash ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $page || 'publish' !== $page->post_status ) {
		WP_CLI::error( 'Published page missing: ' . $slug );
	}
	$content = $page->post_content;
	if ( ! hash_equals( $expected_hash, hash( 'sha256', $content ) ) ) {
		WP_CLI::error( 'Page changed since review: ' . $slug );
	}
	if ( 'home' === $slug ) {
		if ( 2 !== substr_count( $content, 'Book Now' ) ) {
			WP_CLI::error( 'Home booking button has changed.' );
		}
		$content = str_replace( 'Book Now', 'Get in Touch', $content );
	} elseif ( 'about' === $slug ) {
		$content = ts_staging9_replace_once(
			$content,
			'our studio now welcomes individuals seeking not just temporary relief, but lasting transformation through intentional, expert touch.',
			'Thai Solitude is preparing to welcome clients seeking thoughtful, personalized massage care when sessions begin in November.',
			$slug
		);
	} elseif ( 'contact' === $slug ) {
		$content = ts_staging9_replace_once(
			$content,
			'Hi!%20I%20would%20like%20to%20book%20a%20massage.',
			'Hi%20Butsaba!%20I%20have%20a%20question%20about%20November%20availability.',
			$slug
		);
	}
	$updates[ $slug ] = array( 'ID' => $page->ID, 'post_content' => $notice . $content );
}

$astra = get_option( 'astra-settings' );
if ( ! is_array( $astra ) || ( $astra['header-button1-text'] ?? null ) !== 'Book Now' ) {
	WP_CLI::error( 'Header button differs from reviewed staging9 setting.' );
}
$astra['header-button1-text'] = 'Get in Touch';

$widgets = get_option( 'widget_block' );
if ( ! is_array( $widgets ) || ! isset( $widgets[7]['content'] ) ) {
	WP_CLI::error( 'Footer callout widget 7 is missing.' );
}
$footer = $widgets[7]['content'];
$footer = ts_staging9_replace_once( $footer, 'Ready to Relax and Recharge?', 'Coming Soon at Thai Solitude', 'footer' );
$footer = ts_staging9_replace_once(
	$footer,
	'Contact us today to schedule your session and experience the transformative benefits of professional massage therapy.',
	'Massage sessions are planned to begin in November. Have a question or want to ask about availability? Get in touch with Butsaba.',
	'footer'
);
if ( 2 !== substr_count( $footer, 'Book Now' ) ) {
	WP_CLI::error( 'Footer booking button differs from reviewed staging9 widget.' );
}
$widgets[7]['content'] = str_replace( 'Book Now', 'Get in Touch', $footer );

$css = wp_get_custom_css();
if ( false !== strpos( $css, '/* TS COMING SOON START */' ) || false !== strpos( $css, '/* TS COMING SOON END */' ) ) {
	WP_CLI::error( 'Coming-soon CSS already exists; review before retrying.' );
}
$new_css = rtrim( $css ) . "\n\n" . <<<'CSS'
/* TS COMING SOON START */
.ts-coming-soon {
  box-sizing: border-box;
  max-width: 1180px;
  margin: 24px auto 12px !important;
  padding: 15px 22px;
  border-left: 4px solid var(--ast-global-color-0);
  border-radius: 10px;
  background: #eaf7eb;
  box-shadow: 0 8px 22px rgba(24, 58, 31, 0.14);
  color: var(--ast-global-color-2);
  line-height: 1.55;
}
.ts-coming-soon a { font-weight: 600; }
@media (max-width: 767px) {
  .ts-coming-soon { margin: 16px 20px 12px !important; padding: 13px 16px; }
}
/* TS COMING SOON END */
CSS;

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
if ( ! in_array( 'apply-staging9', $cli_args, true ) ) {
	WP_CLI::success( 'Read-only preflight passed for staging9: eight pages, header button, footer callout, and scoped CSS. No changes made.' );
	return;
}

foreach ( $updates as $slug => $update ) {
	$result = wp_update_post( wp_slash( $update ), true );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $slug . ': ' . $result->get_error_message() );
	}
}
update_option( 'astra-settings', $astra );
update_option( 'widget_block', $widgets );
$css_result = wp_update_custom_css_post( $new_css );
if ( is_wp_error( $css_result ) ) {
	WP_CLI::error( $css_result->get_error_message() );
}
WP_CLI::success( 'Staging9 coming-soon copy installed on eight pages, header, and footer.' );
