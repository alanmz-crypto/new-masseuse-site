<?php
/**
 * On-page "Mt. Tabor" becomes "Portland" in three visible places, for ONE site at a time.
 * Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." One item at a time; this is item 3 (after portland-meta = item 1, the SEO
 * titles/descriptions, and contact-address = item 2, the Contact Address line). He answered the proposal with "Go".
 *
 *   HOME         the licence line under the H1      BEFORE  ...</a>) · Mt. Tabor, Portland</p>          AFTER  ...</a>) · Portland, OR</p>        (matches the footer, which already says "Portland, OR")
 *   THAI         the page's H1                      BEFORE  <h1 class="uagb-heading-text">Thai Massage in Mt. Tabor</h1>          AFTER  <h1 class="uagb-heading-text">Thai Massage in Portland</h1>
 *   DEEP TISSUE  the page's H1                      BEFORE  <h1 class="uagb-heading-text">Deep Tissue Massage in Mt. Tabor</h1>   AFTER  <h1 class="uagb-heading-text">Deep Tissue Massage in Portland</h1>
 * (The Therapeutic page's H1 already says "Therapeutic Massage in Portland"; this makes the other two match it.) Each old text occurs exactly once in its page (read on staging38, 2026-10-29).
 *
 * What it changes: those three pieces of page content (WordPress's own storage; no files, no MU-plugin). Every other byte of the three pages stays as it is. NOT touched: the Contact page's Google map (Ryan:
 * "Skip the map"), the About "From Chicago to Portland" section, every page's SEO title and description, the research sections, the announcement strip, menus. (WordPress keeps its own revision of every page too.)
 * The guard: ready = EVERY old text occurs EXACTLY ONCE in its page (and every new text zero times); applied = EVERY new text occurs EXACTLY ONCE (and every old zero times); anything else (including only some
 * of them done) stops, so edits made elsewhere in the meantime survive and are never overwritten.
 * Rollback is exact and surgical: only the three new texts go back to the old (other edits made since are kept); the snapshot holds each page's whole old content and its sha256.
 * The new wording is checked against the site's whole banned-word list and we/our/us.
 *
 * Allow-list: localhost:8090 and staging38 only. Live is added only after staging38 is applied, checked and proven AND Ryan says go; he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the three pages, make the three replacements, read everything back; put everything back if any read-back differs
 *   rollback     FILE = the before-snapshot: turn the three new texts back into the old (other edits made since are kept)
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging38.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging38 only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved block markup.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$pairs = array(
	array(
		'slug'  => 'home',
		'label' => 'Home licence line',
		'old'   => '</a>) · Mt. Tabor, Portland</p>',
		'new'   => '</a>) · Portland, OR</p>',
	),
	array(
		'slug'  => 'thai-massage',
		'label' => 'Thai Massage H1',
		'old'   => '<h1 class="uagb-heading-text">Thai Massage in Mt. Tabor</h1>',
		'new'   => '<h1 class="uagb-heading-text">Thai Massage in Portland</h1>',
	),
	array(
		'slug'  => 'deep-tissue-massage',
		'label' => 'Deep Tissue Massage H1',
		'old'   => '<h1 class="uagb-heading-text">Deep Tissue Massage in Mt. Tabor</h1>',
		'new'   => '<h1 class="uagb-heading-text">Deep Tissue Massage in Portland</h1>',
	),
);
foreach ( $pairs as $p ) {
	if ( $p['old'] === $p['new'] || false !== strpos( $p['new'], $p['old'] ) ) {
		WP_CLI::error( 'Internal error: a replacement is not what was reviewed: ' . $p['label'] );
	}
}

// ---- checks on the NEW wording (the whole house list applies) ---------------------------------------------------------------------------------------------
foreach ( $pairs as $p ) {
	$text = html_entity_decode( wp_strip_all_tags( $p['new'] ), ENT_QUOTES, 'UTF-8' );
	foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio', 'cure', 'treat', 'diagnos', 'inflammation', 'detox', 'guarantee', 'relieve', 'relief', 'portland-area', 'portland metro', 'mt. tabor' ) as $bad ) {
		if ( false !== stripos( $text, $bad ) ) {
			WP_CLI::error( 'The new text of "' . $p['label'] . '" contains a term this site must not use: ' . $bad );
		}
	}
	if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $text ) || preg_match( '/\b(we|our|us|ours)\b/i', $text ) ) {
		WP_CLI::error( 'The new text of "' . $p['label'] . '" contains heal/healing or we/our/us, which this site must not use.' );
	}
}

// ---- the site as it is ------------------------------------------------------------------------------------------------------------------------------
$slugs = array();
foreach ( $pairs as $p ) {
	$slugs[ $p['slug'] ] = true;
}
$slugs = array_keys( $slugs );

$read = function () use ( $pairs, $slugs ) {
	$r = array( 'items' => array(), 'pages' => array() );
	foreach ( $slugs as $slug ) {
		$found = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
		$pg    = ( 1 === count( $found ) && 'page' === $found[0]->post_type && 'publish' === $found[0]->post_status ) ? $found[0] : null;
		if ( $pg && 'home' === $slug && (int) get_option( 'page_on_front' ) !== (int) $pg->ID ) {
			$pg = null;   // the Home page must be the front page
		}
		$r['pages'][ $slug ] = $pg;
	}
	foreach ( $pairs as $i => $p ) {
		$pg = $r['pages'][ $p['slug'] ];
		if ( ! $pg ) {
			$r['items'][ $i ] = 'CHANGED';
			continue;
		}
		$c     = (string) $pg->post_content;
		$n_old = substr_count( $c, $p['old'] );
		$n_new = substr_count( $c, $p['new'] );
		$r['items'][ $i ] = ( 1 === $n_old && 0 === $n_new ) ? 'ready' : ( ( 0 === $n_old && 1 === $n_new ) ? 'applied' : 'CHANGED' );
	}
	$all = array_values( $r['items'] );
	if ( count( $all ) === count( array_filter( $all, function ( $s ) { return 'ready' === $s; } ) ) ) {
		$r['state'] = 'ready';
	} elseif ( count( $all ) === count( array_filter( $all, function ( $s ) { return 'applied' === $s; } ) ) ) {
		$r['state'] = 'applied';
	} else {
		$r['state'] = 'CHANGED';
	}
	$bad = array();
	foreach ( $r['items'] as $i => $s ) {
		if ( 'CHANGED' === $s ) {
			$bad[] = $pairs[ $i ]['label'];
		}
	}
	$r['report'] = sprintf( 'replacements ready=%d applied=%d changed=%d%s', count( array_keys( $r['items'], 'ready', true ) ), count( array_keys( $r['items'], 'applied', true ) ), count( $bad ), $bad ? ' [CHANGED: ' . implode( '; ', $bad ) . ']' : '' );
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s %s', $home, $state, $now['report'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'Not every old text is there exactly once (or every new text exactly once), or a page is missing or unpublished; stop and review. ' . $report );
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
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'pages' => array() );
	foreach ( $slugs as $slug ) {
		$pg = $now['pages'][ $slug ];
		$snap['pages'][ $slug ] = array( 'id' => (int) $pg->ID, 'old_sha256' => hash( 'sha256', (string) $pg->post_content ), 'old_content' => (string) $pg->post_content );
	}
	$file = rtrim( $backup_dir, '/' ) . '/onpage-portland-before-' . gmdate( 'Ymd-His' ) . '.json';
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );

	$undo = function () use ( $snap, $set_content ) {
		foreach ( $snap['pages'] as $pg ) {
			if ( (string) get_post_field( 'post_content', (int) $pg['id'] ) !== $pg['old_content'] ) {
				$set_content( (int) $pg['id'], $pg['old_content'] );
			}
		}
	};
	$fail = function ( $why ) use ( $undo, $read ) {
		$undo();
		$check = $read();
		WP_CLI::error( $why . ' Everything was put back (state now: ' . $check['state'] . ').' );
	};

	foreach ( $slugs as $slug ) {
		$content = $snap['pages'][ $slug ]['old_content'];
		foreach ( $pairs as $p ) {
			if ( $p['slug'] !== $slug ) {
				continue;
			}
			$content = str_replace( $p['old'], $p['new'], $content, $count );
			if ( 1 !== $count ) {
				$fail( 'A replacement did not match exactly once (' . $p['label'] . ').' );
			}
		}
		$res = $set_content( $snap['pages'][ $slug ]['id'], $content );
		if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', $snap['pages'][ $slug ]['id'] ) !== $content ) {
			$fail( 'The ' . $slug . ' page did not save exactly as written.' );
		}
	}
	$after = $read();
	if ( 'applied' !== $after['state'] ) {
		$fail( 'Read-back differed from what was written (' . $after['report'] . ').' );
	}
	WP_CLI::success( 'Applied ' . count( $pairs ) . ' replacements on ' . count( $slugs ) . ' pages (Home licence line, Thai H1, Deep Tissue H1). Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['pages'] ) || ! is_array( $snap['pages'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
foreach ( $slugs as $slug ) {
	$sp = $snap['pages'][ $slug ] ?? null;
	if ( ! $sp || empty( $sp['old_content'] ) || hash( 'sha256', (string) $sp['old_content'] ) !== ( $sp['old_sha256'] ?? '' ) ) {
		WP_CLI::error( 'Snapshot is damaged or incomplete (the ' . $slug . ' page).' );
	}
	if ( ! $now['pages'][ $slug ] || (int) $now['pages'][ $slug ]->ID !== (int) $sp['id'] ) {
		WP_CLI::error( 'The snapshot does not match these pages; stop and inspect.' );
	}
}
if ( 'applied' !== $state ) {
	WP_CLI::success( 'Nothing to roll back: the pages do not hold all three new texts exactly once each. ' . $report );
	return;
}
foreach ( $slugs as $slug ) {
	$content = (string) $now['pages'][ $slug ]->post_content;
	foreach ( $pairs as $p ) {
		if ( $p['slug'] === $slug ) {
			$content = str_replace( $p['new'], $p['old'], $content );   // only these texts go back: edits made elsewhere since the apply are kept
		}
	}
	$set_content( (int) $now['pages'][ $slug ]->ID, $content );
}
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['report'] . '); stop and inspect.' );
}
$same = true;
foreach ( $snap['pages'] as $slug => $sp ) {
	$same = $same && hash( 'sha256', (string) get_post_field( 'post_content', (int) $sp['id'] ) ) === $sp['old_sha256'];
}
WP_CLI::success( 'Rollback done: the three old texts are back (pages ' . ( $same ? 'byte-identical to the snapshot' : 'differ from the snapshot elsewhere: edited after the apply, and those edits were kept' ) . ').' );

// ref: ../contact-address/install-contact-address.php — the one-replacement installer this extends to three pages (same guard, snapshot and rollback); ../availability/install-availability-update.php — the multi-page exact-replacement pattern
// ref: ../portland-meta/install-portland-meta.php — item 1 of the same job (the SEO titles and descriptions of Home, Thai, Therapeutic, Deep Tissue)
