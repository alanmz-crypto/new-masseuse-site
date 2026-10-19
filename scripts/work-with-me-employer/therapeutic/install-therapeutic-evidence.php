<?php
/**
 * Therapeutic Massage page: a cited "What the Research Says" section, four generic FAQ questions, honest body research sentences and honest picture descriptions, for ONE site at a time.
 * Source: Ryan, 2026-10-19 ("that is the page we want to emulate [Thai Massage] ... the Therapeutic page is the page we are working on ... these are the instructions we have to follow" [the research report]; then, to the six
 * questions in docs/inter-model/therapeutic-massage-page-enhancement-plan.md: "use your judgement to make it honest"). The exact text and blocks come from build-therapeutic-evidence.py (therapeutic-evidence-content.php, generated).
 *
 *   1. The two body paragraphs that carry numbered research links are replaced EXACTLY (each old text occurs once): the stroke and cancer-pain studies the page cited are out (studies of massage as a treatment for a disease are not
 *      used, as on the Thai page), the caregiver sentence is reworded, the neck/shoulder sentence now says the research is mixed and names the 2024 Cochrane review, and the numbers are re-derived from the registry.
 *   2. Four generic questions are appended to the existing FAQ accordion (link-free answers; every statement restates what the site already says or what Ryan said).
 *   3. Two things are APPENDED after the last block, so nothing above moves: the heading "What the Research Says About Therapeutic Massage", its intro, and five native "details" panels (30 numbered sources; the last panel lists
 *      them with their titles quoted as published).
 *   4. The five pictures (one illustration in five colour tints) get an alt text and tooltip that say what they show, instead of one identical sentence and "Therapeutic Massage by Butsaba" on every one.
 * What it writes: the page's own post_content in the posts table (no files, no MU-plugin, no theme or CSS change, no new picture).
 * The guard: ready = every old text occurs exactly once, none of the new text is there, the page still ENDS with the reviewed tail and no new block id is used anywhere; applied = every new text exactly once and the section
 * at the very end; anything else (a half-applied or hand-edited page, a block id already used by another post) stops the run.
 *
 * WORDING (AGENTS.md, 2026-10-19). Three tiers, mirrored by client-pages/wording_tiers.py:
 *   - never, anywhere: we/our/us, studio, Portland-area/metro and the claim verbs heal, cure, treat, diagnose, guarantee, detox, inflammation;
 *   - the OBMT-derived "evidence vocabulary" (chronic, anxiety, effective, relief...) only inside the research section; the new body sentences, FAQ answers, headings and alt texts get the FULL list;
 *   - verbatim paper titles in the Sources panel (<cite class="ts-source">) as published, apart from the house-voice words.
 * COMPLIANCE: each research sentence was checked against its paper's own abstract (2026-10-19). Not legal advice or a Board ruling.
 *
 * Allow-list: localhost:8090, staging34 and live. Live was added by Ryan's go ("go live", 2026-10-19) after the change was applied, checked and rollback-proven on localhost and staging34;
 * he applies it himself (the auto-mode classifier refuses an agent adding a live host).
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready / applied / CHANGED (stop)
 *   apply        snapshot the whole old content, make the edits, read them back; put the old content back if any read-back differs
 *   rollback     FILE = the before-snapshot: undo every edit, if the page still holds exactly what apply wrote; the result must be byte-identical to the snapshot
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root). (WordPress also keeps its own revision of the old content.)
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
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved page.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}
$reg = WP_Block_Type_Registry::get_instance();
// (uagb/advanced-heading and uagb/image are static blocks: not registered on the PHP side, the saved markup is what renders; editor-validate.mjs proves the markup is valid.)
foreach ( array( 'uagb/faq', 'uagb/faq-child', 'core/paragraph', 'core/details', 'core/list', 'core/list-item' ) as $b ) {
	if ( ! $reg->is_registered( $b ) ) {
		WP_CLI::error( 'Block type not registered on this site: ' . $b );
	}
}

$data   = require __DIR__ . '/therapeutic-evidence-content.php';
$slug   = 'therapeutic-massage';
$tail   = $data['tail'];
$sec    = $data['sections'];
$images = $data['images'];
// The whole-text replacements, in the order they are applied.
$edits = array(
	array( $data['body_a_old'], $data['body_a_new'] ),
	array( $data['body_b_old'], $data['body_b_new'] ),
	array( $data['faq_old'], $data['faq_new'] ),
);

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
$scan( 'the new body paragraphs', $plain( $data['body_a_new'] . ' ' . $data['body_b_new'] ), array( $voice, $claims, $evidence ) );
$scan( 'the new FAQ questions', $plain( $data['faq_text'] ), array( $voice, $claims, $evidence ) );
$scan( 'the new picture descriptions', $plain( $data['image_text'] ), array( $voice, $claims, $evidence ) );
$scan( 'the new heading', $plain( $before ), array( $voice, $claims, $evidence ) );
$scan( 'the research section', $plain( $research ), array( $voice, $claims ) );
$scan( 'the source titles', $plain( $cites ), array( $voice ) );
foreach ( array( $tail, $sec ) as $s ) {
	if ( '' === $s ) {
		WP_CLI::error( 'Internal error: an empty reviewed text.' );
	}
}
foreach ( $edits as $e ) {
	if ( '' === $e[0] || '' === $e[1] || $e[0] === $e[1] ) {
		WP_CLI::error( 'Internal error: an empty or unchanged replacement.' );
	}
}
if ( false !== strpos( $data['faq_text'], 'href' ) ) {
	WP_CLI::error( 'Internal error: FAQ answers must not contain links (nested-interactive in Spectra accordions).' );
}
// Every link in the new text is https and goes to one of the reviewed hosts.
$ok_hosts = array( 'pubmed.ncbi.nlm.nih.gov', 'doi.org', 'www.cochrane.org', 'www.nccih.nih.gov' );
preg_match_all( '#href="([^"]+)"#', $data['body_a_new'] . $data['body_b_new'] . $data['faq_new'] . $sec, $hm );
foreach ( array_unique( $hm[1] ) as $u ) {
	$h = (string) wp_parse_url( $u, PHP_URL_HOST );
	if ( 0 !== strpos( $u, 'https://' ) || ! in_array( $h, $ok_hosts, true ) ) {
		WP_CLI::error( 'A link goes somewhere that was not reviewed: ' . $u );
	}
}
// Numbered markers over the two body paragraphs and then the section: 1..N in first-use order, N sources, one list entry each.
preg_match_all( '#>\[(\d+)\]</a>#', $data['body_a_new'] . $data['body_b_new'] . $sec, $nm );
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

/** The four exact pairs for one picture block: [from-old, to-new]. */
$image_pairs = function ( $spec ) {
	return array(
		array( '"alt":"' . $spec['old_alt'] . '"', '"alt":"' . $spec['new_alt'] . '"' ),
		array( ' alt="' . $spec['old_alt'] . '"', ' alt="' . $spec['new_alt'] . '"' ),
		array( '"title":"' . $spec['old_title'] . '"', '"title":"' . $spec['new_title'] . '"' ),
		array( ' title="' . $spec['old_title'] . '"', ' title="' . $spec['new_title'] . '"' ),
	);
};
$image_block_re = function ( $id ) {
	return '#<!-- wp:uagb/image \{"block_id":"' . preg_quote( $id, '#' ) . '".*?<!-- /wp:uagb/image -->#s';
};
/** 'old' | 'new' | 'CHANGED' for all pictures together. */
$image_state = function ( $c ) use ( $images, $image_pairs, $image_block_re ) {
	$states = array();
	foreach ( $images as $spec ) {
		if ( 1 !== preg_match_all( $image_block_re( $spec['id'] ), $c, $m ) ) {
			return 'CHANGED';
		}
		$b = $m[0][0];
		$old = true;
		$new = true;
		foreach ( $image_pairs( $spec ) as $p ) {
			$old = $old && 1 === substr_count( $b, $p[0] ) && 0 === substr_count( $b, $p[1] );
			$new = $new && 0 === substr_count( $b, $p[0] ) && 1 === substr_count( $b, $p[1] );
		}
		$states[] = $old ? 'old' : ( $new ? 'new' : 'CHANGED' );
	}
	$u = array_unique( $states );
	return 1 === count( $u ) ? $u[0] : 'CHANGED';
};
$edit_images = function ( $c, $to_new ) use ( $images, $image_pairs, $image_block_re ) {
	foreach ( $images as $spec ) {
		$c = preg_replace_callback( $image_block_re( $spec['id'] ), function ( $m ) use ( $spec, $to_new, $image_pairs ) {
			$b = $m[0];
			foreach ( $image_pairs( $spec ) as $p ) {
				$b = str_replace( $to_new ? $p[0] : $p[1], $to_new ? $p[1] : $p[0], $b );
			}
			return $b;
		}, $c );
	}
	return $c;
};

/** Current state of the one page with this slug. */
$read = function () use ( $slug, $edits, $tail, $sec, $data, $image_state ) {
	$pages = get_posts( array( 'post_type' => 'any', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'numberposts' => -1, 'suppress_filters' => true ) );
	$state = 'CHANGED';
	$c     = '';
	if ( 1 === count( $pages ) && 'page' === $pages[0]->post_type && 'publish' === $pages[0]->post_status ) {
		$c      = (string) $pages[0]->post_content;
		$olds   = true;
		$news   = true;
		foreach ( $edits as $e ) {
			$olds = $olds && 1 === substr_count( $c, $e[0] ) && 0 === substr_count( $c, $e[1] );
			$news = $news && 0 === substr_count( $c, $e[0] ) && 1 === substr_count( $c, $e[1] );
		}
		$s      = substr_count( $c, $sec );
		$ends   = $tail === substr( $c, -strlen( $tail ) );
		$ends_s = $tail . $sec === substr( $c, -strlen( $tail . $sec ) );
		$ids    = 0;
		foreach ( $data['ids'] as $id ) {
			$ids += substr_count( $c, 'uagb-block-' . $id ) + substr_count( $c, '"block_id":"' . $id . '"' );
		}
		$img = $image_state( $c );
		if ( $olds && 0 === $s && 0 === $ids && $ends && 'old' === $img ) {
			$state = 'ready';
		} elseif ( $news && 1 === $s && $ends_s && 'new' === $img ) {
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
		WP_CLI::error( 'The page is not in a reviewed state (missing, not published, an old text or the end of the page differs, or only part of the change is in); stop and review. ' . $report );
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
$apply_edits = function ( $c ) use ( $edits, $sec, $edit_images ) {
	foreach ( $edits as $e ) {
		$c = str_replace( $e[0], $e[1], $c );
	}
	return $edit_images( $c, true ) . $sec;
};
$undo_edits = function ( $c ) use ( $edits, $sec, $edit_images ) {
	$len = strlen( $sec );
	if ( substr( $c, -$len ) === $sec ) {
		$c = substr( $c, 0, -$len );
	}
	$c = $edit_images( $c, false );
	foreach ( array_reverse( $edits ) as $e ) {
		$c = str_replace( $e[1], $e[0], $c );
	}
	return $c;
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
	$file        = rtrim( $backup_dir, '/' ) . '/therapeutic-evidence-before-' . gmdate( 'Ymd-His' ) . '.json';
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
	// Read-back: the stored page is exactly what was written, and undoing every edit gives back the old page byte for byte (so nothing else moved, safety lines and picture files included).
	if ( is_wp_error( $res ) || ! $res || 'applied' !== $after['state'] || $after['content'] !== $new_content || $undo_edits( $after['content'] ) !== $old_content
		|| substr_count( $after['content'], 'doctor' ) !== substr_count( $old_content, 'doctor' ) || substr_count( $after['content'], '<img' ) !== substr_count( $old_content, '<img' ) ) {
		$set_content( $id, $old_content );
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; the old content was put back (state now: ' . $check['state'] . ', identical to before: ' . ( $check['content'] === $old_content ? 'yes' : 'NO' ) . ').' );
	}
	WP_CLI::success( 'Page #' . $id . ' (/therapeutic-massage/): body research sentences, four FAQ questions, research section and picture descriptions are in. Before-snapshot: ' . $file );
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

// ref: ./build-therapeutic-evidence.py — generates therapeutic-evidence-content.php (the exact text and blocks); ../thai/install-thai-evidence.php — the guarded pattern this follows
// ref: ../client-pages/verify-client-pages.py — the rendered-page gate that mirrors the wording tiers above; ../../../docs/inter-model/therapeutic-massage-page-enhancement-plan.md — the plan
