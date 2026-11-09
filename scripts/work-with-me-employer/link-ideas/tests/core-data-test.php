<?php
// Offline behavior test: title-only visitors, safe current-page JSON, draft privacy
// and excluded contexts. No WordPress or site writes.
$hooks = array(); $current_slug = 'thai-massage'; $admin = false; $feed = false; $page_view = true;
$page_title = '<b>Thai &amp; Massage</b> &lt;/script&gt;&lt;script&gt;alert("title")&lt;/script&gt;';
$document_title = 'Document &amp; fallback';
$payload = array( 'schema' => 1, 'slug' => 'thai-massage', 'approval' => array( 'status' => 'approved', 'source' => 'test' ), 'items' => array( array( '</script><script>alert("x")</script>&', 'ts-one' ) ), 'coverage' => array( 'ts-one' => 'most' ) );
$pages = array( 'thai-massage' => $payload, 'chair-massage' => array( 'secret-other-page' => 'must not leak' ) );
function is_admin() { return $GLOBALS['admin']; }
function is_feed() { return $GLOBALS['feed']; }
function is_page() { return $GLOBALS['page_view']; }
function get_queried_object() { return null === $GLOBALS['current_slug'] ? null : (object) array( 'post_name' => $GLOBALS['current_slug'], 'post_title' => $GLOBALS['page_title'] ); }
function wp_get_document_title() { return $GLOBALS['document_title']; }
function wp_strip_all_tags( $text ) { return trim( strip_tags( preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $text ) ) ); }
function apply_filters( $name, $value ) { if ( 'ts_pk_pages' !== $name ) { throw new Exception( 'Wrong filter' ); } return $GLOBALS['pages']; }
function add_action( $name, $fn, $priority ) { $GLOBALS['hooks'][ $name ][] = $fn; }
function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
function render( $name ) { ob_start(); foreach ( $GLOBALS['hooks'][ $name ] as $fn ) { $fn(); } return ob_get_clean(); }
$passed = 0;
function check( $name, $ok ) { if ( ! $ok ) { throw new Exception( $name ); } ++$GLOBALS['passed']; echo "PASS $name\n"; }
function script_json( $output, $id ) {
    if ( ! preg_match( '/<script type="application\/json" id="' . preg_quote( $id, '/' ) . '">(.*?)<\/script>/s', $output, $match ) ) { throw new Exception( 'Missing JSON: ' . $id ); }
    return json_decode( $match[1], true, 512, JSON_THROW_ON_ERROR );
}
require __DIR__ . '/../peek-nav.php';
require __DIR__ . '/../peek-nav.php';
check( 'duplicate inclusion registers only one pair of hooks', count( $hooks['wp_head'] ) === 1 && count( $hooks['wp_footer'] ) === 1 );
$output = render( 'wp_footer' );
check( 'current page emits title config once', substr_count( $output, 'id="ts-peek-nav-config"' ) === 1 );
check( 'page title is plain text with entities decoded', script_json( $output, 'ts-peek-nav-config' ) === array( 'title' => 'Thai & Massage </script><script>alert("title")</script>' ) );
check( 'current page emits data once', substr_count( $output, 'id="ts-peek-nav-data"' ) === 1 );
check( 'other page data stays private', ! str_contains( $output, 'secret-other-page' ) );
check( 'script-closing text safely encoded', ! str_contains( $output, '<script>alert(' ) && str_contains( $output, '\u003C' ) );
preg_match( '/<script type="application\/json" id="ts-peek-nav-data">(.*?)<\/script>/s', $output, $match );
check( 'safe JSON round trips exact approved text', json_decode( $match[1], true ) === $payload );
$pages['thai-massage']['approval']['status'] = 'draft';
$output = render( 'wp_footer' );
preg_match( '/<script type="application\/json" id="ts-peek-nav-data">(.*?)<\/script>/s', $output, $match );
$draft = json_decode( $match[1], true );
check( 'draft text absent from public JSON', $draft['items'] === array() && ! str_contains( $match[1], 'alert' ) );
check( 'draft still receives core for whole-paragraph fallback', str_contains( $output, 'id="ts-peek-nav-runtime"' ) );
check( 'draft status does not change the title config', script_json( $output, 'ts-peek-nav-config' ) === array( 'title' => 'Thai & Massage </script><script>alert("title")</script>' ) );
$current_slug = 'unregistered';
$page_title = 'Practice &amp; unlisted';
$output = render( 'wp_footer' );
check( 'unregistered page receives CSS and runtime', str_contains( render( 'wp_head' ), 'id="ts-peek-nav-css"' ) && str_contains( $output, 'id="ts-peek-nav-runtime"' ) );
check( 'unregistered page emits only its title config', script_json( $output, 'ts-peek-nav-config' ) === array( 'title' => 'Practice & unlisted' ) && ! str_contains( $output, 'id="ts-peek-nav-data"' ) );
check( 'unregistered output contains no registered payload', ! str_contains( $output, 'secret-other-page' ) && ! str_contains( $output, 'alert("title")' ) );
$current_slug = 'thai-massage'; $pages['thai-massage']['schema'] = 2;
$output = render( 'wp_footer' );
check( 'invalid registration still gets title-only runtime', str_contains( $output, 'id="ts-peek-nav-runtime"' ) && ! str_contains( $output, 'id="ts-peek-nav-data"' ) && script_json( $output, 'ts-peek-nav-config' )['title'] === 'Practice & unlisted' );
$pages['thai-massage'] = $payload; $pages['thai-massage']['slug'] = 'another-page';
check( 'mismatched registration never emits page data', ! str_contains( render( 'wp_footer' ), 'id="ts-peek-nav-data"' ) );
$pages['thai-massage'] = $payload; $page_view = false;
$output = render( 'wp_footer' );
check( 'non-page visitor receives CSS and runtime', str_contains( render( 'wp_head' ), 'id="ts-peek-nav-css"' ) && str_contains( $output, 'id="ts-peek-nav-runtime"' ) );
check( 'non-page visitor uses document title without page data', script_json( $output, 'ts-peek-nav-config' ) === array( 'title' => 'Document & fallback' ) && ! str_contains( $output, 'id="ts-peek-nav-data"' ) );
$current_slug = null;
check( 'missing queried object uses document title', script_json( render( 'wp_footer' ), 'ts-peek-nav-config' ) === array( 'title' => 'Document & fallback' ) );
$page_view = true; $current_slug = 'unregistered'; $page_title = '';
check( 'empty page title uses document title', script_json( render( 'wp_footer' ), 'ts-peek-nav-config' ) === array( 'title' => 'Document & fallback' ) );
$document_title = 'Search &lt;/script&gt;&lt;script&gt;alert("fallback")&lt;/script&gt; &amp; "quotes"';
$output = render( 'wp_footer' );
check( 'document fallback safely round trips script-like text', script_json( $output, 'ts-peek-nav-config' ) === array( 'title' => 'Search </script><script>alert("fallback")</script> & "quotes"' ) && ! str_contains( $output, '<script>alert(' ) );
$current_slug = 'thai-massage'; $admin = true;
check( 'admin emits nothing', render( 'wp_head' ) === '' && render( 'wp_footer' ) === '' );
$admin = false; $feed = true;
check( 'feed emits nothing', render( 'wp_head' ) === '' && render( 'wp_footer' ) === '' );
echo "Core data: $passed passed\n";
