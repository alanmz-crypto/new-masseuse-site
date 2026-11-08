<?php
/**
 * Sitewide cleanup on staging39: REMOVE the 3px reading-progress bar (the marked block in the Additional CSS) and SWITCH OFF Astra's scroll-to-top button.
 * Ryan, 2026-11-08 (cloud session): "because we have the progress bar with the indexed items. It's unnecessary to have that incredibly minimal progress bar right
 * above it. That's on every page. Remove it ... You can also get rid of the scroll to the top button". Asked where: "Every page on staging39".
 * Nothing else changes: the rest of the Additional CSS is byte-identical, and only the one Astra setting `scroll-to-top-enable` is switched (true -> false).
 *
 * Allow-list: localhost:8090 (the rehearsal copy) and staging39. Live needs Ryan's go and its own enable-live change.
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = the block is there exactly once and the button is on; applied = no block and the button is off; CHANGED = anything else (stop)
 *   apply        write a before-snapshot (the whole CSS and the old setting), remove the block, switch the button off, read both back; put both back if anything differs
 *   rollback     FILE = the before-snapshot: put the block back exactly where it was and switch the button on again
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
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'edit_css' ) || ! current_user_can( 'manage_options' ) ) {
	WP_CLI::error( 'Needs Astra and --user=1 (an administrator).' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$key = 'scroll-to-top-enable';
$read = function () use ( $key ) {
	$post  = wp_get_custom_css_post( 'astra' );
	$css   = $post ? (string) $post->post_content : '';
	$found = preg_match_all( '#\n?/\* ts-reading-progress:start[^*]*\*/.*?/\* ts-reading-progress:end \*/\n?#s', $css, $m, PREG_OFFSET_CAPTURE );
	$opt   = get_option( 'astra-settings' );
	$on    = is_array( $opt ) && array_key_exists( $key, $opt ) ? $opt[ $key ] : null;
	$state = 'CHANGED';
	if ( 1 === $found && true === $on ) {
		$state = 'ready';
	} elseif ( 0 === $found && false === $on && false === strpos( $css, 'ts-reading-progress' ) ) {
		$state = 'applied';
	}
	return array( 'state' => $state, 'css' => $css, 'block' => $found ? $m[0][0][0] : '', 'offset' => $found ? (int) $m[0][0][1] : -1, 'count' => (int) $found, 'on' => $on,
		'report' => 'reading-progress blocks: ' . (int) $found . ', ' . $key . ': ' . var_export( $on, true ) );
};
$set_css = function ( $css ) {
	$r = wp_update_custom_css_post( $css, array( 'stylesheet' => 'astra' ) );
	$p = wp_get_custom_css_post( 'astra' );
	return ! is_wp_error( $r ) && $p && (string) $p->post_content === $css;
};
$set_on = function ( $value ) use ( $key ) {
	$opt = get_option( 'astra-settings' );
	if ( ! is_array( $opt ) ) {
		return false;
	}
	$opt[ $key ] = $value;
	update_option( 'astra-settings', $opt );
	$check = get_option( 'astra-settings' );
	return is_array( $check ) && array_key_exists( $key, $check ) && $value === $check[ $key ];
};
$now = $read();

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $now['state'] ) {
		WP_CLI::error( 'CHANGED: ' . $now['report'] . '. Stop and inspect.' );
	}
	WP_CLI::success( $now['state'] . ': ' . $now['report'] . ' (CSS ' . strlen( $now['css'] ) . ' bytes, sha256 ' . substr( hash( 'sha256', $now['css'] ), 0, 12 ) . ').' );
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
	// keep one line break where the block was, so the CSS around it is unchanged
	$lead    = "\n" === substr( $now['block'], 0, 1 ) ? "\n" : '';
	$new_css = substr( $now['css'], 0, $now['offset'] ) . ( $lead && "\n" === substr( $now['block'], -1 ) ? "\n" : '' ) . substr( $now['css'], $now['offset'] + strlen( $now['block'] ) );
	$file    = rtrim( $dir, '/' ) . '/sitewide-cleanup-before-' . gmdate( 'Ymd-His' ) . '-' . substr( md5( uniqid( '', true ) ), 0, 8 ) . '.json';
	$snap    = array( 'home' => $home, 'created' => gmdate( 'c' ), 'css' => $now['css'], 'css_sha256' => hash( 'sha256', $now['css'] ), 'new_css_sha256' => hash( 'sha256', $new_css ), 'key' => $key, 'old' => $now['on'] );
	if ( false === file_put_contents( $file, wp_json_encode( $snap, JSON_PRETTY_PRINT ) ) ) {
		WP_CLI::error( 'Could not write the snapshot; nothing changed.' );
	}
	chmod( $file, 0600 );
	$ok = $set_css( $new_css ) && $set_on( false );
	$after = $read();
	if ( ! $ok || 'applied' !== $after['state'] || hash( 'sha256', $after['css'] ) !== $snap['new_css_sha256'] ) {
		$set_css( $now['css'] );
		$set_on( true );
		WP_CLI::error( 'Read-back differed (' . $after['report'] . '); both were put back.' );
	}
	WP_CLI::success( 'Removed the reading-progress block (' . strlen( $now['block'] ) . ' bytes) from the Additional CSS and switched off ' . $key . '. Before-snapshot: ' . $file );
	return;
}

// rollback FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || ! isset( $snap['css'] ) || hash( 'sha256', (string) $snap['css'] ) !== ( $snap['css_sha256'] ?? '' ) || true !== ( $snap['old'] ?? null ) ) {
	WP_CLI::error( 'Snapshot is missing, damaged, or from another site.' );
}
if ( 'applied' !== $now['state'] ) {
	WP_CLI::success( 'Nothing to roll back: ' . $now['report'] . '.' );
	return;
}
if ( hash( 'sha256', $now['css'] ) !== ( $snap['new_css_sha256'] ?? '' ) ) {
	WP_CLI::error( 'The Additional CSS was edited after the apply; refusing to overwrite it. Put the block back by hand from the snapshot ' . $file . '.' );
}
if ( ! $set_css( (string) $snap['css'] ) || ! $set_on( true ) ) {
	WP_CLI::error( 'Rollback did not read back; stop and inspect.' );
}
$check = $read();
if ( 'ready' !== $check['state'] || hash( 'sha256', $check['css'] ) !== $snap['css_sha256'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (' . $check['report'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: the reading-progress block is back (CSS byte-identical to the snapshot) and ' . $key . ' is on again.' );

// ref: ../reading-progress-sitewide/install-reading-progress-sitewide.php — added the block this removes (wp_update_custom_css_post pattern)
