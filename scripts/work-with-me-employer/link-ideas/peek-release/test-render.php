<?php
// Synthetic response fixtures only. This is not a browser or live HTTP test.
$dir = sys_get_temp_dir() . '/release-render-' . bin2hex( random_bytes( 6 ) ); mkdir( $dir, 0700 );
$source = file_get_contents( __DIR__ . '/payload/core.php' ); $blocks = '';
foreach ( array( 'style' => 'ts-peek-nav-css', 'script' => 'ts-peek-nav-runtime' ) as $tag => $id ) {
    if ( preg_match( '#<' . $tag . ' id="' . $id . '">(.*?)</' . $tag . '>#s', $source, $match ) !== 1 ) { throw new RuntimeException( 'Fixture core extract failed' ); }
    $blocks .= $match[0];
}
$css = array( 'old' => 'body{color:old}', 'new' => 'body{color:new}' );
$snapshot = array( 'bundle' => 'peek-visitor-release-v1', 'home' => 'http://thaisolitude.com', 'manifest_sha256' => hash_file( 'sha256', __DIR__ . '/manifest.json' ), 'profile_sha256' => hash_file( 'sha256', __DIR__ . '/live-profile.json' ), 'complete' => true );
foreach ( $css as $state => $value ) { $snapshot[$state] = array( 'records' => array( 29 => array( 'post' => array( 'post_content' => $value ) ) ) ); }
$data = '<script type="application/json" id="ts-peek-nav-data">' . file_get_contents( __DIR__ . '/payload/data/thai-massage.json' ) . '</script>';
$config = '<script type="application/json" id="ts-peek-nav-config">{"title":"Synthetic"}</script>';
$license = '<p>LMT-29905</p>'; $jump = '<nav class="ts-jump"></nav>';
$applied = '<body class="page page-id-1160"><style id="wp-custom-css">' . $css['new'] . '</style>' . $blocks . $config . $data . $license . $jump;
$original = '<body class="page page-id-1160"><style id="wp-custom-css">' . $css['old'] . '</style>' . $license . $jump;
$checks = 0;
$test = static function ( $html, $snap, $state, $route, $ok ) use ( $dir, &$checks ) {
    file_put_contents( "$dir/snapshot.json", json_encode( $snap ) ); file_put_contents( "$dir/page.html", $html );
    $process = proc_open( array( PHP_BINARY, __DIR__ . '/verify-render.php', "$dir/page.html", "$dir/snapshot.json", $state, $route ), array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ), $pipes );
    fclose( $pipes[0] ); $out = stream_get_contents( $pipes[1] ); fclose( $pipes[1] ); $err = stream_get_contents( $pipes[2] ); fclose( $pipes[2] ); $rc = proc_close( $process ); $checks++;
    if ( ( $rc === 0 ) !== $ok || str_contains( $out . $err, 'SYNTHETIC_PRIVATE' ) ) { throw new RuntimeException( 'Rendered fixture failed at case ' . $checks ); }
};
try {
    $test( $applied, $snapshot, 'applied', '/thai-massage/', true );
    $test( $original, $snapshot, 'original', '/thai-massage/', true );
    foreach ( array( '/about/' => 'page page-id-298', '/?s=massage' => 'search search-results', '/peek-release-not-a-real-page/' => 'error404' ) as $route => $body_class ) {
        $good = str_replace( array( $data, 'page page-id-1160' ), array( '', $body_class ), $applied );
        $test( $good, $snapshot, 'applied', $route, true );
        $test( str_replace( $body_class, 'home page page-id-296', $good ), $snapshot, 'applied', $route, false );
    }
    $test( str_replace( 'page-id-1160', 'page-id-296', $applied ), $snapshot, 'applied', '/thai-massage/', false );
    foreach ( array( str_replace( 'color:new', 'color:SYNTHETIC_PRIVATE', $applied ), str_replace( $license, '', $applied ), str_replace( $jump, '', $applied ), $applied . $config, $applied . $data, str_replace( 'ts-peek-nav-runtime', 'unexpected-runtime', $applied ), str_replace( '"approved"', '"SYNTHETIC_PRIVATE"', $applied ) ) as $bad ) { $test( $bad, $snapshot, 'applied', '/thai-massage/', false ); }
    $test( $original . $blocks, $snapshot, 'original', '/thai-massage/', false );
    $test( $applied, $snapshot, 'applied', '/unreviewed-route/', false );
    foreach ( array( 'home' => 'https://other.invalid', 'manifest_sha256' => str_repeat( '0', 64 ), 'profile_sha256' => str_repeat( '0', 64 ), 'complete' => false ) as $key => $value ) { $bad = $snapshot; $bad[$key] = $value; $test( $applied, $bad, 'applied', '/thai-massage/', false ); }
    $bad = $snapshot; $bad['complete'] = false; $test( $original, $bad, 'original', '/thai-massage/', true );
    echo "PASS release rendered-response fixtures: $checks controls; no browser or live HTTP proof.\n";
} finally { unlink( "$dir/snapshot.json" ); unlink( "$dir/page.html" ); rmdir( $dir ); }
