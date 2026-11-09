<?php
/**
 * Peek template: exact legacy -> shared v1 -> shared v2 -> shared v3 -> current, or page data.
 * wp eval-file install-peek-nav.php preflight|apply [core|core-upgrade|core-summary-touch|core-title-strip|page SLUG]
 * wp eval-file install-peek-nav.php rollback FILE (component comes from snapshot).
 * Default core keeps the generic Actions rehearsal interface. WordPress storage only.
 * Exact localhost:8090 / staging39 origins; push takes verified offsite backups.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$home = untrailingslashit( home_url() );
$allowed_hosts = array( 'localhost:8090', 'staging39.thaisolitude.com' );
$host = (string) wp_parse_url( $home, PHP_URL_HOST ) . ( wp_parse_url( $home, PHP_URL_PORT ) ? ':' . wp_parse_url( $home, PHP_URL_PORT ) : '' );
if ( ! in_array( $host, $allowed_hosts, true ) ) { WP_CLI::error( 'Host is outside localhost:8090/staging39; nothing changed.' ); }
if ( ! in_array( $home, array( 'http://localhost:8090', 'http://staging39.thaisolitude.com', 'https://staging39.thaisolitude.com' ), true ) ) { WP_CLI::error( 'Only exact localhost:8090 and staging39 origins are allowed; nothing changed.' ); }
if ( 'astra' !== get_stylesheet() || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) { WP_CLI::error( 'Requires Astra, WPCode and an administrator (--user=1).' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) ) { WP_CLI::error( 'Use preflight|apply [core|core-upgrade|core-summary-touch|core-title-strip|page SLUG], or rollback FILE.' ); }
$backup_dir = (string) getenv( 'WWM_BACKUP_DIR' );
$private = realpath( $backup_dir ); $webroot = realpath( ABSPATH );
$private_ok = isset( $backup_dir[0] ) && '/' === $backup_dir[0] && $private && $webroot && is_dir( $private ) && is_writable( $private ) && $private !== $webroot && 0 !== strpos( $private . '/', rtrim( $webroot, '/' ) . '/' );
$snapshot = null; $path = null; $legacy_rollback = false;
if ( 'rollback' === $mode ) {
    $path = isset( $args[1] ) ? realpath( $args[1] ) : false;
    if ( count( $args ) !== 2 || ! $private_ok || ! $path || ! is_file( $path ) || 0 !== strpos( $path, $private . '/' ) ) { WP_CLI::error( 'Rollback requires a snapshot inside the private WWM_BACKUP_DIR.' ); }
    $snapshot = json_decode( file_get_contents( $path ), true );
    if ( ! is_array( $snapshot ) || ( $snapshot['home'] ?? '' ) !== $home ) { WP_CLI::error( 'Snapshot invalid or from another site; nothing changed.' ); }
    $legacy_rollback = ! isset( $snapshot['bundle'] ) && isset( $snapshot['code_sha256'], $snapshot['snippet_id'] );
    $component = $legacy_rollback ? 'core' : ( $snapshot['component'] ?? '' );
    $slug = $snapshot['slug'] ?? '';
} else {
    $component = $args[1] ?? 'core'; $slug = $args[2] ?? '';
    if ( count( $args ) > ( 'page' === $component ? 3 : 2 ) ) { WP_CLI::error( 'Unexpected installer arguments; nothing changed.' ); }
}
if ( ! in_array( $component, array( 'core', 'core-upgrade', 'core-summary-touch', 'core-title-strip', 'page' ), true ) || ( 'page' !== $component && '' !== $slug ) || ( 'page' === $component && ( ! is_string( $slug ) || ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug ) ) ) ) { WP_CLI::error( 'Use core, core-upgrade, core-summary-touch, core-title-strip or page SLUG; nothing changed.' ); }
$is_core = 'page' !== $component;
$is_upgrade = in_array( $component, array( 'core-upgrade', 'core-summary-touch', 'core-title-strip' ), true );
$legacy_title = 'TSPeekNav practice (Thai, staging39)';
$legacy_note = 'Created by link-ideas/install-peek-nav.php (2026-11-08); exact owned-snippet rollback only.';
$legacy_file = file_get_contents( __DIR__ . '/peek-nav-legacy.php' );
if ( false === $legacy_file || hash( 'sha256', $legacy_file ) !== '7ddb7f06f782719acebfa504b5e86ce2cd9cf8c8805fe6a6868e29734cf04fbb' ) { WP_CLI::error( 'Frozen legacy payload differs; nothing changed.' ); }
$legacy_code = substr( $legacy_file, 6 );
$shared_v1_file = file_get_contents( __DIR__ . '/peek-nav-shared-v1.php' );
if ( false === $shared_v1_file || hash( 'sha256', $shared_v1_file ) !== '5941becfb3ff70e7359ae61a7c4a2cdd7e0a5b88869e58a5c2fd790bd5824cc3' ) { WP_CLI::error( 'Frozen shared v1 payload differs; nothing changed.' ); }
$shared_v1_code = substr( $shared_v1_file, 6 );
if ( hash( 'sha256', $shared_v1_code ) !== 'dda8aecd38e417ad37766d2df951c6bdc761bf44997e030bbef5a36e9321f573' ) { WP_CLI::error( 'Frozen shared v1 code hash differs; nothing changed.' ); }
$shared_v2_file = file_get_contents( __DIR__ . '/peek-nav-shared-v2.php' );
if ( false === $shared_v2_file || hash( 'sha256', $shared_v2_file ) !== 'a270f074640714b8524a60c13971766898bb7d7c82f36c2148de1fd9ebe2c8b4' ) { WP_CLI::error( 'Frozen shared v2 payload differs; nothing changed.' ); }
$shared_v2_code = substr( $shared_v2_file, 6 );
if ( hash( 'sha256', $shared_v2_code ) !== '357e1d447ecf21ce1fa130f08f917bf7965d074463905106182fd3db9f8c39d8' ) { WP_CLI::error( 'Frozen shared v2 code hash differs; nothing changed.' ); }
$shared_v3_file = file_get_contents( __DIR__ . '/peek-nav-shared-v3.php' );
if ( false === $shared_v3_file || hash( 'sha256', $shared_v3_file ) !== '6535607a90787db678fad058ab5c6a1e105ac790b3020ab49a6fd4a8634abab4' ) { WP_CLI::error( 'Frozen shared v3 payload differs; nothing changed.' ); }
$shared_v3_code = substr( $shared_v3_file, 6 );
if ( hash( 'sha256', $shared_v3_code ) !== 'a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac' ) { WP_CLI::error( 'Frozen shared v3 code hash differs; nothing changed.' ); }
$before_code = 'core-title-strip' === $component ? $shared_v3_code : ( 'core-summary-touch' === $component ? $shared_v2_code : ( 'core-upgrade' === $component ? $shared_v1_code : $legacy_code ) );
$data_code = static function ( $page_slug, $must_be_approved = false ) {
    $file = __DIR__ . '/peek-data/' . $page_slug . '.json';
    $data = is_file( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
    if ( ! is_array( $data ) || ( $data['schema'] ?? null ) !== 1 || ( $data['slug'] ?? null ) !== $page_slug || ! in_array( $data['approval']['status'] ?? '', array( 'approved', 'draft' ), true ) || ! is_string( $data['approval']['source'] ?? null ) || '' === trim( $data['approval']['source'] ) || ! is_array( $data['items'] ?? null ) || ! is_array( $data['coverage'] ?? null ) || ! ( is_string( $data['research_anchor'] ?? null ) || ( array_key_exists( 'research_anchor', $data ) && null === $data['research_anchor'] ) ) ) { throw new \RuntimeException( 'Missing or invalid page data: ' . $page_slug ); }
    if ( $must_be_approved && 'approved' !== $data['approval']['status'] ) { throw new \RuntimeException( 'Core upgrade requires approved data: ' . $page_slug ); }
    foreach ( $data['items'] as $item ) {
        if ( ! is_array( $item ) || array_keys( $item ) !== array( 0, 1 ) || ! is_string( $item[0] ) || '' === trim( $item[0] ) || ! is_string( $item[1] ) || ! preg_match( '/^[A-Za-z][A-Za-z0-9_-]*$/D', $item[1] ) || ! in_array( $data['coverage'][ $item[1] ] ?? '', array( 'most', 'partial' ), true ) ) { throw new \RuntimeException( 'Malformed page summary item: ' . $page_slug ); }
    }
    // var_export creates a PHP string/array literal, never executable page-supplied PHP.
    return '// ts-peek-nav-data:' . $page_slug . "\nadd_filter( 'ts_pk_pages', static function ( \$pages ) {\n    if ( is_page( " . var_export( $page_slug, true ) . " ) ) {\n        \$pages[" . var_export( $page_slug, true ) . '] = ' . var_export( $data, true ) . ";\n    }\n    return \$pages;\n} );\n";
};
try {
    if ( $is_core ) {
        $file = 'core' === $component ? $shared_v1_file : ( 'core-upgrade' === $component ? $shared_v2_file : ( 'core-summary-touch' === $component ? $shared_v3_file : file_get_contents( __DIR__ . '/peek-nav.php' ) ) );
        if ( false === $file || 0 !== strpos( $file, "<?php\n" ) || substr_count( $file, 'id="ts-peek-nav-runtime"' ) !== 1 || substr_count( $file, 'id="ts-peek-nav-css"' ) !== 1 || false === strpos( $file, 'ts_pk_pages' ) ) { throw new \RuntimeException( 'Shared core payload is missing or malformed.' ); }
        $code = substr( $file, 6 ); $title = $legacy_title; $note = $legacy_note;
        if ( $is_upgrade && $code === $before_code ) { throw new \RuntimeException( 'Core upgrade must have a distinct reviewed payload.' ); }
    } else {
        $code = $data_code( $slug ); $title = 'TSPeekData ' . $slug . ' (staging39)'; $note = 'Created by link-ideas/install-peek-nav.php; owned page data v1.';
    }
    token_get_all( "<?php\n" . $code, TOKEN_PARSE );
} catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
$taxonomies = get_object_taxonomies( 'wpcode' ); sort( $taxonomies );
if ( ! in_array( 'wpcode_type', $taxonomies, true ) || ! in_array( 'wpcode_location', $taxonomies, true ) ) { WP_CLI::error( 'WPCode taxonomies unavailable.' ); }
$terms = array_fill_keys( $taxonomies, array() ); $terms['wpcode_type'] = array( 'php' ); $terms['wpcode_location'] = array( 'everywhere' );
$meta = array( '_wpcode_auto_insert' => array( '1' ), '_wpcode_priority' => array( '10' ), '_wpcode_note' => array( $note ), '_wpcode_shortcode_attributes' => array( 'a:0:{}' ) ); ksort( $meta );
$capture_post = static function ( $id ) use ( $taxonomies ) {
    $post = get_post( $id, ARRAY_A, 'raw' ); if ( ! $post ) { throw new \RuntimeException( 'Snippet no longer exists.' ); }
    $m = get_post_meta( $id ); ksort( $m ); $t = array();
    foreach ( $taxonomies as $tax ) { $v = wp_get_object_terms( $id, $tax, array( 'fields' => 'slugs' ) ); if ( is_wp_error( $v ) ) { throw new \RuntimeException( 'Snippet taxonomy unreadable.' ); } sort( $v ); $t[ $tax ] = $v; }
    return array( 'id' => (int) $id, 'post' => $post, 'meta' => $m, 'terms' => $t );
};
$find = static function ( $wanted_title, $marker ) use ( $capture_post ) {
    $found = array();
    foreach ( get_posts( array( 'post_type' => 'wpcode', 'post_status' => array_values( get_post_stati() ), 'numberposts' => -1, 'suppress_filters' => true ) ) as $p ) {
        if ( $p->post_title === $wanted_title || false !== strpos( (string) $p->post_content, $marker ) ) { $found[] = (int) $p->ID; }
    }
    if ( count( $found ) > 1 ) { throw new \RuntimeException( 'Duplicate owned snippet: ' . $wanted_title ); }
    return $found ? $capture_post( $found[0] ) : null;
};
$marker = $is_core ? 'ts-peek-nav-runtime' : '// ts-peek-nav-data:' . $slug . "\n";
$capture = static function () use ( $find, $title, $marker ) { return $find( $title, $marker ); };
$matches = static function ( $record, $payload ) use ( $title, $meta, $terms ) {
    return is_array( $record ) && is_int( $record['id'] ?? null ) && $record['id'] > 0 && (int) ( $record['post']['ID'] ?? 0 ) === $record['id'] && ( $record['post']['post_type'] ?? '' ) === 'wpcode' && ( $record['post']['post_title'] ?? '' ) === $title && ( $record['post']['post_status'] ?? '' ) === 'publish' && ( $record['post']['post_content'] ?? '' ) === $payload && ( $record['meta'] ?? null ) === $meta && ( $record['terms'] ?? null ) === $terms;
};
try { $current = $capture(); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
$capture_pages = static function () use ( $find, $data_code, $terms ) {
    $records = array();
    $page_meta = array( '_wpcode_auto_insert' => array( '1' ), '_wpcode_priority' => array( '10' ), '_wpcode_note' => array( 'Created by link-ideas/install-peek-nav.php; owned page data v1.' ), '_wpcode_shortcode_attributes' => array( 'a:0:{}' ) ); ksort( $page_meta );
    foreach ( array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ) as $page_slug ) {
        $page_title = 'TSPeekData ' . $page_slug . ' (staging39)';
        $r = $find( $page_title, '// ts-peek-nav-data:' . $page_slug . "\n" );
        if ( ! $r || $r['post']['post_type'] !== 'wpcode' || $r['post']['post_status'] !== 'publish' || $r['post']['post_title'] !== $page_title || $r['post']['post_content'] !== $data_code( $page_slug, true ) || $r['meta'] !== $page_meta || $r['terms'] !== $terms ) { throw new \RuntimeException( 'Core upgrade requires exact approved page data: ' . $page_slug ); }
        $records[ $page_slug ] = $r;
    }
    return $records;
};
$page_records = null;
$state = $matches( $current, $code ) ? 'applied' : ( ( $is_core ? $matches( $current, $before_code ) : null === $current ) ? 'ready' : 'CHANGED' );
if ( $is_upgrade ) {
    if ( ! $current || $current['id'] !== 1380 ) { WP_CLI::error( 'Core upgrade requires the uniquely owned snippet #1380; nothing changed.' ); }
    // An already-applied core can still be inspected after a separate page-only rollback.
    // Actual upgrades and reversals require all four exact page states.
    if ( 'applied' !== $state || 'rollback' === $mode ) {
        try { $page_records = $capture_pages(); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
    }
}
if ( $legacy_rollback ) {
    if ( ( $snapshot['title'] ?? '' ) !== $legacy_title || ! is_int( $snapshot['snippet_id'] ) || $snapshot['snippet_id'] < 1 || ! hash_equals( hash( 'sha256', $legacy_code ), (string) $snapshot['code_sha256'] ) ) { WP_CLI::error( 'Legacy snapshot ownership/hash invalid; nothing changed.' ); }
    if ( null === $current && ! get_post( $snapshot['snippet_id'] ) ) { WP_CLI::success( 'Legacy snippet already absent; nothing written.' ); return; }
    if ( ! $matches( $current, $legacy_code ) || $current['id'] !== $snapshot['snippet_id'] ) { WP_CLI::error( 'CHANGED legacy snippet identity, code or settings; nothing deleted.' ); }
} elseif ( 'CHANGED' === $state ) { WP_CLI::error( 'CHANGED snippet code, identity or settings; nothing written.' ); }
if ( 'preflight' === $mode || ( 'apply' === $mode && 'applied' === $state ) ) { WP_CLI::success( $component . ( $slug ? ':' . $slug : '' ) . ' ' . $state . '; nothing written.' ); return; }
if ( ! $private_ok ) { WP_CLI::error( 'WWM_BACKUP_DIR must be an existing writable directory outside the web root.' ); }
$save = static function ( $file, $snap ) {
    $json = wp_json_encode( $snap ); $mask = umask( 0077 ); $ok = false !== $json && false !== file_put_contents( $file, $json, LOCK_EX ); umask( $mask );
    if ( ! $ok || ! chmod( $file, 0600 ) || file_get_contents( $file ) !== $json ) { throw new \RuntimeException( 'Private snapshot save/read-back failed.' ); }
};
$refresh = static function () { if ( ! isset( wpcode()->cache ) || ! method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { throw new \RuntimeException( 'WPCode cache refresh unavailable.' ); } wpcode()->cache->cache_all_loaded_snippets(); };
$write = static function ( $before, $after ) use ( $capture_post ) {
    $allowed = $before; foreach ( array( 'post_content', 'post_modified', 'post_modified_gmt' ) as $key ) { $allowed['post'][ $key ] = $after['post'][ $key ]; }
    if ( $allowed !== $after || $capture_post( $before['id'] ) !== $before ) { throw new \RuntimeException( 'Snippet identity/settings changed before write.' ); }
    $dates = static function ( $data, $postarr ) use ( $after ) { if ( (int) ( $postarr['ID'] ?? 0 ) === $after['id'] && ( $data['post_type'] ?? '' ) === 'wpcode' ) { $data['post_modified'] = $after['post']['post_modified']; $data['post_modified_gmt'] = $after['post']['post_modified_gmt']; } return $data; };
    add_filter( 'wp_insert_post_data', $dates, PHP_INT_MAX, 2 );
    try { $result = wp_update_post( wp_slash( array( 'ID' => $before['id'], 'post_content' => $after['post']['post_content'] ) ), true ); }
    finally { remove_filter( 'wp_insert_post_data', $dates, PHP_INT_MAX ); }
    if ( is_wp_error( $result ) || ! $result || $capture_post( $before['id'] ) !== $after ) { throw new \RuntimeException( 'Snippet save/full-state read-back failed.' ); }
};
$delete = static function ( $record ) use ( $capture, $refresh ) {
    if ( $capture() !== $record ) { throw new \RuntimeException( 'Owned snippet changed before removal.' ); }
    if ( ! wp_delete_post( $record['id'], true ) ) { throw new \RuntimeException( 'Owned snippet removal failed.' ); }
    $refresh(); if ( null !== $capture() ) { throw new \RuntimeException( 'Removal read-back differs.' ); }
};
$assert_pages = static function () use ( $page_records, $capture_pages ) { if ( null !== $page_records && $capture_pages() !== $page_records ) { throw new \RuntimeException( 'Page-data state changed during core upgrade; no page data was written.' ); } };
$transition = static function ( $before, $after ) use ( $capture, $write, $refresh, $assert_pages ) {
    $assert_pages();
    if ( $capture() !== $before ) { throw new \RuntimeException( 'Snippet changed before transition.' ); }
    try { $write( $before, $after ); $refresh(); if ( $capture() !== $after ) { throw new \RuntimeException( 'Transition read-back differs.' ); } $assert_pages(); }
    catch ( \Throwable $e ) {
        try { $now = $capture(); if ( $now === $after ) { $write( $after, $before ); } elseif ( $now !== $before ) { throw new \RuntimeException( 'Unknown partial state; refusing recovery overwrite.' ); } $refresh(); if ( $capture() !== $before ) { throw new \RuntimeException( 'Recovery read-back differs.' ); } }
        catch ( \Throwable $r ) { throw new \RuntimeException( $e->getMessage() . '; recovery failed: ' . $r->getMessage() ); }
        throw new \RuntimeException( $e->getMessage() . '; original state restored and verified.' );
    }
};
if ( $legacy_rollback ) {
    try { $delete( $current ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() ); }
    WP_CLI::success( 'Exact owned legacy snippet removed; plain jump row remains.' ); return;
}
$manifest = $is_upgrade ? array( 'code_sha256' => hash( 'sha256', $code ), 'before_sha256' => hash( 'sha256', $before_code ) ) : array( 'code_sha256' => hash( 'sha256', $code ), 'legacy_sha256' => 'core' === $component ? hash( 'sha256', $legacy_code ) : null );
$bundle = 'core-title-strip' === $component ? 'peek-core-title-strip-v1' : ( 'core-summary-touch' === $component ? 'peek-core-summary-touch-v1' : ( 'core-upgrade' === $component ? 'peek-core-upgrade-v1' : 'peek-template-v1' ) );
if ( 'rollback' === $mode ) {
    $old = $snapshot['old'] ?? null; $new = $snapshot['new'] ?? null;
    $valid = ( $snapshot['bundle'] ?? '' ) === $bundle && true === ( $snapshot['complete'] ?? false ) && ( $snapshot['payloads'] ?? null ) === $manifest && hash( 'sha256', wp_json_encode( $old ) ) === ( $snapshot['old_sha256'] ?? '' ) && hash( 'sha256', wp_json_encode( $new ) ) === ( $snapshot['new_sha256'] ?? '' ) && $matches( $new, $code );
    if ( $is_core ) { $allowed = $old; if ( is_array( $allowed ) ) { foreach ( array( 'post_content', 'post_modified', 'post_modified_gmt' ) as $key ) { $allowed['post'][ $key ] = $new['post'][ $key ] ?? null; } } $valid = $valid && $matches( $old, $before_code ) && $allowed === $new; }
    else { $valid = $valid && null === $old; }
    if ( $is_upgrade ) { $valid = $valid && ( $snapshot['pages'] ?? null ) === $page_records && hash( 'sha256', wp_json_encode( $page_records ) ) === ( $snapshot['pages_sha256'] ?? '' ); }
    if ( ! $valid ) { WP_CLI::error( 'Snapshot damaged, incomplete or from another payload; nothing changed.' ); }
    if ( $current === $old ) { if ( null === $old && get_post( $new['id'] ) ) { WP_CLI::error( 'Snapshot ID is occupied by a renamed/changed record; nothing deleted.' ); } WP_CLI::success( 'Exact original state already restored.' ); return; }
    if ( $current !== $new ) { WP_CLI::error( 'Complete snippet state changed since apply; nothing changed.' ); }
    try { if ( $is_core ) { $transition( $new, $old ); } else { $delete( $new ); } } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Snapshot: ' . $path ); }
    WP_CLI::success( 'Exact ' . $component . ' rollback complete. Snapshot: ' . $path ); return;
}
// An inert Thai payload must precede the migration so visitors keep approved lists throughout.
if ( 'core' === $component ) {
    try {
        $thai = $find( 'TSPeekData thai-massage (staging39)', "// ts-peek-nav-data:thai-massage\n" );
        $thai_meta = $meta; $thai_meta['_wpcode_note'] = array( 'Created by link-ideas/install-peek-nav.php; owned page data v1.' );
        if ( ! $thai || $thai['post']['post_type'] !== 'wpcode' || $thai['post']['post_status'] !== 'publish' || $thai['post']['post_title'] !== 'TSPeekData thai-massage (staging39)' || $thai['post']['post_content'] !== $data_code( 'thai-massage' ) || $thai['meta'] !== $thai_meta || $thai['terms'] !== $terms ) { throw new \RuntimeException( 'Install the exact Thai page-data snippet before core migration.' ); }
    } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
}
$next = $current;
if ( $is_core ) { $next['post']['post_content'] = $code; $next['post']['post_modified'] = current_time( 'mysql' ); $next['post']['post_modified_gmt'] = current_time( 'mysql', true ); }
$file = $private . '/peek-template-' . $component . ( $slug ? '-' . $slug : '' ) . '-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
$snapshot = array( 'bundle' => $bundle, 'home' => $home, 'component' => $component, 'slug' => $slug, 'created' => gmdate( 'c' ), 'complete' => false, 'payloads' => $manifest, 'old' => $current, 'old_sha256' => hash( 'sha256', wp_json_encode( $current ) ), 'new' => $next, 'new_sha256' => hash( 'sha256', wp_json_encode( $next ) ) );
if ( $is_upgrade ) { $snapshot['pages'] = $page_records; $snapshot['pages_sha256'] = hash( 'sha256', wp_json_encode( $page_records ) ); }
try { $save( $file, $snapshot ); } catch ( \Throwable $e ) { WP_CLI::error( $e->getMessage() . ' Nothing changed.' ); }
try {
    if ( $is_core ) { $transition( $current, $next ); }
    else {
        if ( null !== $capture() ) { throw new \RuntimeException( 'Page data appeared before creation.' ); }
        $obj = new WPCode_Snippet( array( 'title' => $title, 'code' => $code, 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => true, 'note' => $note ) );
        $id = $obj->save();
        if ( is_wp_error( $id ) || ! is_numeric( $id ) || (int) $id < 1 ) { throw new \RuntimeException( 'Could not create page data.' ); }
        // Record the exact new ID before further writes. An incomplete snapshot never authorizes removal.
        $snapshot['created_id'] = (int) $id; $save( $file, $snapshot );
        $result = wp_update_post( wp_slash( array( 'ID' => (int) $id, 'post_content' => $code ) ), true );
        $next = $capture();
        if ( is_wp_error( $result ) || ! $result || ! $matches( $next, $code ) || $next['id'] !== (int) $id ) { throw new \RuntimeException( 'Page-data creation/read-back differs; inspect the recorded ID.' ); }
        $refresh(); if ( $capture() !== $next ) { throw new \RuntimeException( 'Page-data refresh read-back differs.' ); }
        $snapshot['new'] = $next; $snapshot['new_sha256'] = hash( 'sha256', wp_json_encode( $next ) );
    }
    $snapshot['complete'] = true; $save( $file, $snapshot );
} catch ( \Throwable $e ) {
    // Recover only a fully recognized state: never delete a concurrently edited record.
    try {
        $now = $capture();
        if ( $is_core && $now === $next && $now !== $current ) { $transition( $next, $current ); }
        elseif ( 'page' === $component && isset( $id ) && $matches( $now, $code ) && $now['id'] === (int) $id ) { $delete( $now ); }
        elseif ( $now !== $current ) { throw new \RuntimeException( 'Unknown partial state; inspect before recovery.' ); }
    } catch ( \Throwable $r ) { WP_CLI::error( $e->getMessage() . '; recovery failed: ' . $r->getMessage() . '. Snapshot: ' . $file ); }
    WP_CLI::error( $e->getMessage() . '; original state restored. Snapshot: ' . $file );
}
WP_CLI::success( 'Peek ' . $component . ( $slug ? ':' . $slug : '' ) . ' applied; WordPress storage only. Before-snapshot: ' . $file );
