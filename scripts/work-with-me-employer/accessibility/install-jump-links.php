<?php
/**
 * A discreet row of 2 to 4 "jump" links directly under the header of every page that has sections, for ONE site at a time.
 * Ryan, 2026-10-18: "I don't see a skip to content on any page except work with me" (he means the small row in the For Practices hero, "Availability . Experience . Credentials .
 * For clients"; the real keyboard "Skip to content" link already exists on every page but is invisible until Tab is pressed) ... "Can you put those under the header of every
 * page. discreetly. Try to keep the number of those at two or three, four max."
 *
 * What it writes (both in WordPress's own storage so a SiteGround push carries them; no files, no MU-plugin):
 *   1. a small WPCode PHP snippet "A11yPageJumpLinks" (posts table). For each page below it puts ONE row of links at the very top of the page content (directly under the header)
 *      and gives each target an id (an existing id is kept). The links per page are listed explicitly, so the row is predictable; a link whose target is not found is left out, and
 *      with fewer than 2 links no row is shown. No page content is edited.
 *        Home            About Thai Solitude . The massage I offer . How I work
 *        About           The person behind Thai Solitude . About Thai Solitude . From Chicago to Portland
 *        Services        Therapeutic massage . Deep tissue massage . Thai massage . What to expect
 *        Thai / Deep tissue massage   What it is . Common reasons . What to expect          (Therapeutic massage adds: Who can benefit)
 *        Contact         Contact details . Map . For employers
 *        Privacy Policy  Online privacy . Client privacy        (a click also opens that accordion panel)
 *        Accessibility   What has been checked . Known limitations . Feedback
 *      The For Practices page already has its own row in its hero and is left alone.
 *   2. one marked block in Additional CSS ("ts-a11y-jump") that styles the row small and muted, like the For Practices one (underlined links separated by middots).
 * It is built as <nav class="ts-jump" aria-label="On this page"> with a list, links at least 24 px tall, a focus ring, and no motion. The existing hidden "Skip to content" link is
 * untouched and stays the first thing a keyboard user reaches (before the header), which is where a skip link has to be.
 *
 * Allow-list: localhost:8090, staging32 and live. Live was added on Ryan's "push it" (2026-10-18), after the links were applied, tested and rollback-proven on localhost and staging32.
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

$title = 'A11yPageJumpLinks';
$code  = <<<'CODE'
// Discreet "jump" links (2 to 4) directly under the header of every page that has sections (accessibility; Ryan 2026-10-18). Remove this snippet to take them away.
// Entries: label, kind, match. kind h = a heading whose text STARTS with match; cls = first element whose class contains match; blk = first wp-block-group that mentions match;
// id = an element that already has this id; faq = the accordion item whose title is match.
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || is_feed() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$page = get_queried_object();
	if ( ! $page || false !== strpos( $content, 'ts-jump' ) ) {
		return $content;
	}
	$plans = array(
		'home'                => array( array( 'About Thai Solitude', 'h', 'Learn About Thai Solitude' ), array( 'The massage I offer', 'h', 'The Massage I Offer' ), array( 'How I work', 'h', 'How I Work' ) ),
		'about'               => array( array( 'The person behind Thai Solitude', 'h', 'The Person Behind' ), array( 'About Thai Solitude', 'h', 'Learn About Thai Solitude' ), array( 'From Chicago to Portland', 'h', 'From Chicago to Portland' ) ),
		'services'            => array( array( 'Therapeutic massage', 'h', 'Therapeutic Massage' ), array( 'Deep tissue massage', 'h', 'Deep Tissue Massage' ), array( 'Thai massage', 'h', 'Thai Massage' ), array( 'What to expect', 'h', 'What to Expect' ) ),
		'thai-massage'        => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'What to expect', 'h', 'What To Expect' ) ),
		'therapeutic-massage' => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'Who can benefit', 'h', 'Who Can Benefit' ), array( 'What to expect', 'h', 'What To Expect' ) ),
		'deep-tissue-massage' => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'What to expect', 'h', 'What To Expect' ) ),
		'contact'             => array( array( 'Contact details', 'blk', 'Phone' ), array( 'Map', 'cls', 'wp-block-uagb-google-map' ), array( 'For employers', 'id', 'employers' ) ),
		'privacy-policy'      => array( array( 'Online privacy', 'faq', 'Online privacy' ), array( 'Client privacy', 'faq', 'Client privacy' ) ),
		'accessibility'       => array( array( 'What has been checked', 'h', 'What has been checked' ), array( 'Known limitations', 'h', 'Known limitations' ), array( 'Feedback', 'h', 'Feedback' ) ),
	);
	if ( ! isset( $plans[ $page->post_name ] ) ) {
		return $content;
	}
	$links    = array();
	$has_faq  = false;
	foreach ( array_slice( $plans[ $page->post_name ], 0, 4 ) as $item ) {
		list( $label, $kind, $match ) = $item;
		$q = preg_quote( $match, '#' );
		if ( 'h' === $kind ) {
			$pattern = '#(<h[1-6]\b[^>]*>)(?=\s*(?:<[^>]+>\s*)*' . $q . ')#i';
		} elseif ( 'cls' === $kind ) {
			$pattern = '#(<div\b[^>]*class="[^"]*' . $q . '[^"]*"[^>]*>)#i';
		} elseif ( 'blk' === $kind ) {
			$pattern = '#(<div\b[^>]*class="[^"]*wp-block-group[^"]*"[^>]*>)(?=.{0,900}?' . $q . ')#is';
		} else {
			$pattern = '#(<[a-z0-9]+\b[^>]*\sid="' . $q . '"[^>]*>)#i';
		}
		if ( 'faq' === $kind ) {
			// Step by step (a single regular expression over the long accordion markup can hit PHP's regex limits): find each accordion item and the question title inside it.
			$m      = null;
			$offset = 0;
			while ( preg_match( '#<div\b[^>]*class="[^"]*uagb-faq-item[^"]*"[^>]*>#i', $content, $d, PREG_OFFSET_CAPTURE, $offset ) ) {
				$end  = $d[0][1] + strlen( $d[0][0] );
				$next = preg_match( '#<div\b[^>]*uagb-faq-item#i', $content, $nx, PREG_OFFSET_CAPTURE, $end ) ? $nx[0][1] : strlen( $content );
				if ( preg_match( '#class="uagb-question"[^>]*>\s*' . $q . '#i', substr( $content, $end, $next - $end ) ) ) {
					$m = array( $d[0], $d[0] );
					break;
				}
				$offset = $end;
			}
			if ( ! $m ) {
				continue;
			}
		} elseif ( ! preg_match( $pattern, $content, $m, PREG_OFFSET_CAPTURE ) ) {
			continue;
		}
		$tag = $m[1][0];
		if ( preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', $tag, $im ) ) {
			$id = $im[1];
		} else {
			$id      = 'ts-' . sanitize_title( $label );
			$new_tag = preg_replace( '/^<([a-z0-9]+)/i', '<$1 id="' . esc_attr( $id ) . '"', $tag, 1 );
			$content = substr_replace( $content, $new_tag, $m[1][1], strlen( $tag ) );
		}
		$has_faq = $has_faq || 'faq' === $kind;
		$links[] = '<li><a href="#' . esc_attr( $id ) . '">' . esc_html( $label ) . '</a></li>';
	}
	if ( count( $links ) < 2 ) {
		return $content;
	}
	if ( $has_faq ) {
		// A jump link to an accordion panel also opens it, then scrolls it into view once the other panel has slid shut.
		add_action( 'wp_footer', function () {
			echo '<script>document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest(".ts-jump a");if(!a){return;}var t=document.getElementById(a.getAttribute("href").slice(1));if(t&&t.classList.contains("uagb-faq-item")&&!t.classList.contains("uagb-faq-item-active")){var b=t.querySelector(".uagb-faq-questions-button");if(b){setTimeout(function(){b.click();},0);setTimeout(function(){t.scrollIntoView();},700);}}});</script>';
		}, 99 );
	}
	return '<nav class="ts-jump" aria-label="On this page"><ul class="ts-jump__list">' . implode( '', $links ) . '</ul></nav>' . $content;
}, 20 );
CODE;
$code = $code . "\n";

$start = '/* ts-a11y-jump:start (2026-10-18, accessibility) */';
$end   = '/* ts-a11y-jump:end */';
$block = $start . "\n"
	. ".ts-jump{margin:0 0 1.25rem;padding:.35rem 0;font-size:.85rem;line-height:1.4}\n"
	. ".entry-content .ts-jump .ts-jump__list{display:flex;flex-wrap:wrap;align-items:center;gap:0 1rem;margin:0;padding:0;list-style:none}\n"
	. ".entry-content .ts-jump .ts-jump__list li{margin:0;padding:0}\n"
	. ".entry-content .ts-jump__list li+li::before{content:\"\\00b7\" / \"\";margin:0 .65rem 0 0;color:var(--ts-muted,#4A5563)}\n"
	. ".entry-content .ts-jump__list a{display:inline-block;padding:.3rem .1rem;color:var(--ts-muted,#4A5563);text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:.25em}\n"
	. ".entry-content .ts-jump__list a:hover{color:var(--ts-ink,#1C2531)}\n"
	. ".entry-content .ts-jump__list a:focus-visible{outline:3px solid var(--ts-accent,#C8963E);outline-offset:2px}\n"
	. "@media (max-width:600px){.entry-content .ts-jump__list li+li::before{display:none}}\n"
	. $end;

$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$css = (string) $post->post_content;

$read = function () use ( $title, $code, $block ) {
	$p = wp_get_custom_css_post( 'astra' );
	$c = $p ? (string) $p->post_content : '';
	$n = substr_count( $c, 'ts-a11y-jump:start' );
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
	$file = rtrim( $backup_dir, '/' ) . '/jump-links-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array( 'created' => gmdate( 'c' ), 'home' => $home, 'css_sha256' => hash( 'sha256', $css ), 'css' => $css, 'created_id' => null ) ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$snippet = new WPCode_Snippet( array(
		'title' => $title, 'code' => $code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true,
		'note'  => 'Created 2026-10-18 by scripts/work-with-me-employer/accessibility/install-jump-links.php (rollback: delete this snippet and the ts-a11y-jump CSS block).',
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
	WP_CLI::success( 'Created snippet #' . $id . ' "' . $title . '" and added the ts-a11y-jump block to the Additional CSS. Before-snapshot: ' . $file );
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
WP_CLI::success( 'Rollback done: snippet #' . (int) $snap['created_id'] . ' deleted and the ts-a11y-jump block is out of the Additional CSS.' );
