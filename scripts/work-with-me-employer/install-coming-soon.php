<?php
/**
 * Remove the "Coming soon." banner paragraph from the top of the seven client pages (Ryan, 2026-10-16: "I'm getting rid of
 * 'Coming soon.'"). Never production.
 *
 * Hosts it may write to: localhost:8090 (practice), staging26.thaisolitude.com, and the throwaway rehearsal restores
 * localhost:8094 / localhost:8099.
 *
 * Modes (first WP-CLI argument; run with --user=1 so WordPress does not filter the saved page markup):
 *   (none) / preflight    read-only: per-page state
 *   apply                 delete the exact banner block at the very start of each page, verify each page, snapshot first
 *   rollback FILE         put the banner block back on every page that is still exactly what apply wrote
 *
 * Surgical by design: only the exact banner block is deleted. A page whose banner differs from the known block (someone edited
 * it) stops the run; everything else on a page is untouched. Page content only: posts table, deployable by a Custom Deploy.
 * Remote hosts require WWM_BACKUP_DIR (an existing writable directory outside the web root) for the rollback snapshot.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$allowed_hosts = array( 'localhost:8090', 'staging26.thaisolitude.com', 'staging27.thaisolitude.com', 'localhost:8094', 'localhost:8099' );
$home = untrailingslashit( home_url() );
$home_host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $home_host, $allowed_hosts, true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target.' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page markup.' );
}

$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$pages = array(
	296  => 'home',
	298  => 'about',
	299  => 'services',
	1160 => 'thai-massage',
	1107 => 'therapeutic-massage',
	1091 => 'deep-tissue-massage',
	301  => 'contact',
);
$banner = "<!-- wp:paragraph {\"className\":\"ts-coming-soon\"} -->\n"
	. "<p class=\"ts-coming-soon\"><strong>Coming soon.</strong> I plan to begin massage sessions in November. Questions are welcome now: <a href=\"/contact/\">get in touch with me</a>.</p>\n"
	. "<!-- /wp:paragraph -->\n\n";

// About's closing paragraph repeats the same notice ("...welcoming clients here when sessions begin in November."). It is changed with
// one exact-match replacement, only when the old sentence occurs exactly once on that page.
$extra = array(
	298 => array( array( ' welcoming clients here when sessions begin in November.</p>', ' welcoming clients here.</p>' ) ),
);
$build = static function ( $content, $id ) use ( $banner, $extra ) {
	if ( 0 !== strpos( $content, $banner ) ) {
		return null;
	}
	$new = substr( $content, strlen( $banner ) );
	foreach ( $extra[ $id ] ?? array() as $pair ) {
		if ( 1 !== substr_count( $new, $pair[0] ) ) {
			return null;
		}
		$new = str_replace( $pair[0], $pair[1], $new );
	}
	return ( false === strpos( $new, 'ts-coming-soon' ) && false === stripos( $new, 'in November' ) ) ? $new : null;
};

$state = array();
$problems = array();
foreach ( $pages as $id => $slug ) {
	$p = get_post( $id );
	if ( ! $p || 'page' !== $p->post_type || $slug !== $p->post_name || 'publish' !== $p->post_status ) {
		WP_CLI::error( "Page $id ($slug) was not found as a published page." );
	}
	// Known page = starts with the exact banner block and, once the banner (and About's one known sentence) is removed, no
	// "ts-coming-soon" or "in November" remains. Anything else means it is not the page this installer was reviewed against.
	if ( null !== $build( $p->post_content, $id ) ) {
		$state[ $id ] = 'banner';
	} elseif ( false === strpos( $p->post_content, 'ts-coming-soon' ) && false === stripos( $p->post_content, 'in November' ) ) {
		$state[ $id ] = 'clean';
	} else {
		$state[ $id ] = 'CHANGED';
		$problems[] = $slug;
	}
}
$report = implode( ', ', array_map( static function ( $id ) use ( $pages, $state ) { return $pages[ $id ] . '=' . $state[ $id ]; }, array_keys( $pages ) ) );

if ( 'preflight' === $mode ) {
	if ( $problems ) {
		WP_CLI::error( 'A page differs from the known banner block or still mentions November elsewhere; stop and review: ' . implode( ', ', $problems ) . ' | ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. host=' . $home . ' | ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
$is_local_practice = 'localhost:8090' === $home_host;
if ( $backup_dir ) {
	if ( '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
		WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
	}
} elseif ( ! $is_local_practice ) {
	WP_CLI::error( 'WWM_BACKUP_DIR is required for this host (rollback snapshot).' );
}

if ( 'apply' === $mode ) {
	if ( $problems ) {
		WP_CLI::error( 'A page differs from the known banner block; nothing was changed: ' . implode( ', ', $problems ) . ' | ' . $report );
	}
	$todo = array_keys( array_filter( $state, static function ( $s ) { return 'banner' === $s; } ) );
	if ( ! $todo ) {
		WP_CLI::success( 'Nothing to do: no page carries the banner. ' . $report );
		return;
	}
	$snapshot = array( 'created' => gmdate( 'c' ), 'home' => $home, 'banner_sha' => hash( 'sha256', $banner ), 'pages' => array() );
	foreach ( $todo as $id ) {
		$c = get_post( $id )->post_content;
		$snapshot['pages'][ $id ] = array( 'slug' => $pages[ $id ], 'before_content' => $c, 'before_hash' => hash( 'sha256', $c ), 'after_hash' => hash( 'sha256', $build( $c, $id ) ) );
	}
	$file = '';
	if ( $backup_dir ) {
		$file = rtrim( $backup_dir, '/' ) . '/coming-soon-before-' . gmdate( 'Ymd-His' ) . '.json';
		if ( false === file_put_contents( $file, wp_json_encode( $snapshot ) ) || ! filesize( $file ) ) {
			WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
		}
	}
	$done = array();
	foreach ( $todo as $id ) {
		$new = $build( get_post( $id )->post_content, $id );
		$r = wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $new ) ), true );
		$ok = ! is_wp_error( $r ) && get_post( $id )->post_content === $new;
		if ( ! $ok ) {
			foreach ( array_merge( $done, array( $id ) ) as $back ) {   // undo this run completely
				wp_update_post( wp_slash( array( 'ID' => $back, 'post_content' => $snapshot['pages'][ $back ]['before_content'] ) ), true );
			}
			WP_CLI::error( 'Page ' . $pages[ $id ] . ' did not save exactly as expected; every page changed in this run was restored.' );
		}
		$done[] = $id;
	}
	WP_CLI::success( 'Removed the banner from ' . count( $done ) . ' page(s): ' . implode( ', ', array_map( static function ( $i ) use ( $pages ) { return $pages[ $i ]; }, $done ) ) . ( $file ? ' | rollback: rollback ' . $file : '' ) );
	return;
}

// rollback FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || empty( $snap['pages'] ) || ( $snap['home'] ?? '' ) !== $home || ( $snap['banner_sha'] ?? '' ) !== hash( 'sha256', $banner ) ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
$restored = array();
$skipped = array();
foreach ( $snap['pages'] as $id => $entry ) {
	$p = get_post( (int) $id );
	if ( $p && hash( 'sha256', $p->post_content ) === $entry['after_hash'] ) {
		$r = wp_update_post( wp_slash( array( 'ID' => (int) $id, 'post_content' => $entry['before_content'] ) ), true );
		if ( is_wp_error( $r ) || get_post( (int) $id )->post_content !== $entry['before_content'] ) {
			WP_CLI::error( 'Could not restore ' . $entry['slug'] . '; stop and restore from the database export.' );
		}
		$restored[] = $entry['slug'];
	} else {
		$skipped[] = $entry['slug'];   // edited since apply: never overwrite
	}
}
WP_CLI::success( 'Restored the banner on: ' . ( $restored ? implode( ', ', $restored ) : 'none' ) . ( $skipped ? ' | left alone (changed since apply): ' . implode( ', ', $skipped ) : '' ) );

// ref: ./install-staging.php — the hiring-page installer this sits beside; ./push-coming-soon-staging26.sh — runs this on staging26
// ref: ../../AGENTS.md — page content lives in WordPress storage; ask twice before removing (Ryan confirmed 2026-10-16)
