<?php
/**
 * Site review Bundle A: booking paths and internal links, authorized by the
 * 2026-11-01 handoff. ONE site: localhost:8090 or staging38 ONLY. Live is disabled.
 *
 * Exact reviewed replacements on nine pages, the frozen A11yPageJumpLinks
 * snippet, and one appended Additional CSS block. No new plugin or site files,
 * deletions, credentials, unconfirmed service facts or WordPress options.
 * See changes.json for every before/after, and README.md for the scope.
 *
 * preflight: read-only, ready/applied/CHANGED per part, all-or-nothing.
 * apply: only the push script's --apply writes; it exports/copies the DB first.
 * rollback FILE: every whole resource must still equal the saved applied bytes;
 * restores exact previous bytes. Drift refuses BEFORE any write.
 * Both mutations save/validate a private snapshot outside ABSPATH. On any save
 * or read-back failure, put all resources back before reporting the failure.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing target ' . $home . ': localhost:8090 or staging38 only; live is disabled.' );
}
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) ) {
	WP_CLI::error( 'Requires Astra and an administrator (--user=1) with unfiltered_html and edit_css.' );
}
if ( ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) {
	WP_CLI::error( 'WPCode is not loaded.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode.' );
}
$data = require __DIR__ . '/bundle-a-content.php';
if ( ! is_array( $data ) || count( $data['pairs'] ?? array() ) !== 23 || hash( 'sha256', $data['snippet_old'] ) !== $data['snippet_old_sha256'] || hash( 'sha256', $data['snippet_new'] ) !== $data['snippet_new_sha256'] ) {
	WP_CLI::error( 'Package is incomplete or its frozen snippet hash differs.' );
}
try {
	token_get_all( "<?php\n" . $data['snippet_old'], TOKEN_PARSE );
	token_get_all( "<?php\n" . $data['snippet_new'], TOKEN_PARSE );
} catch ( \ParseError $e ) {
	WP_CLI::error( 'Snippet syntax error: ' . $e->getMessage() );
}
$site_url = 'localhost:8090' === $host ? $home : set_url_scheme( $home, 'https' );
$pairs = array();
foreach ( $data['pairs'] as $pair ) {
	$pair['old'] = str_replace( '{{SITE_URL}}', $site_url, $pair['old'] );
	$pair['new'] = str_replace( '{{SITE_URL}}', $site_url, $pair['new'] );
	$pairs[ $pair['slug'] ][] = $pair;
}
$resources = array();
$states = array();
foreach ( $pairs as $slug => $changes ) {
	$found = get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'name' => $slug, 'numberposts' => -1, 'suppress_filters' => true ) );
	if ( 1 !== count( $found ) || 'publish' !== $found[0]->post_status || ( 'home' === $slug && (int) get_option( 'page_on_front' ) !== (int) $found[0]->ID ) ) {
		WP_CLI::error( 'Missing, duplicate, unpublished or wrong front page: ' . $slug );
	}
	$page = $found[0];
	$content = (string) $page->post_content;
	$part_states = array();
	foreach ( $changes as $pair ) {
		// Insertions intentionally contain their old anchor: test the NEW fragment first.
		$n_new = substr_count( $content, $pair['new'] );
		$n_old = substr_count( $content, $pair['old'] );
		$s = $n_new === $pair['count'] ? 'applied' : ( 0 === $n_new && $n_old === $pair['count'] ? 'ready' : 'CHANGED' );
		$part_states[] = $s;
		WP_CLI::log( $slug . ': ' . $pair['label'] . '=' . $s );
	}
	$unique = array_values( array_unique( $part_states ) );
	$state = 1 === count( $unique ) ? $unique[0] : 'CHANGED';
	$new_content = $content;
	if ( 'ready' === $state ) {
		foreach ( $changes as $pair ) {
			if ( substr_count( $new_content, $pair['old'] ) !== $pair['count'] ) {
				WP_CLI::error( 'Overlapping or changed replacement: ' . $pair['label'] );
			}
			$new_content = str_replace( $pair['old'], $pair['new'], $new_content );
		}
	}
	$resources[ 'page:' . $slug ] = array( 'id' => (int) $page->ID, 'type' => 'page', 'name' => $slug, 'old' => $content, 'new' => $new_content );
	$states[] = $state;
}
$snippets = get_posts( array( 'post_type' => 'wpcode', 'post_status' => 'any', 'title' => $data['snippet_title'], 'numberposts' => -1, 'suppress_filters' => true ) );
if ( 1 !== count( $snippets ) || 'publish' !== $snippets[0]->post_status ) {
	WP_CLI::error( 'Need exactly one published A11yPageJumpLinks snippet.' );
}
$snippet = $snippets[0];
$code = (string) $snippet->post_content;
$sha = hash( 'sha256', $code );
$state = $sha === $data['snippet_old_sha256'] ? 'ready' : ( $sha === $data['snippet_new_sha256'] ? 'applied' : 'CHANGED' );
WP_CLI::log( 'A11yPageJumpLinks=' . $state . ' sha256=' . $sha );
$states[] = $state;
$resources['snippet'] = array( 'id' => (int) $snippet->ID, 'type' => 'wpcode', 'name' => $data['snippet_title'], 'old' => $code, 'new' => $data['snippet_new'] );
$css_post = wp_get_custom_css_post();
if ( ! $css_post ) {
	WP_CLI::error( 'No Astra Additional CSS post.' );
}
$css = (string) $css_post->post_content;
$css_block = "\n" . $data['css'];
$state = false === strpos( $css, 'TS REVIEW A' ) ? 'ready' : ( 1 === substr_count( $css, $css_block ) && 1 === substr_count( $css, 'TS REVIEW A START' ) && 1 === substr_count( $css, 'TS REVIEW A END' ) ? 'applied' : 'CHANGED' );
$states[] = $state;
WP_CLI::log( 'Additional CSS=' . $state );
$resources['css'] = array( 'id' => (int) $css_post->ID, 'type' => 'custom_css', 'name' => $css_post->post_name, 'old' => $css, 'new' => 'ready' === $state ? $css . $css_block : $css );
$unique = array_values( array_unique( $states ) );
$overall = 1 === count( $unique ) ? $unique[0] : 'CHANGED';
WP_CLI::log( 'host=' . $home . ' state=' . $overall );
if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $overall ) {
		WP_CLI::error( 'Drift or partial application: nothing changed; inspect before proceeding.' );
	}
	WP_CLI::success( 'Preflight: ' . $overall . '; nothing changed.' );
	return;
}
$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be writable and outside the web root.' );
}
$refresh = function () {
	if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
};
$write = function ( $resource, $which ) {
	$result = wp_update_post( wp_slash( array( 'ID' => $resource['id'], 'post_content' => $resource[ $which ] ) ), true );
	if ( is_wp_error( $result ) || ! $result || (string) get_post_field( 'post_content', $resource['id'] ) !== $resource[ $which ] ) {
		throw new \RuntimeException( 'Save/read-back failed: ' . $resource['type'] . ' ' . $resource['name'] );
	}
};
if ( 'apply' === $mode ) {
	if ( 'applied' === $overall ) {
		WP_CLI::success( 'Already applied; nothing changed.' );
		return;
	}
	if ( 'ready' !== $overall ) {
		WP_CLI::error( 'Not the reviewed state; nothing changed.' );
	}
	foreach ( $resources as &$r ) {
		$r['old_sha256'] = hash( 'sha256', $r['old'] );
		$r['new_sha256'] = hash( 'sha256', $r['new'] );
	}
	unset( $r );
	$snapshot = array( 'bundle' => 'review-a-v1', 'home' => $home, 'created' => gmdate( 'c' ), 'resources' => $resources );
	$file = $backup_dir . '/bundle-a-before-' . gmdate( 'Ymd-His' ) . '.json';
	$encoded = wp_json_encode( $snapshot );
	if ( false === $encoded || false === file_put_contents( $file, $encoded, LOCK_EX ) || file_get_contents( $file ) !== $encoded || ! chmod( $file, 0600 ) ) {
		WP_CLI::error( 'Could not write/verify the private snapshot; nothing changed.' );
	}
	$destination = 'new';
} else {
	$file = $args[1] ?? '';
	$snapshot = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
	if ( ! is_array( $snapshot ) || ( $snapshot['bundle'] ?? '' ) !== 'review-a-v1' || ( $snapshot['home'] ?? '' ) !== $home || array_keys( $snapshot['resources'] ?? array() ) !== array_keys( $resources ) ) {
		WP_CLI::error( 'Wrong, missing or incomplete snapshot; nothing changed.' );
	}
	foreach ( $snapshot['resources'] as $key => $r ) {
		$p = get_post( $r['id'] );
		if ( ! $p || $p->post_type !== $r['type'] || (int) $r['id'] !== $resources[ $key ]['id'] || hash( 'sha256', $r['old'] ) !== $r['old_sha256'] || hash( 'sha256', $r['new'] ) !== $r['new_sha256'] || (string) $p->post_content !== $r['new'] ) {
			WP_CLI::error( 'Snapshot damaged or resource changed since apply: ' . $key . '. Nothing changed.' );
		}
	}
	$resources = $snapshot['resources'];
	$destination = 'old';
}
try {
	foreach ( $resources as $r ) {
		$write( $r, $destination );
	}
	$refresh();
} catch ( \Throwable $e ) {
	$undo = 'new' === $destination ? 'old' : 'new';
	try {
		foreach ( $resources as $r ) {
			$write( $r, $undo );
		}
		$refresh();
	} catch ( \Throwable $restore_error ) {
		WP_CLI::error( $e->getMessage() . '; restore also failed: ' . $restore_error->getMessage() . '. Stop and use snapshot ' . $file );
	}
	WP_CLI::error( $e->getMessage() . '; all resources restored byte for byte.' );
}
WP_CLI::success( ( 'apply' === $mode ? 'Applied Bundle A to nine pages, jump snippet and Additional CSS. Before-snapshot: ' : 'Rolled back Bundle A exactly from ' ) . $file );
