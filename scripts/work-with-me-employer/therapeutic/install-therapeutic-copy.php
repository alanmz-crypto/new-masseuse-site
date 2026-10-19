<?php
/**
 * Therapeutic Massage page: three small copy changes, for ONE site at a time.
 * Source: the implementation handoff Ryan pasted on 2026-10-18 ("Improve Thai Solitude's Therapeutic Massage Page"), applied on staging33 on his "do this on staging33".
 *
 *   1. "Common Reasons Clients Choose Therapeutic Massage": the paragraph is replaced (more concrete reasons, neck/shoulder and low-back research stated with its limits), and the two research
 *      sentences carry the page's own numbered source marker, <a href="https://www.nccih.nih.gov/health/massage-therapy-what-you-need-to-know">[11]</a>. The page already numbers its sources [1] to [10]
 *      in reading order; this section comes after them, so the NCCIH article is [11], used for both sentences (a source used twice keeps its number, as [2] and [5] do above).
 *   2. FAQ "Is Therapeutic Massage Appropriate If I Have No Specific Complaint?": the answer is replaced (no complaint or goal needed to book).
 *   3. FAQ "What Is the Difference Between Therapeutic and Relaxation Massage?": one sentence is added to the answer ("It does not have to mean intense or painful pressure.").
 *
 * What it writes (the page's own post_content in the posts table; no files, no MU-plugin, no theme or CSS change): three exact replacements inside the stored page content. Each old text is anchored to its
 * heading or question so it can only match the intended section. Nothing else is read as an instruction, so edits made elsewhere on the page in the meantime survive, and a rollback keeps them.
 * The guard demands every old text to occur EXACTLY once and no new text (ready), or every new text exactly once and no old text (applied); a half-applied or hand-edited page stops the run.
 * The new wording is checked against the site's OBMT forbidden-term list, we/our/us, and a list of claims the handoff said not to make (cure, treat, diagnose, inflammation, detox, guarantee).
 * The safety guidance on the page ("see a doctor" lines) is outside these edits; the read-back proves the page is otherwise byte-identical.
 *
 * WORDING DECISION (Ryan, 2026-10-18): the pasted handoff said "...may provide short-term relief for some people with neck or shoulder pain." The repo's own Therapeutic-page gate (client-pages/verify-client-pages.py) bans the bare
 * word "relief" (and "relieve", "restore"), so Ryan asked for a new way to say it; the sentence now follows the NCCIH article's own wording ("may be helpful for neck or shoulder pain, but the benefits may only last for a short time").
 * The check below mirrors that gate so the installer refuses the banned words itself. To change this wording later: roll back FIRST (the guard compares against this file's text), edit the 'reasons' text, apply again.
 *
 * COMPLIANCE CHECK (2026-10-18, Ryan: "give it a compliance check. if it passes go live."): the new wording was checked against the Board's own rule text (OAR 334-010-0025, 334-030-0005, 334-040-0010(7); identical in the
 * July 2024 and April 2025 editions) and ORS 687.011; it passed (no diagnosis, no cure/guarantee/outcome promise, hedged and sourced research, nothing misleading, licence number in the footer). See AGENTS.md.
 *
 * Allow-list: localhost:8090 and staging33 ONLY, as of this commit. Live is NOT enabled yet: Ryan's go was given, but the edit that adds the live host was refused by the auto-mode classifier (Production Deploy), so Ryan applies it himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = every old text is there once; applied = every new text is there once; CHANGED = anything else (stop)
 *   apply        snapshot the whole old content, make the three replacements, read them back; put the old content back if any read-back differs
 *   rollback     FILE = the before-snapshot: turn the three new texts back into the old ones, if the page still holds all of them
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the old content.)
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging33.thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090 or staging33 only).' );
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

$slug = 'therapeutic-massage';
$src  = 'https://www.nccih.nih.gov/health/massage-therapy-what-you-need-to-know';
$mark = '<a href="' . $src . '">[11]</a>';

$q2 = '"question":"Is Therapeutic Massage Appropriate If I Have No Specific Complaint?","answer":"';
$q3 = '"question":"What Is the Difference Between Therapeutic and Relaxation Massage?","answer":"';

// Each edit: the exact stored old text, the exact stored new text.
$edits = array(
	'reasons' => array(
		'old' => 'People often book therapeutic massage for everyday tension and stiffness, or simply for time to slow down. Before I begin, I ask about your preferences and anything I should know about your health. During the session you can ask for lighter pressure, a different focus, or a pause at any time.',
		'new' => 'People often book therapeutic massage for everyday muscle tension and stiffness, or simply to make time to slow down. Some also seek focused work for neck or shoulder discomfort. Research suggests massage may be helpful for some people with neck or shoulder pain, though the benefits may last only a short time.' . $mark . ' Massage has also been studied for low-back pain, although the evidence is limited.' . $mark . ' Before I begin, I ask about your preferences and anything I should know about your health. You can request lighter pressure, a different focus, or a pause at any time.',
	),
	'faq_no_complaint' => array(
		'old' => $q2 . 'Yes. You don’t need a particular area of concern to book. Tell me what kind of session would feel good, and I will plan around that."',
		'new' => $q2 . 'Yes. You don’t need a particular area of concern to book. You can come simply to make time for yourself, slow down, and receive attentive care without arriving with a specific complaint or goal. Tell me what kind of session would feel good, and I’ll plan around that."',
	),
	'faq_difference' => array(
		'old' => $q3 . 'Relaxation massage prioritizes a calm experience. Therapeutic massage can give more attention to a particular area of tension while still adapting pressure and pace to your comfort."',
		'new' => $q3 . 'Relaxation massage prioritizes a calm experience. Therapeutic massage can give more attention to a particular area of tension while still adapting pressure and pace to your comfort. It does not have to mean intense or painful pressure."',
	),
);

// ---- wording checks on the NEW text (what the site must not say) ------------------------------------------------------------
$new_text = '';
foreach ( $edits as $e ) {
	$new_text .= ' ' . $e['new'];
}
$text = html_entity_decode( wp_strip_all_tags( $new_text ), ENT_QUOTES, 'UTF-8' );
foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore balance', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio', 'cure', 'treat', 'diagnos', 'inflammation', 'detox', 'guarantee', 'relieve', 'relief', 'restore' ) as $bad ) {
	if ( false !== stripos( $text, $bad ) ) {
		WP_CLI::error( 'The new wording contains a term this site must not use: ' . $bad );
	}
}
if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $text ) || preg_match( '/\b(we|our|us|ours)\b/i', $text ) ) {
	WP_CLI::error( 'The new wording contains heal/healing or we/our/us, which this site must not use.' );
}
foreach ( $edits as $k => $e ) {
	if ( $e['old'] === $e['new'] || '' === $e['old'] || '' === $e['new'] ) {
		WP_CLI::error( 'Internal error: the old and new text of "' . $k . '" are not what was reviewed.' );
	}
}
if ( 2 !== substr_count( $new_text, $src ) || 2 !== substr_count( $new_text, '>[11]<' ) ) {
	WP_CLI::error( 'Internal error: the NCCIH source marker must appear exactly twice in the new wording.' );
}

/** Current state of the one page with this slug. */
$read = function () use ( $slug, $edits, $src ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$state = 'CHANGED';
	$c     = '';
	if ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) {
		$c     = (string) $pages[0]->post_content;
		$old_n = 0;
		$new_n = 0;
		$bad   = false;
		foreach ( $edits as $e ) {
			$o = substr_count( $c, $e['old'] );
			$n = substr_count( $c, $e['new'] );
			if ( $o > 1 || $n > 1 ) {
				$bad = true;
			}
			$old_n += $o;
			$new_n += $n;
		}
		$count = count( $edits );
		$links = substr_count( $c, $src );
		$marks = substr_count( $c, '>[11]<' );
		if ( ! $bad && $count === $old_n && 0 === $new_n && 0 === $links && 0 === $marks ) {
			$state = 'ready';
		} elseif ( ! $bad && 0 === $old_n && $count === $new_n && 2 === $links && 2 === $marks ) {
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
		WP_CLI::error( 'The page is not in a reviewed state (missing, not published, one of the three sections differs, or only some edits are in); stop and review. ' . $report );
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
		WP_CLI::success( 'Nothing to do: the three changes are already there. ' . $report );
		return;
	}
	$id          = (int) $now['pages'][0]->ID;
	$old_content = $now['content'];
	$new_content = $old_content;
	foreach ( $edits as $e ) {
		$new_content = str_replace( $e['old'], $e['new'], $new_content );
	}
	$file = rtrim( $backup_dir, '/' ) . '/therapeutic-copy-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array(
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
	// Read-back: the stored page is exactly what was written, and undoing the three edits gives back the old page byte for byte (so nothing else moved, safety lines included).
	$undone = $after['content'];
	foreach ( $edits as $e ) {
		$undone = str_replace( $e['new'], $e['old'], $undone );
	}
	if ( is_wp_error( $res ) || ! $res || 'applied' !== $after['state'] || $after['content'] !== $new_content || $undone !== $old_content || substr_count( $after['content'], 'doctor' ) !== substr_count( $old_content, 'doctor' ) ) {
		$set_content( $id, $old_content );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; the old content was put back (state now: ' . $check['state'] . ', identical to before: ' . ( $check['content'] === $old_content ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/therapeutic-massage/): the three copy changes are in. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || empty( $snap['old_content_sha256'] ) || ! isset( $snap['old_content'] ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), or from another site.' );
}
if ( 'applied' !== $state || (int) $now['pages'][0]->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the page does not hold all three new texts exactly once. ' . $report );
	return;
}
$restored = $now['content'];
foreach ( $edits as $e ) {
	$restored = str_replace( $e['new'], $e['old'], $restored );
}
$set_content( (int) $snap['page_id'], $restored );
$check = $read();
if ( 'ready' !== $check['state'] || $check['content'] !== $restored ) {
	WP_CLI::error( 'Rollback did not return to the old wording (now: ' . $check['state'] . '); stop and inspect.' );
}
$same = hash( 'sha256', $check['content'] ) === $snap['old_content_sha256'];
WP_CLI::success( 'Rollback done: the old wording is back' . ( $same ? ' and the page is byte-identical to the snapshot.' : ' (the page differs from the snapshot elsewhere: it was edited after the apply, and those edits were kept).' ) );

// ref: ../links/install-thammasat-link.php — the guarded exact-replacement pattern this follows; ./push-therapeutic-copy.sh — runs this on staging33
