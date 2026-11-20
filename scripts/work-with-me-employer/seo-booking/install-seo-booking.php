<?php
/**
 * SEO booking path, for ONE site at a time. Ryan, 2026-11-20: "approach the whole website from an seo perspective. the site is very low on impressions and very low on clicks",
 * then "make changes so the SEO gets better scores. that's all i want". The reasoning and the exact text are in build-seo-booking.py (it writes seo-booking.json, which this loads).
 *
 * What it changes (WordPress's own storage; no files, no MU-plugin):
 *   1. one sentence appended to the "How Do I Book a Session?" FAQ answer on the Thai, Therapeutic and Deep Tissue pages (where to book an individual session now: Winding Willow);
 *   2. the SureRank search-result description of Thai, Deep Tissue and Home (titles unchanged);
 *   3. the LocalBusiness structured data (WPCode snippet "LocalBusinessInfo"): sameAs on the founder reference (her Winding Willow profile).
 * NOTHING ELSE changes: every old value must be exactly the reviewed one (ready) or every new value (applied); anything else stops the run, so edits made elsewhere survive.
 *
 * Allow-list: localhost:8090 and staging40. Live is added only on Ryan's go, after staging40 is applied and checked; Ryan starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the three pages, the three pages' SureRank rows and the snippet, write everything, read it back; put everything back if any read-back differs
 *   rollback     FILE = the before-snapshot: the FAQ sentences come out (other edits kept), the SureRank rows and the snippet code go back exactly
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging40.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging40 only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved code.' );
}
if ( ! class_exists( '\SureRank\Inc\API\Post' ) || ! method_exists( '\SureRank\Inc\API\Post', 'update_post_meta_common' ) ) {
	WP_CLI::error( 'SureRank page-level SEO API is not available.' );
}
if ( ! function_exists( 'wpcode' ) ) {
	WP_CLI::error( 'WPCode (insert-headers-and-footers) is not loaded.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

// ---- the reviewed content ----------------------------------------------------------------------------------------------------------------------------
$data = is_file( __DIR__ . '/seo-booking.json' ) ? json_decode( (string) file_get_contents( __DIR__ . '/seo-booking.json' ), true ) : null;
if ( ! is_array( $data ) || empty( $data['pairs'] ) || empty( $data['meta'] ) || empty( $data['snippet']['old'] ) || empty( $data['snippet']['new'] ) ) {
	WP_CLI::error( 'seo-booking.json is missing or incomplete (run build-seo-booking.py).' );
}
$pairs = $data['pairs'];
$metas = $data['meta'];
$sn    = $data['snippet'];
if ( hash( 'sha256', $sn['old'] ) !== $sn['old_sha256'] || hash( 'sha256', $sn['new'] ) !== $sn['new_sha256'] ) {
	WP_CLI::error( 'Internal error: the snippet code is not what was reviewed.' );
}
foreach ( $pairs as $p ) {
	if ( empty( $p['slug'] ) || empty( $p['old'] ) || empty( $p['new'] ) || $p['old'] === $p['new'] ) {
		WP_CLI::error( 'Internal error: a replacement is not what was reviewed: ' . ( $p['label'] ?? '?' ) );
	}
}

// ---- wording checks on everything that becomes visible (the house list; no research exemption applies here) ------------------------------------------------
$bad = array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/\bproof\b/i', '/\bmeasurable\b/i', '/\beffective/i', '/pain relief/i', '/relieve/i', '/\brelief\b/i', '/pain management/i', '/\bsciatica\b/i', '/\bheadaches?\b/i',
	'/\bchronic\b/i', '/\badhesions?\b/i', '/\bcorrective\b/i', '/\baccelerat/i', '/restore/i', '/nervous system/i', '/\bcirculation\b/i', '/\banxiety\b/i', '/\bcancer\b/i', '/\bneurological\b/i',
	'/\bdisabilit/i', '/\bprevention\b/i', '/\blasting\b/i', '/long-term wellness/i', '/overall health/i', '/real results/i', '/\bstudio\b/i', '/\b(we|our|us|ours)\b/i', '/Portland[- ]area/i',
	'/Portland metro/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i' );
$visible = array();
foreach ( $pairs as $p ) {
	$visible[ $p['label'] ] = html_entity_decode( $p['new'], ENT_QUOTES, 'UTF-8' );
}
foreach ( $metas as $m ) {
	$visible[ $m['label'] . ' description' ] = $m['new'];
	if ( strlen( $m['new'] ) > 160 || '' === $m['new'] ) {
		WP_CLI::error( 'Internal error: the ' . $m['label'] . ' description is outside the length limit (<= 160).' );
	}
}
foreach ( $visible as $label => $text ) {
	foreach ( $bad as $re ) {
		if ( preg_match( $re, $text, $mm ) ) {
			WP_CLI::error( 'The new text of "' . $label . '" contains a term this site must not use: "' . $mm[0] . '"' );
		}
	}
}
try {
	token_get_all( "<?php\n" . $sn['new'], TOKEN_PARSE );
} catch ( \ParseError $e ) {
	WP_CLI::error( 'The new snippet code does not parse: ' . $e->getMessage() . '. Nothing was changed.' );
}

// ---- the site as it is ------------------------------------------------------------------------------------------------------------------------------------
$page_of = function ( $slug ) {
	if ( 'front' === $slug ) {
		$id = (int) get_option( 'page_on_front' );
		$pg = $id && 'page' === get_option( 'show_on_front' ) ? get_post( $id ) : null;
	} else {
		$pg = get_page_by_path( $slug );
	}
	return ( $pg && 'page' === $pg->post_type && 'publish' === $pg->post_status ) ? $pg : null;
};
$rows_of = function ( $id ) {
	$rows = array();
	foreach ( get_post_meta( $id ) as $k => $v ) {
		if ( 0 === strpos( $k, 'surerank' ) ) {
			$rows[ $k ] = $v;
		}
	}
	return $rows;
};
$read = function () use ( $pairs, $metas, $sn, $page_of ) {
	$r = array( 'items' => array(), 'pages' => array() );
	foreach ( $pairs as $i => $p ) {
		$pg = $page_of( $p['slug'] );
		$r['pages'][ $p['slug'] ] = $pg;
		if ( ! $pg ) {
			$r['items'][ 'faq ' . $p['slug'] ] = 'CHANGED';
			continue;
		}
		$c     = (string) $pg->post_content;
		$n_old = substr_count( $c, $p['old'] );
		$n_new = substr_count( $c, $p['new'] );
		$r['items'][ 'faq ' . $p['slug'] ] = ( 1 === $n_old && 0 === $n_new ) ? 'ready' : ( ( 0 === $n_old && 1 === $n_new ) ? 'applied' : 'CHANGED' );
	}
	foreach ( $metas as $m ) {
		$pg = $page_of( $m['slug'] );
		$r['pages'][ $m['slug'] ] = $pg;
		$st = 'CHANGED';
		if ( $pg ) {
			$gen = get_post_meta( $pg->ID, 'surerank_settings_general', true );
			if ( is_array( $gen ) && isset( $gen['page_description'] ) && ( null === $m['title'] || ( isset( $gen['page_title'] ) && $gen['page_title'] === $m['title'] ) ) ) {
				$st = $gen['page_description'] === $m['old'] ? 'ready' : ( $gen['page_description'] === $m['new'] ? 'applied' : 'CHANGED' );
			}
		}
		$r['items'][ 'description ' . $m['slug'] ] = $st;
	}
	$sp = get_post( (int) $sn['id'] );
	$r['snippet_post'] = $sp;
	if ( ! $sp || 'wpcode' !== $sp->post_type || 'publish' !== $sp->post_status || $sp->post_title !== $sn['title'] ) {
		$r['items']['snippet'] = 'CHANGED';
	} else {
		$code = trim( (string) $sp->post_content );
		$r['items']['snippet'] = $code === trim( $sn['old'] ) ? 'ready' : ( $code === trim( $sn['new'] ) ? 'applied' : 'CHANGED' );
	}
	$vals = array_values( $r['items'] );
	$r['state'] = count( array_unique( $vals ) ) === 1 && 'CHANGED' !== $vals[0] ? $vals[0] : 'CHANGED';
	$parts = array();
	foreach ( $r['items'] as $k => $v ) {
		$parts[] = $k . '=' . $v;
	}
	$r['report'] = implode( ' ', $parts );
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s %s', $home, $state, $now['report'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Not every item is in the reviewed before (or after) state; stop and review. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}
$refresh = function () {
	if ( isset( wpcode()->cache ) && method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) {
		wpcode()->cache->cache_all_loaded_snippets();
	}
};
$set_content = function ( $post_id, $content ) {
	return wp_update_post( wp_slash( array( 'ID' => (int) $post_id, 'post_content' => $content ) ), true );
};
/** Put exactly these SureRank rows on the page (raw stored values): rows not listed are removed. */
$restore_rows = function ( $page_id, array $rows ) {
	foreach ( get_post_meta( $page_id ) as $k => $v ) {
		if ( 0 === strpos( $k, 'surerank' ) ) {
			delete_post_meta( $page_id, $k );
		}
	}
	foreach ( $rows as $k => $vals ) {
		foreach ( $vals as $raw ) {
			add_post_meta( $page_id, $k, wp_slash( maybe_unserialize( $raw ) ) );
		}
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
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'pages' => array(), 'rows' => array(), 'snippet_id' => (int) $sn['id'], 'snippet_old_code' => (string) $now['snippet_post']->post_content );
	foreach ( $now['pages'] as $slug => $pg ) {
		$snap['pages'][ $slug ] = array( 'id' => (int) $pg->ID, 'old_sha256' => hash( 'sha256', (string) $pg->post_content ), 'old_content' => (string) $pg->post_content );
	}
	foreach ( $metas as $m ) {
		$id                 = (int) $now['pages'][ $m['slug'] ]->ID;
		$snap['rows'][ $id ] = $rows_of( $id );
	}
	$snap['snippet_old_sha256'] = hash( 'sha256', $snap['snippet_old_code'] );
	$file = rtrim( $backup_dir, '/' ) . '/seo-booking-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( $snap ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );

	$undo = function () use ( $snap, $set_content, $refresh, $restore_rows ) {
		foreach ( $snap['pages'] as $pg ) {
			if ( (string) get_post_field( 'post_content', (int) $pg['id'] ) !== $pg['old_content'] ) {
				$set_content( (int) $pg['id'], $pg['old_content'] );
			}
		}
		foreach ( $snap['rows'] as $id => $rows ) {
			$restore_rows( (int) $id, $rows );
		}
		if ( (string) get_post_field( 'post_content', (int) $snap['snippet_id'] ) !== $snap['snippet_old_code'] ) {
			wp_update_post( wp_slash( array( 'ID' => (int) $snap['snippet_id'], 'post_content' => $snap['snippet_old_code'] ) ), true );
		}
		$refresh();
	};
	$fail = function ( $why ) use ( $undo, $read ) {
		$undo();
		$check = $read();
		WP_CLI::error( $why . ' Everything was put back (state now: ' . $check['state'] . ').' );
	};

	// 1. the FAQ sentences (one page each)
	foreach ( $pairs as $p ) {
		$id      = $snap['pages'][ $p['slug'] ]['id'];
		$content = str_replace( $p['old'], $p['new'], (string) get_post_field( 'post_content', $id ), $count );
		if ( 1 !== $count ) {
			$fail( 'A replacement did not match exactly once (' . $p['label'] . ').' );
		}
		$res = $set_content( $id, $content );
		if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', $id ) !== $content ) {
			$fail( 'The ' . $p['slug'] . ' page did not save exactly as written.' );
		}
	}
	// 2. the descriptions (through SureRank's own saver; the title, when stored, is passed back unchanged)
	foreach ( $metas as $m ) {
		$id     = (int) $now['pages'][ $m['slug'] ]->ID;
		$before = (string) get_post_field( 'post_content', $id );
		$gen    = get_post_meta( $id, 'surerank_settings_general', true );
		$set    = array( 'page_description' => $m['new'] );
		if ( isset( $gen['page_title'] ) ) {
			$set['page_title'] = $gen['page_title'];
		}
		\SureRank\Inc\API\Post::update_post_meta_common( $id, $set );
		if ( (string) get_post_field( 'post_content', $id ) !== $before ) {
			$fail( 'Saving the ' . $m['label'] . ' description changed the page content.' );
		}
	}
	// 3. the snippet
	$res = wp_update_post( wp_slash( array( 'ID' => (int) $sn['id'], 'post_content' => $sn['new'] ) ), true );
	$refresh();
	if ( is_wp_error( $res ) || trim( (string) get_post_field( 'post_content', (int) $sn['id'] ) ) !== trim( $sn['new'] ) ) {
		$fail( 'The snippet did not save exactly as written.' );
	}
	$after = $read();
	if ( 'applied' !== $after['state'] ) {
		$fail( 'Read-back differed from what was written (' . $after['report'] . ').' );
	}
	WP_CLI::success( 'Applied: ' . count( $pairs ) . ' FAQ sentences, ' . count( $metas ) . ' descriptions, snippet #' . (int) $sn['id'] . ' sameAs. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE --------------------------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['pages'] ) || ! isset( $snap['rows'] ) || ! isset( $snap['snippet_old_code'] ) || hash( 'sha256', (string) $snap['snippet_old_code'] ) !== ( $snap['snippet_old_sha256'] ?? '' ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, or from another site.' );
}
foreach ( $snap['pages'] as $slug => $pg ) {
	if ( empty( $pg['old_content'] ) || hash( 'sha256', (string) $pg['old_content'] ) !== ( $pg['old_sha256'] ?? '' ) ) {
		WP_CLI::error( 'Snapshot is damaged (the ' . $slug . ' page).' );
	}
}
if ( 'applied' !== $state ) {
	WP_CLI::success( 'Nothing to roll back: the site does not hold every new value exactly once. ' . $report );
	return;
}
foreach ( $pairs as $p ) {
	$id = (int) $snap['pages'][ $p['slug'] ]['id'];
	$set_content( $id, str_replace( $p['new'], $p['old'], (string) get_post_field( 'post_content', $id ) ) );   // only this sentence goes: edits made elsewhere since are kept
}
foreach ( $snap['rows'] as $id => $rows ) {
	$restore_rows( (int) $id, $rows );
}
wp_update_post( wp_slash( array( 'ID' => (int) $snap['snippet_id'], 'post_content' => (string) $snap['snippet_old_code'] ) ), true );
$refresh();
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['report'] . '); stop and inspect.' );
}
$same = true;
foreach ( $snap['pages'] as $pg ) {
	$same = $same && hash( 'sha256', (string) get_post_field( 'post_content', (int) $pg['id'] ) ) === $pg['old_sha256'];
}
$rows_same = true;
foreach ( $snap['rows'] as $id => $rows ) {
	$rows_same = $rows_same && $rows_of( (int) $id ) === $rows;
}
WP_CLI::success( 'Rollback done: pages ' . ( $same ? 'byte-identical to the snapshot' : 'differ from the snapshot elsewhere (edited after the apply; those edits were kept)' ) . '; SureRank rows ' . ( $rows_same ? 'identical' : 'DIFFER' ) . '; snippet code restored.' );

// ref: ./build-seo-booking.py, ./seo-booking.json — the reviewed text; ../availability/install-availability-update.php — the page/snippet pattern; ../contact-meta/install-contact-meta.php — the SureRank pattern
