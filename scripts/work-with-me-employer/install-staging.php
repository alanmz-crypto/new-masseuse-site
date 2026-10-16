<?php
/**
 * Employer-focused Work With Me (page 1165) for a REMOTE STAGING site. Never for production.
 *
 * Modes (first WP-CLI argument):
 *   (none) / preflight      read-only: host guard, "page still matches the reviewed baseline" check
 *   apply-staging           save a before-snapshot JSON, then write page content, CSS block, SEO fields
 *   rollback-staging FILE   restore from that before-snapshot JSON (refuses if the page changed since)
 *
 * Required environment: WWM_BACKUP_DIR = absolute, writable directory OUTSIDE the web root.
 * Run only after a fresh `wp db export` of the target site. Stored in WordPress tables only
 * (posts: the page + the Additional CSS post; postmeta: SureRank) - no MU plugin, no theme/plugin files.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

// Hosts this installer may ever write to (scheme-agnostic: SiteGround installs report http:// and redirect to https).
// Production (thaisolitude.com) and every other staging site are deliberately absent.
$allowed_hosts = array( 'staging26.thaisolitude.com', 'staging27.thaisolitude.com', 'localhost:8094', 'localhost:8099' );
$home = untrailingslashit( home_url() );
$home_host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $home_host, $allowed_hosts, true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed staging target.' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}

$root = __DIR__;
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply-staging', 'rollback-staging' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$page = get_post( 1165 );
if ( ! $page || 'page' !== $page->post_type || 'work-with-me' !== $page->post_name || 'publish' !== $page->post_status ) {
	WP_CLI::error( 'Work With Me (ID 1165) was not found as a published page.' );
}

$new_content = rtrim( (string) file_get_contents( $root . '/page-content.html' ), "\r\n" );
$before_content = (string) file_get_contents( $root . '/baseline/page-1165-before.html' );
$additions = (string) file_get_contents( $root . '/employer-additions.css' );
if ( '' === $new_content || '' === $before_content || '' === $additions ) {
	WP_CLI::error( 'Reviewed source files are missing.' );
}

$seo_title = 'Butsaba Dusrai, LMT | Thai Bodywork | Portland, OR';
$seo_description = 'Butsaba Dusrai, Oregon LMT-29905, is based in Portland and open to full-time or part-time roles. Explore her Thai and deep tissue massage experience.';
$seo_fields = array(
	'page_title'           => $seo_title,
	'page_description'     => $seo_description,
	'facebook_title'       => $seo_title,
	'facebook_description' => $seo_description,
);
$seo_meta_keys = array(
	'surerank_settings_general', 'surerank_settings_social', 'surerank_settings_schemas',
	'surerank_settings_post_no_index', 'surerank_settings_post_no_follow', 'surerank_settings_post_no_archive',
);
if ( ! class_exists( '\SureRank\Inc\API\Post' ) || ! method_exists( '\SureRank\Inc\API\Post', 'update_post_meta_common' ) ) {
	WP_CLI::error( 'SureRank page-level SEO API is not available on this site.' );
}

// Content sanity (same guards as the local installer).
if ( false !== strpos( $new_content, 'ts-coming-soon' ) || 0 !== strpos( $new_content, '<!-- wp:group {"className":"ts-wwm"' ) ) {
	WP_CLI::error( 'Reviewed content must start at the Work With Me group with no coming-soon banner.' );
}
$resume_path = '/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf';
// Three résumé links (hero, closing, mobile bar); five email links, all with the same prefilled message body.
$mailto = 'mailto:butsabad@gmail.com?subject=LMT%20position%20in%20Portland&amp;body=Hi%20Butsaba%2C%0A%0APractice%3A%20%0ARole%20and%20hours%3A%20%0ABest%20time%20to%20talk%3A%20';
if ( 3 !== substr_count( $new_content, 'href="' . $resume_path . '"' ) || 5 !== substr_count( $new_content, 'href="' . $mailto . '"' ) || 5 !== substr_count( $new_content, 'href="mailto:' ) ) {
	WP_CLI::error( 'Résumé or email links in the reviewed content are not as expected.' );
}
// The Thai signature section's image (Ryan's supplied WebP, unaltered) travels with the page: checksum-pinned, staged next to its
// destination, and placed BEFORE any database write (an unreferenced file is harmless if a later step fails).
$thai_rel = '/wp-content/uploads/2026/10/thai-massage-therapy-session-cutout.webp';
$thai_sha = '2f2fc1b345e6bfbf5bfec7006422af17841d6b905e16489318341c188c46d545';
$thai_src = $root . '/images/thai-massage-therapy-session-cutout.webp';
$thai_dst = ABSPATH . ltrim( $thai_rel, '/' );
if ( 1 !== substr_count( $new_content, 'src="' . $thai_rel . '"' ) ) {
	WP_CLI::error( 'Expected exactly one Thai section image in the reviewed content.' );
}
if ( ! is_file( $thai_src ) || ! hash_equals( $thai_sha, hash_file( 'sha256', $thai_src ) ) ) {
	WP_CLI::error( 'The packaged Thai section image is missing or is not the reviewed file.' );
}
if ( ! is_file( $thai_dst ) ) {
	$thai_state = 'absent';
} elseif ( hash_equals( $thai_sha, hash_file( 'sha256', $thai_dst ) ) ) {
	$thai_state = 'installed';
} else {
	$thai_state = 'CHANGED';   // some other file already sits at that path: never overwrite it
}
$resume_dst = ABSPATH . ltrim( $resume_path, '/' );
if ( ! is_file( $resume_dst ) || ! is_file( WP_CONTENT_DIR . '/uploads/2026/09/butsaba_homepage.webp' ) ) {
	WP_CLI::error( 'The linked résumé PDF or portrait is missing on this site.' );
}
// The corrected public résumé travels with the page. Known hashes: the original file and the reviewed candidate.
$resume_src = $root . '/resume/Butsaba_Dusrai_LMT_Resume_License_proposed.pdf';
$resume_original_sha = '6d0dea8e7b19c3577b25c905ebcaae750a97ee7fbe83b12db49920ba446d7d85';
$resume_candidate_sha = 'c0c8b7c61e09c535222b41afa08f9894fd633881a0a1132bb92d372209242a4c';
if ( ! is_file( $resume_src ) || ! hash_equals( $resume_candidate_sha, hash_file( 'sha256', $resume_src ) ) ) {
	WP_CLI::error( 'The reviewed résumé candidate is missing or differs from the reviewed file.' );
}
$resume_now = hash_file( 'sha256', $resume_dst );
if ( hash_equals( $resume_original_sha, $resume_now ) ) {
	$resume_state = 'original';
} elseif ( hash_equals( $resume_candidate_sha, $resume_now ) ) {
	$resume_state = 'candidate-installed';
} else {
	$resume_state = 'CHANGED';
}
$visible = wp_strip_all_tags( $new_content ) . ' ' . $seo_title . ' ' . $seo_description;
foreach ( array( '/Thai Lotus/i', '/Portland[- ]area/i', '/Portland metro/i', '/October 28/i', '/\bstudio\b/i', '/\b(we|our|us)\b/i', '/\bheal/i', '/\bchronic\b/i' ) as $pattern ) {
	if ( preg_match( $pattern, $visible, $hit ) ) {
		WP_CLI::error( 'Restricted wording in the reviewed copy: ' . $hit[0] );
	}
}

// The page must still render exactly like the reviewed baseline (i.e. nobody edited it since).
$norm = static function ( $html ) use ( $home ) {
	$html = str_replace( $home, 'HOME', $html );
	// WordPress adds loading/fetchpriority/decoding/srcset to images depending on how many it has already
	// rendered in this request, so identical content can differ between two passes; ignore those.
	$html = preg_replace( '/\s(loading|fetchpriority|decoding|sizes|srcset)="[^"]*"/', '', $html );
	$html = preg_replace( '/(is-style-[a-z-]+)--\d+/', '$1--N', $html ); // per-request style counters
	return hash( 'sha256', preg_replace( '/uagb-block-[a-z0-9]+/', 'U', preg_replace( '/\s+/', ' ', $html ) ) );
};
$current_render = $norm( apply_filters( 'the_content', $page->post_content ) );
$baseline_render = $norm( apply_filters( 'the_content', $before_content ) );
$new_render = $norm( apply_filters( 'the_content', $new_content ) );
// Exact raw equality is the strong test; the normalized rendering is the fallback for tiny serialization differences.
// 'previous-applied' = the page is exactly what this installer wrote last time (its recorded hash), so an update of
// our own earlier version cannot overwrite anybody else's edit.
$current_hash = hash( 'sha256', $page->post_content );
$installed_hash = (string) get_post_meta( $page->ID, '_ts_wwm_installed_hash', true );
// Human edits that were reviewed byte for byte and adopted as the base of the next push. Only these exact checksums may be
// replaced; any other unexpected change still stops the installer. A rollback restores the edited version exactly.
$reviewed_edits = array(
	// Ryan edited staging26's page in the block editor on 2026-10-16 (story wording, modalities line, closing paragraph).
	// Copy: baseline/page-1165-staging26-edit-20261016.html
	'df5655777dc472ad7b8f9196c1cd4312f1a0061df894b6ac46de62e29e4176e5' => 'staging26-editor-edit-2026-10-16',
);
if ( $page->post_content === $before_content || $current_render === $baseline_render ) {
	$state = 'baseline';
} elseif ( $page->post_content === $new_content || $current_render === $new_render ) {
	$state = 'already-applied';
} elseif ( '' !== $installed_hash && hash_equals( $installed_hash, $current_hash ) ) {
	$state = 'previous-applied';
} elseif ( isset( $reviewed_edits[ $current_hash ] ) ) {
	$state = 'reviewed-edit';   // a human edit whose exact bytes were reviewed and adopted as the new base (baseline/ keeps a copy)
} else {
	$state = 'CHANGED';
}

// Additional CSS structure.
$css = wp_get_custom_css();
$sec_start = '/* TS WORK WITH ME START */';
$sec_end = '/* TS WORK WITH ME END */';
$emp_start = '/* TS WWM EMPLOYER START */';
$emp_end = '/* TS WWM EMPLOYER END */';
if ( 1 !== substr_count( $css, $sec_start ) || 1 !== substr_count( $css, $sec_end ) ) {
	WP_CLI::error( 'Expected exactly one Work With Me section in Additional CSS on this site.' );
}
$from = strpos( $additions, $emp_start );
$to = strpos( $additions, $emp_end );
if ( false === $from || false === $to ) {
	WP_CLI::error( 'Employer CSS markers missing in source.' );
}
$block = substr( $additions, $from, $to + strlen( $emp_end ) - $from );
// WordPress core forces margin-left/right:auto !important on constrained-group children, so the left-aligned sections
// need these two margin declarations (and only these) to use !important. Colours and @import stay banned (Dark Reader).
$block_check = str_replace( array( 'margin-left:0!important', 'margin-right:auto!important' ), '', $block );
if ( false !== stripos( $block_check, '!important' ) || false !== stripos( $block_check, '@import' ) ) {
	WP_CLI::error( 'Employer CSS must not use !important (except the two margin overrides) or @import.' );
}
$css_without_block = $css;
if ( substr_count( $css, $emp_start ) ) {
	$a = strpos( $css, $emp_start );
	$b = strpos( $css, $emp_end ) + strlen( $emp_end );
	$css_without_block = rtrim( substr( $css, 0, $a ), " \t" ) . ltrim( substr( $css, $b ), "\r\n" );
}
// A site copied from live after the live footer edit carries a small marked footer block that this installer's reviewed CSS block
// supersedes (it holds the same two rules). Remove exactly that marked block so the rules are not duplicated; the before-snapshot
// keeps the exact previous CSS, so rollback puts it back.
$foot_start = '/* TS FOOTER LOCATION START */';
$foot_end = '/* TS FOOTER LOCATION END */';
if ( 1 === substr_count( $css_without_block, $foot_start ) && 1 === substr_count( $css_without_block, $foot_end ) && strpos( $css_without_block, $foot_end ) > strpos( $css_without_block, $foot_start ) ) {
	$fa = strpos( $css_without_block, $foot_start );
	$fb = strpos( $css_without_block, $foot_end ) + strlen( $foot_end );
	$css_without_block = rtrim( substr( $css_without_block, 0, $fa ) ) . "\n" . ltrim( substr( $css_without_block, $fb ), "\r\n" );
}

$resume_dir_ok = wp_is_writable( dirname( $resume_dst ) );
$thai_dir_ok = wp_is_writable( dirname( $thai_dst ) );
$report = sprintf( 'host=%s page=%d state=%s css_block=%s resume=%s resume_folder_writable=%s image=%s image_folder_writable=%s', $home, $page->ID, $state, substr_count( $css, $emp_start ) ? 'present' : 'absent', $resume_state, $resume_dir_ok ? 'yes' : 'NO', $thai_state, $thai_dir_ok ? 'yes' : 'NO' );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state || 'CHANGED' === $resume_state || 'CHANGED' === $thai_state ) {
		WP_CLI::error( 'The page, the résumé or the Thai image file differs from what was reviewed; stop and review. ' . $report );
	}
	if ( 'absent' === $thai_state && ! $thai_dir_ok ) {
		WP_CLI::error( 'The uploads folder for the Thai image is not writable by this user, so the image cannot be delivered. ' . $report );
	}
	if ( 'original' === $resume_state && ! $resume_dir_ok ) {
		WP_CLI::error( 'The résumé folder is not writable by this user, so the résumé cannot be swapped. ' . $report );
	}
	WP_CLI::success( 'Preflight passed; nothing changed. ' . $report );
	return;
}

$backup_dir = getenv( 'WWM_BACKUP_DIR' );
if ( ! $backup_dir || '/' !== $backup_dir[0] || ! is_dir( $backup_dir ) || ! is_writable( $backup_dir ) || 0 === strpos( realpath( $backup_dir ), realpath( ABSPATH ) ) ) {
	WP_CLI::error( 'WWM_BACKUP_DIR must be an existing, writable absolute directory outside the web root.' );
}

if ( 'apply-staging' === $mode ) {
	if ( 'CHANGED' === $state || 'CHANGED' === $resume_state || 'CHANGED' === $thai_state ) {
		WP_CLI::error( 'The page, the résumé or the Thai image file differs from what was reviewed; stop and review. ' . $report );
	}
	if ( 'original' === $resume_state && ! $resume_dir_ok ) {
		WP_CLI::error( 'The résumé folder is not writable by this user; nothing was changed. ' . $report );
	}
	if ( 'absent' === $thai_state && ! $thai_dir_ok ) {
		WP_CLI::error( 'The uploads folder for the Thai image is not writable by this user; nothing was changed. ' . $report );
	}
	$snapshot = array(
		'created'       => gmdate( 'c' ),
		'home'          => $home,
		'page_id'       => $page->ID,
		'before_content' => $page->post_content,
		'before_hash'   => hash( 'sha256', $page->post_content ),
		'before_css'    => $css,
		'before_meta'   => array(),
		'before_installed_hash' => get_post_meta( $page->ID, '_ts_wwm_installed_hash', true ),
	);
	foreach ( $seo_meta_keys as $key ) {
		$snapshot['before_meta'][ $key ] = metadata_exists( 'post', $page->ID, $key ) ? get_post_meta( $page->ID, $key, true ) : null;
	}
	$stamp = gmdate( 'Ymd-His' );
	$file = rtrim( $backup_dir, '/' ) . '/wwm-before-' . $stamp . '.json';
	$snapshot['resume_state_before'] = $resume_state;
	$snapshot['image_state_before'] = $thai_state;     // 'absent' = this run adds the file, so rollback may remove it
	$snapshot['image_path'] = $thai_dst;
	$snapshot['image_sha'] = $thai_sha;
	if ( 'original' === $resume_state ) {
		$resume_backup = rtrim( $backup_dir, '/' ) . '/resume-before-' . $stamp . '.pdf';
		if ( ! copy( $resume_dst, $resume_backup ) || ! hash_equals( $resume_original_sha, hash_file( 'sha256', $resume_backup ) ) ) {
			WP_CLI::error( 'Could not back up the original résumé; nothing was changed.' );
		}
		$snapshot['resume_backup'] = $resume_backup;
	}
	if ( false === file_put_contents( $file, wp_json_encode( $snapshot ) ) || ! filesize( $file ) ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	$resume_tmp = $resume_dst . '.wwm-new';
	if ( 'original' === $resume_state ) {
		if ( ! copy( $resume_src, $resume_tmp ) || ! hash_equals( $resume_candidate_sha, hash_file( 'sha256', $resume_tmp ) ) ) {
			@unlink( $resume_tmp );
			WP_CLI::error( 'Could not stage the new résumé next to the old one; nothing was changed in the database.' );
		}
	}
	// Image first: the snapshot above already records that this run adds it. Nothing references the file yet, so a failure
	// after this point leaves only a harmless unreferenced file that rollback-staging removes.
	if ( 'absent' === $thai_state ) {
		$thai_tmp = $thai_dst . '.wwm-new';
		if ( ! copy( $thai_src, $thai_tmp ) || ! hash_equals( $thai_sha, hash_file( 'sha256', $thai_tmp ) ) || ! rename( $thai_tmp, $thai_dst ) ) {
			@unlink( $thai_tmp );
			WP_CLI::error( 'Could not place the Thai section image; nothing was changed in the database.' );
		}
	}
	$updated_css = str_replace( $sec_end, $block . "\n" . $sec_end, $css_without_block );
	if ( 'already-applied' !== $state ) {
		$r = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $new_content ) ), true );
		if ( is_wp_error( $r ) ) {
			WP_CLI::error( $r->get_error_message() );
		}
	}
	$r = wp_update_custom_css_post( $updated_css );
	if ( is_wp_error( $r ) ) {
		WP_CLI::error( $r->get_error_message() );
	}
	foreach ( $seo_meta_keys as $key ) {
		delete_post_meta( $page->ID, $key );
	}
	\SureRank\Inc\API\Post::update_post_meta_common( $page->ID, $seo_fields );
	$after_hash = hash( 'sha256', get_post( $page->ID )->post_content );
	update_post_meta( $page->ID, '_ts_wwm_installed_hash', $after_hash );
	$snapshot['after_hash'] = $after_hash;
	$snapshot['after_css'] = wp_get_custom_css();
	$snapshot['resume_after_sha'] = $resume_candidate_sha;
	file_put_contents( $file, wp_json_encode( $snapshot ) );   // complete BEFORE the last (atomic) step
	if ( 'original' === $resume_state && ! rename( $resume_tmp, $resume_dst ) ) {
		@unlink( $resume_tmp );
		WP_CLI::error( 'Page applied, but the résumé could not be moved into place; run rollback-staging ' . $file );
	}
	WP_CLI::success( 'Applied. Before-snapshot: ' . $file . ' | rollback: rollback-staging ' . $file );
	return;
}

// rollback-staging FILE
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snapshot = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snapshot ) || empty( $snapshot['before_content'] ) || empty( $snapshot['after_hash'] ) || $snapshot['home'] !== $home ) {
	WP_CLI::error( 'Before-snapshot is missing, incomplete, or from another site.' );
}
if ( hash( 'sha256', $page->post_content ) !== $snapshot['after_hash'] ) {
	WP_CLI::error( 'The page was edited after this installer wrote it; roll back by hand from the database export.' );
}
if ( isset( $snapshot['after_css'] ) && wp_get_custom_css() !== $snapshot['after_css'] ) {
	WP_CLI::error( 'Additional CSS was edited after this installer wrote it; nothing was rolled back. Restore from the database export instead.' );
}
$resume_restore = false;
if ( ! empty( $snapshot['resume_backup'] ) ) {
	if ( ! is_file( $snapshot['resume_backup'] ) || ! hash_equals( $resume_original_sha, hash_file( 'sha256', $snapshot['resume_backup'] ) ) ) {
		WP_CLI::error( 'The résumé backup is missing or damaged; nothing was rolled back.' );
	}
	if ( hash_equals( $resume_candidate_sha, $resume_now ) ) {
		$resume_restore = true;                       // the candidate is installed: put the original back
	} elseif ( ! hash_equals( $resume_original_sha, $resume_now ) ) {
		WP_CLI::error( 'The résumé was changed after this installer wrote it; nothing was rolled back.' );
	}                                                  // else: the original is already in place (the swap never happened)
}
$r = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $snapshot['before_content'] ) ), true );
if ( is_wp_error( $r ) ) {
	WP_CLI::error( $r->get_error_message() );
}
if ( isset( $snapshot['after_css'] ) ) {
	if ( wp_get_custom_css() !== $snapshot['after_css'] ) {
		WP_CLI::error( 'Additional CSS was edited after this installer wrote it; nothing was rolled back. Restore from the database export instead.' );
	}
	$css_restore = (string) $snapshot['before_css'];   // exact previous CSS (works across several updates)
} else {
	$css_restore = $css_without_block;                  // older snapshot format: just remove the employer block
}
$r = wp_update_custom_css_post( $css_restore );
if ( is_wp_error( $r ) ) {
	WP_CLI::error( $r->get_error_message() );
}
foreach ( $seo_meta_keys as $key ) {
	delete_post_meta( $page->ID, $key );
	if ( null !== $snapshot['before_meta'][ $key ] ) {
		update_post_meta( $page->ID, $key, wp_slash( $snapshot['before_meta'][ $key ] ) );
	}
}
if ( '' === (string) $snapshot['before_installed_hash'] ) {
	delete_post_meta( $page->ID, '_ts_wwm_installed_hash' );
} else {
	update_post_meta( $page->ID, '_ts_wwm_installed_hash', $snapshot['before_installed_hash'] );
}
if ( $resume_restore ) {
	$tmp = $resume_dst . '.wwm-old';
	if ( ! copy( $snapshot['resume_backup'], $tmp ) || ! rename( $tmp, $resume_dst ) ) {
		@unlink( $tmp );
		WP_CLI::error( 'Page rolled back, but the original résumé could not be restored from ' . $snapshot['resume_backup'] );
	}
}
// Remove the Thai image only if this installer added it and the file is still exactly the reviewed one. The page no longer
// references it at this point, so removal cannot break anything.
$image_note = '';
if ( isset( $snapshot['image_state_before'] ) && 'absent' === $snapshot['image_state_before'] && is_file( $thai_dst ) ) {
	if ( hash_equals( $thai_sha, hash_file( 'sha256', $thai_dst ) ) && @unlink( $thai_dst ) ) {
		$image_note = ' The Thai section image this run added was removed.';
	} else {
		$image_note = ' The Thai image file was left in place (it is not the reviewed file, or could not be removed).';
	}
}
WP_CLI::success( 'Rolled back to the before-snapshot content' . ( $resume_restore ? ' and the original résumé.' : '.' ) . $image_note );

// ref: ./install-local.php — localhost version this was derived from; ./page-content.html, ./employer-additions.css, ./baseline/page-1165-before.html are its inputs
// ref: ../../AGENTS.md — staging-only guard, no-MU-plugin storage rule, restricted-wording list
