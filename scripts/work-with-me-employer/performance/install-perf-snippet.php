<?php
/**
 * Create ONE of the small performance WPCode PHP snippets for ONE site at a time. Allow-list: staging31 and live. Live was added on Ryan's go (2026-10-17) after both snippets were
 * applied, measured and rolled back/re-applied on staging31 and live's page text was proven identical to staging31's.
 * Ryan, 2026-10-17: "the homepage loads images slower than it could. we can fix that." (then: read Google's PageSpeed report directly)
 *
 * Keys (second WP-CLI argument):
 *   lazy-images   PerformanceLazyLoadImages    only the FIRST image counts as above the fold (WordPress's default is three): hero 2.26 s -> 1.32 s on a throttled phone.
 *                 Side effect, by design and already true today for the 4th image onward: lazy images get sizes="auto", so on a 1x screen the browser picks the 300 px copy for a
 *                 298 px slot (slightly softer, like the third illustration on live today); on 2x phones/retina the 512 px copy is still chosen (pixel-identical).
 *   work-sans     PerformanceDropUnusedWorkSans   Astra puts Google's "Work Sans" 600 in a render-blocking stylesheet, but no page ever downloads Work Sans (the Additional CSS uses
 *                 Newsreader and Hanken Grotesk everywhere; checked on all 8 pages). Lighthouse mobile what-if on live: first paint 3.4 s -> 1.7 s, score 77 -> 84.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> <key> [FILE]
 *   preflight    read-only: report the state (ready = snippet absent, applied = exactly this snippet is there, CHANGED = anything else)
 *   apply        snapshot, then create + activate the snippet and refresh WPCode's cache
 *   rollback     put things back by deleting the snippet this installer created, if it is still exactly what it wrote (FILE = the before-snapshot)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'staging31.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (staging31 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved code.' );
}
if ( ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) {
	WP_CLI::error( 'WPCode (insert-headers-and-footers) is not loaded.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
$key  = isset( $args[1] ) ? (string) $args[1] : '';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$lazy_code = <<<'CODE'
// Only the FIRST image on a page counts as above the fold (WordPress's default is the first three); the rest lazy-load as the visitor scrolls,
// so the main photo gets a slow phone's connection to itself. Measured 2026-10-17: hero 2.26 s -> 1.30 s. Remove this snippet to go back to the default.
add_filter( 'wp_omit_loading_attr_threshold', function () {
	return 1;
} );
CODE;
$work_sans_code = <<<'CODE'
// Astra loads Google's "Work Sans" (weight 600) in a render-blocking stylesheet, but the site's own CSS uses Newsreader and Hanken Grotesk everywhere and no page ever
// downloads Work Sans. Dropping the unused request removes a blocking round trip to another company's server on every visit (Lighthouse mobile what-if 2026-10-17: first paint 3.4 s -> 1.7 s).
// Remove this snippet to go back to Astra's default.
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'astra-google-fonts' );
	wp_deregister_style( 'astra-google-fonts' );
}, 1000 );
// The preconnect / dns-prefetch hints for fonts.googleapis.com are then pointless; the one for fonts.gstatic.com (the real font files) stays.
add_filter( 'wp_resource_hints', function ( $urls ) {
	return array_values( array_filter( (array) $urls, function ( $u ) {
		$h = is_array( $u ) ? ( isset( $u['href'] ) ? $u['href'] : '' ) : (string) $u;
		return false === strpos( $h, 'fonts.googleapis.com' );
	} ) );
}, 1000 );
CODE;
$variants = array(
	'lazy-images' => array( 'title' => 'PerformanceLazyLoadImages', 'code' => $lazy_code . "\n", 'marker' => 'wp_omit_loading_attr_threshold' ),
	'work-sans'   => array( 'title' => 'PerformanceDropUnusedWorkSans', 'code' => $work_sans_code . "\n", 'marker' => 'astra-google-fonts' ),
);
if ( ! isset( $variants[ $key ] ) ) {
	WP_CLI::error( 'Second argument must be one of: ' . implode( ', ', array_keys( $variants ) ) );
}
$title  = $variants[ $key ]['title'];
$code   = $variants[ $key ]['code'];
$marker = $variants[ $key ]['marker'];

$found = get_posts( array( 'post_type' => 'wpcode', 'post_status' => 'any', 'title' => $title, 'numberposts' => -1, 'suppress_filters' => true ) );
if ( 0 === count( $found ) ) {
	$state = 'ready';
} elseif ( 1 === count( $found ) && 'publish' === $found[0]->post_status && trim( (string) $found[0]->post_content ) === trim( $code ) ) {
	$state = 'applied';
} else {
	$state = 'CHANGED';
}
$report = sprintf( 'host=%s snippet_%s=%s', $home, $title, $state );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'A snippet with this title exists but is not exactly what this installer writes; stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
/** Rebuild WPCode's snippet cache; true if the cache now reflects whether the filter text is present. */
$refresh_cache = function ( $expected ) use ( $marker ) {
	if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
	return $expected === ( false !== strpos( maybe_serialize( get_option( 'wpcode_snippets' ) ), $marker ) );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/perf-' . $key . '-before-' . gmdate( 'Ymd-His' ) . '.json';
	if ( false === file_put_contents( $file, wp_json_encode( array( 'created' => gmdate( 'c' ), 'home' => $home, 'title' => $title, 'key' => $key, 'code_sha256' => hash( 'sha256', $code ), 'created_id' => null ) ) ) || ! filesize( $file ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$snippet = new WPCode_Snippet( array(
		'title'       => $title,
		'code'        => $code,
		'code_type'   => 'php',
		'location'    => 'everywhere',
		'auto_insert' => 1,
		'priority'    => 10,
		'active'      => true,
		'note'        => 'Created 2026-10-17 by scripts/work-with-me-employer/performance/install-perf-snippet.php (rollback: delete this snippet).',
	) );
	$id = $snippet->save();
	$p  = $id ? get_post( $id ) : null;
	$ok = $p && 'wpcode' === $p->post_type && 'publish' === $p->post_status && trim( (string) $p->post_content ) === trim( $code )
		&& array( 'php' ) === wp_get_object_terms( $id, 'wpcode_type', array( 'fields' => 'slugs' ) ) && array( 'everywhere' ) === wp_get_object_terms( $id, 'wpcode_location', array( 'fields' => 'slugs' ) );
	if ( $ok ) {
		file_put_contents( $file, wp_json_encode( array( 'created' => gmdate( 'c' ), 'home' => $home, 'title' => $title, 'key' => $key, 'code_sha256' => hash( 'sha256', $code ), 'created_id' => (int) $id ) ) );
		$ok = $refresh_cache( true );
	}
	if ( ! $ok ) {
		if ( $id ) {
			wp_delete_post( $id, true );
		}
		$refresh_cache( false );
		WP_CLI::error( 'The snippet did not save exactly as expected; it was removed again. Nothing remains changed.' );
	}
	WP_CLI::success( 'Created snippet #' . $id . ' "' . $title . '". Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[2] ) ? (string) $args[2] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['created_id'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $found[0]->ID !== (int) $snap['created_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the snippet this installer created is not there in its original form. ' . $report );
	return;
}
wp_delete_post( (int) $snap['created_id'], true );
$gone = null === get_post( (int) $snap['created_id'] ) || false === get_post( (int) $snap['created_id'] );
if ( ! $gone || ! $refresh_cache( false ) ) {
	WP_CLI::error( 'The snippet could not be removed cleanly; stop and inspect.' );
}
WP_CLI::success( 'Rollback done: snippet #' . (int) $snap['created_id'] . ' deleted; WPCode cache refreshed.' );
