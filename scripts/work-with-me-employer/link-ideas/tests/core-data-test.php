<?php
// Offline behavior test: registered data, safe current-page JSON, draft privacy,
// duplicate inclusion and empty-page fallback. No WordPress or site writes.
$hooks = array(); $current_slug = 'thai-massage'; $admin = false; $feed = false;
$payload = array( 'schema' => 1, 'slug' => 'thai-massage', 'approval' => array( 'status' => 'approved', 'source' => 'test' ), 'items' => array( array( '</script><script>alert("x")</script>&', 'ts-one' ) ), 'coverage' => array( 'ts-one' => 'most' ) );
$pages = array( 'thai-massage' => $payload, 'chair-massage' => array( 'secret-other-page' => 'must not leak' ) );
function is_admin() { return $GLOBALS['admin']; }
function is_feed() { return $GLOBALS['feed']; }
function is_page() { return true; }
function get_queried_object() { return (object) array( 'post_name' => $GLOBALS['current_slug'] ); }
function apply_filters( $name, $value ) { if ( 'ts_pk_pages' !== $name ) { throw new Exception( 'Wrong filter' ); } return $GLOBALS['pages']; }
function add_action( $name, $fn, $priority ) { $GLOBALS['hooks'][ $name ][] = $fn; }
function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
function render( $name ) { ob_start(); foreach ( $GLOBALS['hooks'][ $name ] as $fn ) { $fn(); } return ob_get_clean(); }
$passed = 0;
function check( $name, $ok ) { if ( ! $ok ) { throw new Exception( $name ); } ++$GLOBALS['passed']; echo "PASS $name\n"; }
require __DIR__ . '/../peek-nav.php';
require __DIR__ . '/../peek-nav.php';
check( 'duplicate inclusion registers only one pair of hooks', count( $hooks['wp_head'] ) === 1 && count( $hooks['wp_footer'] ) === 1 );
$output = render( 'wp_footer' );
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
$current_slug = 'unregistered';
check( 'unregistered page emits no core or data', render( 'wp_head' ) === '' && render( 'wp_footer' ) === '' );
$current_slug = 'thai-massage'; $admin = true;
check( 'admin emits nothing', render( 'wp_head' ) === '' && render( 'wp_footer' ) === '' );
$admin = false; $feed = true;
check( 'feed emits nothing', render( 'wp_head' ) === '' && render( 'wp_footer' ) === '' );
echo "Core data: $passed passed\n";
