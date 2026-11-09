<?php
// Execute the dedicated push wrapper's real PHP renderer; no shell/network mocks.
function run_focus_render_tests() {
    $passed = 0;
    $check = static function ( $ok, $label ) use ( &$passed ) { if ( ! $ok ) { throw new RuntimeException( 'FAIL input-focus renderer: ' . $label ); } $passed++; };
    $wrapper = file_get_contents( dirname( __DIR__ ) . '/push-peek-input-focus.sh' );
    $historical = file_get_contents( dirname( __DIR__ ) . '/push-peek-nav.sh' );
    $pattern = <<<'REGEX'
#    php -r '(.*?)' "\$source" "\$tmp/\$key\.html" "\$slug"#s
REGEX;
    $check( preg_match_all( $pattern, $wrapper, $programs ) === 1, 'one actual embedded renderer extracted' );
    $renderer = $programs[1][0];
    $inventory_pattern = "/read -r -d '' INVENTORY <<'PHP' \\|\\| true\\n(.*?)\\nPHP\\n/s";
    $check( preg_match( $inventory_pattern, $wrapper, $current_inventory ) === 1 && preg_match( $inventory_pattern, $historical, $old_inventory ) === 1 && $current_inventory[1] === $old_inventory[1], 'published inventory guard is identical to the fully exercised historical body' );
    $dir = sys_get_temp_dir() . '/peek-focus-render-' . bin2hex( random_bytes( 5 ) );
    if ( ! mkdir( $dir, 0700 ) ) { throw new RuntimeException( 'Cannot create renderer fixture directory.' ); }
    try {
        $bodies = array();
        foreach ( array( 'current' => 'peek-nav.php', 'v6' => 'peek-nav-shared-v6.php' ) as $version => $name ) {
            $source = file_get_contents( dirname( __DIR__ ) . '/' . $name );
            file_put_contents( $dir . '/' . $version . '.php', $source );
            foreach ( array( 'style' => 'ts-peek-nav-css', 'script' => 'ts-peek-nav-runtime' ) as $tag => $id ) {
                if ( preg_match( '#<' . $tag . ' id="' . $id . '">(.*?)</' . $tag . '>#s', $source, $match ) !== 1 ) { throw new RuntimeException( 'Missing real source fixture: ' . $version . '/' . $id ); }
                $bodies[ $version ][ $tag ] = $match[1];
            }
        }
        $html = static function ( $version, $slug ) use ( $bodies ) {
            $out = '<style id="ts-peek-nav-css">' . $bodies[ $version ]['style'] . '</style><script id="ts-peek-nav-runtime">' . $bodies[ $version ]['script'] . '</script><script id="ts-peek-nav-config">{}</script>';
            if ( in_array( $slug, array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ), true ) ) { $out .= '<script id="ts-peek-nav-data">{}</script>'; }
            if ( in_array( $slug, array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage', 'services' ), true ) ) { $out .= '<nav class="ts-jump"></nav>'; }
            return $out;
        };
        $run = static function ( $version, $slug, $markup, $should_pass, $label ) use ( $dir, $renderer, $check ) {
            file_put_contents( $dir . '/page.html', $markup );
            $pipes = array();
            $proc = proc_open( array( PHP_BINARY, '-r', $renderer, $dir . '/' . $version . '.php', $dir . '/page.html', $slug ), array( array( 'pipe', 'r' ), array( 'pipe', 'w' ), array( 'pipe', 'w' ) ), $pipes );
            if ( ! is_resource( $proc ) ) { throw new RuntimeException( 'Cannot execute actual PHP renderer.' ); }
            fclose( $pipes[0] ); $out = stream_get_contents( $pipes[1] ); $err = stream_get_contents( $pipes[2] ); fclose( $pipes[1] ); fclose( $pipes[2] );
            $rc = proc_close( $proc );
            $check( $should_pass ? $rc === 0 && $err === '' && str_contains( $out, 'exact served core/style and data scope: ' . $slug ) : $rc === 1 && str_contains( $err, 'FAIL ' ), $label );
        };
        foreach ( array( 'current', 'v6' ) as $version ) {
            foreach ( array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage', 'services', 'about' ) as $slug ) { $run( $version, $slug, $html( $version, $slug ), true, $version . ' exact runtime/style and page scope: ' . $slug ); }
        }
        $run( 'current', 'thai-massage', $html( 'v6', 'thai-massage' ), false, 'frozen-v6 response cannot verify a current deployment' );
        $valid = $html( 'current', 'thai-massage' );
        $run( 'current', 'thai-massage', str_replace( '<script id="ts-peek-nav-runtime">', '<script id="ts-peek-nav-runtime">/* changed */', $valid ), false, 'changed runtime refused' );
        $run( 'current', 'thai-massage', str_replace( '<style id="ts-peek-nav-css">', '<style id="ts-peek-nav-css">/* changed */', $valid ), false, 'changed stylesheet refused' );
        $run( 'current', 'thai-massage', $valid . '<span id="ts-peek-nav-runtime"></span>', false, 'duplicate runtime marker refused even with one exact script' );
        $run( 'current', 'thai-massage', str_replace( '<script id="ts-peek-nav-config">{}</script>', '', $valid ), false, 'missing shared config refused' );
        foreach ( array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ) as $slug ) {
            $run( 'current', $slug, str_replace( '<script id="ts-peek-nav-data">{}</script>', '', $html( 'current', $slug ) ), false, 'missing approved page data refused: ' . $slug );
        }
        $run( 'current', 'thai-massage', $valid . '<script id="ts-peek-nav-data">{}</script>', false, 'duplicate approved page data refused' );
        $run( 'current', 'about', $html( 'current', 'about' ) . '<script id="ts-peek-nav-data">{}</script>', false, 'unexpected summary data refused on a title-only page' );
        $run( 'current', 'services', str_replace( '<nav class="ts-jump"></nav>', '', $html( 'current', 'services' ) ), false, 'missing Services jump-row fallback refused' );
        $run( 'current', 'thai-massage', $valid . '<nav class="ts-pill-nav"></nav>', false, 'obsolete pill navigation refused' );
    } finally {
        foreach ( glob( $dir . '/*' ) as $file ) { unlink( $file ); }
        rmdir( $dir );
    }
    return $passed;
}
if ( realpath( $_SERVER['SCRIPT_FILENAME'] ?? '' ) === __FILE__ ) { echo 'Input-focus actual renderer: ' . run_focus_render_tests() . " checks passed\n"; }
