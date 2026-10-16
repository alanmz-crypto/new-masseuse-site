<?php
/**
 * Structured-data fixes for staging31 and, since Ryan's second explicit go, live (thaisolitude.com). No other site is allowed.
 * Ryan, 2026-10-16: "fix these" (the structured-data findings of the staging31 backend audit); then "i made a backup on siteground. go" for live.
 *
 * What it changes (three stores, each snapshotted and reversible):
 *   1. SureRank global option surerank_settings: the Organization schema's knowsLanguage "Hungarian" -> "Thai, English".
 *      The site's own Home page says "I am a native Thai speaker and fluent in English"; nothing on the site mentions Hungarian.
 *   2. Home page (ID 296) postmeta surerank_settings_schemas: the same Organization knowsLanguage leaf, same change.
 *   3. WPCode snippet 1023 "LocalBusinessInfo" (PHP, published): drops "priceRange": "$$" (no page shows any price) and the empty
 *      "streetAddress": "" (invalid as an empty value), and replaces the hard-coded site address with home_url() so a clone or deploy
 *      cannot carry the wrong host. The phone (+1-312-539-4328) is left as is: the Contact page publishes the same number.
 * Nothing else is touched. Every store must be exactly the reviewed "before" (or already the "after") or the run stops.
 *
 * Modes (first WP-CLI argument; run with --user=1):
 *   (none) / preflight    read-only: report each store's state (ready, applied, or CHANGED = not the reviewed state)
 *   apply                 snapshot, then apply all three (undoes the ones already done if one fails)
 *   rollback FILE         put each store back from the snapshot, if it is still exactly what this installer wrote
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home       = untrailingslashit( home_url() );
$home_https = untrailingslashit( set_url_scheme( home_url( '/' ), 'https' ) );   // not home_url('/', 'https'): WP-CLI filters home_url and ignores the scheme
$host       = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'staging31.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (staging31 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved code.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

// ---- The reviewed values -------------------------------------------------------------------------------------------------
$org_id     = '32eb489e-b9c3-4aba-be46-1968f1fc2588';   // the Organization schema's id in SureRank (same on every clone of live)
$lang_old   = 'Hungarian';
$lang_new   = 'Thai, English';
$home_id    = 296;
$snippet_id = 1023;
// sha256 of the snippet's exact stored bytes (Windows line endings included) with "https://<this site>" replaced by "{HOME}".
// Identical on staging31 and live, which is why one guard covers both.
$snippet_old_sha = '0513f8b7620b4b2013ad3c5b91e1e5f330a606fed4ab7b4ba8f4646f069d781c';
$snippet_new     = <<<'CODE'
add_action( 'wp_head', function () {
	$home = untrailingslashit( set_url_scheme( home_url( '/' ), 'https' ) );
	$data = array(
		'@context'  => 'https://schema.org',
		'@type'     => 'LocalBusiness',
		'@id'       => $home . '/#localbusiness',
		'name'      => 'Thai Solitude',
		'telephone' => '+1-312-539-4328',
		'address'   => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Portland',
			'addressRegion'   => 'OR',
			'postalCode'      => '97213',
			'addressCountry'  => 'US',
		),
		'url'       => $home,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
} );
CODE;
$snippet_new .= "\n";

/** Read $a['schemas'][$org]['fields']['knowsLanguage'] without notices; null if the path is missing or the schema is not an Organization. */
$leaf = function ( $a, $org ) {
	if ( ! is_array( $a ) || ! isset( $a['schemas'][ $org ]['fields'] ) || 'Organization' !== ( $a['schemas'][ $org ]['type'] ?? null ) ) {
		return null;
	}
	return array_key_exists( 'knowsLanguage', $a['schemas'][ $org ]['fields'] ) ? $a['schemas'][ $org ]['fields']['knowsLanguage'] : null;
};
$state_of = function ( $now, $old, $new ) {
	return $old === $now ? 'ready' : ( $new === $now ? 'applied' : 'CHANGED' );
};

// ---- 1. SureRank global option ---------------------------------------------------------------------------------------------
$sr       = get_option( 'surerank_settings' );
$sr_state = $state_of( $leaf( $sr, $org_id ), $lang_old, $lang_new );

// ---- 2. Home page schema postmeta ------------------------------------------------------------------------------------------
$hp = get_post( $home_id );
if ( ! $hp || 'page' !== $hp->post_type || 'home' !== $hp->post_name || 'publish' !== $hp->post_status ) {
	WP_CLI::error( 'Home (ID 296) was not found as a published page.' );
}
$hm       = get_post_meta( $home_id, 'surerank_settings_schemas', true );
$hm_state = $state_of( $leaf( $hm, $org_id ), $lang_old, $lang_new );

// ---- 3. WPCode snippet 1023 ------------------------------------------------------------------------------------------------
$sn = get_post( $snippet_id );
if ( ! $sn || 'wpcode' !== $sn->post_type || 'publish' !== $sn->post_status || 'LocalBusinessInfo' !== $sn->post_title ) {
	WP_CLI::error( 'WPCode snippet 1023 "LocalBusinessInfo" was not found as a published snippet.' );
}
$sn_now = (string) $sn->post_content;
if ( hash( 'sha256', str_replace( $home_https, '{HOME}', $sn_now ) ) === $snippet_old_sha ) {
	$sn_state = 'ready';
} elseif ( $sn_now === $snippet_new ) {
	$sn_state = 'applied';
} else {
	$sn_state = 'CHANGED';
}

$states = array( $sr_state, $hm_state, $sn_state );
$report = sprintf( 'host=%s surerank_option=%s home_schema=%s snippet_1023=%s', $home, $sr_state, $hm_state, $sn_state );

if ( 'preflight' === $mode ) {
	if ( in_array( 'CHANGED', $states, true ) ) {
		WP_CLI::error( 'A store is neither ready nor already applied; stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}

/** Write $leafvalue into the Organization schema of $a and return the array. */
$with_leaf = function ( $a, $org, $value ) {
	$a['schemas'][ $org ]['fields']['knowsLanguage'] = $value;
	return $a;
};
/** Rebuild WPCode's snippet cache and report whether the cached code still holds the old price field. */
$refresh_wpcode_cache = function () {
	if ( function_exists( 'wpcode' ) && isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
	return false === strpos( maybe_serialize( get_option( 'wpcode_snippets' ) ), 'priceRange' );
};

if ( 'apply' === $mode ) {
	if ( in_array( 'CHANGED', $states, true ) ) {
		WP_CLI::error( 'A store is not in the reviewed state; nothing was changed. ' . $report );
	}
	if ( array( 'applied', 'applied', 'applied' ) === $states ) {
		WP_CLI::success( 'Nothing to do: everything is already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/structured-data-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap = array(
		'created' => gmdate( 'c' ), 'home' => $home, 'org_id' => $org_id,
		'surerank_option' => array( 'before_leaf' => $leaf( $sr, $org_id ), 'after_leaf' => $lang_new ),
		'home_schema'     => array( 'id' => $home_id, 'before_leaf' => $leaf( $hm, $org_id ), 'after_leaf' => $lang_new ),
		'snippet'         => array( 'id' => $snippet_id, 'before' => $sn_now, 'after_sha' => hash( 'sha256', $snippet_new ) ),
	);
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || ! filesize( $file ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$undo = array();
	$fail = function ( $why ) use ( &$undo ) {
		foreach ( array_reverse( $undo ) as $fn ) {
			$fn();
		}
		WP_CLI::error( $why . ' Everything already written was put back.' );
	};
	if ( 'ready' === $sr_state ) {
		$new = $with_leaf( $sr, $org_id, $lang_new );
		update_option( 'surerank_settings', $new );
		$undo[] = function () use ( $org_id, $lang_old, $with_leaf ) { update_option( 'surerank_settings', $with_leaf( get_option( 'surerank_settings' ), $org_id, $lang_old ) ); };
		if ( get_option( 'surerank_settings' ) != $new ) {   // loose on purpose: WordPress may return the same array with scalar types normalised
			$fail( 'The SureRank global setting did not save as expected.' );
		}
	}
	if ( 'ready' === $hm_state ) {
		$new = $with_leaf( $hm, $org_id, $lang_new );
		update_post_meta( $home_id, 'surerank_settings_schemas', wp_slash( $new ) );
		$undo[] = function () use ( $home_id, $org_id, $lang_old, $with_leaf ) { update_post_meta( $home_id, 'surerank_settings_schemas', wp_slash( $with_leaf( get_post_meta( $home_id, 'surerank_settings_schemas', true ), $org_id, $lang_old ) ) ); };
		if ( get_post_meta( $home_id, 'surerank_settings_schemas', true ) != $new ) {
			$fail( 'The Home page schema did not save as expected.' );
		}
	}
	if ( 'ready' === $sn_state ) {
		$r = wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $snippet_new ) ), true );
		$undo[] = function () use ( $snippet_id, $sn_now, $refresh_wpcode_cache ) { wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => $sn_now ) ), true ); $refresh_wpcode_cache(); };
		if ( is_wp_error( $r ) || get_post( $snippet_id )->post_content !== $snippet_new ) {
			$fail( 'The WPCode snippet did not save exactly as expected.' );
		}
		if ( ! $refresh_wpcode_cache() ) {
			$fail( 'WPCode\'s cached copy of the snippet still holds the old code.' );
		}
	}
	WP_CLI::success( 'Applied the structured-data fixes. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || ( $snap['org_id'] ?? '' ) !== $org_id || ! isset( $snap['snippet']['before'] ) ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
$done = array();
if ( 'applied' === $sr_state ) {
	update_option( 'surerank_settings', $with_leaf( $sr, $org_id, $snap['surerank_option']['before_leaf'] ) );
	$done[] = 'surerank_option restored';
}
if ( 'applied' === $hm_state ) {
	update_post_meta( $home_id, 'surerank_settings_schemas', wp_slash( $with_leaf( $hm, $org_id, $snap['home_schema']['before_leaf'] ) ) );
	$done[] = 'home_schema restored';
}
if ( 'applied' === $sn_state ) {
	$r = wp_update_post( wp_slash( array( 'ID' => $snippet_id, 'post_content' => (string) $snap['snippet']['before'] ) ), true );
	if ( is_wp_error( $r ) ) {
		WP_CLI::error( 'The snippet could not be restored: ' . $r->get_error_message() );
	}
	if ( function_exists( 'wpcode' ) && isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
	$done[] = 'snippet restored';
}
$skipped = array_diff( array( 'surerank_option', 'home_schema', 'snippet_1023' ), array_map( function ( $d ) { return str_replace( array( ' restored', 'snippet' ), array( '', 'snippet_1023' ), $d ); }, $done ) );
WP_CLI::success( 'Rollback done: ' . ( $done ? implode( ', ', $done ) : 'nothing needed restoring' ) . ( $skipped ? ' | left alone (not in the state this installer wrote): ' . implode( ', ', $skipped ) : '' ) );
