<?php
/**
 * Work With Me page: make "Thammasat University" (the BA line in the credentials list) a link to https://tu.ac.th/en/, for ONE site at a time.
 * Ryan, 2026-10-18: "add a link to Thammasat University on the work-with-me page. https://tu.ac.th/en/"
 *
 * The line sits in the same list as the Still Light Center and American Heart Association links, which are plain links in the same way, so this one is plain too
 * (no new-window attribute, no extra class). It is the only place the page names the university.
 *
 * What it writes (the page's own post_content in the posts table; no files, no MU-plugin): ONE exact replacement inside the page content,
 *   <li><strong>BA in Liberal Arts (English / Communication Arts)</strong> — Thammasat University</li>
 *   -> the same line with <a href="https://tu.ac.th/en/">Thammasat University</a>.
 * Nothing else on the page is read as an instruction or changed, so edits made elsewhere on the page in the meantime survive. The guard demands the old line to occur
 * EXACTLY once and the new line zero times (ready), or the new line exactly once and the old zero times (applied); anything else stops the run.
 *
 * Allow-list: localhost:8090, staging32 and live. Live was added on Ryan's "push it" (2026-10-18), after the change was applied, checked and rollback-proven on localhost and staging32.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = the old line is there once; applied = the linked line is there once; CHANGED = anything else (stop)
 *   apply        snapshot the whole old content, make the replacement, read it back; put the old content back if any read-back differs
 *   rollback     FILE = the before-snapshot: turn the linked line back into the plain one, if the page still holds it exactly once
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the old content.)
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging32.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging32 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$slug = 'work-with-me';
$url  = 'https://tu.ac.th/en/';
$old  = '<li><strong>BA in Liberal Arts (English / Communication Arts)</strong> — Thammasat University</li>';
$new  = '<li><strong>BA in Liberal Arts (English / Communication Arts)</strong> — <a href="' . $url . '">Thammasat University</a></li>';
if ( 1 !== substr_count( $new, $url ) || $old === $new ) {
	WP_CLI::error( 'Internal error: the old and new lines are not what was reviewed.' );
}

/** Current state of the one page with this slug. */
$read = function () use ( $slug, $old, $new ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$state = 'CHANGED';
	$c     = '';
	if ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) {
		$c      = (string) $pages[0]->post_content;
		$n_old  = substr_count( $c, $old );
		$n_new  = substr_count( $c, $new );
		$n_name = substr_count( $c, 'Thammasat' );
		if ( 1 === $n_old && 0 === $n_new && 1 === $n_name ) {
			$state = 'ready';
		} elseif ( 0 === $n_old && 1 === $n_new && 1 === $n_name ) {
			$state = 'applied';
		}
	}
	return array( 'state' => $state, 'pages' => $pages, 'content' => $c );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d', $home, $state, count( $now['pages'] ) );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not in a reviewed state (missing, not published, the credentials line differs, or the university is named elsewhere); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$set_content = function ( $page_id, $content ) {
	return wp_update_post( wp_slash( array( 'ID' => (int) $page_id, 'post_content' => $content ) ), true );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: the link is already there. ' . $report );
		return;
	}
	$id          = (int) $now['pages'][0]->ID;
	$old_content = $now['content'];
	$new_content = str_replace( $old, $new, $old_content );
	$file        = rtrim( $backup_dir, '/' ) . '/thammasat-link-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok          = false !== file_put_contents( $file, wp_json_encode( array(
		'created'            => gmdate( 'c' ),
		'home'               => $home,
		'page_id'            => $id,
		'old_content_sha256' => hash( 'sha256', $old_content ),
		'new_content_sha256' => hash( 'sha256', $new_content ),
		'old_content'        => $old_content,
	) ) );
	if ( ! $ok || filesize( $file ) < 100 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$res   = $set_content( $id, $new_content );
	$after = $read();
	if ( is_wp_error( $res ) || ! $res || 'applied' !== $after['state'] || $after['content'] !== $new_content ) {
		$set_content( $id, $old_content );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; the old content was put back (state now: ' . $check['state'] . ', identical to before: ' . ( $check['content'] === $old_content ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/work-with-me/): Thammasat University now links to ' . $url . '. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || empty( $snap['old_content_sha256'] ) || ! isset( $snap['old_content'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold the linked line exactly once. ' . $report );
	return;
}
$restored = str_replace( $new, $old, $now['content'] );
$set_content( (int) $snap['page_id'], $restored );
$check = $read();
if ( 'ready' !== $check['state'] || $check['content'] !== $restored ) {
	WP_CLI::error( 'Rollback did not return to the plain line (now: ' . $check['state'] . '); stop and inspect.' );
}
$same = hash( 'sha256', $check['content'] ) === $snap['old_content_sha256'];
WP_CLI::success( 'Rollback done: the plain line is back' . ( $same ? ' and the page is byte-identical to the snapshot.' : ' (the page differs from the snapshot elsewhere: it was edited after the apply, and those edits were kept).' ) );

// ref: ../privacy/install-privacy-page-v3.php — the guarded-installer pattern this follows; ../page-content.html — the repo copy of the page, changed to match
