<?php
/**
 * Make the six hearts under "Every session starts with your goals." on the Home page link to the matching part of the Services page, for ONE site at a time.
 * Ryan, 2026-10-18: "Can you make these link to the proper style of massage on the services page ... Personalized, Relief, Mobility, Balance, Comfort, Relaxation ... Just making the
 * heart and area around the heart link would be perfect." Ryan's answers for the mapping:
 *     Personalized -> Your Session      Relief, Mobility, Balance, Comfort, Relaxation -> Therapeutic Massage
 *
 * What it writes (both in WordPress's own storage so a SiteGround push carries them; no files, no MU-plugin; no page content is edited):
 *   1. a small WPCode PHP snippet "A11yHeartLinks" (posts table) that, on the Home page, turns each of the six items (the heart icon and its label) into ONE link by changing its outer tag
 *      from <div> to <a> and keeping every class, so the theme lays it out exactly as before (a first version that WRAPPED the contents in an extra link collapsed the labels and was
 *      rolled back the same day), and on the Services page
 *      gives the "Therapeutic Massage" and "Your Session" headings an id (an existing id is kept; "ts-therapeutic-massage" is the same id the "On this page" row uses) so the links
 *      land on the right section. The link carries an aria-label that starts with the visible word ("Relief: Therapeutic massage") so screen-reader users hear where it goes.
 *   2. one marked block in Additional CSS ("ts-a11y-heart") that makes the click area a little larger than the heart plus label (an invisible pseudo-element, 5 px above and below and 10 px
 *      to each side, so nothing moves), shows a pointer cursor and a gold keyboard focus ring. The ONLY visible change is the theme's standard thin gold link underline under each word
 *      (the same look as the site's other links); positions, sizes and page height are identical to before (measured: 0 of 6 items moved, desktop and phone).
 * Why a snippet and not the page content: these items are saved as static HTML, so a link added in the editor needs the exact markup Spectra's own save() produces; the snippet needs none
 * of that and is undone by deactivating it. Trade-off: the editor does not show the links (a note says where they come from).
 *
 * Allow-list: localhost:8090, staging32 and live. Live was added on Ryan's "push it" (2026-10-18), after the corrected version was applied, tested and rollback-proven on localhost and staging32.
 *
 * Modes (first WP-CLI argument; run with --user=1):  <mode> [FILE]
 *   preflight    read-only: ready = no such snippet and no CSS block; applied = exactly both are there; CHANGED = anything else (stop)
 *   apply        snapshot the Additional CSS, create the snippet, append the CSS block, read back; undo both if any read-back differs
 *   rollback     FILE = the before-snapshot: delete the snippet this installer created and take the CSS block out again
 * apply and rollback need WWM_BACKUP_DIR (an existing, writable absolute directory outside the web root).
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) {
	exit( 'Run with WP-CLI.' );
}

$home = untrailingslashit( home_url() );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, array( 'localhost:8090', 'staging32.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging32 or live only).' );
}
if ( 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'Active theme is not Astra.' );
}
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! current_user_can( 'edit_css' ) ) {
	WP_CLI::error( 'Run with --user=1 (an administrator) so WordPress does not filter the saved code.' );
}
if ( ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) {
	WP_CLI::error( 'WPCode (insert-headers-and-footers) is not loaded.' );
}
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args ? (string) $args[0] : 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) {
	WP_CLI::error( 'Unknown mode: ' . $mode );
}

$title = 'A11yHeartLinks';
$code  = <<<'CODE'
// The six hearts under "Every session starts with your goals." on the Home page link to the Services page (Ryan 2026-10-18). Remove this snippet to take the links away.
// Mapping (Ryan): Personalized -> Your Session; Relief, Mobility, Balance, Comfort, Relaxation -> Therapeutic Massage.
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || is_feed() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$page = get_queried_object();
	if ( ! $page ) {
		return $content;
	}
	if ( 'services' === $page->post_name ) {
		// Anchors for the heart links: give these two headings an id if they have none (same id the "On this page" row uses for Therapeutic Massage).
		foreach ( array( 'Therapeutic Massage' => 'ts-therapeutic-massage', 'Your Session' => 'ts-your-session' ) as $text => $id ) {
			if ( false !== strpos( $content, 'id="' . $id . '"' ) ) {
				continue;
			}
			if ( preg_match( '#(<h3\b[^>]*>)(?=\s*(?:<[^>]+>\s*)*' . preg_quote( $text, '#' ) . ')#i', $content, $m, PREG_OFFSET_CAPTURE ) && ! preg_match( '/\sid\s*=/i', $m[1][0] ) ) {
				$content = substr_replace( $content, preg_replace( '/^<h3/i', '<h3 id="' . $id . '"', $m[1][0], 1 ), $m[1][1], strlen( $m[1][0] ) );
			}
		}
		return $content;
	}
	if ( 'home' !== $page->post_name || false !== strpos( $content, 'ts-heart-link' ) ) {
		return $content;
	}
	$map = array(
		'personalized' => array( '/services/#ts-your-session', 'Your Session' ),
		'relief'       => array( '/services/#ts-therapeutic-massage', 'Therapeutic massage' ),
		'mobility'     => array( '/services/#ts-therapeutic-massage', 'Therapeutic massage' ),
		'balance'      => array( '/services/#ts-therapeutic-massage', 'Therapeutic massage' ),
		'comfort'      => array( '/services/#ts-therapeutic-massage', 'Therapeutic massage' ),
		'relaxation'   => array( '/services/#ts-therapeutic-massage', 'Therapeutic massage' ),
	);
	return preg_replace_callback( '#<div class="(wp-block-uagb-icon-list-child[^"]*)"([^>]*)>(.*?)</div>#is', function ( $m ) use ( $map ) {
		if ( ! preg_match( '#class="uagb-icon-list__label"[^>]*>\s*([^<]+?)\s*<#i', $m[3], $lm ) ) {
			return $m[0];
		}
		$key = strtolower( html_entity_decode( $lm[1], ENT_QUOTES, 'UTF-8' ) );
		if ( ! isset( $map[ $key ] ) ) {
			return $m[0];
		}
		list( $href, $where ) = $map[ $key ];
		// Same classes, same children: only the tag changes from <div> to <a>, so the theme's styles lay it out exactly as before.
		return '<a class="' . $m[1] . ' ts-heart-link"' . $m[2] . ' href="' . esc_url( home_url( $href ) ) . '" aria-label="' . esc_attr( trim( $lm[1] ) . ': ' . $where ) . '">' . $m[3] . '</a>';
	}, $content );
}, 19 );
CODE;
$code = $code . "\n";

$start = '/* ts-a11y-heart:start (2026-10-18) */';
$end   = '/* ts-a11y-heart:end */';
$block = $start . "\n"
	. ".entry-content a.wp-block-uagb-icon-list-child.ts-heart-link{position:relative;display:flex;color:inherit;text-decoration:none;cursor:pointer}\n"
	. ".entry-content a.wp-block-uagb-icon-list-child.ts-heart-link::after{content:\"\";position:absolute;inset:-5px -10px}\n"
	. ".entry-content a.wp-block-uagb-icon-list-child.ts-heart-link:hover .uagb-icon-list__label,.entry-content a.wp-block-uagb-icon-list-child.ts-heart-link:focus-visible .uagb-icon-list__label{text-decoration:underline;text-underline-offset:.3em}\n"
	. ".entry-content a.wp-block-uagb-icon-list-child.ts-heart-link:focus-visible{outline:3px solid var(--ts-accent,#C8963E);outline-offset:6px;border-radius:.4rem}\n"
	. $end;

$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$css = (string) $post->post_content;

$read = function () use ( $title, $code, $block ) {
	$p = wp_get_custom_css_post( 'astra' );
	$c = $p ? (string) $p->post_content : '';
	$n = substr_count( $c, 'ts-a11y-heart:start' );
	if ( 0 === $n ) {
		$css_state = 'absent';
	} elseif ( 1 === $n && 1 === substr_count( $c, $block ) ) {
		$css_state = 'exact';
	} else {
		$css_state = 'CHANGED';
	}
	$found = get_posts( array( 'post_type' => 'wpcode', 'post_status' => 'any', 'title' => $title, 'numberposts' => -1, 'suppress_filters' => true ) );
	if ( 0 === count( $found ) ) {
		$sn_state = 'absent';
	} elseif ( 1 === count( $found ) && 'publish' === $found[0]->post_status && trim( (string) $found[0]->post_content ) === trim( $code ) ) {
		$sn_state = 'exact';
	} else {
		$sn_state = 'CHANGED';
	}
	$state = 'CHANGED';
	if ( 'absent' === $css_state && 'absent' === $sn_state ) {
		$state = 'ready';
	} elseif ( 'exact' === $css_state && 'exact' === $sn_state ) {
		$state = 'applied';
	}
	return array( 'state' => $state, 'css' => $c, 'found' => $found, 'css_state' => $css_state, 'sn_state' => $sn_state );
};
$now    = $read();
$state  = $now['state'];
$report = sprintf( 'host=%s state=%s snippet=%s css_block=%s', $home, $state, $now['sn_state'], $now['css_state'] );

if ( 'preflight' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The snippet and the CSS block are not both absent or both exactly as written; stop and review. ' . $report );
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

if ( 'apply' === $mode ) {
	if ( 'CHANGED' === $state ) {
		WP_CLI::error( 'The state is not the reviewed one; nothing was changed. ' . $report );
	}
	if ( 'applied' === $state ) {
		WP_CLI::success( 'Nothing to do: already applied. ' . $report );
		return;
	}
	$file = rtrim( $backup_dir, '/' ) . '/heart-links-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array( 'created' => gmdate( 'c' ), 'home' => $home, 'css_sha256' => hash( 'sha256', $css ), 'css' => $css, 'created_id' => null ) ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$snippet = new WPCode_Snippet( array(
		'title' => $title, 'code' => $code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true,
		'note'  => 'Created 2026-10-18 by scripts/work-with-me-employer/accessibility/install-heart-links.php. The heart links on the Home page (and two heading ids on the Services page) come from this snippet, not from the page content. Rollback: delete this snippet and the ts-a11y-heart CSS block.',
	) );
	$id = $snippet->save();
	// WPCode's save() syntax-checks the code as given and then un-slashes it, which would eat the backslashes in the regular expressions; so it is created from the plain code
	// (the syntax check passes) and the exact text is written back with wp_update_post( wp_slash( ... ) ), the same way the CSP installer updates a snippet.
	if ( $id ) {
		wp_update_post( wp_slash( array( 'ID' => (int) $id, 'post_content' => $code ) ), true );
	}
	$p  = $id ? get_post( $id ) : null;
	$sn_ok = $p && 'wpcode' === $p->post_type && 'publish' === $p->post_status && trim( (string) $p->post_content ) === trim( $code )
		&& array( 'php' ) === wp_get_object_terms( $id, 'wpcode_type', array( 'fields' => 'slugs' ) ) && array( 'everywhere' ) === wp_get_object_terms( $id, 'wpcode_location', array( 'fields' => 'slugs' ) );
	$undo = function () use ( $id, $css, $refresh ) {
		if ( $id ) {
			wp_delete_post( (int) $id, true );
		}
		wp_update_custom_css_post( $css, array( 'stylesheet' => 'astra' ) );
		$refresh();
	};
	if ( ! $sn_ok ) {
		$stored = $p ? (string) $p->post_content : '';
		$want   = trim( $code );
		$have   = trim( $stored );
		$at     = 0;
		$max    = min( strlen( $want ), strlen( $have ) );
		while ( $at < $max && $want[ $at ] === $have[ $at ] ) {
			$at++;
		}
		$why = sprintf( 'saved=%s type=%s location=%s length expected %d stored %d, first difference at byte %d: expected "%s" stored "%s"', $p ? $p->post_status : 'none',
			$id ? implode( ',', wp_get_object_terms( $id, 'wpcode_type', array( 'fields' => 'slugs' ) ) ) : '-', $id ? implode( ',', wp_get_object_terms( $id, 'wpcode_location', array( 'fields' => 'slugs' ) ) ) : '-',
			strlen( $want ), strlen( $have ), $at, addcslashes( substr( $want, max( 0, $at - 10 ), 40 ), "\n\r" ), addcslashes( substr( $have, max( 0, $at - 10 ), 40 ), "\n\r" ) );
		$undo();
		WP_CLI::error( 'The snippet did not save exactly as expected; everything was put back. ' . $why );
	}
	file_put_contents( $file, wp_json_encode( array( 'created' => gmdate( 'c' ), 'home' => $home, 'css_sha256' => hash( 'sha256', $css ), 'css' => $css, 'created_id' => (int) $id ) ) );
	$res = wp_update_custom_css_post( rtrim( $css ) . "\n\n" . $block . "\n", array( 'stylesheet' => 'astra' ) );
	$refresh();
	$after = $read();
	if ( is_wp_error( $res ) || 'applied' !== $after['state'] || 0 !== strpos( $after['css'], rtrim( $css ) ) ) {
		$undo();
		$check = $read();
		WP_CLI::error( 'Read-back differed from what was written; everything was put back (state now: ' . $check['state'] . ').' );
	}
	WP_CLI::success( 'Created snippet #' . $id . ' "' . $title . '" and added the ts-a11y-heart block to the Additional CSS. Before-snapshot: ' . $file );
	return;
}

// ---- rollback FILE ---------------------------------------------------------------------------------------------------------
$file = isset( $args[1] ) ? (string) $args[1] : '';
$snap = $file && is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== $home || empty( $snap['created_id'] ) || empty( $snap['css'] ) || hash( 'sha256', (string) $snap['css'] ) !== ( $snap['css_sha256'] ?? '' ) ) {
	WP_CLI::error( 'Snapshot is missing, incomplete (the apply may not have finished), damaged, or from another site.' );
}
if ( 'applied' !== $state || (int) $now['found'][0]->ID !== (int) $snap['created_id'] ) {
	WP_CLI::success( 'Nothing to roll back: what this installer wrote is not there in its original form. ' . $report );
	return;
}
$old      = (string) $snap['css'];
$restored = ( $now['css'] === rtrim( $old ) . "\n\n" . $block . "\n" ) ? $old : str_replace( "\n\n" . $block . "\n", '', $now['css'] );
wp_delete_post( (int) $snap['created_id'], true );
wp_update_custom_css_post( $restored, array( 'stylesheet' => 'astra' ) );
$refresh();
$check = $read();
if ( 'ready' !== $check['state'] ) {
	WP_CLI::error( 'Rollback did not return to the original state (now: ' . $check['state'] . '); stop and inspect.' );
}
WP_CLI::success( 'Rollback done: snippet #' . (int) $snap['created_id'] . ' deleted and the ts-a11y-heart block is out of the Additional CSS.' );
