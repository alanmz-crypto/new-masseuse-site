<?php
/**
 * One screen-only Search contrast block in Astra Additional CSS.
 * Local rehearsal and staging39 only. No navigation, page-copy or option writes.
 * Exact baseline and payload hashes are frozen in manifest.json. Any drift fails.
 * apply: private exclusive before-snapshot, one binary compare-and-swap, readback.
 * rollback FILE: require exact owned applied bytes; restore exact original bytes.
 * verify FILE [original]: read-only actual-snapshot verification.
 * Remote mutations additionally require the push wrapper's fresh off-site backup.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
global $wpdb;
$fail = static function ( $message ) { WP_CLI::error( $message . ' Stop and inspect before proceeding.' ); };
$home = untrailingslashit( home_url() );
$local_host = 'localhost:8090';
$local = 'http://' . $local_host === $home;
if ( ! $local && ! in_array( $home, array( 'http://staging39.thaisolitude.com', 'https://staging39.thaisolitude.com' ), true ) ) { $fail( 'Only exact localhost:8090 and staging39 origins are enabled.' ); }
if ( $local && 'local' !== wp_get_environment_type() ) { $fail( 'Localhost requires the isolated local environment.' ); }
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_css' ) || ! current_user_can( 'unfiltered_html' ) ) { $fail( 'Requires Astra and an administrator with CSS and unfiltered HTML permission.' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
$modes = array( 'preflight', 'apply', 'rollback' );
if ( ! in_array( $mode, array_merge( $modes, array( 'verify' ) ), true ) || ( in_array( $mode, array( 'preflight', 'apply' ), true ) && count( $args ) > 1 ) || ( 'rollback' === $mode && count( $args ) !== 2 ) || ( 'verify' === $mode && ( ! in_array( count( $args ), array( 2, 3 ), true ) || ( isset( $args[2] ) && 'original' !== $args[2] ) ) ) ) { $fail( 'Use preflight|apply|rollback FILE|verify FILE [original].' ); }
$manifest_bytes = file_get_contents( __DIR__ . '/manifest.json' );
$manifest = is_string( $manifest_bytes ) ? json_decode( $manifest_bytes, true ) : null;
$block = file_get_contents( __DIR__ . '/search-contrast.css' );
$hash_ok = static fn( $value ) => is_string( $value ) && 1 === preg_match( '/^[a-f0-9]{64}$/D', $value );
if ( ! is_array( $manifest ) || ( $manifest['schema'] ?? null ) !== 1 || ( $manifest['bundle'] ?? null ) !== 'search-contrast-v1' || ( $manifest['css_id'] ?? null ) !== 29 || ( $manifest['css_name'] ?? null ) !== 'astra' || ! $hash_ok( $manifest['baseline']['staging39'] ?? null ) || ! $hash_ok( $manifest['baseline']['local'] ?? null ) || ! is_string( $block ) || ! $hash_ok( $manifest['payload_sha256'] ?? null ) || hash( 'sha256', $block ) !== $manifest['payload_sha256'] ) { $fail( 'Frozen manifest or payload is incomplete or changed.' ); }
if ( substr_count( $block, '/* ts-search-contrast:start */' ) !== 1 || substr_count( $block, '/* ts-search-contrast:end */' ) !== 1 || ! str_starts_with( $block, '/* ts-search-contrast:start */' ) || ! str_ends_with( $block, "/* ts-search-contrast:end */\n" ) || strlen( $block ) > 1000 || preg_match( '/@import|url\s*\(|expression|!important|javascript:|</i', $block ) ) { $fail( 'Payload is not the single reviewed CSS block.' ); }
$manifest_sha = hash( 'sha256', $manifest_bytes );
$baseline_sha = $manifest['baseline'][ $local ? 'local' : 'staging39' ];
$suffix = "\n" . $block; // Preserve every original byte, including its trailing whitespace.
$read = static function () use ( $fail ) {
    $post = wp_get_custom_css_post( 'astra' );
    if ( ! $post || (int) $post->ID !== 29 || $post->post_type !== 'custom_css' || $post->post_name !== 'astra' || $post->post_status !== 'publish' ) { $fail( 'Astra Additional CSS identity differs from the reviewed record.' ); }
    return (string) $post->post_content;
};
$css = $read();
$state = 'CHANGED';
if ( hash( 'sha256', $css ) === $baseline_sha && ! str_contains( $css, 'ts-search-contrast:' ) ) {
    $state = 'ready';
} elseif ( str_ends_with( $css, $suffix ) && substr_count( $css, 'ts-search-contrast:start' ) === 1 && substr_count( $css, 'ts-search-contrast:end' ) === 1 && hash( 'sha256', substr( $css, 0, -strlen( $suffix ) ) ) === $baseline_sha ) {
    $state = 'applied';
}
if ( 'CHANGED' === $state ) { $fail( 'Additional CSS is CHANGED; inspect it before any mutation or recovery.' ); }
$backup = getenv( 'WWM_BACKUP_DIR' );
$root = realpath( ABSPATH );
$private = is_string( $backup ) && str_starts_with( $backup, '/' ) ? realpath( $backup ) : false;
if ( ! $root || ! $private || $private !== rtrim( $backup, '/' ) || is_link( $backup ) || ! is_dir( $private ) || ! is_writable( $private ) || $private === $root || str_starts_with( $private . '/', rtrim( $root, '/' ) . '/' ) ) {
    // A dry run must expose the same path prerequisite before a fresh export or
    // mutation. Report only these safe filesystem predicates, never credentials.
    WP_CLI::log( 'SEARCH-PATH ' . wp_json_encode( array(
        'input' => $backup,
        'realpath' => $private,
        'webroot_realpath' => $root,
        'input_is_link' => is_string( $backup ) && '' !== $backup ? is_link( $backup ) : false,
        'is_dir' => $private ? is_dir( $private ) : false,
        'is_writable' => $private ? is_writable( $private ) : false,
        'owner_uid' => $private ? fileowner( $private ) : null,
        'group_gid' => $private ? filegroup( $private ) : null,
        'mode' => $private ? sprintf( '%04o', fileperms( $private ) & 0777 ) : null,
        'process_euid' => function_exists( 'posix_geteuid' ) ? posix_geteuid() : null,
    ), JSON_UNESCAPED_SLASHES ) );
    $fail( 'WWM_BACKUP_DIR must be a real writable private directory outside the web root.' );
}
if ( 'preflight' === $mode ) { WP_CLI::success( 'search-contrast ' . $state . '; css_sha256=' . hash( 'sha256', $css ) . '; nothing changed.' ); return; }
$original = 'ready' === $state ? $css : substr( $css, 0, -strlen( $suffix ) );
$applied = $original . $suffix;
$snapshot = null;
$file = '';
$snapshot_bytes = '';
if ( in_array( $mode, array( 'rollback', 'verify' ), true ) ) {
    $file = $args[1];
    if ( ! is_string( $file ) || dirname( $file ) !== $private || ! preg_match( '/^search-contrast-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$/D', basename( $file ) ) || is_link( $file ) || ! is_file( $file ) || realpath( $file ) !== $file || ( fileperms( $file ) & 0077 ) !== 0 ) { $fail( 'Snapshot must be this update\'s private regular before-snapshot.' ); }
    $snapshot_bytes = file_get_contents( $file );
    $snapshot = is_string( $snapshot_bytes ) ? json_decode( $snapshot_bytes, true ) : null;
    if ( ! is_array( $snapshot ) || ( $snapshot['schema'] ?? null ) !== 1 || ( $snapshot['bundle'] ?? null ) !== 'search-contrast-v1' || ( $snapshot['home'] ?? null ) !== $home || ( $snapshot['manifest_sha256'] ?? null ) !== $manifest_sha || ( $snapshot['css_id'] ?? null ) !== 29 || ( $snapshot['css_name'] ?? null ) !== 'astra' || ( $snapshot['original'] ?? null ) !== $original || ( $snapshot['applied'] ?? null ) !== $applied || ( $snapshot['original_sha256'] ?? null ) !== hash( 'sha256', $original ) || ( $snapshot['applied_sha256'] ?? null ) !== hash( 'sha256', $applied ) ) { $fail( 'Snapshot ownership, hashes or exact CSS state differ.' ); }
    WP_CLI::log( 'SEARCH-SNAPSHOT ' . $file . ' ' . hash( 'sha256', $snapshot_bytes ) . ' ' . strlen( $snapshot_bytes ) );
    $expected = isset( $args[2] ) ? 'ready' : 'applied';
    if ( 'verify' === $mode ) {
        if ( $state !== $expected ) { $fail( 'Current CSS does not match the requested snapshot state.' ); }
        WP_CLI::success( 'Exact search-contrast ' . ( 'ready' === $state ? 'original' : 'applied' ) . ' CSS verified read-only; css_sha256=' . hash( 'sha256', $css ) . '. Snapshot: ' . $file );
        return;
    }
    if ( 'ready' === $state ) { WP_CLI::success( 'Exact original CSS already restored; nothing changed. Snapshot: ' . $file ); return; }
}
if ( 'apply' === $mode ) {
    if ( 'applied' === $state ) { WP_CLI::success( 'Exact search-contrast CSS already applied; nothing changed.' ); return; }
    $snapshot = array( 'schema' => 1, 'bundle' => 'search-contrast-v1', 'created' => gmdate( 'c' ), 'home' => $home, 'manifest_sha256' => $manifest_sha, 'css_id' => 29, 'css_name' => 'astra', 'original' => $original, 'original_sha256' => hash( 'sha256', $original ), 'applied' => $applied, 'applied_sha256' => hash( 'sha256', $applied ) );
    $snapshot_bytes = wp_json_encode( $snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    if ( ! is_string( $snapshot_bytes ) || json_decode( $snapshot_bytes, true ) !== $snapshot ) { $fail( 'Cannot safely encode the exact before-snapshot.' ); }
    $file = $private . '/search-contrast-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
    $mask = umask( 0077 );
    $handle = fopen( $file, 'x+b' );
    umask( $mask );
    if ( false === $handle ) { $fail( 'Could not create an exclusive before-snapshot.' ); }
    $written = fwrite( $handle, $snapshot_bytes );
    $flushed = fflush( $handle );
    fclose( $handle );
    if ( $written !== strlen( $snapshot_bytes ) || ! $flushed || ! chmod( $file, 0600 ) || file_get_contents( $file ) !== $snapshot_bytes || ( fileperms( $file ) & 0077 ) !== 0 ) { $fail( 'Could not write and verify the private before-snapshot.' ); }
    WP_CLI::log( 'SEARCH-SNAPSHOT ' . $file . ' ' . hash( 'sha256', $snapshot_bytes ) . ' ' . strlen( $snapshot_bytes ) );
}
clearstatcache( true, $file );
if ( $read() !== $css || is_link( $file ) || realpath( $file ) !== $file || ( fileperms( $file ) & 0077 ) !== 0 || file_get_contents( $file ) !== $snapshot_bytes ) { $fail( 'CSS or snapshot changed before the save.' ); }
$destination = 'apply' === $mode ? $applied : $original;
$cas = static function ( $expected, $replacement ) use ( $wpdb ) {
    return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content=%s WHERE ID=29 AND post_type='custom_css' AND post_name='astra' AND post_status='publish' AND BINARY post_content=BINARY %s", $replacement, $expected ) );
};
$affected = $cas( $css, $destination );
if ( 1 !== $affected ) { WP_CLI::error( 'CSS compare-and-swap did not update exactly one matching record; no recovery overwrite attempted. Inspect current state. Snapshot: ' . $file ); }
try {
    clean_post_cache( 29 );
    if ( $read() !== $destination ) { throw new RuntimeException( 'CSS readback differs from the exact destination.' ); }
} catch ( Throwable $error ) {
    // Recovery is another binary compare-and-swap: it can undo only the exact
    // bytes just saved here, never a concurrent or unexpected later edit.
    $restored = $cas( $destination, $css );
    if ( 1 === $restored ) {
        $recovery_verified = false;
        try {
            clean_post_cache( 29 );
            $recovery_verified = $read() === $css;
        } catch ( Throwable $restore_error ) { /* Report the unverified recovery below. */ }
        if ( $recovery_verified ) { WP_CLI::error( 'CSS readback/cache refresh failed; exact pre-operation CSS restored. Snapshot: ' . $file ); }
        WP_CLI::error( 'CSS readback/cache refresh failed; recovery compare-and-swap ran but its readback could not verify. Stop and inspect. Snapshot: ' . $file );
    }
    WP_CLI::error( 'CSS readback/cache refresh failed; recovery refused changed state or did not verify. Stop and inspect. Snapshot: ' . $file );
}
clearstatcache( true, $file );
if ( is_link( $file ) || realpath( $file ) !== $file || ( fileperms( $file ) & 0077 ) !== 0 || file_get_contents( $file ) !== $snapshot_bytes ) { WP_CLI::error( 'CSS was saved, but its before-snapshot changed during the operation. Stop and inspect. Snapshot: ' . $file ); }
WP_CLI::success( ( 'apply' === $mode ? 'Applied exact search-contrast CSS. Before-snapshot: ' : 'Restored exact original search-contrast CSS. Snapshot: ' ) . $file );
