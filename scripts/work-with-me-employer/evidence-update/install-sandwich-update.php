<?php
/**
 * Thai Massage and Therapeutic Massage pages: replace the research section with the "sandwich" version (positive findings, then the weaker, mixed and negative ones in full, then an encouraging close; plus the results the
 * 2026-10-19 audit found were missing), for ONE site at a time. Both pages together.
 * Source: Ryan, 2026-10-19 ("would you double check on both of the pages we just researched to make sure I didn't miss any discouraging or poor results ... so we can sandwich the discouraging results between two
 * assertive positive claims", then "you can sandwich with the method you used on the Therapeutic page"). The old and new text come from build-sandwich-update.py (sandwich-update-content.php, generated); the old text
 * is the research section exactly as it is live today (thai-research-v1.html, therapeutic-research-v1.html).
 *
 * What it writes: the posts-table content of the two pages, replacing ONLY the text from the research heading to the end of the page. The body, FAQ, heritage line, pictures, headings above and the numbered markers
 * above the heading are not touched, so nothing above can renumber. No files, no MU-plugin, no CSS.
 * The guard: ready = on BOTH pages the old research text occurs exactly once and is the very end of the page; applied = on BOTH pages the new text occurs once and is the very end; anything else (one page done and
 * not the other, a hand-edited section, a page missing or unpublished) stops the run.
 *
 * WORDING (AGENTS.md, 2026-10-19): the new research text gets the claim verbs and house-voice words refused everywhere; the evidence vocabulary is accepted because it is inside the research section; the verbatim
 * titles in the Sources list are accepted as published apart from the house-voice words. Every sentence was checked against its paper's abstract. Not legal advice or a Board ruling.
 *
 * Allow-list: localhost:8090, staging34 and live. Live was added by Ryan's go ("you can sandwich", 2026-10-19) after the change was applied, checked and rollback-proven on localhost and staging34;
 * he applies it himself (the auto-mode classifier refuses an agent adding a live host).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot both pages' whole old content, swap the sections, read everything back; put both pages' old content back if any read-back differs
 *   rollback     FILE = the before-snapshot: put the old research text back on both pages, if they still hold exactly what apply wrote
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of each old content.)
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
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved pages.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$data  = require __DIR__ . '/sandwich-update-content.php';
$pages = $data['pages'];
if ( 2 !== count( $pages ) ) {
	WP_CLI::error( 'Internal error: the update content must hold exactly the Thai Massage and Therapeutic Massage pages.' );
}

// ---- wording checks on the NEW text ----------------------------------------------------------------------------------------------------------------
$voice  = array( '/\b(we|our|us|ours)\b/i', '/\bstudio\b/i', '/Portland[- ]area/i', '/Portland metro/i' );
$claims = array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i' );
$scan   = function ( $label, $text, $lists ) {
	foreach ( $lists as $list ) {
		foreach ( $list as $re ) {
			if ( preg_match( $re, $text, $m ) ) {
				WP_CLI::error( 'Wording check failed in ' . $label . ': "' . $m[0] . '" (' . $re . ')' );
			}
		}
	}
};
$plain = function ( $html ) {
	return html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );
};
$ok_hosts = array( 'pubmed.ncbi.nlm.nih.gov', 'doi.org', 'www.cochrane.org', 'www.nccih.nih.gov', 'ich.unesco.org' );
foreach ( $pages as $pg ) {
	foreach ( array( 'old_research', 'new_research', 'research_start' ) as $k ) {
		if ( '' === (string) $pg[ $k ] ) {
			WP_CLI::error( 'Internal error: an empty reviewed text for ' . $pg['slug'] . ' (' . $k . ').' );
		}
	}
	if ( 0 !== strpos( $pg['new_research'], $pg['research_start'] ) || 1 !== substr_count( $pg['new_research'], $pg['heading'] ) ) {
		WP_CLI::error( 'Internal error: the new text for ' . $pg['slug'] . ' must start with its research heading, once.' );
	}
	$cites    = '';
	$no_cites = preg_replace_callback( '#<cite class="ts-source">.*?</cite>#s', function ( $m ) use ( &$cites ) {
		$cites .= ' ' . $m[0];
		return ' ';
	}, $pg['new_research'] );
	$scan( 'the ' . $pg['label'] . ' research section', $plain( $no_cites ), array( $voice, $claims ) );
	$scan( 'the ' . $pg['label'] . ' source titles', $plain( $cites ), array( $voice ) );
	preg_match_all( '#href="([^"]+)"#', $pg['new_research'], $hm );
	foreach ( array_unique( $hm[1] ) as $u ) {
		$h = (string) wp_parse_url( $u, PHP_URL_HOST );
		if ( 0 !== strpos( $u, 'https://' ) || ! in_array( $h, $ok_hosts, true ) ) {
			WP_CLI::error( 'A link in the ' . $pg['label'] . ' text goes somewhere that was not reviewed: ' . $u );
		}
	}
	if ( (int) $pg['source_count'] !== substr_count( $pg['new_research'], '<cite class="ts-source">' ) ) {
		WP_CLI::error( 'Internal error: the ' . $pg['label'] . ' Sources list does not have one entry per numbered source.' );
	}
}

/** For one page: the page, its content and its state: 'old' (ready), 'new' (applied) or 'CHANGED'. */
$read_page = function ( $pg ) {
	$found = get_posts( array( 'post_type' => 'any', 'name' => $pg['slug'], 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$state = 'CHANGED';
	$c     = '';
	if ( 1 === count( $found ) && 'page' === $found[0]->post_type && 'publish' === $found[0]->post_status ) {
		$c  = (string) $found[0]->post_content;
		$o  = substr_count( $c, $pg['old_research'] );
		$n  = substr_count( $c, $pg['new_research'] );
		$eo = $pg['old_research'] === substr( $c, -strlen( $pg['old_research'] ) );
		$en = $pg['new_research'] === substr( $c, -strlen( $pg['new_research'] ) );
		if ( 1 === $o && 0 === $n && $eo ) {
			$state = 'old';
		} elseif ( 0 === $o && 1 === $n && $en ) {
			$state = 'new';
		}
	}
	return array( 'state' => $state, 'page' => 1 === count( $found ) ? $found[0] : null, 'content' => $c );
};
$read = function () use ( $pages, $read_page ) {
	$out    = array();
	$states = array();
	foreach ( $pages as $pg ) {
		$r                    = $read_page( $pg );
		$out[ $pg['slug'] ]   = $r;
		$states[]             = $r['state'];
	}
	$u = array_unique( $states );
	return array( 'state' => 1 === count( $u ) ? $u[0] : 'CHANGED', 'pages' => $out, 'detail' => implode( '/', $states ) );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s (thai/therapeutic: %s)', $home, 'old' === $state ? 'ready' : ( 'new' === $state ? 'applied' : $state ), $now['detail'] );

/** The page content after the swap, and the checks that must hold on it: markers 1..N in first-use order, one list entry per number, no new link host. */
$swap = function ( $pg, $c ) {
	return str_replace( $pg['old_research'], $pg['new_research'], $c );
};
$check_final = function ( $pg, $final ) {
	preg_match_all( '#>\[(\d+)\]</a>#', $final, $nm );
	$first = array();
	foreach ( $nm[1] as $n ) {
		if ( ! in_array( $n, $first, true ) ) {
			$first[] = $n;
		}
	}
	$entries = substr_count( $final, '<cite class="ts-source">' );
	if ( $first !== array_map( 'strval', range( 1, count( $first ) ) ) || count( $first ) !== $entries || (int) $pg['source_count'] !== $entries ) {
		WP_CLI::error( 'The numbered markers on the ' . $pg['label'] . ' page would not be 1..N in order of first use with one Sources entry each (markers ' . count( $first ) . ', entries ' . $entries . ', expected ' . (int) $pg['source_count'] . '); nothing was changed.' );
	}
};
if ( 'old' === $state ) {
	foreach ( $pages as $pg ) {
		$check_final( $pg, $swap( $pg, $now['pages'][ $pg['slug'] ]['content'] ) );
	}
}

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The pages are not in a reviewed state (a page is missing or unpublished, its research section is not exactly the reviewed old or new text or is not at the end, or only one page is done); stop and review. ' . $report );
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
	if ( 'new' === $state ) {
		WP_CLI::success( 'Nothing to do: both pages already hold the new research sections. ' . $report );
		return;
	}
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'pages' => array() );
	foreach ( $pages as $pg ) {
		$r                       = $now['pages'][ $pg['slug'] ];
		$snap['pages'][ $pg['slug'] ] = array(
			'page_id'            => (int) $r['page']->ID,
			'old_content_sha256' => hash( 'sha256', $r['content'] ),
			'new_content_sha256' => hash( 'sha256', $swap( $pg, $r['content'] ) ),
			'old_content'        => $r['content'],
		);
	}
	$file = rtrim( $backup_dir, '/' ) . '/sandwich-update-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( $snap ) );
	if ( ! $ok || filesize( $file ) < 200 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	foreach ( $pages as $pg ) {
		$s   = $snap['pages'][ $pg['slug'] ];
		$res = $set_content( $s['page_id'], $swap( $pg, $s['old_content'] ) );
		if ( is_wp_error( $res ) || ! $res ) {
			break;
		}
	}
	$after = $read();
	$bad   = 'new' !== $after['state'];
	foreach ( $pages as $pg ) {
		$s  = $snap['pages'][ $pg['slug'] ];
		$c  = $after['pages'][ $pg['slug'] ]['content'];
		// Read-back: the stored page is exactly the swap of the old one, and swapping back gives the old page byte for byte (so nothing above the section moved).
		$bad = $bad || $c !== $swap( $pg, $s['old_content'] ) || str_replace( $pg['new_research'], $pg['old_research'], $c ) !== $s['old_content']
			|| substr_count( $c, '<img' ) !== substr_count( $s['old_content'], '<img' ) || substr_count( $c, 'doctor' ) !== substr_count( $s['old_content'], 'doctor' );
	}
	if ( $bad ) {
		foreach ( $pages as $pg ) {
			$set_content( $snap['pages'][ $pg['slug'] ]['page_id'], $snap['pages'][ $pg['slug'] ]['old_content'] );
		}
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; both old pages were put back (state now: ' . $check['detail'] . ').' );
	}
	WP_CLI::success( 'The Thai Massage and Therapeutic Massage research sections are replaced. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['pages'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
foreach ( $pages as $pg ) {
	$s = $snap['pages'][ $pg['slug'] ] ?? null;
	if ( ! is_array( $s ) || empty( $s['page_id'] ) || ! isset( $s['old_content'] ) || empty( $s['old_content_sha256'] ) ) {
		WP_CLI::error( 'Snapshot is incomplete for ' . $pg['label'] . '.' );
	}
}
if ( 'new' !== $state ) {
	WP_CLI::success( 'Nothing to roll back: the pages do not both hold the new research text exactly as applied. ' . $report );
	return;
}
foreach ( $pages as $pg ) {
	if ( (int) $now['pages'][ $pg['slug'] ]['page']->ID !== (int) $snap['pages'][ $pg['slug'] ]['page_id'] ) {
		WP_CLI::error( 'The snapshot belongs to a different page than the one on this site (' . $pg['label'] . '); nothing was changed.' );
	}
}
$restored = array();
foreach ( $pages as $pg ) {
	$restored[ $pg['slug'] ] = str_replace( $pg['new_research'], $pg['old_research'], $now['pages'][ $pg['slug'] ]['content'] );
	$set_content( (int) $snap['pages'][ $pg['slug'] ]['page_id'], $restored[ $pg['slug'] ] );
}
$check = $read();
$same  = true;
foreach ( $pages as $pg ) {
	$same = $same && hash( 'sha256', $check['pages'][ $pg['slug'] ]['content'] ) === $snap['pages'][ $pg['slug'] ]['old_content_sha256'];
}
if ( 'old' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the old research text (now: ' . $check['detail'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: both old research sections are back' . ( $same ? ' and both pages are byte-identical to the snapshot.' : ' (a page differs from the snapshot elsewhere: it was edited after the apply, and those edits were kept).' ) );

// ref: ./build-sandwich-update.py — generates sandwich-update-content.php; ../thai/install-thai-evidence.php and ../therapeutic/install-therapeutic-evidence.php — the guarded patterns this follows
// ref: ../client-pages/verify-client-pages.py — the rendered-page gate that mirrors the wording tiers above
