<?php
/**
 * Thai Massage page: heritage paragraph, a generic FAQ and a cited "What the Research Says" section, for ONE site at a time.
 * Source: Ryan, 2026-10-19 ("You should add all the evidence you can. No new images. Keep the FAQ generic enough we can finish it later. She uses a mat. Lengths 60 and 90 min.
 * She doesn't have plans to travel to clients. If something is on my banned words list but evidence says those words should be used, then update my banned words to accept it."),
 * after the plan in docs/inter-model/thai-massage-page-enhancement-plan.md. The exact text and blocks come from build-thai-evidence.py (thai-evidence-content.php, generated).
 *
 *   1. "What Is Thai Massage?": one paragraph is added at the end (UNESCO 2019 listing, numbered source [1]; her training, which the About page already states). The old text is anchored to its own
 *      closing sentence and occurs exactly once.
 *   2. Two sections are APPENDED after the last block on the page, so nothing above moves: "Questions About Thai Massage" (Spectra FAQ, link-free answers) and "What the Research Says About Thai Massage"
 *      (an intro and five native "details" panels; 31 numbered sources; the last panel lists them with their titles quoted as published).
 * What it writes: the page's own post_content in the posts table (no files, no MU-plugin, no theme or CSS change). Images, headings above, the safety paragraph and the four steps are not touched.
 * The guard: ready = the heritage anchor occurs once, the new sections are absent and the page still ENDS with the reviewed tail; applied = the new heritage text once and the sections once at the end;
 * anything else (a half-applied or hand-edited page, a block id already used by another post) stops the run.
 *
 * WORDING (AGENTS.md, as updated 2026-10-19). Three tiers, mirrored by client-pages/verify-client-pages.py:
 *   - never, anywhere: we/our/us, studio, Portland-area/metro (house voice) and the claim verbs heal, cure, treat, diagnose, guarantee, detox, inflammation;
 *   - the OBMT-derived "evidence vocabulary" (chronic, anxiety, disability, effective, relief...) is accepted ONLY inside the research section, where a study's own subject has to be named accurately;
 *   - the verbatim paper titles in the Sources panel (<cite class="ts-source">) are accepted as published, apart from the house-voice words.
 * The visible FAQ, the heritage paragraph and the headings still get the full list.
 *
 * COMPLIANCE: the research sentences were checked against each paper's own abstract (2026-10-19). Studies of Thai massage as a treatment for a disease are deliberately not used (OAR 334-010-0025(2)(c) and (4);
 * 334-040-0010(7)). See the builder's header and AGENTS.md. This is a check against the published rules, not legal advice or a Board ruling.
 *
 * Allow-list: localhost:8090, staging33 and live. Live was added by Ryan's go ("push it live", 2026-10-19) after the change was applied, checked and rollback-proven on localhost and staging33; he applies it himself (the auto-mode classifier refuses an agent adding a live host).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the whole old content, make the edits, read them back; put the old content back if any read-back differs
 *   rollback     FILE = the before-snapshot: take the new text out again, if the page still holds exactly what apply wrote
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the old content.)
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging33.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging33 or live only).' );
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
$reg = WP_Block_Type_Registry::get_instance();
// (uagb/advanced-heading is a static block: it is not registered on the PHP side, the saved markup is what renders; editor-validate.mjs proves the markup is valid.)
foreach ( array( 'uagb/faq', 'uagb/faq-child', 'core/paragraph', 'core/details', 'core/list', 'core/list-item' ) as $b ) {
	if ( ! $reg->is_registered( $b ) ) {
		WP_CLI::error( 'Block type not registered on this site: ' . $b );
	}
}

$data = require __DIR__ . '/thai-evidence-content.php';
$slug = 'thai-massage';
$her_old = $data['heritage_old'];
$her_new = $data['heritage_new'];
$tail    = $data['tail'];
$sec     = $data['sections'];

// ---- wording checks on the NEW text -------------------------------------------------------------------------------------------------------------------
$voice    = array( '/\b(we|our|us|ours)\b/i', '/\bstudio\b/i', '/Portland[- ]area/i', '/Portland metro/i' );
$claims   = array( '/\bheal(s|ed|ing|er|ers)?\b/i', '/cure/i', '/\btreat/i', '/diagnos/i', '/guarantee/i', '/detox/i', '/inflammation/i' );
$evidence = array( '/proof/i', '/measurable/i', '/effective/i', '/pain relief/i', '/pain management/i', '/sciatica/i', '/headache/i', '/chronic/i', '/adhesion/i', '/corrective/i', '/accelerat/i', '/restore/i',
	'/nervous system/i', '/circulation/i', '/anxiety/i', '/cancer/i', '/neurological/i', '/disabilit/i', '/prevention/i', '/lasting/i', '/long-term wellness/i', '/overall health/i', '/real results/i', '/relie(ve|f)/i' );
$scan = function ( $label, $text, $lists ) {
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
$cites = '';
$no_cites = preg_replace_callback( '#<cite class="ts-source">.*?</cite>#s', function ( $m ) use ( &$cites ) {
	$cites .= ' ' . $m[0];
	return ' ';
}, $sec );
$pos = strpos( $no_cites, $data['research_start'] );
if ( false === $pos ) {
	WP_CLI::error( 'Internal error: the research section start marker is missing from the new sections.' );
}
$before   = substr( $no_cites, 0, $pos );
$research = substr( $no_cites, $pos );
$scan( 'the heritage paragraph', $plain( $data['heritage_text'] ), array( $voice, $claims, $evidence ) );
$scan( 'the FAQ', $plain( $data['faq_text'] ), array( $voice, $claims, $evidence ) );
$scan( 'the new headings', $plain( $before ), array( $voice, $claims, $evidence ) );
$scan( 'the research section', $plain( $research ), array( $voice, $claims ) );
$scan( 'the source titles', $plain( $cites ), array( $voice ) );
foreach ( array( $her_old, $her_new, $tail, $sec ) as $s ) {
	if ( '' === $s ) {
		WP_CLI::error( 'Internal error: an empty reviewed text.' );
	}
}
if ( false !== strpos( $data['faq_text'], 'href' ) ) {
	WP_CLI::error( 'Internal error: FAQ answers must not contain links (nested-interactive in Spectra accordions).' );
}
// Every link in the new text is https and goes to one of the reviewed hosts.
$ok_hosts = array( 'pubmed.ncbi.nlm.nih.gov', 'doi.org', 'www.cochrane.org', 'www.nccih.nih.gov', 'ich.unesco.org' );
preg_match_all( '#href="([^"]+)"#', $her_new . $sec, $hm );
foreach ( array_unique( $hm[1] ) as $u ) {
	$h = (string) wp_parse_url( $u, PHP_URL_HOST );
	if ( 0 !== strpos( $u, 'https://' ) || ! in_array( $h, $ok_hosts, true ) ) {
		WP_CLI::error( 'A link goes somewhere that was not reviewed: ' . $u );
	}
}
// Numbered markers: 1..N in first-use order, N sources, each marker also listed once in Sources.
preg_match_all( '#>\[(\d+)\]</a>#', $her_new . $sec, $nm );
$first = array();
foreach ( $nm[1] as $n ) {
	if ( ! in_array( $n, $first, true ) ) {
		$first[] = $n;
	}
}
if ( $first !== array_map( 'strval', range( 1, (int) $data['source_count'] ) ) ) {
	WP_CLI::error( 'Internal error: the numbered markers are not 1..' . (int) $data['source_count'] . ' in order of first use.' );
}
if ( (int) $data['source_count'] !== substr_count( $sec, '<cite class="ts-source">' ) ) {
	WP_CLI::error( 'Internal error: the Sources list does not have one entry per numbered source.' );
}

/** Current state of the one page with this slug. */
$read = function () use ( $slug, $her_old, $her_new, $tail, $sec, $data ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$state = 'CHANGED';
	$c     = '';
	if ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) {
		$c      = (string) $pages[0]->post_content;
		$o      = substr_count( $c, $her_old );
		$n      = substr_count( $c, $her_new );
		$s      = substr_count( $c, $sec );
		$ends   = $tail === substr( $c, -strlen( $tail ) );
		$ends_s = $tail . $sec === substr( $c, -strlen( $tail . $sec ) );
		$ids    = 0;
		foreach ( $data['ids'] as $id ) {
			$ids += substr_count( $c, 'uagb-block-' . $id );
		}
		if ( 1 === $o && 0 === $n && 0 === $s && 0 === $ids && $ends ) {
			$state = 'ready';
		} elseif ( 0 === $o && 1 === $n && 1 === $s && $ends_s ) {
			$state = 'applied';
		}
	}
	return array( 'state' => $state, 'pages' => $pages, 'content' => $c );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s pages_with_slug=%d', $home, $state, count( $now['pages'] ) );

// Block ids must be new on this site (a repeated id makes Spectra's per-block CSS collide).
if ( 'ready' === $state ) {
	global $wpdb;
	foreach ( $data['ids'] as $id ) {
		// Revisions are WordPress's own history of this page (an earlier apply leaves some), not live content, so they do not count.
		$hit = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type <> 'revision' AND post_content LIKE %s", '%' . $wpdb->esc_like( $id ) . '%' ) );
		if ( $hit > 0 ) {
			WP_CLI::error( 'Block id ' . $id . ' already occurs in ' . $hit . ' post(s) on this site; change it in the builder. ' . $report );
		}
	}
}

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The page is not in a reviewed state (missing, not published, the anchor sentence or the end of the page differs, or only part of the change is in); stop and review. ' . $report );
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
$apply_edits = function ( $c ) use ( $her_old, $her_new, $sec ) {
	return str_replace( $her_old, $her_new, $c ) . $sec;
};
$undo_edits = function ( $c ) use ( $her_old, $her_new, $sec ) {
	$len = strlen( $sec );
	if ( substr( $c, -$len ) === $sec ) {
		$c = substr( $c, 0, -$len );
	}
	return str_replace( $her_new, $her_old, $c );
};

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: the changes are already there. ' . $report );
		return;
	}
	$id          = (int) $now['pages'][0]->ID;
	$old_content = $now['content'];
	$new_content = $apply_edits( $old_content );
	$file        = rtrim( $backup_dir, '/' ) . '/thai-evidence-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	// Read-back: the stored page is exactly what was written, and taking the new text out again gives back the old page byte for byte (so nothing else moved, safety lines and images included).
	if ( is_wp_error( $res ) || ! $res || 'applied' !== $after['state'] || $after['content'] !== $new_content || $undo_edits( $after['content'] ) !== $old_content
		|| substr_count( $after['content'], 'doctor' ) !== substr_count( $old_content, 'doctor' ) || substr_count( $after['content'], '<img' ) !== substr_count( $old_content, '<img' ) ) {
		$set_content( $id, $old_content );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; the old content was put back (state now: ' . $check['state'] . ', identical to before: ' . ( $check['content'] === $old_content ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/thai-massage/): heritage paragraph, FAQ and research section are in. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || empty( $snap['old_content_sha256'] ) || ! isset( $snap['old_content'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold the new text exactly as applied. ' . $report );
	return;
}
$restored = $undo_edits( $now['content'] );
$set_content( (int) $snap['page_id'], $restored );
$check = $read();
if ( 'ready' !== $check['state'] || $check['content'] !== $restored ) {
	WP_CLI::error( 'Rollback did not return to the old text (now: ' . $check['state'] . '); stop and inspect.' );
}
$same = hash( 'sha256', $check['content'] ) === $snap['old_content_sha256'];
WP_CLI::success( 'Rollback done: the old page is back' . ( $same ? ' and it is byte-identical to the snapshot.' : ' (the page differs from the snapshot elsewhere: it was edited after the apply, and those edits were kept).' ) );

// ref: ./build-thai-evidence.py — generates thai-evidence-content.php (the exact text and blocks); ../therapeutic/install-therapeutic-copy.php — the guarded pattern this follows
// ref: ../client-pages/verify-client-pages.py — the rendered-page gate that mirrors the wording tiers above
