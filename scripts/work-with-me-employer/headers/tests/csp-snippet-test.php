<?php
// Offline test of the headers snippet change (runs in check-all.sh). Pulls the new snippet and the guard hash out of install-headers-csp.php and runs BOTH the
// original snippet (from the byte-exact fixture) and the new one in a harness that captures every header() call, for every combination of HTTPS and logged-in.
namespace WWM_Test {
	$GLOBALS['wwm'] = array( 'hooks' => array(), 'headers' => array(), 'ssl' => true, 'in' => false );
	function add_action( $tag, $fn, $prio = 10 ) { $GLOBALS['wwm']['hooks'][ $tag ] = array( $fn, $prio ); }
	function is_ssl() { return $GLOBALS['wwm']['ssl']; }
	function is_user_logged_in() { return $GLOBALS['wwm']['in']; }
	function header( $line ) { $GLOBALS['wwm']['headers'][] = $line; }
}
namespace {
	// The exact policy that was validated in a real browser with tools/csp-test.mjs (0 violations, 16/16 page-views pixel-identical).
	const TESTED_POLICY = "default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://maps.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; img-src 'self' data: https://*.google.com https://*.gstatic.com https://*.googleapis.com; connect-src 'self' https://maps.googleapis.com https://places.googleapis.com https://www.google.com; frame-src 'self' https://www.google.com https://maps.google.com; worker-src 'self' blob:; media-src 'self'; object-src https://maps.google.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";
	// Version 2 (2026-10-18): the same policy with object-src also allowing www.google.com, which Firefox and Safari need to load the Contact page map (embed redirect chain).
	// Validated in Firefox and Chromium on staging32 and live (0 violations, full map). headers/install-headers-csp-v2.php produces it from the version-1 snippet; posture-check.py pins it.
	const TESTED_POLICY_V2 = "default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://maps.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; img-src 'self' data: https://*.google.com https://*.gstatic.com https://*.googleapis.com; connect-src 'self' https://maps.googleapis.com https://places.googleapis.com https://www.google.com; frame-src 'self' https://www.google.com https://maps.google.com; worker-src 'self' blob:; media-src 'self'; object-src https://maps.google.com https://www.google.com; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";
	$src = file_get_contents( '/pkg/install-headers-csp.php' );
	preg_match( "/<<<'CODE'\n(.*?)\nCODE;/s", $src, $m );
	$new = $m[1] . "\n";
	preg_match( "/\\\$snippet_old_sha = '([0-9a-f]{64})'/", $src, $g );
	$guard = $g[1];
	$old = file_get_contents( '/pkg/tests/fixtures/snippet-1036-old.bin' );
	$fail = 0;
	$t = function ( $ok, $name, $detail = '' ) use ( &$fail ) { echo ( $ok ? 'PASS ' : 'FAIL ' ) . $name . ( '' !== $detail ? "  [$detail]" : '' ) . "\n"; if ( ! $ok ) { $fail++; } };
	$run = function ( $code, $ssl, $in ) {
		$GLOBALS['wwm'] = array( 'hooks' => array(), 'headers' => array(), 'ssl' => $ssl, 'in' => $in );
		eval( 'namespace WWM_Test; ' . $code );
		list( $fn, $prio ) = $GLOBALS['wwm']['hooks']['send_headers'];
		$fn();
		return array( $GLOBALS['wwm']['headers'], $prio );
	};
	$t( hash( 'sha256', $old ) === $guard, 'guard constant equals sha256 of the real stored bytes of snippet 1036', substr( $guard, 0, 16 ) );
	$t( false === strpos( $new, "\r" ), 'new code uses LF line endings' );
	foreach ( array( array( true, false, 'HTTPS, logged out' ), array( true, true, 'HTTPS, logged in' ), array( false, false, 'plain http, logged out' ), array( false, true, 'plain http, logged in' ) ) as $c ) {
		list( $ssl, $in, $label ) = $c;
		list( $oh, $op ) = $run( $old, $ssl, $in );
		list( $nh, $np ) = $run( $new, $ssl, $in );
		$csp = array_values( array_filter( $nh, function ( $h ) { return 0 === strpos( $h, 'Content-Security-Policy:' ); } ) );
		$rest = array_values( array_filter( $nh, function ( $h ) { return 0 !== strpos( $h, 'Content-Security-Policy:' ); } ) );
		$t( $rest === $oh, "[$label] every header the old snippet sent is still sent, unchanged and in the same order", count( $oh ) . ' headers' );
		$t( $np === 999 && $op === 999, "[$label] still hooked on send_headers at priority 999" );
		if ( $in ) {
			$t( 0 === count( $csp ), "[$label] NO Content-Security-Policy for a logged-in user" );
		} else {
			$t( 1 === count( $csp ) && 'Content-Security-Policy: ' . TESTED_POLICY === $csp[0], "[$label] exactly one CSP header, byte-equal to the browser-tested policy" );
		}
		$t( $ssl === (bool) array_filter( $nh, function ( $h ) { return 0 === strpos( $h, 'Strict-Transport-Security:' ); } ), "[$label] HSTS is sent only over HTTPS" );
	}
	$pol = TESTED_POLICY;
	$t( false === strpos( $pol, 'unsafe-eval' ) && 1 === preg_match( "/frame-ancestors 'self'/", $pol ) && 1 === preg_match( "/base-uri 'self'/", $pol ) && 1 === preg_match( "/form-action 'self'/", $pol ), 'policy has no unsafe-eval and keeps frame-ancestors, base-uri and form-action on self' );
	$t( 1 === preg_match( "/default-src 'self'/", $pol ) && ! preg_match( '/(^|[ ;])\*(;|$| )/', $pol ), 'policy defaults to self and has no bare wildcard source' );
	// ---- version 2: install-headers-csp-v2.php changes ONE line of the snippet; prove the result sends the version-2 policy and nothing else changed ----
	$src2 = file_get_contents( '/pkg/install-headers-csp-v2.php' );
	$t( 1 === preg_match( "/\\\$snippet_old = <<<'CODE'\n(.*?)\nCODE;/s", $src2, $m2 ) && 1 === preg_match( "/str_replace\( '([^']+)', '([^']+)', \\\$snippet_old/", $src2, $r2 ), 'v2 installer: old snippet text and the one replacement found' );
	$old2 = $m2[1] . "\n";
	$new2 = str_replace( $r2[1], $r2[2], $old2, $cnt2 );
	$t( $old2 === $new, 'v2 installer expects exactly the snippet that the version-1 installer writes' );
	$t( 1 === $cnt2 && $new2 !== $old2, 'v2 replacement matches exactly once' );
	foreach ( array( array( true, false, 'HTTPS, logged out' ), array( true, true, 'HTTPS, logged in' ), array( false, false, 'plain http, logged out' ), array( false, true, 'plain http, logged in' ) ) as $c2 ) {
		list( $ssl2, $in2, $label2 ) = $c2;
		list( $h1 ) = $run( $new, $ssl2, $in2 );
		list( $h2, $p2 ) = $run( $new2, $ssl2, $in2 );
		$want2 = array_map( function ( $h ) { return 'Content-Security-Policy: ' . TESTED_POLICY === $h ? 'Content-Security-Policy: ' . TESTED_POLICY_V2 : $h; }, $h1 );
		$t( $h2 === $want2 && 999 === $p2, "[v2, $label2] same headers in the same order as version 1; only the policy line differs" );
		if ( $ssl2 && ! $in2 ) {
			$csp2 = array_values( array_filter( $h2, function ( $h ) { return 0 === strpos( $h, 'Content-Security-Policy:' ); } ) );
			$t( 1 === count( $csp2 ) && 'Content-Security-Policy: ' . TESTED_POLICY_V2 === $csp2[0], "[v2, $label2] exactly one CSP header, byte-equal to the version-2 policy" );
		}
	}
	$t( TESTED_POLICY_V2 === str_replace( 'object-src https://maps.google.com;', 'object-src https://maps.google.com https://www.google.com;', TESTED_POLICY ) && 1 === substr_count( TESTED_POLICY_V2, 'object-src https://maps.google.com https://www.google.com;' ), 'version-2 policy differs from version 1 in the object-src source list only' );
	echo "\nRESULT: " . ( $fail ? "$fail FAILED" : 'all passed' ) . "\n";
	exit( $fail ? 1 : 0 );
}
