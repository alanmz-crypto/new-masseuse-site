<?php
/**
 * Install the Thai Solitude Work With Me page in local practice or staging8.
 * Local practice, after ./scripts/backup-site.sh:
 * docker compose run --rm -v "$PWD/scripts:/site-scripts:ro" wpcli wp eval-file /site-scripts/install-work-with-me.php
 *
 * For staging8, first verify the SiteGround backup for that site and run this
 * file with WP-CLI and no argument for a read-only preflight. The final
 * positional argument apply-staging8 enables the write on staging8 only.
 * The script refuses staging7, the live site, and any other WordPress host.
 *
 * The operation is idempotent. It refuses to overwrite independently edited page content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Run with WP-CLI.' );
}

$site_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
if ( ! in_array( $site_host, array( 'localhost', 'staging8.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Unsupported target host: ' . $site_host );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Expected the Astra theme; found ' . get_stylesheet() );
}
if ( ! is_file( WP_CONTENT_DIR . '/uploads/2026/09/butsaba_homepage.webp' ) ) {
	WP_CLI::error( 'The approved Butsaba photo from staging7 is missing in this WordPress uploads directory.' );
}
$resume_path = '/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf';
if ( ! is_file( ABSPATH . ltrim( $resume_path, '/' ) ) ) {
	WP_CLI::error( 'The approved Butsaba résumé PDF is missing in this WordPress uploads directory.' );
}
$content = file_get_contents( __DIR__ . '/work-with-me-content.html' );
$css = file_get_contents( __DIR__ . '/work-with-me.css' );
if ( false === $content || false === $css ) {
	WP_CLI::error( 'Work With Me source files are missing.' );
}

// Check the routes and in-page jumps against the target WordPress install.
foreach ( array( 'contact', 'services' ) as $route ) {
	$target_page = get_page_by_path( $route, OBJECT, 'page' );
	$expected_path = '/' . $route . '/';
	if ( ! $target_page || 'publish' !== $target_page->post_status ) {
		WP_CLI::error( 'Expected published page missing: ' . $expected_path );
	}
	$actual_path = wp_parse_url( get_permalink( $target_page ), PHP_URL_PATH );
	if ( untrailingslashit( $actual_path ) !== untrailingslashit( $expected_path ) ) {
		WP_CLI::error( 'Page permalink differs from Work With Me link: ' . $expected_path . ' resolves to ' . $actual_path );
	}
}
preg_match_all( '/\bhref="([^"]+)"/', $content, $href_matches );
foreach ( $href_matches[1] as $href ) {
	if ( 0 === strpos( $href, '#' ) ) {
		$anchor = substr( $href, 1 );
		if ( 1 !== preg_match_all( '/\bid="' . preg_quote( $anchor, '/' ) . '"/', $content ) ) {
			WP_CLI::error( 'In-page link does not have exactly one target: ' . $href );
		}
	} elseif ( 0 === strpos( $href, '/' ) && ! in_array( $href, array( '/', '/contact/', '/services/', $resume_path ), true ) ) {
		WP_CLI::error( 'Unreviewed site-relative Work With Me link: ' . $href );
	} elseif ( false !== strpos( $href, 'thaisolitude.com' ) ) {
		WP_CLI::error( 'Use a site-relative link for Thai Solitude: ' . $href );
	}
}

$page = get_page_by_path( 'work-with-me', OBJECT, 'page' );
$installed_hash = $page ? get_post_meta( $page->ID, '_ts_wwm_installed_hash', true ) : '';
$prior_local_hash = '0d343f2fd17402670c5fb16406ca06535217bd160bf6773cd2adb87d727308ab';
if ( $page && '' !== trim( $page->post_content ) && $page->post_content !== $content ) {
	$current_hash = hash( 'sha256', $page->post_content );
	if ( ! hash_equals( $installed_hash ?: $prior_local_hash, $current_hash ) ) {
		WP_CLI::error( 'Existing Work With Me content was edited independently; review it before changing the page.' );
	}
}

$custom_css = wp_get_custom_css();
$start = '/* TS WORK WITH ME START */';
$end = '/* TS WORK WITH ME END */';
$start_pos = strpos( $custom_css, $start );
$end_pos = strpos( $custom_css, $end );
if ( ( false === $start_pos ) !== ( false === $end_pos ) ) {
	WP_CLI::error( 'The Work With Me CSS markers are incomplete; review Additional CSS before retrying.' );
}
if ( false === $start_pos ) {
	$updated_css = trim( $custom_css ) . ( '' === trim( $custom_css ) ? '' : "\n\n" ) . trim( $css ) . "\n";
} else {
	$updated_css = substr( $custom_css, 0, $start_pos ) . trim( $css ) . substr( $custom_css, $end_pos + strlen( $end ) );
}

$menu = wp_get_nav_menu_object( 'Primary Menu' );
if ( ! $menu ) {
	$menu_locations = get_nav_menu_locations();
	$menu = isset( $menu_locations['primary'] ) ? wp_get_nav_menu_object( $menu_locations['primary'] ) : false;
}
if ( ! $menu ) {
	WP_CLI::error( 'Primary navigation menu was not found.' );
}

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
if ( in_array( 'check', $cli_args, true ) || ( 'staging8.thaisolitude.com' === $site_host && ! in_array( 'apply-staging8', $cli_args, true ) ) ) {
	WP_CLI::success( 'Read-only preflight passed: host=' . $site_host . ', theme=' . get_stylesheet() . ', menu=' . $menu->name . ', portrait=present, page=' . ( $page ? $page->post_status : 'new' ) . '. No changes made.' );
	return;
}

if ( ! $page ) {
	$page_id = wp_insert_post(
		wp_slash(
			array(
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_title' => 'Work With Me',
				'post_name' => 'work-with-me',
				'post_content' => $content,
			)
		),
		true
	);
} elseif ( $page->post_content !== $content ) {
	$page_id = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );
} else {
	$page_id = $page->ID;
}
if ( is_wp_error( $page_id ) ) {
	WP_CLI::error( $page_id->get_error_message() );
}

// The page supplies its own hero H1, as the existing About page does.
update_post_meta( $page_id, 'site-post-title', 'disabled' );
update_post_meta( $page_id, '_ts_wwm_installed_hash', hash( 'sha256', $content ) );

if ( $updated_css !== $custom_css ) {
	$result = wp_update_custom_css_post( $updated_css );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}
}

$found = false;
foreach ( wp_get_nav_menu_items( $menu->term_id ) ?: array() as $item ) {
	if ( (int) $item->object_id === (int) $page_id && 'page' === $item->object ) {
		$found = true;
		break;
	}
}
if ( ! $found ) {
	wp_update_nav_menu_item(
		$menu->term_id,
		0,
		array(
			'menu-item-title' => 'Work With Me',
			'menu-item-object' => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type' => 'post_type',
			'menu-item-status' => 'publish',
			'menu-item-position' => 3,
		)
	);
}

WP_CLI::success( 'Work With Me installed at ' . get_permalink( $page_id ) );
