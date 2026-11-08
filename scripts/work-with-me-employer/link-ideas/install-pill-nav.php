<?php
/**
 * Pinned header + sticky pills on the Thai Massage page: ADD one WPCode snippet (pill-nav.php), staging39 only. No page, CSS, menu or other snippet changes.
 * Ryan, 2026-11-08 (cloud session), after the /in-page-link-ideas/ practice page: "Pills is best. Can we also make the header static. You can practice on a real staging page, now."
 * Then, asked which header behaviour and which page: "Header stays on top", "Thai Massage".
 *
 * Allow-list: localhost:8090 (the rehearsal copy) and staging39. Live needs Ryan's go and its own enable-live change.
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = no snippet of this installer; applied = exactly one, active, code exactly as written; CHANGED = anything else (stop)
 *   apply        write a before-snapshot, create the snippet, rewrite its exact bytes, refresh WPCode's cache, read back; delete it again if anything differs
 *   rollback     FILE = the before-snapshot: delete the snippet it names (only that id, only if it carries this installer's title), refresh the cache
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}
$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging39.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging39 only).' );
}
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) {
	WP_CLI::error( 'Needs Astra, WPCode and --user=1 (an administrator).' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$bytes = (string) file_get_contents( __DIR__ . '/pill-nav.php' );
if ( 0 !== strpos( $bytes, "<?php\n" ) ) {
	WP_CLI::error( 'pill-nav.php is missing or does not start with <?php.' );
}
try {
	token_get_all( $bytes, TOKEN_PARSE );
} catch ( \ParseError $e ) {
	WP_CLI::error( 'pill-nav.php syntax: ' . $e->getMessage() );
}
$code  = substr( $bytes, 6 );
$title = 'TSPillNav practice (Thai, staging39)';
$note  = 'Created by link-ideas/install-pill-nav.php (2026-11-08); exact owned-snippet rollback only.';
if ( 2 !== substr_count( $code, "! is_page( 'thai-massage' )" ) || 1 !== substr_count( $code, 'id="ts-pill-nav-runtime"' ) || 1 !== substr_count( $code, 'id="ts-pill-nav-css"' ) ) {
	WP_CLI::error( 'Internal error: pill-nav.php is not the reviewed runtime.' );
}

$owned = function () use ( $title ) {
	$ids = array();
	foreach ( get_posts( array( 'post_type' => 'wpcode', 'post_status' => array_values( get_post_stati() ), 'numberposts' => -1, 'suppress_filters' => true ) ) as $c ) {
		if ( $c->post_title === $title || false !== strpos( (string) $c->post_content, 'ts-pill-nav-runtime' ) ) {
			$ids[] = (int) $c->ID;
		}
	}
	return $ids;
};
$refresh = function () {
	if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
};
$read = function () use ( $owned, $code ) {
	$ids = $owned();
	if ( ! $ids ) {
		return array( 'state' => 'ready', 'id' => 0, 'report' => 'no pill-nav snippet' );
	}
	$id  = $ids[0];
	$ok  = 1 === count( $ids ) && (string) get_post_field( 'post_content', $id ) === $code && 'publish' === get_post_status( $id ) && has_term( 'php', 'wpcode_type', $id );   // WPCode: active = published; the code type is the wpcode_type term
	return array( 'state' => $ok ? 'applied' : 'CHANGED', 'id' => $id, 'report' => count( $ids ) . ' pill-nav snippet(s), #' . $id . ( $ok ? ' active, code exactly as written' : ' NOT as this installer writes it' ) );
};
$now = $read();

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $now['state'] ) {
		WP_CLI::error( 'CHANGED: ' . $now['report'] . '. Stop and inspect.' );
	}
	WP_CLI::success( $now['state'] . ': ' . $now['report'] . ' (code ' . strlen( $code ) . ' bytes, sha256 ' . substr( hash( 'sha256', $code ), 0, 12 ) . ').' );
	return;
}
$dir = (string) getenv( 'WWM_BACKUP_DIR' );
if ( '' === $dir || '/' !== $dir[0] || ! is_dir( $dir ) || ! is_writable( $dir ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory.' );
}

if ( 'apply' === $mode ) {
	if ( 'applied' === $now['state'] ) {
		WP_CLI::success( 'Already applied: ' . $now['report'] . '. Nothing written.' );
		return;
	}
	if ( 'ready' !== $now['state'] ) {
		WP_CLI::error( 'CHANGED: ' . $now['report'] . '. Nothing written.' );
	}
	$file = rtrim( $dir, '/' ) . '/pill-nav-before-' . gmdate( 'Ymd-His' ) . '-' . substr( md5( uniqid( '', true ) ), 0, 8 ) . '.json';
	$snap = array( 'home' => $home, 'title' => $title, 'created' => gmdate( 'c' ), 'code_sha256' => hash( 'sha256', $code ), 'snippet_id' => 0 );
	$save = function () use ( $file, &$snap ) {
		if ( false === file_put_contents( $file, wp_json_encode( $snap, JSON_PRETTY_PRINT ) ) ) {
			WP_CLI::error( 'Could not write the snapshot ' . $file );
		}
		chmod( $file, 0600 );
	};
	$save();
	$obj = new WPCode_Snippet( array( 'title' => $title, 'code' => $code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true, 'note' => $note ) );
	$id  = $obj->save();
	if ( is_wp_error( $id ) || ! is_numeric( $id ) || (int) $id < 1 ) {
		WP_CLI::error( 'Could not create the snippet; nothing else was written.' );
	}
	$id                 = (int) $id;
	$snap['snippet_id'] = $id;
	$save();
	wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $code ) ), true );   // WPCode's first save may unslash: write the exact bytes
	$refresh();
	$after = $read();
	if ( 'applied' !== $after['state'] || $after['id'] !== $id ) {
		wp_delete_post( $id, true );
		$refresh();
		WP_CLI::error( 'Read-back differed (' . $after['report'] . '); the snippet was deleted again.' );
	}
	WP_CLI::success( 'Added WPCode snippet #' . $id . ' (' . $title . '). Before-snapshot: ' . $file );
	return;
}

// rollback FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || ( $snap['title'] ?? '' ) !== $title || empty( $snap['snippet_id'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
$sid = (int) $snap['snippet_id'];
if ( 'ready' === $now['state'] || $now['id'] !== $sid || get_post_field( 'post_title', $sid ) !== $title ) {
	WP_CLI::success( 'Nothing to roll back: the snippet this snapshot names is not there with this installer\'s title (' . $now['report'] . ').' );
	return;
}
wp_delete_post( $sid, true );
$refresh();
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (' . $check['report'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: snippet #' . $sid . ' is deleted; the header and the link row are back to how they were.' );

// ref: ./pill-nav.php — the reviewed runtime; ../section-guide/install-section-guide.php — the owned-snippet create/rollback pattern this follows
