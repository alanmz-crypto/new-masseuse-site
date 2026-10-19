<?php
/**
 * Thai Massage page: swap its three illustrations, for ONE site at a time.
 * Ryan, 2026-10-19: "take the image that matches Thai Massage on the services page and apply it to the first image on the thai massage page. then replace the next two images with these
 * thai_massage_immage.webp thai_massage_immage_magenta.webp. doing this on [the new staging]" (he wrote staging34 and, once, staging44; only staging34 exists).
 *
 *   image 1 (the hero, block 5b6bb4f3, was media item 911 ComfyUI_01044_.webp): the Services page's Thai Massage image, media item 915 (00illaCNN512Curve.webp). The SAME media item is reused, not a copy.
 *   image 2 (block 04d0e681, was item 1107): thai_massage_immage.webp           (imported into the media library by this installer)
 *   image 3 (block f48b2ed7, was item 1132): thai_massage_immage_magenta.webp   (imported into the media library by this installer)
 * The three blocks keep their ids, sizes, crop (object-fit: cover), radius and shadow; only the image fields change: url/urlTablet/urlMobile, id, alt and the natural size (512 x 460), inside each block's
 * own text (each replacement is counted and must match). Nothing else on the page is touched, and the old media items are NOT deleted (the Deep Tissue page still uses them).
 *
 * Alt text (new; the old text described a different picture): 1 "Illustration of a massage therapist working on the back of a client lying on a table."
 * 2 "Illustration of a massage therapist giving a back massage in a calm room with plants."  3 "Illustration of a client lying on a massage table while a therapist works on their back, with plants behind them."
 *
 * What it writes: the page's own post_content, plus two new media-library items (files under wp-content/uploads and their attachment rows). No MU-plugin, no theme or CSS change.
 * The guard: ready = the three blocks are found once each, hold their old media ids and urls; applied = they hold the new ones; anything else stops. The two source files are checked against their sha256.
 * Rollback: puts the three blocks back exactly (the page returns byte-for-byte when nothing else was edited) and deletes ONLY the media items this apply imported, and only if nothing else references them.
 *
 * Allow-list: localhost:8090, staging34 and live. Live was added by Ryan's go ("finish one and two" = the image swap on live, 2026-10-19) after the change was applied, checked and rollback-proven on localhost and
 * staging34; he applies it himself (the auto-mode classifier refuses an agent adding a live host).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the page, import the two images, make the three replacements, read back; undo everything if the read-back differs
 *   rollback     FILE = the before-snapshot
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging34.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging34 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'upload_files' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress may save the page and upload files.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$slug    = 'thai-massage';
$old_alt = 'Illustration of a massage therapist standing beside a client lying on a massage table.';
$files   = array(
	'natural' => array( 'name' => 'thai_massage_immage.webp', 'sha256' => 'c6c8d58c7d7eef00579e83ee8034b527f75de7edcdae8963f37ed13036044a92', 'title' => 'Thai massage illustration' ),
	'magenta' => array( 'name' => 'thai_massage_immage_magenta.webp', 'sha256' => 'c61db2400849683dd3f5b50eb27f6d6b68b076b56b7356ec541d8abbc12a274e', 'title' => 'Thai massage illustration (magenta)' ),
);
$blocks  = array(
	'5b6bb4f3' => array( 'old_id' => 911, 'old_file' => 'ComfyUI_01044_.webp', 'source' => 'item915', 'alt' => 'Illustration of a massage therapist working on the back of a client lying on a table.' ),
	'04d0e681' => array( 'old_id' => 1107, 'old_file' => 'mcdZCAyR9U1OAhvDYTI4I_206e1effba9046e2a30ec4af77b77463.webp', 'source' => 'natural', 'alt' => 'Illustration of a massage therapist giving a back massage in a calm room with plants.' ),
	'f48b2ed7' => array( 'old_id' => 1132, 'old_file' => 'yCn9FkO5cUOtHrjOvXn2V_2409a2f00dd34934aa54ff6447f34c40-1.webp', 'source' => 'magenta', 'alt' => 'Illustration of a client lying on a massage table while a therapist works on their back, with plants behind them.' ),
);
foreach ( $files as $f ) {
	$p = __DIR__ . '/images/' . $f['name'];
	if ( ! is_file( $p ) || hash_file( 'sha256', $p ) !== $f['sha256'] ) {
		WP_CLI::error( 'Source image missing or not the reviewed file: ' . $f['name'] );
	}
}

// ---- wording check on the new alt text (this site's forbidden terms and house voice) ---------------------------------------------------------------------
$bad = array( '/\bheal/i', '/\bproof\b/i', '/\beffective/i', '/relie(f|ve)/i', '/\bchronic\b/i', '/\banxiety\b/i', '/\bstudio\b/i', '/\b(we|our|us)\b/i', '/cure/i', '/\btreat/i', '/diagnos/i' );
foreach ( $blocks as $b ) {
	foreach ( $bad as $re ) {
		if ( preg_match( $re, $b['alt'], $m ) ) {
			WP_CLI::error( 'The new alt text contains a term this site must not use: "' . $m[0] . '"' );
		}
	}
	if ( false !== strpos( $b['alt'], '"' ) || false !== strpos( $b['alt'], "'" ) || false !== strpos( $b['alt'], '&' ) ) {
		WP_CLI::error( 'Internal error: the alt text must not contain quotes or ampersands (they are stored differently in the attribute and the block comment).' );
	}
}

// ---- helpers -----------------------------------------------------------------------------------------------------------------------------------------
$alt_for_source = array( 'natural' => $blocks['04d0e681']['alt'], 'magenta' => $blocks['f48b2ed7']['alt'] );   // the imported media items carry the same alt text as their block
$find_page = function () use ( $slug ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	return ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) ? $pages[0] : null;
};
/** The one stored image block with this block_id, or null if it is missing or duplicated. */
$find_block = function ( $content, $block_id ) {
	$re = '#<!-- wp:uagb/image \{"block_id":"' . $block_id . '".*?\} -->\n.*?\n<!-- /wp:uagb/image -->#s';
	if ( 1 !== preg_match_all( $re, $content, $m ) ) {
		return null;
	}
	return $m[0][0];
};
$by_sha = function ( $sha ) {
	$q = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 2, 'meta_key' => '_ts_src_sha256', 'meta_value' => $sha, 'suppress_filters' => true ) );
	return 1 === count( $q ) ? (int) $q[0]->ID : 0;
};
/** The new media id for a block, or 0 if it does not exist yet. */
$new_id_for = function ( $b ) use ( $files, $by_sha ) {
	return 'item915' === $b['source'] ? 915 : $by_sha( $files[ $b['source'] ]['sha256'] );
};
/** The block's own old image url (from its "url" attribute), only if it is the reviewed file and the stored block still looks exactly as reviewed; else null. */
$old_url_of = function ( $t, $b ) use ( $old_alt ) {
	if ( 1 !== preg_match( '#"url":"([^"]+/' . preg_quote( $b['old_file'], '#' ) . ')"#', $t, $m ) ) {
		return null;
	}
	$ok = 7 === substr_count( $t, $m[1] ) && 2 === substr_count( $t, $old_alt ) && 1 === substr_count( $t, '"id":' . $b['old_id'] . ',"linkDestination"' )
		&& 1 === substr_count( $t, 'uag-image-' . $b['old_id'] . '"' ) && 1 === substr_count( $t, '"naturalWidth":500,"naturalHeight":340' );
	return $ok ? $m[1] : null;
};
/** The exact (old text, new text, expected count) pairs for one block. */
$pairs_for = function ( $b, $old_url, $new_id ) use ( $old_alt ) {
	$new_url = wp_get_attachment_url( $new_id );
	if ( ! $new_url ) {
		return null;
	}
	return array(
		array( $old_url, $new_url, 7 ),
		array( $old_alt, $b['alt'], 2 ),
		array( '"id":' . $b['old_id'] . ',"linkDestination"', '"id":' . $new_id . ',"linkDestination"', 1 ),
		array( 'uag-image-' . $b['old_id'] . '"', 'uag-image-' . $new_id . '"', 1 ),
		array( '"naturalWidth":500,"naturalHeight":340', '"naturalWidth":512,"naturalHeight":460', 1 ),
	);
};
/** Does the block already hold the NEW image (item $nid) exactly as written by this installer? */
$is_new = function ( $t, $b, $nid ) {
	$url = $nid ? wp_get_attachment_url( $nid ) : false;
	return $url && 7 === substr_count( $t, $url ) && 2 === substr_count( $t, $b['alt'] ) && 1 === substr_count( $t, '"id":' . $nid . ',"linkDestination"' )
		&& 1 === substr_count( $t, 'uag-image-' . $nid . '"' ) && 1 === substr_count( $t, '"naturalWidth":512,"naturalHeight":460' );
};
/** Apply (or reverse) the pairs inside ONE block text; null if any pair does not match the expected number of times. */
$xform = function ( $text, $pairs, $reverse ) {
	foreach ( $pairs as $p ) {
		list( $from, $to, $n ) = $reverse ? array( $p[1], $p[0], $p[2] ) : array( $p[0], $p[1], $p[2] );
		if ( $n !== substr_count( $text, $from ) ) {
			return null;
		}
		$text = str_replace( $from, $to, $text );
	}
	return $text;
};
/** State of the page: ready / applied / CHANGED, with the per-block detail. */
$read = function () use ( $find_page, $find_block, $blocks, $new_id_for, $old_url_of, $is_new ) {
	$page = $find_page();
	$out  = array( 'state' => 'CHANGED', 'page' => $page, 'content' => $page ? (string) $page->post_content : '', 'detail' => array(), 'old_urls' => array() );
	if ( ! $page ) {
		return $out;
	}
	$ready = true;
	$appl  = true;
	foreach ( $blocks as $bid => $b ) {
		$t = $find_block( $out['content'], $bid );
		if ( null === $t ) {
			$out['detail'][ $bid ] = 'CHANGED (block missing or duplicated)';
			$ready = $appl = false;
			continue;
		}
		$old = $old_url_of( $t, $b );
		if ( $old ) {
			$out['detail'][ $bid ]   = 'ready';
			$out['old_urls'][ $bid ] = $old;
			$appl = false;
		} elseif ( $is_new( $t, $b, $new_id_for( $b ) ) ) {
			$out['detail'][ $bid ] = 'applied';
			$ready = false;
		} else {
			$out['detail'][ $bid ] = 'CHANGED';
			$ready = $appl = false;
		}
	}
	$out['state'] = $ready ? 'ready' : ( $appl ? 'applied' : 'CHANGED' );
	return $out;
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s blocks=%s', $home, $state, wp_json_encode( $now['detail'] ) );

// media item 915 must be the Services page's Thai image
$i915 = get_post( 915 );
if ( ! $i915 || 'attachment' !== $i915->post_type || '00illaCNN512Curve.webp' !== basename( (string) get_attached_file( 915 ) ) || 'image/webp' !== get_post_mime_type( 915 ) ) {
	WP_CLI::error( 'Media item 915 is not the Services page Thai image (00illaCNN512Curve.webp); stop and review. ' . $report );
}
if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not in a reviewed state; stop and review. ' . $report );
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
$all_pairs = function ( array $old_urls ) use ( $blocks, $new_id_for, $pairs_for ) {
	$m = array();
	foreach ( $blocks as $bid => $b ) {
		$nid = $new_id_for( $b );
		if ( ! $nid || empty( $old_urls[ $bid ] ) ) {
			return null;
		}
		$pairs = $pairs_for( $b, $old_urls[ $bid ], $nid );
		if ( ! $pairs ) {
			return null;
		}
		$m[ $bid ] = array( 'new_id' => $nid, 'old_url' => $old_urls[ $bid ], 'pairs' => $pairs );
	}
	return $m;
};
$edit_content = function ( $content, $map, $reverse ) use ( $find_block, $xform ) {
	foreach ( $map as $bid => $m ) {
		$t = $find_block( $content, $bid );
		if ( null === $t ) {
			return null;
		}
		$n = $xform( $t, $m['pairs'], $reverse );
		if ( null === $n ) {
			return null;
		}
		if ( 1 !== substr_count( $content, $t ) ) {
			return null;
		}
		$content = str_replace( $t, $n, $content );
	}
	return $content;
};
/** Delete a media item only if nothing but its own revisions mention it. */
$delete_if_unused = function ( $id ) {
	global $wpdb;
	$hits = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','attachment') AND post_status <> 'trash' AND ( post_content LIKE %s OR post_content LIKE %s )", '%uag-image-' . (int) $id . '"%', '%"id":' . (int) $id . ',"linkDestination"%' ) );
	if ( $hits > 0 ) {
		return false;
	}
	return (bool) wp_delete_attachment( (int) $id, true );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: the images are already in. ' . $report );
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$page        = $now['page'];
	$old_content = $now['content'];
	$file        = rtrim( $backup_dir, '/' ) . '/thai-images-before-' . gmdate( 'Ymd-His' ) . '.json';
	$snap        = array( 'created' => gmdate( 'c' ), 'home' => $home, 'page_id' => (int) $page->ID, 'old_content_sha256' => hash( 'sha256', $old_content ), 'old_content' => $old_content, 'created_attachments' => array() );
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || filesize( $file ) < 100 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$undo_all = function ( $why ) use ( &$snap, $set_content, $page, $old_content, $delete_if_unused ) {
		$set_content( $page->ID, $old_content );
		foreach ( $snap['created_attachments'] as $id ) {
			$delete_if_unused( $id );
		}
		WP_CLI::error( $why . ' Everything already written was put back (page restored, imported images removed).' );
	};
	// 1. import the two images (a repeat run reuses an item it already imported)
	foreach ( $files as $key => $f ) {
		if ( $by_sha( $f['sha256'] ) ) {
			continue;
		}
		$tmp = wp_tempnam( $f['name'] );
		if ( ! copy( __DIR__ . '/images/' . $f['name'], $tmp ) ) {
			$undo_all( 'Could not stage ' . $f['name'] . '.' );
		}
		$id = media_handle_sideload( array( 'name' => $f['name'], 'tmp_name' => $tmp ), 0, $f['title'] );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
			$undo_all( 'Import of ' . $f['name'] . ' failed: ' . $id->get_error_message() . '.' );
		}
		$snap['created_attachments'][] = (int) $id;
		file_put_contents( $file, wp_json_encode( $snap ) );   // keep the list current in case a later step fails
		update_post_meta( $id, '_ts_src_sha256', $f['sha256'] );
		update_post_meta( $id, '_wp_attachment_image_alt', $alt_for_source[ $key ] );
	}
	$map = $all_pairs( $now['old_urls'] );
	if ( ! $map ) {
		$undo_all( 'A media item needed for the replacement is missing.' );
	}
	$new_content = $edit_content( $old_content, $map, false );
	if ( null === $new_content || $new_content === $old_content ) {
		$undo_all( 'The three image blocks did not match what was reviewed.' );
	}
	$snap['new_content_sha256'] = hash( 'sha256', $new_content );
	$snap['map']                = array_map( function ( $m ) {
		return array( 'new_id' => $m['new_id'], 'old_url' => $m['old_url'] );
	}, $map );
	file_put_contents( $file, wp_json_encode( $snap ) );
	// 2. write the page and read it back: the stored page is exactly what was written, and reversing the three edits gives the old page byte for byte
	$res   = $set_content( $page->ID, $new_content );
	$after = $read();
	$back  = $edit_content( $after['content'], $map, true );
	$ok    = ! is_wp_error( $res ) && $res && 'applied' === $after['state'] && $after['content'] === $new_content && $back === $old_content
		&& substr_count( $after['content'], 'What the Research Says About Thai Massage' ) === substr_count( $old_content, 'What the Research Says About Thai Massage' )
		&& substr_count( $after['content'], 'doctor' ) === substr_count( $old_content, 'doctor' );
	foreach ( $map as $m ) {
		$p = get_attached_file( $m['new_id'] );
		$ok = $ok && $p && is_file( $p );
	}
	if ( ! $ok ) {
		$undo_all( 'Read-back differed from what was written.' );
	}
	WP_CLI::success( 'Page #' . $page->ID . ' (/thai-massage/): the three images are swapped (new media items: ' . ( $snap['created_attachments'] ? implode( ', ', $snap['created_attachments'] ) : 'none, reused' ) . '). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || empty( $snap['old_content_sha256'] ) || ! isset( $snap['old_content'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $now['page']->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold all three new images. ' . $report );
	return;
}
$snap_urls = array();
foreach ( (array) ( $snap['map'] ?? array() ) as $bid => $m ) {
	$snap_urls[ $bid ] = $m['old_url'] ?? '';
}
$map = $all_pairs( $snap_urls );
if ( ! $map ) {
	WP_CLI::error( 'The new media items or the snapshot map are not there any more; stop and inspect.' );
}
$restored = $edit_content( $now['content'], $map, true );
if ( null === $restored ) {
	WP_CLI::error( 'The three image blocks do not match what the apply wrote; stop and inspect.' );
}
$set_content( (int) $snap['page_id'], $restored );
$check = $read();
$not_ready = array_filter( $check['detail'], function ( $d ) {
	return 0 !== strpos( $d, 'ready' );
} );
if ( $not_ready || 3 !== count( $check['detail'] ) ) {
	WP_CLI::error( 'Rollback did not return the old images (now: ' . wp_json_encode( $check['detail'] ) . '); stop and inspect.' );
}
$same = hash( 'sha256', $check['content'] ) === $snap['old_content_sha256'];
$gone = array();
foreach ( (array) ( $snap['created_attachments'] ?? array() ) as $id ) {
	$gone[] = $id . ( $delete_if_unused( (int) $id ) ? ' removed' : ' kept (still referenced)' );
}
WP_CLI::success( 'Rollback done: the old images are back' . ( $same ? ' and the page is byte-identical to the snapshot' : ' (the page differs from the snapshot elsewhere: it was edited after the apply, and those edits were kept)' ) . '. Imported media: ' . ( $gone ? implode( '; ', $gone ) : 'none to remove' ) . '.' );

// ref: ./images/ — the two reviewed source files (sha256 checked); ./install-thai-evidence.php — the sibling installer for the same page; ../therapeutic/install-therapeutic-copy.php — the guarded pattern
