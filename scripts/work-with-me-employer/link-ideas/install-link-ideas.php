<?php
/**
 * In-page link ideas: ADD one practice page, /in-page-link-ideas/, on staging39 only. Nothing else is touched (no snippet, CSS, menu or other page).
 * Ryan, 2026-11-08 (cloud session): "https://staging39.thaisolitude.com use this staging ... Create a practice page and give me examples of the most modern GUI ideas I could use
 * instead of the current links that I am using for in-page links ... 5 ideas ... It needs to be real code that really works so that we know we can use it on the final version."
 * The page holds one HTML block (link-ideas.html: its own CSS and plain JavaScript, no plugin); the sample sections are placeholders with no wording.
 *
 * Allow-list: localhost:8090 (the rehearsal copy) and staging39. Never live: this is a practice page.
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = no page with the slug; applied = the page is there exactly as written; CHANGED = anything else (stop)
 *   apply        write a before-snapshot, create the page, read it back; delete it again if the read-back differs
 *   rollback     FILE = the before-snapshot: delete the page this installer created (only that page id, only if it is still exactly as written)
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
if ( ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not strip the page\'s style and script.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$slug  = 'in-page-link-ideas';
$title = 'In-page link ideas';
$src   = __DIR__ . '/link-ideas.html';
if ( ! is_file( $src ) ) {
	WP_CLI::error( 'link-ideas.html is missing next to the installer.' );
}
$html    = rtrim( (string) file_get_contents( $src ) );
$content = "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";
if ( preg_match( '/\\[[^\\]\\n]*&[^\\]\\n]*\\]/', (string) strstr( $html, '<script>' ) ) ) {
	WP_CLI::error( 'Internal error: the script has an ampersand inside [...], which WordPress escapes when it renders the page.' );
}
if ( 1 !== substr_count( $html, '<script>' ) || 1 !== substr_count( $html, '<style>' ) || false !== strpos( $html, '<!-- wp:' ) ) {
	WP_CLI::error( 'Internal error: link-ideas.html is not the reviewed single block.' );
}
// wording lock (AGENTS.md house list): the visible text must not use we/our/us, studio, or a claim verb
$visible = html_entity_decode( wp_strip_all_tags( preg_replace( '#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html ) ), ENT_QUOTES, 'UTF-8' );
if ( preg_match( '/\b(we|our|us|ours|studio)\b/i', $visible ) || preg_match( '/\bheal|cure|\btreat|diagnos|guarantee|detox|inflammation|relie[fv]/i', $visible ) ) {
	WP_CLI::error( 'The visible text uses a word this site never uses.' );
}

$read = function () use ( $slug, $content ) {
	$pages = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 5, 'suppress_filters' => true ) );
	if ( ! $pages ) {
		return array( 'state' => 'ready', 'id' => 0, 'report' => 'no page with slug ' . $slug );
	}
	$p  = $pages[0];
	$ok = 1 === count( $pages ) && 'publish' === $p->post_status && (string) $p->post_content === $content && '1' === (string) get_post_meta( $p->ID, '_ts_link_ideas', true );
	return array( 'state' => $ok ? 'applied' : 'CHANGED', 'id' => (int) $p->ID, 'report' => count( $pages ) . ' page(s) with slug ' . $slug . ', #' . $p->ID . ' ' . $p->post_status . ( $ok ? ', exactly as written' : ', NOT as this installer writes it' ) );
};
$now = $read();

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $now['state'] ) {
		WP_CLI::error( 'CHANGED: ' . $now['report'] . '. Stop and inspect.' );
	}
	WP_CLI::success( $now['state'] . ': ' . $now['report'] . ' (content ' . strlen( $content ) . ' bytes, sha256 ' . substr( hash( 'sha256', $content ), 0, 12 ) . ').' );
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
	$file = rtrim( $dir, '/' ) . '/link-ideas-before-' . gmdate( 'Ymd-His' ) . '-' . substr( md5( uniqid( '', true ) ), 0, 8 ) . '.json';
	$snap = array( 'home' => $home, 'slug' => $slug, 'created' => gmdate( 'c' ), 'content_sha256' => hash( 'sha256', $content ), 'page_id' => 0 );
	$save = function () use ( $file, &$snap ) {
		if ( false === file_put_contents( $file, wp_json_encode( $snap, JSON_PRETTY_PRINT ) ) ) {
			WP_CLI::error( 'Could not write the snapshot ' . $file );
		}
		chmod( $file, 0600 );
	};
	$save();
	$id = wp_insert_post( wp_slash( array(
		'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content,
		'post_author' => get_current_user_id(), 'comment_status' => 'closed', 'ping_status' => 'closed',
	) ), true );
	if ( is_wp_error( $id ) || ! $id ) {
		WP_CLI::error( 'Could not create the page; nothing else was written.' );
	}
	$snap['page_id'] = (int) $id;
	$save();
	update_post_meta( $id, '_ts_link_ideas', '1' );
	update_post_meta( $id, 'site-post-title', 'disabled' );   // Astra: the block carries its own heading
	$after = $read();
	if ( 'applied' !== $after['state'] || (int) $after['id'] !== (int) $id ) {
		wp_delete_post( (int) $id, true );
		WP_CLI::error( 'Read-back differed (' . $after['report'] . '); the page was deleted again.' );
	}
	WP_CLI::success( 'Added page #' . $id . ' (/' . $slug . '/). Before-snapshot: ' . $file );
	return;
}

// rollback FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || ( $snap['slug'] ?? '' ) !== $slug || empty( $snap['page_id'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
// the page this snapshot names, carrying this installer's marker (any earlier version of link-ideas.html): it is ours to delete
if ( 'ready' === $now['state'] || (int) $now['id'] !== (int) $snap['page_id'] || '1' !== (string) get_post_meta( (int) $snap['page_id'], '_ts_link_ideas', true ) ) {
	WP_CLI::success( 'Nothing to roll back: the page this snapshot names is not there with this installer\'s marker (' . $now['report'] . ').' );
	return;
}
wp_delete_post( (int) $snap['page_id'], true );
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (' . $check['report'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: page #' . $snap['page_id'] . ' (/' . $slug . '/) is deleted; nothing else was ever changed.' );

// ref: ./link-ideas.html — the reviewed page block; ../chair-massage/install-chair-massage.php — the preflight/apply/rollback pattern this follows
