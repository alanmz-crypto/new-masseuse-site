<?php
// Read-only public response gate. Snapshot/state guards run before and after GETs.
try {
    if ( PHP_SAPI !== 'cli' || count( $argv ) !== 5 || ! in_array( $argv[3], array( 'applied', 'original' ), true ) ) { throw new RuntimeException(); }
    $s = json_decode( file_get_contents( $argv[2] ), true, 512, JSON_THROW_ON_ERROR );
    $which = $argv[3]; $route = $argv[4];
    $m = json_decode( file_get_contents( __DIR__ . '/manifest.json' ), true, 512, JSON_THROW_ON_ERROR );
    $routes = array_column( $m['pages'], 'path' );
    if ( ! in_array( $route, array_merge( $routes, array( '/?s=massage', '/peek-release-not-a-real-page/' ) ), true ) || $s['bundle'] !== 'peek-visitor-release-v1' || $s['home'] !== 'http://thaisolitude.com' || $s['manifest_sha256'] !== hash_file( 'sha256', __DIR__ . '/manifest.json' ) || $s['profile_sha256'] !== hash_file( 'sha256', __DIR__ . '/live-profile.json' ) ) { throw new RuntimeException(); }
    if ( $which === 'applied' && $s['complete'] !== true ) { throw new RuntimeException(); }
    $state = $s[$which === 'applied' ? 'new' : 'old'];
    $html = file_get_contents( $argv[1] ); $css = $state['records'][29]['post']['post_content'];
    if ( preg_match_all( '~<body\b[^>]*\bclass=(["\x27])(.*?)\1[^>]*>~is', $html, $body ) !== 1 ) { throw new RuntimeException(); }
    $classes = preg_split( '/\s+/', trim( html_entity_decode( $body[2][0], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
    $index = array_search( $route, $routes, true );
    if ( $index !== false ) {
        $expected_page = 'page-id-' . $m['pages'][$index]['id'];
        $page_classes = array_values( preg_grep( '/^page-id-[0-9]+$/D', $classes ) );
        if ( $page_classes !== array( $expected_page ) || in_array( 'search', $classes, true ) || in_array( 'error404', $classes, true ) ) { throw new RuntimeException(); }
    } elseif ( $route === '/?s=massage' ) {
        if ( ! in_array( 'search', $classes, true ) || in_array( 'error404', $classes, true ) || preg_grep( '/^page-id-[0-9]+$/D', $classes ) ) { throw new RuntimeException(); }
    } elseif ( ! in_array( 'error404', $classes, true ) || in_array( 'search', $classes, true ) || preg_grep( '/^page-id-[0-9]+$/D', $classes ) ) { throw new RuntimeException(); }

    if ( preg_match_all( '~<style\b[^>]*\bid=["\x27]wp-custom-css["\x27][^>]*>(.*?)</style\s*>~is', $html, $match ) !== 1 || trim( $match[1][0], " \t\n\r\f" ) !== trim( $css, " \t\n\r\f" ) || ! str_contains( $html, 'LMT-29905' ) ) { throw new RuntimeException(); }
    $source = file_get_contents( __DIR__ . '/payload/core.php' );
    foreach ( array( 'style' => 'ts-peek-nav-css', 'script' => 'ts-peek-nav-runtime' ) as $tag => $id ) {
        $pattern = '#<' . $tag . ' id="' . $id . '">(.*?)</' . $tag . '>#s';
        $count = preg_match_all( $pattern, $html, $actual );
        if ( $which === 'original' ) { if ( $count !== 0 || str_contains( $html, $id ) ) { throw new RuntimeException(); } }
        elseif ( preg_match( $pattern, $source, $expected ) !== 1 || $count !== 1 || $actual[1][0] !== $expected[1] ) { throw new RuntimeException(); }
    }
    $slug = trim( $route, '/' ); $summary = in_array( $slug, $m['summary_slugs'], true );
    foreach ( array( 'id="ts-peek-nav-config"' => $which === 'applied' ? 1 : 0, 'id="ts-peek-nav-data"' => $which === 'applied' && $summary ? 1 : 0, 'ts-pill-nav' => 0 ) as $needle => $count ) { if ( substr_count( $html, $needle ) !== $count ) { throw new RuntimeException(); } }
    if ( $summary && $which === 'applied' ) {
        if ( preg_match_all( '~<script\b[^>]*id="ts-peek-nav-data"[^>]*>(.*?)</script>~s', $html, $data ) !== 1 ) { throw new RuntimeException(); }
        $expected = json_decode( file_get_contents( __DIR__ . '/payload/data/' . $slug . '.json' ), true, 512, JSON_THROW_ON_ERROR );
        if ( json_decode( $data[1][0], true, 512, JSON_THROW_ON_ERROR ) !== $expected ) { throw new RuntimeException(); }
    }
    if ( ( $summary || $slug === 'services' ) && substr_count( $html, 'class="ts-jump"' ) !== 1 ) { throw new RuntimeException(); }
    echo "Exact public response verified: $which $route\n";
} catch ( Throwable $e ) { fwrite( STDERR, "Rendered release/snapshot scope differs; no private values disclosed.\n" ); exit( 1 ); }
