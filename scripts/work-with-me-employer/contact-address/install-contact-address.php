<?php
/**
 * Contact page: the Address line says "Portland, OR" instead of "Mt. Tabor, Portland", for ONE site at a time.
 * Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." One item at a time; this is item 2. He answered the proposal
 * (address line + map) with "Skip the map": ONLY the address line changes. The Google map block (its own "address" setting, "Mt. Tabor, Portland") is NOT touched.
 *
 *   BEFORE  <p class="uagb-desc-text">Address</p><p class="uagb-heading-text">Mt. Tabor, Portland</p>
 *   AFTER   <p class="uagb-desc-text">Address</p><p class="uagb-heading-text">Portland, OR</p>
 *
 * What it changes: that one paragraph in the Contact page's content (WordPress's own storage; no files, no MU-plugin). Every other byte of the page stays as it is. The page's SEO title and description,
 * the phone number, the email, the "For employers" block and the map are not touched. (WordPress keeps its own revision of the page too.)
 * The guard: ready = the old text occurs EXACTLY ONCE (and the new text zero times); applied = the new text occurs EXACTLY ONCE (and the old zero times); anything else stops, so edits made elsewhere in the
 * meantime survive and are never overwritten. The old text includes the markup around it, so it cannot match the map block's "address" setting.
 * Rollback is exact and surgical: only the new text goes back to the old (other edits made since are kept); the snapshot holds the whole old page content and its sha256.
 * The new wording is checked against the site's whole banned-word list and we/our/us.
 *
 * Allow-list: localhost:8090 and staging38 only. Live is added only after staging38 is applied, checked and proven AND Ryan says go; he starts the live run himself.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the page, make the one replacement, read it back; put the page back if the read-back differs
 *   rollback     FILE = the before-snapshot: turn the new text back into the old (other edits made since are kept)
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

$slug = 'contact';
$old  = '<p class="uagb-desc-text">Address</p><p class="uagb-heading-text">Mt. Tabor, Portland</p>';
$new  = '<p class="uagb-desc-text">Address</p><p class="uagb-heading-text">Portland, OR</p>';
if ( $old === $new || false !== strpos( $new, $old ) ) {
	WP_CLI::error( 'Internal error: the replacement is not what was reviewed.' );
}

// ---- checks on the NEW wording (the whole house list applies) ---------------------------------------------------------------------------------------------
$text = html_entity_decode( wp_strip_all_tags( $new ), ENT_QUOTES, 'UTF-8' );
foreach ( array( 'proof', 'measurable', 'effective', 'pain relief', 'pain management', 'sciatica', 'headache', 'chronic', 'adhesion', 'corrective', 'accelerate', 'restore', 'nervous system', 'circulation', 'anxiety', 'cancer', 'neurological', 'disabilit', 'prevention', 'lasting', 'long-term wellness', 'overall health', 'real results', 'studio', 'cure', 'treat', 'diagnos', 'inflammation', 'detox', 'guarantee', 'relieve', 'relief', 'portland-area', 'portland metro', 'mt. tabor' ) as $bad ) {
	if ( false !== stripos( $text, $bad ) ) {
		WP_CLI::error( 'The new text contains a term this site must not use: ' . $bad );
	}
}
if ( preg_match( '/\bheal(s|ed|ing|er)?\b/i', $text ) || preg_match( '/\b(we|our|us|ours)\b/i', $text ) ) {
	WP_CLI::error( 'The new text contains heal/healing or we/our/us, which this site must not use.' );
}

// ---- the site as it is ------------------------------------------------------------------------------------------------------------------------------
$read = function () use ( $slug, $old, $new ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$r     = array( 'state' => 'CHANGED', 'page' => null, 'n_old' => -1, 'n_new' => -1 );
	if ( 1 !== count( $pages ) || 'page' !== $pages[0]->post_type || 'publish' !== $pages[0]->post_status ) {
		return $r;
	}
	$c           = (string) $pages[0]->post_content;
	$r['page']   = $pages[0];
	$r['n_old']  = substr_count( $c, $old );
	$r['n_new']  = substr_count( $c, $new );
	if ( 1 === $r['n_old'] && 0 === $r['n_new'] ) {
		$r['state'] = 'ready';
	} elseif ( 0 === $r['n_old'] && 1 === $r['n_new'] ) {
		$r['state'] = 'applied';
	}
	return $r;
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s old_text_count=%d new_text_count=%d', $home, $state, $now['n_old'], $now['n_new'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The Contact page is missing or unpublished, or the old Address text is not there exactly once (or the new text exactly once); stop and review. ' . $report );
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
		WP_CLI::success( 'Nothing to do: the Address line is already there. ' . $report );
		return;
	}
	$pg   = $now['page'];
	$id   = (int) $pg->ID;
	$oldc = (string) $pg->post_content;
	$snap = array( 'created' => gmdate( 'c' ), 'home' => $home, 'page_id' => $id, 'old_sha256' => hash( 'sha256', $oldc ), 'old_content' => $oldc );
	$file = rtrim( $backup_dir, '/' ) . '/contact-address-before-' . gmdate( 'Ymd-His' ) . '.json';
	if ( false === file_put_contents( $file, wp_json_encode( $snap ) ) || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );

	$newc = str_replace( $old, $new, $oldc, $count );
	if ( 1 !== $count ) {
		WP_CLI::error( 'The replacement did not match exactly once; nothing was changed.' );
	}
	$res   = $set_content( $id, $newc );
	$after = $read();
	if ( is_wp_error( $res ) || ! $res || (string) get_post_field( 'post_content', $id ) !== $newc || 'applied' !== $after['state'] ) {
		$set_content( $id, $oldc );
		$check = $read();
		WP_CLI::error( 'The page did not save exactly as written; the old content was put back (state now: ' . $check['state'] . ', byte-identical to before: ' . ( hash( 'sha256', (string) get_post_field( 'post_content', $id ) ) === $snap['old_sha256'] ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/contact/): the Address line now says "Portland, OR". Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['page_id'] ) || ! isset( $snap['old_content'] ) || hash( 'sha256', (string) $snap['old_content'] ) !== ( $snap['old_sha256'] ?? '' ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, or from another site.' );
}
if ( 'applied' !== $state || (int) $now['page']->ID !== (int) $snap['page_id'] ) {
	WP_CLI::success( 'Nothing to roll back: the Contact page does not hold the new Address text exactly once. ' . $report );
	return;
}
$set_content( (int) $snap['page_id'], str_replace( $new, $old, (string) $now['page']->post_content ) );   // only this text goes back: edits made elsewhere since the apply are kept
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['state'] . '); stop and inspect.' );
}
$same = hash( 'sha256', (string) get_post_field( 'post_content', (int) $snap['page_id'] ) ) === $snap['old_sha256'];
WP_CLI::success( 'Rollback done: the old Address text is back (page ' . ( $same ? 'byte-identical to the snapshot' : 'differs from the snapshot elsewhere: edited after the apply, and those edits were kept' ) . ').' );

// ref: ../availability/install-availability-update.php — the exact-replacement installer this is reduced from (same guard, snapshot and rollback); ../portland-meta/install-portland-meta.php — item 1 of the same job
// ref: ../contact-meta/install-contact-meta.php — the Contact page's SEO description (a different part of the same page; untouched here)
