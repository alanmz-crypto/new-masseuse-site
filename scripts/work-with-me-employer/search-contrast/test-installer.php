<?php
// Isolated WordPress API model: mutation refusals, exact bytes and private recovery.
if ( PHP_SAPI !== 'cli' ) { exit( 1 ); }
$tmp = sys_get_temp_dir() . '/ts-search-installer-' . bin2hex( random_bytes( 5 ) );
mkdir( $tmp, 0700 ); mkdir( $tmp . '/public', 0700 ); mkdir( $tmp . '/backup', 0700 ); mkdir( $tmp . '/pkg', 0700 );
define( 'ABSPATH', $tmp . '/public/' );
foreach ( array( 'install-search-contrast.php', 'search-contrast.css', 'verify-render.php' ) as $file ) { copy( __DIR__ . '/' . $file, $tmp . '/pkg/' . $file ); }
$real_manifest = json_decode( file_get_contents( __DIR__ . '/manifest.json' ), true );
$base = "/* Preserve tabs, quote, slash and Thai bytes */\n\t.a:before { content: 'ไทย \\\" \\\\'; }\n \t\n";
$manifest = $real_manifest;
$manifest['baseline'] = array( 'local' => hash( 'sha256', $base ), 'staging39' => hash( 'sha256', $base ) );
$manifest_text = json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";
file_put_contents( $tmp . '/pkg/manifest.json', $manifest_text );
$block = file_get_contents( $tmp . '/pkg/search-contrast.css' );
$new = $base . "\n" . $block;
$tests = 0; $failures = 0;
function assert_case( $ok, $name ) { global $tests, $failures; ++$tests; if ( ! $ok ) { ++$failures; echo 'FAIL ' . $name . "\n"; } }
class WP_CLI {
    public static array $messages = array();
    public static function error( $message ) { throw new RuntimeException( $message ); }
    public static function success( $message ) { self::$messages[] = 'Success: ' . $message; }
    public static function log( $message ) { self::$messages[] = $message; }
}
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function home_url() { return $GLOBALS['mock_home']; }
function wp_get_environment_type() { return $GLOBALS['mock_env']; }
function get_stylesheet() { return $GLOBALS['mock_theme']; }
function current_user_can( $cap ) { return ! in_array( $cap, $GLOBALS['mock_denied'], true ); }
function wp_get_custom_css_post( $stylesheet ) {
    $copy = $GLOBALS['mock_post'] ? clone $GLOBALS['mock_post'] : null;
    if ( $copy && $GLOBALS['read_override'] !== null ) { $copy->post_content = $GLOBALS['read_override']; $GLOBALS['read_override'] = null; }
    return $copy;
}
function wp_json_encode( $value, $flags = 0 ) { return $GLOBALS['mock_json_failure'] ? false : json_encode( $value, $flags ); }
function clean_post_cache( $id ) {
    if ( $id !== 29 ) { throw new RuntimeException( 'Wrong cache identity' ); }
    ++$GLOBALS['cache_clears'];
    $behavior = array_shift( $GLOBALS['cache_behaviors'] ) ?? 'ok';
    if ( 'throw' === $behavior ) { throw new RuntimeException( 'Cache clear failed' ); }
    if ( 'wrong-read' === $behavior ) { $GLOBALS['read_override'] = 'stale cached content'; }
    if ( 'concurrent-edit' === $behavior ) { $GLOBALS['mock_post']->post_content .= 'later unrelated edit'; }
}
class SearchDB {
    public string $posts = 'fixture_posts';
    public function prepare( $sql, ...$arguments ) { return array( $sql, $arguments ); }
    public function query( $prepared ) {
        [$sql, $arguments] = $prepared;
        $expected_sql = "UPDATE fixture_posts SET post_content=%s WHERE ID=29 AND post_type='custom_css' AND post_name='astra' AND post_status='publish' AND BINARY post_content=BINARY %s";
        if ( $sql !== $expected_sql || count( $arguments ) !== 2 ) { throw new RuntimeException( 'Unexpected SQL; binary exact record compare-and-swap required' ); }
        ++$GLOBALS['cas_calls'];
        [$replacement, $expected] = $arguments;
        $behavior = array_shift( $GLOBALS['save_behaviors'] ) ?? 'ok';
        if ( 'stale' === $behavior ) { $GLOBALS['mock_post']->post_content .= 'concurrent change'; }
        if ( 'false' === $behavior ) { return false; }
        if ( 'zero' === $behavior ) { return 0; }
        if ( 'two' === $behavior ) { return 2; }
        if ( $GLOBALS['mock_post']->post_content !== $expected ) { return 0; }
        ++$GLOBALS['writes'];
        $GLOBALS['mock_post']->post_content = 'corrupt' === $behavior ? $replacement . 'unexpected database result' : $replacement;
        return 1;
    }
}
$wpdb = new SearchDB();
function reset_model() {
    global $tmp, $base, $manifest_text;
    $GLOBALS['mock_home'] = 'http://localhost:8090'; $GLOBALS['mock_env'] = 'local'; $GLOBALS['mock_theme'] = 'astra';
    $GLOBALS['mock_denied'] = array(); $GLOBALS['mock_json_failure'] = false; $GLOBALS['save_behaviors'] = array(); $GLOBALS['writes'] = 0;
    $GLOBALS['cache_behaviors'] = array(); $GLOBALS['cache_clears'] = 0; $GLOBALS['read_override'] = null; $GLOBALS['cas_calls'] = 0;
    $GLOBALS['mock_post'] = (object) array( 'ID' => 29, 'post_type' => 'custom_css', 'post_name' => 'astra', 'post_status' => 'publish', 'post_content' => $base, 'post_modified' => '2026-11-11 00:00:00', 'post_title' => 'astra' );
    file_put_contents( $tmp . '/pkg/manifest.json', $manifest_text );
    copy( __DIR__ . '/search-contrast.css', $tmp . '/pkg/search-contrast.css' );
    putenv( 'WWM_BACKUP_DIR=' . $tmp . '/backup' );
}
function invoke( $arguments ) {
    global $tmp;
    WP_CLI::$messages = array();
    $args = $arguments;
    try { include $tmp . '/pkg/install-search-contrast.php'; return array( true, implode( "\n", WP_CLI::$messages ) ); }
    catch ( Throwable $e ) { return array( false, $e->getMessage() ); }
}
function refused( $arguments, $label ) {
    $before = $GLOBALS['writes']; $content = $GLOBALS['mock_post']->post_content ?? null;
    [$ok, $message] = invoke( $arguments );
    assert_case( ! $ok && $before === $GLOBALS['writes'] && $content === ( $GLOBALS['mock_post']->post_content ?? null ), $label . ': refuses without a write (' . $message . ')' );
}
function applied_snapshot() {
    [$ok, $output] = invoke( array( 'apply' ) );
    assert_case( $ok, 'apply succeeds: ' . $output );
    preg_match( '/^SEARCH-SNAPSHOT (.+) ([a-f0-9]{64}) ([0-9]+)$/m', $output, $m );
    assert_case( count( $m ) === 4 && is_file( $m[1] ) && hash_file( 'sha256', $m[1] ) === $m[2] && filesize( $m[1] ) === (int) $m[3], 'actual snapshot marker has exact file hash and size' );
    return $m[1] ?? '';
}
reset_model();
[$ok, $output] = invoke( array( 'preflight' ) ); assert_case( $ok && str_contains( $output, 'ready;' ) && $writes === 0, 'ready preflight is read-only' );
$snap = applied_snapshot();
assert_case( $mock_post->post_content === $new && $writes === 1, 'appending block preserves all original bytes' );
assert_case( $cache_clears === 1 && $mock_post->post_modified === '2026-11-11 00:00:00' && $mock_post->post_title === 'astra', 'only content changes; cache cleared, dates and other fields preserved' );
assert_case( ( fileperms( $snap ) & 0077 ) === 0, 'snapshot has owner-only permissions' );
$saved = file_get_contents( $snap );
[$ok, $output] = invoke( array( 'preflight' ) ); assert_case( $ok && str_contains( $output, 'applied;' ) && $writes === 1, 'applied preflight is read-only' );
[$ok, $output] = invoke( array( 'apply' ) ); assert_case( $ok && $writes === 1 && file_get_contents( $snap ) === $saved, 'repeat apply neither writes CSS nor replaces snapshot' );
[$ok, $output] = invoke( array( 'verify', $snap ) ); assert_case( $ok && $writes === 1 && str_contains( $output, hash( 'sha256', $saved ) ), 'verify binds exact immutable snapshot read-only' );
refused( array( 'verify', $snap, 'original' ), 'applied CSS cannot verify as original' );
[$ok] = invoke( array( 'rollback', $snap ) ); assert_case( $ok && $mock_post->post_content === $base && $writes === 2, 'rollback restores all original bytes' );
[$ok] = invoke( array( 'verify', $snap, 'original' ) ); assert_case( $ok && $writes === 2, 'restored original verifies read-only' );
refused( array( 'verify', $snap ), 'original CSS cannot verify as applied' );
[$ok] = invoke( array( 'rollback', $snap ) ); assert_case( $ok && $writes === 2, 'repeat rollback verifies without writing' );
$snap2 = applied_snapshot(); assert_case( $snap2 !== $snap && file_get_contents( $snap ) === $saved, 'reapply gets a new snapshot without replacing prior recovery evidence' );

foreach ( array( 'https://thaisolitude.com', 'http://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com.evil.test', 'https://staging39.thaisolitude.com:443', 'https://user@staging39.thaisolitude.com', 'http://localhost:8091', 'https://localhost:8090' ) as $origin ) {
    reset_model(); $mock_home = $origin; refused( array( 'apply' ), 'foreign/malformed origin ' . $origin );
}
foreach ( array( 'http://staging39.thaisolitude.com', 'https://staging39.thaisolitude.com' ) as $origin ) {
    reset_model(); $mock_home = $origin; [$ok] = invoke( array( 'preflight' ) ); assert_case( $ok && $writes === 0, 'exact staging origin accepted read-only ' . $origin );
}
reset_model(); $mock_env = 'production'; refused( array( 'apply' ), 'nonlocal environment on localhost' );
reset_model(); $mock_theme = 'other'; refused( array( 'apply' ), 'wrong theme' );
foreach ( array( 'manage_options', 'edit_css', 'unfiltered_html' ) as $cap ) { reset_model(); $mock_denied = array( $cap ); refused( array( 'apply' ), 'missing capability ' . $cap ); }
foreach ( array( 'ID' => 30, 'post_type' => 'page', 'post_name' => 'other', 'post_status' => 'draft' ) as $field => $value ) { reset_model(); $mock_post->$field = $value; refused( array( 'apply' ), 'CSS identity ' . $field ); }
reset_model(); $mock_post = null; refused( array( 'apply' ), 'missing CSS record' );
foreach ( array( array( 'delete' ), array( 'apply', 'extra' ), array( 'rollback' ), array( 'verify' ), array( 'verify', $snap, 'anything' ), array( 'rollback', $snap, 'extra' ) ) as $args ) { reset_model(); refused( $args, 'invalid arguments ' . implode( ' ', $args ) ); }
reset_model(); file_put_contents( $tmp . '/pkg/search-contrast.css', "\n", FILE_APPEND ); refused( array( 'apply' ), 'changed payload hash' );
reset_model(); file_put_contents( $tmp . '/pkg/manifest.json', '{}' ); refused( array( 'apply' ), 'incomplete manifest' );
reset_model(); $mock_json_failure = true; refused( array( 'apply' ), 'snapshot encoding failure' );
foreach ( array( '', 'relative', ABSPATH, $tmp . '/missing' ) as $directory ) {
    reset_model(); putenv( 'WWM_BACKUP_DIR=' . $directory );
    refused( array( 'preflight' ), 'dry run rejects invalid private directory ' . $directory );
    refused( array( 'apply' ), 'invalid private directory ' . $directory );
}
reset_model(); symlink( $tmp . '/backup', $tmp . '/backup-link' ); putenv( 'WWM_BACKUP_DIR=' . $tmp . '/backup-link' );
refused( array( 'preflight' ), 'dry run rejects symlink backup directory' );
$path_message = WP_CLI::$messages[0] ?? '';
$path_report = str_starts_with( $path_message, 'SEARCH-PATH ' ) ? json_decode( substr( $path_message, 12 ), true ) : null;
assert_case( is_array( $path_report ) && array_keys( $path_report ) === array( 'input', 'realpath', 'webroot_realpath', 'input_is_link', 'is_dir', 'is_writable', 'owner_uid', 'group_gid', 'mode', 'process_euid' ) && $path_report['input'] === $tmp . '/backup-link' && $path_report['realpath'] === $tmp . '/backup' && $path_report['input_is_link'] === true && $path_report['is_writable'] === true, 'failed dry run reports only safe exact path predicates' );
refused( array( 'apply' ), 'symlink backup directory' );

foreach ( array( $base . 'drift', $new . 'later edit', str_replace( 'background-color', 'color', $new ), $new . "\n" . $block ) as $drift ) {
    reset_model(); $mock_post->post_content = $drift; refused( array( 'preflight' ), 'preflight changed CSS' ); refused( array( 'rollback', $snap ), 'CHANGED rollback' );
}
reset_model(); $mock_post->post_content = $new;
foreach ( array( 'home' => 'https://staging39.thaisolitude.com', 'bundle' => 'other', 'css_id' => 30, 'css_name' => 'other', 'manifest_sha256' => str_repeat( '0', 64 ), 'original_sha256' => str_repeat( '0', 64 ), 'applied_sha256' => str_repeat( '0', 64 ), 'original' => $base . 'x', 'applied' => $new . 'x' ) as $field => $value ) {
    $bad = json_decode( $saved, true ); $bad[$field] = $value; file_put_contents( $snap, json_encode( $bad ) );
    refused( array( 'rollback', $snap ), 'snapshot changed ' . $field );
}
file_put_contents( $snap, $saved );
chmod( $snap, 0644 ); refused( array( 'rollback', $snap ), 'public snapshot permissions' ); chmod( $snap, 0600 ); clearstatcache();
$link = $tmp . '/backup/search-contrast-before-20260101-000000-aaaaaaaa.json'; symlink( $snap, $link ); refused( array( 'rollback', $link ), 'symlink snapshot' );
$outside = $tmp . '/search-contrast-before-20260101-000000-bbbbbbbb.json'; file_put_contents( $outside, $saved ); chmod( $outside, 0600 ); refused( array( 'rollback', $outside ), 'snapshot outside private directory' );
$wrong_name = $tmp . '/backup/other-before.json'; file_put_contents( $wrong_name, $saved ); chmod( $wrong_name, 0600 ); refused( array( 'rollback', $wrong_name ), 'other update snapshot name' );

foreach ( array( 'zero', 'two', 'false' ) as $result ) {
    reset_model(); $save_behaviors = array( $result ); [$ok, $message] = invoke( array( 'apply' ) );
    assert_case( ! $ok && $writes === 0 && $cas_calls === 1 && $mock_post->post_content === $base, 'non-one CAS result refuses without recovery overwrite: ' . $result );
}
reset_model(); $save_behaviors = array( 'stale' ); [$ok, $message] = invoke( array( 'apply' ) ); assert_case( ! $ok && $writes === 0 && $cas_calls === 1 && $mock_post->post_content === $base . 'concurrent change', 'concurrent change between read/save survives failed CAS' );
foreach ( array( 'throw', 'wrong-read' ) as $cache_failure ) {
    reset_model(); $cache_behaviors = array( $cache_failure, 'ok' ); [$ok, $message] = invoke( array( 'apply' ) );
    assert_case( ! $ok && $writes === 2 && $cas_calls === 2 && $mock_post->post_content === $base && str_contains( $message, 'restored' ), 'failed cache/readback restores exact original via CAS: ' . $cache_failure );
}
reset_model(); $cache_behaviors = array( 'concurrent-edit' ); [$ok, $message] = invoke( array( 'apply' ) ); assert_case( ! $ok && $writes === 1 && $cas_calls === 2 && $mock_post->post_content === $new . 'later unrelated edit', 'recovery refuses concurrent post-save edit' );
reset_model(); $cache_behaviors = array( 'throw', 'throw' ); [$ok, $message] = invoke( array( 'apply' ) ); assert_case( ! $ok && $writes === 2 && $mock_post->post_content === $base && str_contains( $message, 'could not verify' ), 'unverified recovery cache clear is not claimed successful' );
reset_model(); $save_behaviors = array( 'corrupt' ); [$ok, $message] = invoke( array( 'apply' ) ); assert_case( ! $ok && $writes === 1 && $cas_calls === 2 && $mock_post->post_content === $new . 'unexpected database result', 'unexpected database result is not blindly overwritten' );

$render_cases = array(
    array( '<style id="wp-custom-css">' . $new . '</style>LMT-29905', 'applied', true, 'exact applied CSS' ),
    array( "<style id='wp-custom-css'>\n\t" . $new . "\n</style>LMT-29905", 'applied', true, 'only style framing whitespace ignored' ),
    array( '<style id="wp-custom-css">' . $base . '</style>LMT-29905', 'original', true, 'exact original CSS' ),
    array( '<style id="wp-custom-css">' . $base . '</style>LMT-29905', 'applied', false, 'old served CSS fails applied verification' ),
    array( '<style id="wp-custom-css">' . $new . '</style><style id="wp-custom-css">' . $new . '</style>LMT-29905', 'applied', false, 'duplicate CSS style refused' ),
    array( '<style id="wp-custom-css">' . $new . "\0</style>LMT-29905", 'applied', false, 'null byte is not ignored as framing whitespace' ),
    array( '<style id="wp-custom-css">' . str_replace( 'background-color', 'color', $new ) . '</style>LMT-29905', 'applied', false, 'changed served CSS refused' ),
    array( '<style id="wp-custom-css">' . $new . '</style>', 'applied', false, 'missing retained license markup refused' ),
);
foreach ( $render_cases as [$html, $which, $want, $label] ) {
    file_put_contents( $tmp . '/render.html', $html );
    $process = proc_open( array( PHP_BINARY, $tmp . '/pkg/verify-render.php', $tmp . '/render.html', $snap2, $which ), array( 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ), $pipes );
    $output = stream_get_contents( $pipes[1] ); $error = stream_get_contents( $pipes[2] ); fclose( $pipes[1] ); fclose( $pipes[2] );
    $rc = proc_close( $process ); assert_case( ( $rc === 0 ) === $want, 'render guard ' . $label . ': ' . $output . $error );
}

// Only this test's random directory is removed; no WordPress or site resources exist.
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $tmp, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
foreach ( $iterator as $item ) { $item->isDir() && ! $item->isLink() ? rmdir( $item->getPathname() ) : unlink( $item->getPathname() ); }
rmdir( $tmp );
echo 'Search installer: ' . ( $tests - $failures ) . ' passed / ' . $failures . " failed.\n";
exit( $failures ? 1 : 0 );
