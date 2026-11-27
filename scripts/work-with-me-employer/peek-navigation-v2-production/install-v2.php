<?php
/**
 * Install or roll back the production navigation v2 release from the archive that package.py builds.
 * Run with WP-CLI and an administrator:  wp eval-file install-v2.php <preflight|apply|rollback> <ARCHIVE.zip> [SNAPSHOT.json] --user=1
 *
 * Every value the packager records is enforced here before anything is replaced, and again before a rollback:
 *   - the archive holds exactly manifest.json plus the four expected files, each matching the manifest's SHA-256;
 *   - the manifest's host, Spectra version, snippet id/title and start/replacement code hashes equal the values pinned below
 *     (a self-consistent but retargeted archive is refused);
 *   - the site's stored home host, the active Spectra plugin version, the Astra theme and the one WPCode snippet by title and id all match;
 *   - apply requires the snippet's stored code to hash to the start code; rollback requires it to hash to the replacement code;
 *   - already-present asset files must hash to the manifest's values (a different file at a published URL is never overwritten).
 * Order on apply: snapshot -> publish both assets (verified) -> replace the snippet -> verify read-back and WPCode cache.
 * Rollback restores the exact start code and checks that nothing else on the snippet moved. The v2 assets are KEPT, because cached HTML may still
 * reference them; a changed asset needs a new URL. WWM_BACKUP_DIR (existing, writable, outside the web root) is required for apply and rollback.
 * Historical v1 installer and manifest are not touched by this file.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

const TS_V2_HOST       = 'thaisolitude.com';
const TS_V2_SPECTRA    = '2.20.7';
const TS_V2_SPECTRA_PLUGIN = 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php';
const TS_V2_SNIPPET_ID = 1343;
const TS_V2_TITLE      = 'TSPeekNav visitor release v1';
const TS_V2_START_SHA  = 'f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566';
const TS_V2_MARKER     = '/ts-peek/v2/navigation.js';
const TS_V2_BEFORE     = 'wpcode/before.txt';
const TS_V2_AFTER      = 'wpcode/after.txt';
const TS_V2_ASSETS     = array( 'wp-content/ts-peek/v2/navigation.js', 'wp-content/ts-peek/v2/navigation.css' );

$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = isset( $args[0] ) ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}
$archive_path = isset( $args[1] ) ? (string) $args[1] : '';
if ( '' === $archive_path || ! is_file( $archive_path ) ) {
	WP_CLI::error( 'Second argument must be the release archive built by package.py.' );
}
if ( ! class_exists( 'ZipArchive' ) ) {
	WP_CLI::error( 'PHP zip support is required.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved code.' );
}
if ( ! function_exists( 'wpcode' ) ) {
	WP_CLI::error( 'WPCode (insert-headers-and-footers) is not loaded.' );
}

// ---- archive ------------------------------------------------------------------------------------------------------------------
$zip = new ZipArchive();
if ( true !== $zip->open( $archive_path, ZipArchive::RDONLY ) ) {
	WP_CLI::error( 'The archive cannot be opened.' );
}
$expected_names = array_merge( array( 'manifest.json', TS_V2_BEFORE, TS_V2_AFTER ), TS_V2_ASSETS );
$found_names    = array();
for ( $i = 0; $i < $zip->numFiles; $i++ ) {
	$found_names[] = $zip->getNameIndex( $i );
}
sort( $found_names );
$sorted_expected = $expected_names;
sort( $sorted_expected );
if ( $found_names !== $sorted_expected ) {
	WP_CLI::error( 'The archive does not contain exactly the expected files.' );
}
$files = array();
foreach ( $expected_names as $name ) {
	$data = $zip->getFromName( $name );
	if ( false === $data ) {
		WP_CLI::error( 'Cannot read ' . $name . ' from the archive.' );
	}
	$files[ $name ] = $data;
}
$zip->close();
$manifest = json_decode( $files['manifest.json'], true );
$want_keys = array( 'host', 'replacement_code_sha256', 'sha256', 'snippet_id', 'snippet_title', 'spectra_version', 'start_code_sha256' );
$manifest_keys = is_array( $manifest ) ? array_keys( $manifest ) : array();
sort( $manifest_keys );
if ( $manifest_keys !== $want_keys ) {
	WP_CLI::error( 'The manifest is missing or has unexpected fields.' );
}
$pins = array(
	'host' => TS_V2_HOST, 'spectra_version' => TS_V2_SPECTRA, 'snippet_id' => TS_V2_SNIPPET_ID,
	'snippet_title' => TS_V2_TITLE, 'start_code_sha256' => TS_V2_START_SHA,
);
foreach ( $pins as $field => $pinned ) {
	if ( $manifest[ $field ] !== $pinned ) {
		WP_CLI::error( 'Manifest ' . $field . ' differs from the value this installer is pinned to.' );
	}
}
$payload_names = array_diff( $expected_names, array( 'manifest.json' ) );
$map           = is_array( $manifest['sha256'] ) ? $manifest['sha256'] : array();
$map_names     = array_keys( $map );
sort( $map_names );
$sorted_payload = array_values( $payload_names );
sort( $sorted_payload );
if ( $map_names !== $sorted_payload ) {
	WP_CLI::error( 'The manifest hash list does not cover exactly the archive files.' );
}
foreach ( $payload_names as $name ) {
	if ( ! is_string( $map[ $name ] ) || ! hash_equals( $map[ $name ], hash( 'sha256', $files[ $name ] ) ) ) {
		WP_CLI::error( 'Archive file does not match the manifest hash: ' . $name );
	}
}
// Pin the actual executable bytes, even when an archive's internal hashes agree.
$reviewed_hashes = array(
	'wpcode/before.txt' => 'f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566',
	'wpcode/after.txt' => '6e17165537261decced46bfe310461ac16e6a69db5fea86173cfc5f86b18dd84',
	'wp-content/ts-peek/v2/navigation.js' => 'fa65b13f3c29638f912bf2e246b74482f3183dc44b68c0db1c3a548d69eac2b8',
	'wp-content/ts-peek/v2/navigation.css' => '722499cd66a4dc12d0dafd4731a5e5fd7cf5c3106e491b0b530ecaa074fe9c41',
 );
foreach ( $reviewed_hashes as $name => $sha ) {
	if ( $map[ $name ] !== $sha ) {
		WP_CLI::error( 'Payload differs from staging-approved release: ' . $name );
	}
}
$before_sha = hash( 'sha256', $files[ TS_V2_BEFORE ] );
$after_sha  = hash( 'sha256', $files[ TS_V2_AFTER ] );
if ( $before_sha !== TS_V2_START_SHA || $manifest['start_code_sha256'] !== $before_sha || $manifest['replacement_code_sha256'] !== $after_sha || $before_sha === $after_sha ) {
	WP_CLI::error( 'The start or replacement code hash is inconsistent.' );
}
foreach ( TS_V2_ASSETS as $asset ) {
	if ( false === strpos( $files[ TS_V2_AFTER ], substr( $asset, strlen( 'wp-content' ) ) ) ) {
		WP_CLI::error( 'The replacement code does not reference ' . $asset );
	}
}
if ( false !== strpos( $files[ TS_V2_BEFORE ], TS_V2_MARKER ) || false === strpos( $files[ TS_V2_AFTER ], TS_V2_MARKER ) ) {
	WP_CLI::error( 'The cache marker is not unique to the replacement code.' );
}
$manifest_sha = hash( 'sha256', $files['manifest.json'] );

// ---- site ---------------------------------------------------------------------------------------------------------------------
global $wpdb;
$stored_urls = $wpdb->get_col( "SELECT option_value FROM {$wpdb->options} WHERE option_name IN ('home','siteurl')" );
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( array( 'http://thaisolitude.com', 'http://thaisolitude.com' ) !== $stored_urls || $host !== $manifest['host'] || 'http://thaisolitude.com' !== $home || $home !== untrailingslashit( site_url() )
	|| realpath( ABSPATH ) !== '/home/customer/www/thaisolitude.com/public_html'
	|| realpath( WP_CONTENT_DIR ) !== '/home/customer/www/thaisolitude.com/public_html/wp-content' ) {
	WP_CLI::error( 'Refusing target ' . $home . ': the manifest is for ' . $manifest['host'] . '.' );
}
if ( 'preflight' !== $mode ) {
	$backup_dir = getenv( 'WWM_BACKUP_DIR' );
	if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
		WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
	}
	$directory = realpath( $backup_dir );
	// Lock the existing site content directory before inspecting the snippet or assets.
	// Its inode is shared by writers even when their backup directories differ.
	$lock = fopen( WP_CONTENT_DIR, 'r' );
	if ( ! $lock || ! flock( $lock, LOCK_EX | LOCK_NB ) ) {
		WP_CLI::error( 'Another snippet operation holds the site lock, or locking failed. Retry when it finishes.' );
	}
	// Plugin bootstrap can have primed this process's object cache before the lock.
	if ( wp_cache_supports( 'flush_runtime' ) ) {
		wp_cache_flush_runtime();
	}
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! is_plugin_active( TS_V2_SPECTRA_PLUGIN ) ) {
	WP_CLI::error( 'Spectra is not active.' );
}
$spectra = get_plugin_data( WP_PLUGIN_DIR . '/' . TS_V2_SPECTRA_PLUGIN, false, false );
if ( ( $spectra['Version'] ?? '' ) !== $manifest['spectra_version'] ) {
	WP_CLI::error( 'Active Spectra is ' . ( $spectra['Version'] ?? 'unknown' ) . '; the manifest is for ' . $manifest['spectra_version'] . '.' );
}
if ( '7.1.5' !== get_bloginfo( 'version' ) || '4.14.1' !== wp_get_theme()->get( 'Version' )
	|| ! is_plugin_active( 'insert-headers-and-footers/ihaf.php' )
	|| '2.4.2' !== get_plugin_data( WP_PLUGIN_DIR . '/insert-headers-and-footers/ihaf.php', false, false )['Version'] ) {
	WP_CLI::error( 'WordPress, Astra or WPCode differs from the production inventory.' );
}
$companions = array(
	1338 => 'ef9ee2fb82fe91be616518b4a02f9ee276855a9b998685e7e5ee13064c84f785',
	1339 => 'fd67d8f8548706bd8597af87e85380581a18f846a1bf71de4401db80b5f0a7e0',
	1340 => '3b12f5e09f6937f41f04122cbed3c6ef6244c9359115ce36ed31d2d6b7336ffd',
	1341 => 'c88e05ae019076e9a173dc239ffa2b091007864087067ef0444efca2acc00101',
	1342 => 'b99558619488cf1506c8b0e8ca2c59066068eaa0727233c90b51f599798a121c',
);
foreach ( $companions as $id => $sha ) {
	$companion = get_post( $id );
	if ( ! $companion || 'wpcode' !== $companion->post_type || 'publish' !== $companion->post_status
		|| hash( 'sha256', $companion->post_content ) !== $sha
		|| '1' !== get_post_meta( $id, '_wpcode_auto_insert', true )
		|| array( 'everywhere' ) !== wp_get_object_terms( $id, 'wpcode_location', array( 'fields' => 'slugs' ) )
		|| array( 'php' ) !== wp_get_object_terms( $id, 'wpcode_type', array( 'fields' => 'slugs' ) ) ) {
		WP_CLI::error( 'Companion snippet differs from production baseline: ' . $id );
	}
}
$by_title = get_posts( array( 'post_type' => 'wpcode', 'post_status' => 'any', 'title' => $manifest['snippet_title'], 'numberposts' => -1, 'suppress_filters' => true ) );
$post     = get_post( (int) $manifest['snippet_id'] );
if ( ! $post || 'wpcode' !== $post->post_type || $post->post_title !== $manifest['snippet_title'] || 'publish' !== $post->post_status
	|| 1 !== count( $by_title ) || (int) $by_title[0]->ID !== (int) $post->ID ) {
	WP_CLI::error( 'Snippet identity check failed: expected exactly one published WPCode snippet #' . $manifest['snippet_id'] . ' "' . $manifest['snippet_title'] . '".' );
}
if ( '1' !== get_post_meta( $post->ID, '_wpcode_auto_insert', true ) || '10' !== get_post_meta( $post->ID, '_wpcode_priority', true )
	|| array( 'everywhere' ) !== wp_get_object_terms( $post->ID, 'wpcode_location', array( 'fields' => 'slugs' ) )
	|| array( 'php' ) !== wp_get_object_terms( $post->ID, 'wpcode_type', array( 'fields' => 'slugs' ) ) ) {
	WP_CLI::error( 'Snippet activation, priority, type or location differs.' );
}
$code_sha = hash( 'sha256', (string) $post->post_content );
if ( $code_sha === $before_sha ) {
	$state = 'ready';
} elseif ( $code_sha === $after_sha ) {
	$state = 'applied';
} else {
	WP_CLI::error( 'Snippet #' . $post->ID . ' holds neither the start nor the replacement code (' . $code_sha . '); stop and review.' );
}
$cache_matches = static function ( $sha ) {
	$matches = array();
	foreach ( (array) get_option( 'wpcode_snippets' ) as $location => $items ) {
		foreach ( (array) $items as $item ) {
			if ( (int) ( $item['id'] ?? 0 ) === TS_V2_SNIPPET_ID ) {
				$matches[] = 'everywhere' === $location && isset( $item['code'] ) && hash( 'sha256', $item['code'] ) === $sha;
			}
		}
	}
	return array( true ) === $matches;
};
if ( 'rollback' !== $mode && ! $cache_matches( $code_sha ) ) {
	WP_CLI::error( 'Stored and cached navigation code differ.' );
}
foreach ( array( WP_CONTENT_DIR . '/ts-peek', WP_CONTENT_DIR . '/ts-peek/v2' ) as $parent ) {
	if ( is_link( $parent ) ) { WP_CLI::error( 'Unexpected symlink in release asset path.' ); }
}
$content_dir = rtrim( WP_CONTENT_DIR, '/' );
$asset_path  = static function ( $name ) use ( $content_dir ) {
	return $content_dir . substr( $name, strlen( 'wp-content' ) );
};
$asset_state = array();
foreach ( TS_V2_ASSETS as $asset ) {
	$path = $asset_path( $asset );
	if ( ! file_exists( $path ) && ! is_link( $path ) ) {
		$asset_state[ $asset ] = 'absent';
	} elseif ( is_file( $path ) && ! is_link( $path ) && hash_equals( $map[ $asset ], hash_file( 'sha256', $path ) ) ) {
		$asset_state[ $asset ] = 'exact';
	} else {
		WP_CLI::error( 'Asset ' . $asset . ' exists but is not exactly the released file; a published URL is never overwritten.' );
	}
}
$report = sprintf( 'host=%s snippet=#%d state=%s navigation.js=%s navigation.css=%s', $home, $post->ID, $state, $asset_state[ TS_V2_ASSETS[0] ], $asset_state[ TS_V2_ASSETS[1] ] );

/** A fingerprint of everything on the snippet except its code and modified dates. */
$fingerprint = static function ( $id ) {
	$p    = get_post( $id );
	$meta = get_post_meta( $id );
	ksort( $meta );
	$terms = array();
	foreach ( get_object_taxonomies( 'wpcode' ) as $taxonomy ) {
		$slugs = wp_get_object_terms( $id, $taxonomy, array( 'fields' => 'slugs' ) );
		sort( $slugs );
		$terms[ $taxonomy ] = $slugs;
	}
	ksort( $terms );
	$core = array();
	foreach ( array( 'post_status', 'post_title', 'post_name', 'post_author', 'post_parent', 'menu_order', 'post_excerpt', 'post_type', 'post_password', 'comment_status', 'ping_status' ) as $field ) {
		$core[ $field ] = $p->$field;
	}
	return hash( 'sha256', serialize( array( $core, $meta, $terms ) ) );
};
/** Rebuild WPCode's snippet cache; true when it now does (or does not) hold the v2 code. */
$refresh_cache = static function ( $expected ) use ( $cache_matches, $after_sha, $before_sha ) {
	if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
	return $cache_matches( $expected ? $after_sha : $before_sha );
};
/** Replace the snippet code with the same WordPress update method the rehearsal proved, then read it back. */
$set_code = static function ( $id, $code ) {
	// These code-only saves must preserve Spectra's generated assets on other pages.
	$skip_snippets = static function ( $types ) {
		$types[] = 'wpcode';
		return $types;
	};
	add_filter( 'uagb_delete_page_assets_skip_post_types', $skip_snippets, PHP_INT_MAX );
	try {
		$result = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $code ) ), true );
	} finally {
		remove_filter( 'uagb_delete_page_assets_skip_post_types', $skip_snippets, PHP_INT_MAX );
	}
	clean_post_cache( $id );
	return ! is_wp_error( $result ) && (int) $result === (int) $id && get_post( $id )->post_content === $code;
};

if ( 'preflight' === $mode ) {
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$write_atomic = static function ( $path, $data ) {
	$temp = tempnam( dirname( $path ), '.ts-v2-' );
	if ( false === $temp ) {
		return false;
	}
	// tempnam can fall back to the system temp directory. Only a sibling
	// temporary file can guarantee same-filesystem atomic replacement.
	if ( dirname( $temp ) !== realpath( dirname( $path ) ) || file_put_contents( $temp, $data ) !== strlen( $data ) || ! chmod( $temp, 0644 ) || ! rename( $temp, $path ) ) {
		@unlink( $temp );
		return false;
	}
	return file_get_contents( $path ) === $data;
};

if ( 'apply' === $mode ) {
	if ( 'applied' === $state ) {
		if ( 'exact' !== $asset_state[ TS_V2_ASSETS[0] ] || 'exact' !== $asset_state[ TS_V2_ASSETS[1] ] || ! $refresh_cache( true ) ) {
			WP_CLI::error( 'The snippet holds v2 but the assets or cache are not verified. Inspect before retrying. ' . $report );
		}
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$snapshot_file = $directory . '/peek-v2-before-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_uuid4() . '.json';
	$snapshot      = array(
		'created' => gmdate( 'c' ), 'home' => $home, 'host' => $host, 'snippet_id' => (int) $post->ID, 'snippet_title' => $post->post_title,
		'spectra_version' => $spectra['Version'], 'manifest_sha256' => $manifest_sha, 'start_code_sha256' => $before_sha, 'replacement_code_sha256' => $after_sha,
		'snippet_fingerprint' => $fingerprint( $post->ID ), 'assets_created' => array(), 'phase' => 'before-write',
	);
	if ( ! $write_atomic( $snapshot_file, wp_json_encode( $snapshot ) ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	$dir = dirname( $asset_path( TS_V2_ASSETS[0] ) );
	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		WP_CLI::error( 'Could not create ' . $dir . '; nothing was changed.' );
	}
	$created = array();
	$undo    = static function () use ( &$created, $asset_path ) {
		foreach ( $created as $asset ) {
			@unlink( $asset_path( $asset ) );
		}
	};
	foreach ( TS_V2_ASSETS as $asset ) {
		if ( 'exact' === $asset_state[ $asset ] ) {
			continue;
		}
		if ( ! $write_atomic( $asset_path( $asset ), $files[ $asset ] ) || ! hash_equals( $map[ $asset ], hash_file( 'sha256', $asset_path( $asset ) ) ) ) {
			$created[] = $asset;
			$undo();
			WP_CLI::error( 'Writing ' . $asset . ' failed verification; the snippet was not changed and new asset files were removed.' );
		}
		$created[] = $asset;
	}
	$snapshot['assets_created'] = $created;
	$snapshot['phase']          = 'assets-published';
	if ( ! $write_atomic( $snapshot_file, wp_json_encode( $snapshot ) ) ) {
		$undo();
		WP_CLI::error( 'Could not update the snapshot; the snippet was not changed and new asset files were removed.' );
	}
	$ok = $set_code( $post->ID, $files[ TS_V2_AFTER ] ) && $fingerprint( $post->ID ) === $snapshot['snippet_fingerprint'] && $refresh_cache( true );
	if ( ! $ok ) {
		$restored = $set_code( $post->ID, $files[ TS_V2_BEFORE ] ) && $refresh_cache( false ) && $fingerprint( $post->ID ) === $snapshot['snippet_fingerprint'];
		if ( $restored ) {
			// A visitor may already have received v2 HTML during the code swap.
			WP_CLI::error( 'Apply failed verification; the start code was restored and the v2 assets retained. Snapshot ' . $snapshot_file );
		}
		WP_CLI::error( 'Apply failed and the restore is incomplete. Inspect snippet #' . $post->ID . ', the WPCode cache and snapshot ' . $snapshot_file );
	}
	$snapshot['phase'] = 'applied';
	if ( ! $write_atomic( $snapshot_file, wp_json_encode( $snapshot ) ) ) {
		WP_CLI::error( 'V2 is active, but the final snapshot update could not be verified. Inspect the rollback snapshot: ' . $snapshot_file );
	}
	WP_CLI::success( 'Applied v2 to snippet #' . $post->ID . '. Rollback snapshot: ' . $snapshot_file );
	return;
}

// ---- rollback SNAPSHOT --------------------------------------------------------------------------------------------------------
$file = isset( $args[2] ) ? (string) $args[2] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
$must = array(
	'home' => $home, 'host' => $host, 'snippet_id' => (int) $post->ID, 'snippet_title' => $post->post_title, 'spectra_version' => $spectra['Version'],
	'manifest_sha256' => $manifest_sha, 'start_code_sha256' => $before_sha, 'replacement_code_sha256' => $after_sha,
);
if ( ! is_array( $snap ) ) {
	WP_CLI::error( 'Snapshot is missing or damaged.' );
}
foreach ( $must as $field => $value ) {
	if ( ( $snap[ $field ] ?? null ) !== $value ) {
		WP_CLI::error( 'Snapshot ' . $field . ' does not match this site and archive.' );
	}
}
// Apply preserves the non-code fields, so they must still match before either
// a rollback write or an already-restored result can be accepted.
$expected_fingerprint = $snap['snippet_fingerprint'] ?? null;
if ( $fingerprint( $post->ID ) !== $expected_fingerprint ) {
	WP_CLI::error( 'Snippet fingerprint does not match the rollback snapshot. Check the snapshot and any independent snippet edits.' );
}
if ( 'ready' === $state ) {
	if ( ! $refresh_cache( false ) ) {
		WP_CLI::error( 'The snippet holds the start code but its cache still holds v2.' );
	}
	WP_CLI::success( 'Nothing to roll back: the snippet already holds the start code. ' . $report );
	return;
}
if ( ! $set_code( $post->ID, $files[ TS_V2_BEFORE ] ) || ! $refresh_cache( false ) || hash( 'sha256', get_post( $post->ID )->post_content ) !== TS_V2_START_SHA ) {
	WP_CLI::error( 'Rollback could not be verified; stop and inspect snippet #' . $post->ID . '.' );
}
if ( $fingerprint( $post->ID ) !== $expected_fingerprint ) {
	WP_CLI::error( 'The start code is back, but another snippet field differs from the snapshot; inspect snippet #' . $post->ID . '.' );
}
WP_CLI::success( 'Rolled back snippet #' . $post->ID . ' to the exact start code; WPCode cache refreshed. The v2 asset files were kept.' );
