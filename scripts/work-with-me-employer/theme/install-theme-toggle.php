<?php
/**
 * Dark / light mode button in the header ("a dark/light reader button"), for ONE site at a time. Ryan, 2026-10-18: "would you test a dark/light reader button in the header. the latest staging is fine for that."
 *
 * What it writes (WordPress's own storage so a SiteGround push carries it; no files, no MU-plugin; no page content and no theme setting is edited):
 *   1. a small WPCode PHP snippet "ThemeToggle" (posts table): (a) in the <head>, a one-line script that sets data-ts-theme="dark" on <html> BEFORE the page paints when the visitor chose dark earlier
 *      (no flash); (b) right after the header (Astra's astra_header_after hook), a script that adds a 44 px round button, one in the desktop header after "Get in Touch" and one in the phone header before the
 *      menu button (the one that is not displayed is skipped by keyboards and screen readers). It is a toggle button (aria-label "Dark mode", aria-pressed), the choice is remembered in the browser
 *      (localStorage "ts-theme"), other open tabs follow, and the DEFAULT STAYS LIGHT: nobody sees a change unless they press it (the site is NOT switched by the operating system's dark setting).
 *   2. one marked block in Additional CSS ("ts-theme"): the button's look, plus the dark palette. The palette only overrides the site's own colour variables (--ts-*) and Astra's global colours under
 *      html[data-ts-theme="dark"], inside @media screen, so light mode and printing are byte-for-byte unaffected; a few explicit rules cover the places that use a colour in the opposite role
 *      (footer text, scroll-to-top button, the Work With Me phone bar, button text on the green).
 * Sources: theme-init.js, theme-toggle.js and theme-toggle.css next to this file (the same files the browser prototype used).
 * Trade-off: no JavaScript = no button (the site stays light and works exactly as before). Google's embedded map stays light (it is Google's content).
 *
 * Allow-list: localhost:8090, staging33 and live. Live was added on Ryan's "add that and i'll push" (2026-10-18), after the button was applied, tested and rollback-proven on localhost and staging33.
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
if ( ! in_array( $host, array( 'localhost:8090', 'staging33.thaisolitude.com', 'thaisolitude.com' ), true ) ) {
	WP_CLI::error( 'Refusing to run: ' . $home . ' is not an allowed target (localhost:8090, staging33 or live only).' );
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

$title = 'ThemeToggle';
$read_src = function ( $name ) {
	$t = file_get_contents( __DIR__ . '/' . $name );
	return false === $t ? '' : trim( $t );
};
$init_js = $read_src( 'theme-init.js' );
$tog_js  = $read_src( 'theme-toggle.js' );
$tog_css = $read_src( 'theme-toggle.css' );
if ( '' === $init_js || '' === $tog_js || '' === $tog_css ) {
	WP_CLI::error( 'theme-init.js, theme-toggle.js and theme-toggle.css must all exist next to the installer.' );
}
foreach ( array( $init_js, $tog_js ) as $js ) {
	if ( false !== stripos( $js, '</script' ) || false !== strpos( $js, 'TSJS' ) ) {
		WP_CLI::error( 'A script contains </script or the heredoc marker TSJS; it cannot be embedded safely.' );
	}
}
if ( false !== stripos( $tog_css, '</style' ) || false !== strpos( $tog_css, 'ts-theme:start' ) || false !== strpos( $tog_css, 'ts-theme:end' ) ) {
	WP_CLI::error( 'The CSS contains </style or a block marker; it cannot be embedded safely.' );
}
$code = "// Dark / light mode button in the header (Ryan 2026-10-18). Remove this snippet and the ts-theme block in Additional CSS to take it away. Default stays light; the choice is kept in localStorage \"ts-theme\".\n"
	. "add_action( 'wp_head', function () {\n\t\$js = <<<'TSJS'\n" . $init_js . "\nTSJS;\n\techo '<script id=\"ts-theme-init\">' . \$js . '</script>' . \"\\n\";\n}, 1 );\n"
	. "add_action( 'astra_header_after', function () {\n\t\$js = <<<'TSJS'\n" . $tog_js . "\nTSJS;\n\techo '<script id=\"ts-theme-toggle\">' . \$js . '</script>' . \"\\n\";\n} );\n";

$start = '/* ts-theme:start (2026-10-18) */';
$end   = '/* ts-theme:end */';
$block = $start . "\n" . $tog_css . "\n" . $end;

$post = wp_get_custom_css_post( 'astra' );
if ( ! $post || strlen( (string) $post->post_content ) < 1000 || false === strpos( (string) $post->post_content, 'ts-footer-links' ) ) {
	WP_CLI::error( 'The Additional CSS is missing or is not the known site CSS; nothing was changed.' );
}
$css = (string) $post->post_content;

$read = function () use ( $title, $code, $block ) {
	$p = wp_get_custom_css_post( 'astra' );
	$c = $p ? (string) $p->post_content : '';
	$n = substr_count( $c, 'ts-theme:start' );
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
	$file = rtrim( $backup_dir, '/' ) . '/theme-toggle-before-' . gmdate( 'Ymd-His' ) . '.json';
	$ok   = false !== file_put_contents( $file, wp_json_encode( array( 'created' => gmdate( 'c' ), 'home' => $home, 'css_sha256' => hash( 'sha256', $css ), 'css' => $css, 'created_id' => null ) ) );
	if ( ! $ok || filesize( $file ) < 1000 ) {
		WP_CLI::error( 'Could not write the before-snapshot; nothing was changed.' );
	}
	chmod( $file, 0600 );
	$snippet = new WPCode_Snippet( array(
		'title' => $title, 'code' => $code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true,
		'note'  => 'Created 2026-10-18 by scripts/work-with-me-employer/theme/install-theme-toggle.php. The dark/light button in the header comes from this snippet (and the ts-theme block in Additional CSS), not from the page content or the theme settings. Rollback: delete this snippet and the ts-theme CSS block.',
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
	WP_CLI::success( 'Created snippet #' . $id . ' "' . $title . '" and added the ts-theme block to the Additional CSS. Before-snapshot: ' . $file );
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
WP_CLI::success( 'Rollback done: snippet #' . (int) $snap['created_id'] . ' deleted and the ts-theme block is out of the Additional CSS.' );
