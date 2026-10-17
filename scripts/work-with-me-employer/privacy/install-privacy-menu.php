<?php
/**
 * Put "Privacy Policy" as the LAST item of the FOOTER menu only, for ONE site at a time. The header (main) menu and the mobile menu are not touched.
 * Ryan, 2026-10-17: "add it to the menu" ... "last item" ... "no add it not to the main menu."
 *
 * Why a second menu: today ONE menu, "Primary Menu", is assigned to three theme locations (primary = the header, mobile_menu = the hamburger menu, footer_menu = the footer),
 * so an item added to it would show in all three. WordPress shows exactly one menu per location, so the footer gets its own menu, "Footer Menu": the same five items as the
 * Primary Menu (Home, About, Services, For Practices, Contact, copied from it) plus Privacy Policy last, and only the footer_menu location is pointed at it.
 * Trade-off: from now on an item added to the main menu does NOT appear in the footer by itself; add it to both.
 *
 * What it writes (all in WordPress's own storage, so a SiteGround push carries it: posts table (menu items), terms tables (the menu) and the options table (theme mod
 * nav_menu_locations); no files, no MU-plugin):
 *   1. a new menu "Footer Menu" with six items (the five copied from Primary Menu, then Privacy Policy last, linked to the /privacy-policy/ page found by its slug)
 *   2. nav_menu_locations['footer_menu'] = that menu. primary and mobile_menu keep pointing at Primary Menu; Primary Menu itself is not edited.
 *
 * Allow-list: localhost:8090 and staging31 ONLY. Live is added only on Ryan's go, after staging31 has been checked.
 * Needs the Privacy Policy page to exist already (install-privacy-page.php).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = the known single-menu setup; applied = exactly this change is there; CHANGED = anything else (stop)
 *   apply        snapshot, create the menu and its items, repoint footer_menu, read everything back; undo itself if any read-back differs
 *   rollback     FILE = the before-snapshot: repoint footer_menu at its old menu and delete the Footer Menu, if everything is still exactly as written
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging31.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging31 only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator).' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

// The Primary Menu exactly as it is on localhost, staging31 and live on 2026-10-17 (title, page id). The guard demands it.
$known = array( array( 'Home', 296 ), array( 'About', 298 ), array( 'Services', 299 ), array( 'For Practices', 1165 ), array( 'Contact', 301 ) );
$locs_wanted = array( 'primary', 'mobile_menu', 'footer_menu' );   // the locations Primary Menu serves today

/** [ [title, object_id], ... ] of a menu's items in order, or null if the menu does not exist; top-level items only count as "known". */
$items_of = function ( $menu ) {
	if ( ! $menu || is_wp_error( $menu ) ) {
		return null;
	}
	$out = array();
	foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $it ) {
		$out[] = array( (string) $it->title, (int) $it->object_id, (int) $it->menu_item_parent, (string) $it->object );
	}
	return $out;
};
$strip = function ( $rows ) {
	return null === $rows ? null : array_map( function ( $r ) {
		return array( $r[0], $r[1] );
	}, $rows );
};

/** Current state. */
$read = function () use ( $known, $locs_wanted, $items_of, $strip ) {
	$primary = wp_get_nav_menu_object( 'primary-menu' );
	$footer  = wp_get_nav_menu_object( 'footer-menu' );
	$locs    = get_nav_menu_locations();
	$page    = get_posts( array( 'post_type' => 'page', 'name' => 'privacy-policy', 'post_status' => 'publish', 'numberposts' => 1, 'suppress_filters' => true ) );
	$pid     = $page ? (int) $page[0]->ID : 0;
	$prim_it = $items_of( $primary );
	$foot_it = $items_of( $footer );
	$prim_ok = $primary && $prim_it && $strip( $prim_it ) === $known && array_sum( array_column( $prim_it, 2 ) ) === 0;
	$state   = 'CHANGED';
	if ( $prim_ok && $pid ) {
		$all_primary = true;
		foreach ( $locs_wanted as $l ) {
			$all_primary = $all_primary && isset( $locs[ $l ] ) && (int) $locs[ $l ] === (int) $primary->term_id;
		}
		if ( ! $footer && $all_primary ) {
			$state = 'ready';
		} elseif ( $footer && $strip( $foot_it ) === array_merge( $known, array( array( 'Privacy Policy', $pid ) ) )
			&& (int) ( $locs['primary'] ?? 0 ) === (int) $primary->term_id && (int) ( $locs['mobile_menu'] ?? 0 ) === (int) $primary->term_id
			&& (int) ( $locs['footer_menu'] ?? 0 ) === (int) $footer->term_id ) {
			$state = 'applied';
		}
	}
	return array( 'state' => $state, 'primary' => $primary, 'footer' => $footer, 'locs' => $locs, 'pid' => $pid, 'prim_items' => $prim_it );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s primary_menu=%s footer_menu=%s privacy_page_id=%d', $home, $state, $now['primary'] ? 'found' : 'MISSING', $now['footer'] ? 'exists' : 'absent', $now['pid'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Something differs from the reviewed state (the Primary Menu items, the menu locations, a "footer-menu" menu, or the Privacy Policy page is missing); stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$write_snap = function ( $file, $menu_id ) use ( $home, $now ) {
	$ok = false !== file_put_contents( $file, wp_json_encode( array(
		'created'       => gmdate( 'c' ),
		'home'          => $home,
		'created_menu'  => $menu_id,
		'old_locations' => $now['locs'],
	) ) );
	if ( $ok ) {
		chmod( $file, 0600 );
	}
	return $ok && filesize( $file ) > 0;
};
/** Put the locations back and delete the Footer Menu (with its items). */
$undo = function ( $menu_id, $old_locations ) {
	set_theme_mod( 'nav_menu_locations', $old_locations );
	if ( $menu_id ) {
		wp_delete_nav_menu( (int) $menu_id );
	}
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/privacy-menu-before-' . gmdate( 'Ymd-His' ) . '.json';
	if ( ! $write_snap( $file, null ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	$mid = wp_create_nav_menu( 'Footer Menu' );
	if ( is_wp_error( $mid ) || ! $mid ) {
		WP_CLI::error( 'Could not create the menu; nothing else was changed.' );
	}
	$write_snap( $file, (int) $mid );
	$pos = 0;
	foreach ( (array) wp_get_nav_menu_items( $now['primary']->term_id ) as $it ) {
		$res = wp_update_nav_menu_item( $mid, 0, array(
			'menu-item-title'       => $it->title,
			'menu-item-object'      => $it->object,
			'menu-item-object-id'   => (int) $it->object_id,
			'menu-item-type'        => $it->type,
			'menu-item-status'      => 'publish',
			'menu-item-position'    => ++$pos,
			'menu-item-parent-id'   => 0,
			'menu-item-classes'     => implode( ' ', array_filter( (array) $it->classes ) ),
			'menu-item-target'      => $it->target,
			'menu-item-attr-title'  => $it->attr_title,
			'menu-item-description' => $it->description,
			'menu-item-xfn'         => $it->xfn,
		) );
		if ( is_wp_error( $res ) ) {
			$undo( (int) $mid, $now['locs'] );
			WP_CLI::error( 'Could not copy a menu item; everything was put back.' );
		}
	}
	$res = wp_update_nav_menu_item( $mid, 0, array(
		'menu-item-title'     => 'Privacy Policy',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => (int) $now['pid'],
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-position'  => ++$pos,
		'menu-item-parent-id' => 0,
	) );
	if ( is_wp_error( $res ) ) {
		$undo( (int) $mid, $now['locs'] );
		WP_CLI::error( 'Could not add the Privacy Policy item; everything was put back.' );
	}
	$locs                = $now['locs'];
	$locs['footer_menu'] = (int) $mid;
	set_theme_mod( 'nav_menu_locations', $locs );

	$after = $read();
	// Primary Menu must be untouched: same items, same ids.
	$prim_same = $after['prim_items'] === $now['prim_items'] && $after['primary'] && (int) $after['primary']->count === (int) $now['primary']->count;
	if ( 'applied' !== $after['state'] || ! $prim_same ) {
		$undo( (int) $mid, $now['locs'] );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; everything was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Created "Footer Menu" #' . $mid . ' (5 copied items + Privacy Policy last) and pointed the footer_menu location at it. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['created_menu'] ) || ! is_array( $snap['old_locations'] ?? null ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $now['footer']->term_id !== (int) $snap['created_menu'] ) {
	WP_CLI::success( 'Nothing to roll back: the change this installer wrote is not there in its original form. ' . $report );
	return;
}
$undo( (int) $snap['created_menu'], $snap['old_locations'] );
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: footer_menu points at Primary Menu again and the Footer Menu is deleted.' );
