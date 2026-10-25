<?php
/**
 * Corporate & Group Massage page: the corporate rate is $100 per service hour, in both places the page says it. Ryan, 2026-10-25: "the price should be $100" (after a comparison of live with staging38 showed
 * live's rate line already saying $100 while its "What does it cost?" FAQ still said $130, and staging38 saying $130 in both).
 *
 * What it changes (WordPress's own storage; nothing else, no files): TWO exact replacements in the corporate page's content, each handled on its own:
 *   rate line   <strong>$130</strong> per service hour                           ->  <strong>$100</strong> per service hour
 *   FAQ answer  The proposed corporate rate is $130 per service hour.            ->  The proposed corporate rate is $100 per service hour.
 * A replacement whose NEW text is already there exactly once (and the old text nowhere) is "applied" and is left alone: that is how live's rate line already is, so on live only the FAQ answer changes.
 * A replacement whose OLD text is there exactly once (and the new nowhere) is "ready". Anything else (a text missing, or present twice, or both old and new) stops the run, so edits made in the meantime survive.
 * The FAQ's structured data (FAQPage) is built by Spectra from that same answer text, so it follows.
 *
 * Allow-list: localhost:8090, staging38 and live. Live was added on Ryan's instruction (the price decision, 2026-10-25) after staging38 was changed and reviewed; Ryan merges the pull request and starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: states of the two replacements (ready / applied / CHANGED); CHANGED = stop
 *   apply        snapshot the page, make every "ready" replacement, read the page back; put it back if the read-back differs
 *   rollback     FILE = the before-snapshot: turn back exactly the replacements THIS apply made (a replacement that was already applied before, like live's rate line, stays $100)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the page.)
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging38 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved blocks.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$slug  = 'corporate-massage-portland';
$pairs = array(
	'rate line'  => array( '<strong>$130</strong> per service hour', '<strong>$100</strong> per service hour' ),
	'FAQ answer' => array( 'The proposed corporate rate is $130 per service hour.', 'The proposed corporate rate is $100 per service hour.' ),
);
foreach ( $pairs as $label => $p ) {
	if ( $p[0] === $p[1] || false !== strpos( $p[1], $p[0] ) || false !== strpos( $p[0], $p[1] ) ) {
		WP_CLI::error( 'Internal error: a replacement is not what was reviewed: ' . $label );
	}
}

$read = function () use ( $slug, $pairs ) {
	$pg = get_page_by_path( $slug );
	$r  = array( 'page' => ( $pg && 'publish' === $pg->post_status ) ? $pg : null, 'items' => array() );
	foreach ( $pairs as $label => $p ) {
		if ( ! $r['page'] ) {
			$r['items'][ $label ] = 'CHANGED';
			continue;
		}
		$c     = (string) $r['page']->post_content;
		$n_old = substr_count( $c, $p[0] );
		$n_new = substr_count( $c, $p[1] );
		$r['items'][ $label ] = ( 1 === $n_old && 0 === $n_new ) ? 'ready' : ( ( 0 === $n_old && 1 === $n_new ) ? 'applied' : 'CHANGED' );
	}
	$vals       = array_values( $r['items'] );
	$r['state'] = in_array( 'CHANGED', $vals, true ) ? 'CHANGED' : ( in_array( 'ready', $vals, true ) ? 'ready' : 'applied' );
	$parts = array();
	foreach ( $r['items'] as $label => $st ) {
		$parts[] = $label . '=' . $st;
	}
	$r['report'] = implode( ' ', $parts );
	return $r;
};
$now    = $read();
$report = sprintf( 'host=%s state=%s %s', $home, $now['state'], $now['report'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $now['state'] ) {
		WP_CLI::error( 'The corporate page is not as reviewed (a price text is missing, doubled, or both old and new are there; or the page is not published); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$set_content = function ( $post_id, $content ) {
	return wp_update_post( wp_slash( array( 'ID' => (int) $post_id, 'post_content' => $content ) ), true );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $now['state'] ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $now['state'] ) {
		WP_CLI::success( 'Nothing to do: already $100 in both places. ' . $report );
		return;
	}
	$old     = (string) $now['page']->post_content;
	$changed = array();
	$new     = $old;
	foreach ( $pairs as $label => $p ) {
		if ( 'ready' === $now['items'][ $label ] ) {
			$new = str_replace( $p[0], $p[1], $new, $count );
			if ( 1 !== $count ) {
				WP_CLI::error( 'A replacement did not match exactly once (' . $label . '); nothing was changed.' );
			}
			$changed[] = $label;
		}
	}
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'page_id' => (int) $now['page']->ID, 'old_sha256' => hash( 'sha256', $old ), 'old_content' => $old, 'new_sha256' => hash( 'sha256', $new ), 'changed' => $changed );
	$file = rtrim( $backup_dir, '/' ) . '/corporate-rate-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( $snap ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$res = $set_content( $snap['page_id'], $new );
	$chk = (string) get_post_field( 'post_content', $snap['page_id'] );
	$after = $read();
	if ( is_wp_error( $res ) || ! $res || $chk !== $new || 'applied' !== $after['state'] ) {
		$set_content( $snap['page_id'], $old );
		WP_CLI::error( 'The page did not save exactly as written (' . $after['report'] . '); it was put back as it was.' );
	}
	WP_CLI::success( 'Changed to $100: ' . implode( ', ', $changed ) . ' (page #' . $snap['page_id'] . '). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || empty( $snap['old_content'] ) || ! isset( $snap['changed'] ) || ! is_array( $snap['changed'] )
	|| hash( 'sha256', (string) $snap['old_content'] ) !== ( $snap['old_sha256'] ?? '' ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, or from another site.' );
}
if ( ! $now['page'] || (int) $snap['page_id'] !== (int) $now['page']->ID ) {
	WP_CLI::error( 'The snapshot belongs to another page; nothing was changed.' );
}
$cur      = (string) $now['page']->post_content;
$restored = $cur;
$undone   = array();
foreach ( $snap['changed'] as $label ) {
	if ( ! isset( $pairs[ $label ] ) ) {
		WP_CLI::error( 'Snapshot names a replacement this installer does not know: ' . $label );
	}
	if ( 1 === substr_count( $restored, $pairs[ $label ][1] ) && 0 === substr_count( $restored, $pairs[ $label ][0] ) ) {
		$restored = str_replace( $pairs[ $label ][1], $pairs[ $label ][0], $restored );
		$undone[] = $label;
	}
}
if ( ! $undone ) {
	WP_CLI::success( 'Nothing to roll back: none of the replacements this apply made is there. ' . $report );
	return;
}
$res = $set_content( (int) $snap['page_id'], $restored );
if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', (int) $snap['page_id'] ) !== $restored ) {
	$set_content( (int) $snap['page_id'], $cur );
	WP_CLI::error( 'The page did not save exactly as written; it was put back as it was.' );
}
WP_CLI::success( 'Rolled back: ' . implode( ', ', $undone ) . ( $restored === (string) $snap['old_content'] ? ' (the page is byte-identical to the before-snapshot).' : ' (other edits made since the apply were kept, so the page differs from the snapshot).' ) );

// ref: ./build-corporate-massage.py — the source of the page text (now $100 in both places); ./install-corporate-massage.php — the release that first created the page
