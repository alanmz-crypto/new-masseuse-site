<?php
/**
 * Visitor release: exact copied or explicitly selected raw-live profile only.
 * wp eval-file install-peek-release.php preflight | apply [FILE] |
 *     verify FILE [original] | recovery-check FILE [original] | rollback FILE
 *     verify-incident FILE [restored] | recovery-check-incident FILE [restored] | rollback-incident FILE
 * No page content, SEO or dependency edit. Live requires TS_PEEK_RELEASE_PROFILE=raw-live-v1.
 * Completed private snapshots are immutable, including rollback and repeats.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$release_home = untrailingslashit( home_url() );
$release_local_host = 'localhost:8090';
$release_profile_request = (string) getenv( 'TS_PEEK_RELEASE_PROFILE' );
$release_live = $release_home === 'http://thaisolitude.com' && $release_profile_request === 'raw-live-v1';
if ( ! $release_live && ( $release_home !== 'http://' . $release_local_host || $release_profile_request !== '' ) ) { WP_CLI::error( 'Exact origin and explicit profile selection are required; nothing changed.' ); }
if ( get_stylesheet() !== 'astra' || ! current_user_can( 'manage_options' ) || ! current_user_can( 'unfiltered_html' ) || ! function_exists( 'wpcode' ) || ! class_exists( 'WPCode_Snippet' ) ) { WP_CLI::error( 'Requires Astra, WPCode and an administrator.' ); }
global $wpdb, $wp_version;
$args = isset( $args ) && is_array( $args ) ? $args : array();
$release_mode = $args[0] ?? 'preflight';
$release_incident = in_array( $release_mode, array( 'verify-incident', 'recovery-check-incident', 'rollback-incident' ), true );
if ( $release_incident ) {
    if ( ! $release_live ) { WP_CLI::error( 'LIVE30 incident modes require the exact raw-live profile.' ); }
    $release_mode = substr( $release_mode, 0, -strlen( '-incident' ) );
}
$release_original = in_array( $release_mode, array( 'verify', 'recovery-check' ), true ) && ( $args[2] ?? null ) === ( $release_incident ? 'restored' : 'original' );
if ( ! in_array( $release_mode, array( 'preflight', 'apply', 'rollback', 'verify', 'recovery-check' ), true ) || count( $args ) > ( $release_original ? 3 : ( $release_mode === 'preflight' ? 1 : 2 ) ) || ( in_array( $release_mode, array( 'verify', 'rollback', 'recovery-check' ), true ) && count( $args ) < 2 ) ) { WP_CLI::error( 'Use preflight, apply [FILE], verify FILE [original], recovery-check FILE [original], or rollback FILE.' ); }
$release_fail = static function ( $message ) { throw new RuntimeException( $message ); };
$release_canonical = static function ( $value ) use ( &$release_canonical ) {
    if ( is_array( $value ) ) {
        if ( ! array_is_list( $value ) ) { ksort( $value, SORT_STRING ); }
        foreach ( $value as &$item ) { $item = $release_canonical( $item ); } unset( $item );
    }
    return $value;
};
$release_json = static fn( $value ) => json_encode( $release_canonical( $value ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_THROW_ON_ERROR );
$release_hash = static fn( $value ) => hash( 'sha256', $release_json( $value ) );
$release_equal = static function ( $actual, $expected, $label ) use ( $release_hash, $release_fail, $release_canonical, $release_json ) {
    $a = $release_canonical( $actual ); $e = $release_canonical( $expected );
    if ( $a === $e ) { return; }
    // Failure evidence only: use these captured values, never recapture or adopt.
    try {
    $safe = explode( ' ', 'records options taxonomies terms witnesses owned pages shared_termmeta_sha256 post meta relationships posts postmeta term_relationships term_taxonomy termmeta core thai-massage therapeutic-massage deep-tissue-massage chair-massage home siteurl template stylesheet show_on_front page_on_front page_for_posts permalink_structure astra-settings __uagb_asset_version ID post_author post_date post_date_gmt post_content post_title post_excerpt post_status comment_status ping_status post_password post_name to_ping pinged post_modified post_modified_gmt post_content_filtered post_parent guid menu_order post_type post_mime_type comment_count meta_id post_id meta_key meta_value object_id term_taxonomy_id term_order term_id taxonomy description parent count name slug term_group option_id option_name option_value autoload id path title' );
    $describe = static function ( $value, $present ) use ( $release_json ) {
        if ( ! $present ) { return array( 'present' => false ); }
        $type = get_debug_type( $value ); $bytes = $release_json( $value );
        $out = array( 'present' => true, 'type' => $type, 'bytes' => strlen( is_string( $value ) ? $value : $bytes ), 'sha256' => hash( 'sha256', $type . ':' . $bytes ) );
        if ( is_array( $value ) ) { $out['count'] = count( $value ); $out['shape'] = array_is_list( $value ) ? 'list' : 'map'; }
        return $out;
    };
    $changes = array(); $truncated = false; $budget = 2048; $halt = false;
    $walk = static function ( $av, $ev, $path, $ap = true, $ep = true ) use ( &$walk, &$changes, &$truncated, &$budget, &$halt, $safe, $describe ) {
        if ( $halt || ( $ap === $ep && $av === $ev ) ) { return; }
        if ( count( $changes ) >= 32 ) { $truncated = true; $halt = true; return; }
        $kind = ! $ap ? 'missing' : ( ! $ep ? 'added' : ( get_debug_type( $av ) !== get_debug_type( $ev ) ? 'type' : 'value' ) );
        if ( $ap && $ep && is_array( $av ) && is_array( $ev ) ) {
            if ( array_is_list( $av ) !== array_is_list( $ev ) ) { $kind = 'shape'; }
            elseif ( count( $path ) >= 8 ) { $kind = 'bounded'; $truncated = true; }
            else {
                foreach ( array_unique( array_merge( array_keys( $ev ), array_keys( $av ) ), SORT_STRING ) as $key ) {
                    if ( --$budget < 0 ) { $kind = 'bounded'; $truncated = true; $halt = true; break; }
                    $token = (string) $key;
                    $id_map = $path === array( 'records' ) || ( count( $path ) === 2 && $path[0] === 'witnesses' && in_array( $path[1], array( 'posts', 'postmeta', 'term_relationships', 'term_taxonomy', 'terms', 'termmeta' ), true ) );
                    $numeric = ( $id_map || array_is_list( $av ) ) && preg_match( '/^[0-9]{1,20}(?::[0-9]{1,20})?$/D', $token );
                    if ( ! in_array( $token, $safe, true ) && ! $numeric ) { $token = 'sha256:' . hash( 'sha256', $token ); }
                    $walk( $av[$key] ?? null, $ev[$key] ?? null, array_merge( $path, array( $token ) ), array_key_exists( $key, $av ), array_key_exists( $key, $ev ) );
                    if ( $halt ) { return; }
                }
                if ( $kind !== 'bounded' ) { return; }
            }
        }
        if ( count( $changes ) >= 32 ) { $truncated = true; $halt = true; return; }
        $changes[] = array( 'path' => $path, 'kind' => $kind, 'expected' => $describe( $ev, $ep ), 'actual' => $describe( $av, $ap ) );
    };
    $walk( $a, $e, array() );
    WP_CLI::log( 'RELEASE-STATE-DIAGNOSTIC ' . json_encode( array( 'label' => $label, 'changes' => $changes, 'truncated' => $truncated ), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
    } catch ( Throwable $release_diagnostic_error ) { /* Evidence failure must not replace the guard error. */ }
    $release_fail( $label . ' differs (expected ' . $release_hash( $expected ) . ', actual ' . $release_hash( $actual ) . ').' );
};
try {
    $release_manifest_bytes = file_get_contents( __DIR__ . '/manifest.json' );
    $release_manifest = json_decode( $release_manifest_bytes, true, 512, JSON_THROW_ON_ERROR );
    $release_manifest_hash = hash( 'sha256', $release_manifest_bytes );
    if ( ( $release_manifest['schema'] ?? null ) !== 1 || ( $release_manifest['bundle'] ?? null ) !== 'peek-visitor-release-v1' || ( $release_manifest['copy_mapping']['origin'] ?? null ) !== 'http://localhost:8090' || ( $release_manifest['source']['core_version'] ?? null ) !== $wp_version ) { $release_fail( 'Manifest/site/core version differs.' ); }
    $release_profile_hash = null;
    if ( $release_live ) {
        $release_profile_bytes = file_get_contents( __DIR__ . '/live-profile.json' );
        $release_profile = json_decode( $release_profile_bytes, true, 512, JSON_THROW_ON_ERROR );
        if ( ( $release_profile['schema'] ?? null ) !== 1 || ( $release_profile['bundle'] ?? null ) !== $release_manifest['bundle'] || ( $release_profile['origin'] ?? null ) !== $release_home ) { $release_fail( 'Raw-live profile identity differs.' ); }
        $release_equal( $release_profile['source'] ?? null, $release_manifest['source'], 'Raw-live baseline provenance' );
        $release_equal( array_keys( $release_profile['baseline'] ?? array() ), array_keys( $release_manifest['baseline'] ), 'Raw-live baseline fields' );
        $release_manifest['baseline'] = $release_profile['baseline'];
        $release_profile_hash = hash( 'sha256', $release_profile_bytes );
    }
    // Preserve the existing local rehearsal contract exactly. The additional
    // profile binding belongs only to the explicit raw-live path.
    $release_marker_fields = static fn( $fields ) => $release_profile_hash === null ? $fields : array_merge( $fields, array( 'profile_sha256' => $release_profile_hash ) );
    require __DIR__ . '/source-binding.php';
    $release_slugs = array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' );
    $release_equal( $release_manifest['summary_slugs'], $release_slugs, 'Approved page roles' );
    $release_roles = array_merge( array( 'core' ), $release_slugs );
    $release_order = array_merge( $release_slugs, array( 'core' ) );
    $release_payload = static function ( $spec, $expected_path ) use ( $release_fail ) {
        if ( ( $spec['file'] ?? null ) !== $expected_path ) { $release_fail( 'Unexpected payload path.' ); }
        $bytes = file_get_contents( __DIR__ . '/' . $expected_path );
        if ( ! is_string( $bytes ) || hash( 'sha256', $bytes ) !== ( $spec['file_sha256'] ?? null ) ) { $release_fail( 'Frozen payload differs: ' . $expected_path ); }
        return $bytes;
    };
    $release_core = $release_payload( $release_manifest['payloads']['core'], 'payload/core.php' );
    if ( ! str_starts_with( $release_core, "<?php\n" ) || substr_count( $release_core, 'id="ts-peek-nav-runtime"' ) !== 1 || substr_count( $release_core, 'id="ts-peek-nav-css"' ) !== 1 ) { $release_fail( 'Malformed core payload.' ); }
    $release_codes = array( 'core' => substr( $release_core, 6 ) );
    foreach ( $release_slugs as $release_slug ) {
        $release_data = json_decode( $release_payload( $release_manifest['payloads']['data'][$release_slug], 'payload/data/' . $release_slug . '.json' ), true, 512, JSON_THROW_ON_ERROR );
        if ( ( $release_data['schema'] ?? null ) !== 1 || ( $release_data['slug'] ?? null ) !== $release_slug || ( $release_data['approval']['status'] ?? null ) !== 'approved' ) { $release_fail( 'Approved page payload differs.' ); }
        $release_codes[$release_slug] = '// ts-peek-nav-data:' . $release_slug . "\nadd_filter( 'ts_pk_pages', static function ( \$pages ) {\n    if ( is_page( " . var_export( $release_slug, true ) . " ) ) {\n        \$pages[" . var_export( $release_slug, true ) . '] = ' . var_export( $release_data, true ) . ";\n    }\n    return \$pages;\n} );\n";
    }
    foreach ( $release_codes as $release_role => $release_code ) {
        $release_spec = $release_role === 'core' ? $release_manifest['payloads']['core'] : $release_manifest['payloads']['data'][$release_role];
        if ( hash( 'sha256', $release_code ) !== $release_spec['code_sha256'] ) { $release_fail( 'Generated code differs: ' . $release_role ); }
        token_get_all( "<?php\n" . $release_code, TOKEN_PARSE );
    }
    $release_progress = $release_payload( $release_manifest['payloads']['reading_progress'], 'payload/reading-progress.css' );
    $release_search = $release_payload( $release_manifest['payloads']['search_contrast'], 'payload/search-contrast.css' );
    if ( ! str_starts_with( $release_search, '/* ts-search-contrast:start */' ) || ! str_ends_with( $release_search, "/* ts-search-contrast:end */\n" ) || substr_count( $release_search, 'ts-search-contrast:start' ) !== 1 || substr_count( $release_search, 'ts-search-contrast:end' ) !== 1 ) { $release_fail( 'Malformed Search contrast payload.' ); }
    // Same exact append contract as staging: retain every preceding byte,
    // remove only the frozen obsolete progress block, then one LF and Search.
    $release_css_result = static fn( $css ) => str_replace( $release_progress, '', $css ) . "\n" . $release_search;
    $release_titles = $release_notes = array();
    foreach ( $release_roles as $release_role ) {
        $release_titles[$release_role] = $release_role === 'core' ? 'TSPeekNav visitor release v1' : 'TSPeekData ' . $release_role . ' visitor release v1';
        $release_notes[$release_role] = 'Created by link-ideas/peek-release/install-peek-release.php; peek-visitor-release-v1 ' . $release_role . '.';
    }
    $release_taxonomies = get_object_taxonomies( 'wpcode' );
    if ( ! in_array( 'wpcode_type', $release_taxonomies, true ) || ! in_array( 'wpcode_location', $release_taxonomies, true ) ) { $release_fail( 'WPCode taxonomies unavailable.' ); }
} catch ( Throwable $release_error ) { WP_CLI::error( $release_error->getMessage() . ' Nothing changed.' ); }

// Spectra's reviewed save_post callback invalidates every page's assets even
// for a WPCode save. Its own exclusion filter returns before those writes.
// Preserve all existing filters and exclusions; only our three owned save
// call sites get this temporal post-type exclusion (the API has no post ID).
$release_wpcode_save = static function ( callable $save ) use ( $release_fail ) {
    $skip = static function ( $types ) use ( $release_fail ) {
        if ( ! is_array( $types ) ) { $release_fail( 'Spectra exclusion filter returned a non-array.' ); }
        if ( ! in_array( 'wpcode', $types, true ) ) { $types[] = 'wpcode'; }
        return $types;
    };
    add_filter( 'uagb_delete_page_assets_skip_post_types', $skip, PHP_INT_MAX, 1 );
    try { return $save(); }
    finally { remove_filter( 'uagb_delete_page_assets_skip_post_types', $skip, PHP_INT_MAX ); }
};

// SQL reads deliberately bypass WordPress object/query caches and virtual post fields.
// Raw unknown metadata is hashed in memory, never copied into the private snapshot.
$release_rows = static function ( $sql ) use ( $wpdb, $release_fail ) {
    $rows = $wpdb->get_results( $sql, ARRAY_A );
    if ( ! is_array( $rows ) || $wpdb->last_error ) { $release_fail( 'Raw database capture failed.' ); }
    foreach ( $rows as &$row ) { foreach ( $row as &$cell ) { if ( $cell !== null ) { $cell = (string) $cell; } } unset( $cell ); ksort( $row, SORT_STRING ); } unset( $row );
    return $rows;
};
$release_capture = static function ( $bound_ids = array() ) use ( $wpdb, $release_rows, $release_roles, $release_titles, $release_notes, $release_manifest, $release_hash, $release_fail ) {
    $tables = array();
    foreach ( array( 'posts' => 'ID', 'postmeta' => 'meta_id', 'term_relationships' => 'object_id,term_taxonomy_id', 'term_taxonomy' => 'term_taxonomy_id', 'terms' => 'term_id', 'termmeta' => 'meta_id' ) as $name => $order ) { $tables[$name] = $release_rows( 'SELECT * FROM ' . $wpdb->$name . ' ORDER BY ' . $order ); }
    $posts = $meta = $relationships = array();
    foreach ( $tables['posts'] as $row ) { $posts[(int) $row['ID']] = $row; }
    foreach ( $tables['postmeta'] as $row ) { $meta[(int) $row['post_id']][] = $row; }
    foreach ( $tables['term_relationships'] as $row ) { $relationships[(int) $row['object_id']][] = $row; }
    $record = static fn( $id ) => isset( $posts[$id] ) ? array( 'post' => $posts[$id], 'meta' => $meta[$id] ?? array(), 'relationships' => $relationships[$id] ?? array() ) : null;
    $owned = array_fill_keys( $release_roles, null ); $ids = array();
    foreach ( $posts as $id => $post ) {
        $candidate = str_starts_with( $post['post_title'], 'TSPeek' ) || str_contains( $post['post_content'], 'id="ts-peek-nav-runtime"' ) || str_contains( $post['post_content'], '// ts-peek-nav-data:' );
        foreach ( $meta[$id] ?? array() as $row ) { if ( $row['meta_key'] === '_wpcode_note' && str_contains( (string) $row['meta_value'], 'peek-visitor-release-v1' ) ) { $candidate = true; } }
        if ( ! $candidate ) { continue; }
        $role = array_search( $post['post_title'], $release_titles, true );
        if ( $role === false || $owned[$role] !== null || $post['post_type'] !== 'wpcode' ) { $release_fail( 'Foreign, duplicate or wrong-type Peek ownership at ID ' . $id . '.' ); }
        if ( isset( $bound_ids[$role] ) && $bound_ids[$role] !== $id ) { $release_fail( 'Owned role moved to another ID.' ); }
        $owned[$role] = $record( $id ); $ids[$role] = $id;
    }
    foreach ( $bound_ids as $role => $id ) {
        if ( $owned[$role] === null && ( isset( $posts[$id] ) || ! empty( $meta[$id] ) || ! empty( $relationships[$id] ) ) ) { $release_fail( 'Owned ID replaced or left orphaned rows.' ); }
    }
    $exclude = array_values( array_replace( $ids, $bound_ids ) );
    $out = array( 'records' => array(), 'options' => array(), 'taxonomies' => array(), 'terms' => array(), 'witnesses' => array(), 'owned' => $owned, 'pages' => array(), 'shared_termmeta_sha256' => $release_hash( array_values( array_filter( $tables['termmeta'], static fn( $row ) => in_array( $row['term_id'], array( '9', '10' ), true ) ) ) ) );
    $fixed = array( 29, 1091, 1107, 1160, 1232, 1293, 1338 ); $full = array( 29, 1232, 1338 );
    foreach ( $fixed as $id ) {
        if ( ! isset( $posts[$id] ) || $posts[$id]['post_password'] !== '' ) { $release_fail( 'Required unprotected source post is missing or password protected.' ); }
        $out['records'][$id] = in_array( $id, $full, true ) ? $record( $id ) : array( 'post' => $posts[$id] );
    }
    foreach ( array( 'posts', 'postmeta', 'term_relationships', 'term_taxonomy', 'terms', 'termmeta' ) as $name ) {
        $witness = array();
        foreach ( $tables[$name] as $row ) {
            $id = (int) ( $row['ID'] ?? $row['post_id'] ?? $row['object_id'] ?? 0 );
            if ( $id && in_array( $id, $exclude, true ) ) { continue; }
            if ( $name === 'posts' && in_array( $id, $fixed, true ) ) { continue; }
            if ( in_array( $name, array( 'postmeta', 'term_relationships' ), true ) && in_array( $id, $full, true ) ) { continue; }
            if ( $name === 'term_taxonomy' && in_array( $row['term_taxonomy_id'], array( '9', '10' ), true ) ) { $out['taxonomies'][] = $row; continue; }
            if ( $name === 'terms' && in_array( $row['term_id'], array( '9', '10' ), true ) ) { $out['terms'][] = $row; continue; }
            $key = $row['ID'] ?? $row['meta_id'] ?? ( isset( $row['object_id'] ) ? $row['object_id'] . ':' . $row['term_taxonomy_id'] : ( $row['term_taxonomy_id'] ?? $row['term_id'] ) );
            $witness[(string) $key] = $release_hash( $row );
        }
        $out['witnesses'][$name] = $witness;
    }
    // The current Spectra version token is dynamic, but its entire raw row
    // must remain exactly as first captured through apply, undo and page GETs.
    foreach ( array_merge( array_keys( $release_manifest['baseline']['option_sha256'] ), array( '__uagb_asset_version' ) ) as $name ) {
        $rows = $release_rows( $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name=%s ORDER BY option_id", $name ) );
        if ( count( $rows ) !== 1 ) { $release_fail( 'Required option missing or duplicated: ' . $name ); }
        $out['options'][$name] = $rows[0];
    }
    foreach ( $posts as $id => $post ) {
        if ( $post['post_type'] !== 'page' || $post['post_status'] !== 'publish' ) { continue; }
        $parts = array( $post['post_name'] ); $parent = (int) $post['post_parent']; $seen = array( $id );
        while ( $parent ) {
            if ( ! isset( $posts[$parent] ) || in_array( $parent, $seen, true ) ) { $release_fail( 'Invalid page ancestry.' ); }
            $seen[] = $parent; array_unshift( $parts, $posts[$parent]['post_name'] ); $parent = (int) $posts[$parent]['post_parent'];
        }
        $out['pages'][] = array( 'id' => $id, 'path' => $id === (int) $out['options']['page_on_front']['option_value'] ? '/' : '/' . implode( '/', $parts ) . '/', 'title' => $post['post_title'] );
    }
    return $out;
};
$release_baseline = static function ( $state ) use ( $release_manifest, $release_hash, $release_equal, $release_fail, $release_roles, $release_progress, $release_css_result ) {
    $b = $release_manifest['baseline'];
    $release_equal( $state['pages'], $release_manifest['pages'], 'Published visitor inventory' );
    foreach ( $b['post_sha256'] as $id => $hash ) { if ( $release_hash( $state['records'][$id]['post'] ?? null ) !== $hash ) { $release_fail( 'Reviewed source post differs: ' . $id ); } }
    foreach ( array( 'meta' => 'meta_sha256', 'relationships' => 'relationship_sha256' ) as $field => $hashes ) { foreach ( $b[$hashes] as $id => $hash ) { if ( $release_hash( $state['records'][$id][$field] ?? null ) !== $hash ) { $release_fail( 'Reviewed source ' . $field . ' differs: ' . $id ); } } }
    foreach ( $b['option_sha256'] as $name => $hash ) { if ( $release_hash( $state['options'][$name] ?? null ) !== $hash ) { $release_fail( 'Reviewed option differs: ' . $name ); } }
    $asset = $state['options']['__uagb_asset_version'] ?? null;
    if ( ! is_array( $asset ) || array_keys( $asset ) !== array( 'autoload', 'option_id', 'option_name', 'option_value' ) || $asset['option_name'] !== '__uagb_asset_version' || ! ctype_digit( $asset['option_id'] ) || ! is_string( $asset['option_value'] ) || ! is_string( $asset['autoload'] ) ) { $release_fail( 'Required exact Spectra version option row is missing or malformed.' ); }
    $release_equal( $state['taxonomies'], $b['taxonomies'], 'Shared taxonomy baseline' );
    $release_equal( $state['terms'], $b['terms'], 'Shared term baseline' );
    if ( $state['shared_termmeta_sha256'] !== $b['termmeta_sha256'] ) { $release_fail( 'Reviewed shared term metadata differs.' ); }
    foreach ( $release_roles as $role ) { if ( $state['owned'][$role] !== null ) { $release_fail( 'Peek already exists; use a recorded complete snapshot to verify/repeat.' ); } }
    $css = $state['records'][29]['post']['post_content'];
    if ( substr_count( $css, $release_progress ) !== 1 || str_contains( $css, 'ts-search-contrast:' ) || hash( 'sha256', $css ) !== $b['css']['old_content_sha256'] || hash( 'sha256', $release_css_result( $css ) ) !== $b['css']['new_content_sha256'] ) { $release_fail( 'Exact progress/Search CSS transition differs.' ); }
};
$release_astra = static function ( $old ) use ( $release_fail, $release_hash, $release_manifest ) {
    $before = unserialize( $old['option_value'], array( 'allowed_classes' => false ) );
    if ( ! is_array( $before ) || ( $before['scroll-to-top-enable'] ?? null ) !== true ) { $release_fail( 'Astra top-level boolean differs.' ); }
    $needle = serialize( 'scroll-to-top-enable' ) . 'b:1;';
    if ( substr_count( $old['option_value'], $needle ) !== 1 ) { $release_fail( 'Astra boolean token is ambiguous.' ); }
    $new = $old; $new['option_value'] = str_replace( $needle, serialize( 'scroll-to-top-enable' ) . 'b:0;', $old['option_value'] ); $before['scroll-to-top-enable'] = false;
    if ( unserialize( $new['option_value'], array( 'allowed_classes' => false ) ) !== $before || $release_hash( $new ) !== $release_manifest['baseline']['astra_new_row_sha256'] ) { $release_fail( 'Astra change is not the reviewed single boolean.' ); }
    return $new;
};
// WordPress 7.1.4 stores a raw permalink at insertion, then the exact numeric
// entity form through get_post_field('guid')'s display filter on a post update.
$release_guid = static fn( $id, $saved ) => $release_home . '/?post_type=wpcode' . ( $saved ? '&#038;p=' : '&p=' ) . $id;
$release_owned_ok = static function ( $record, $role, $status, $code, $initial_factory = false, $saved = false ) use ( $release_titles, $release_notes, $release_guid, $release_fail ) {
    $p = $record['post'] ?? array(); $id = (int) ( $p['ID'] ?? 0 );
    if ( $id < 1 || ( $p['post_type'] ?? null ) !== 'wpcode' || ( $p['post_title'] ?? null ) !== $release_titles[$role] || ( $p['post_status'] ?? null ) !== $status || ( $p['post_content'] ?? null ) !== $code || ( $p['post_password'] ?? null ) !== '' || ( $p['post_parent'] ?? null ) !== '0' || ( $p['guid'] ?? null ) !== $release_guid( $id, $saved ) ) { $release_fail( 'Owned snippet identity/code differs: ' . $role ); }
    $expected = array( '_wpcode_auto_insert' => '1', '_wpcode_priority' => '10', '_wpcode_note' => $release_notes[$role], '_wpcode_shortcode_attributes' => 'a:0:{}' ); ksort( $expected );
    $meta_expected = $expected;
    if ( $initial_factory ) {
        if ( $status !== 'draft' || $saved ) { $release_fail( 'Initial factory profile is only valid for an inactive draft.' ); }
        unset( $meta_expected['_wpcode_shortcode_attributes'] );
    }
    $diagnose = static function ( $reasons ) use ( $record, $role, $expected, $meta_expected ) {
        $describe = static fn( $value ) => array( 'type' => $value === null ? 'null' : 'string', 'bytes' => $value === null ? null : strlen( $value ), 'sha256' => $value === null ? null : hash( 'sha256', $value ) );
        $groups = array(); foreach ( $record['meta'] as $row ) { $groups[(string) $row['meta_key']][] = $describe( $row['meta_value'] ); }
        $known = $unknown = array();
        foreach ( $expected as $key => $value ) { $known[] = array( 'key' => $key, 'expected_count' => array_key_exists( $key, $meta_expected ) ? 1 : 0, 'actual_count' => count( $groups[$key] ?? array() ), 'expected' => array_key_exists( $key, $meta_expected ) ? $describe( $value ) : null, 'actual' => $groups[$key] ?? array() ); unset( $groups[$key] ); }
        foreach ( $groups as $key => $values ) { $unknown[] = array( 'key_sha256' => hash( 'sha256', (string) $key ), 'count' => count( $values ), 'values' => $values ); }
        $relationships = array();
        foreach ( $record['relationships'] as $row ) {
            $safe = array();
            foreach ( array( 'object_id', 'term_taxonomy_id', 'term_order' ) as $key ) {
                $value = $row[$key] ?? null;
                $safe[$key] = is_string( $value ) && preg_match( '/^-?[0-9]+$/D', $value ) ? $value : $describe( $value );
            }
            $relationships[] = $safe;
        }
        WP_CLI::log( 'RELEASE-OWNED-DIAGNOSTIC ' . json_encode( array( 'role' => $role, 'reasons' => $reasons, 'known_meta' => $known, 'unknown_meta' => $unknown, 'expected_taxonomy_ids' => array( 9, 10 ), 'actual_relationship_count' => count( $relationships ), 'relationships' => $relationships ), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
    };
    $meta = array();
    foreach ( $record['meta'] as $row ) {
        if ( $row['post_id'] !== (string) $id || array_key_exists( $row['meta_key'], $meta ) ) { $diagnose( array( 'metadata-identity-or-duplicate' ) ); $release_fail( 'Owned metadata duplicate/identity differs.' ); }
        $meta[$row['meta_key']] = $row['meta_value'];
    }
    ksort( $meta );
    if ( $meta !== $meta_expected || count( $record['relationships'] ) !== 2 ) {
        $reasons = array(); if ( $meta !== $meta_expected ) { $reasons[] = 'metadata'; } if ( count( $record['relationships'] ) !== 2 ) { $reasons[] = 'relationship-count'; }
        $diagnose( $reasons ); $release_fail( 'Owned metadata/relationships differ: ' . $role );
    }
    foreach ( $record['relationships'] as $i => $row ) { if ( $row !== array( 'object_id' => (string) $id, 'term_order' => '0', 'term_taxonomy_id' => (string) ( 9 + $i ) ) ) { $diagnose( array( 'relationship-row' ) ); $release_fail( 'Owned relationship differs.' ); } }
};
$release_expected = static function ( $old, $owned, $cleaned ) use ( $release_astra, $release_css_result ) {
    // Copy4 proved both shared counts include our newly related inactive draft.
    $out = $old; $out['owned'] = $owned; $members = 0;
    foreach ( $owned as $record ) { if ( $record !== null ) { $members++; } }
    foreach ( $out['taxonomies'] as &$row ) { $row['count'] = (string) ( (int) $row['count'] + $members ); } unset( $row );
    if ( $cleaned['css'] ) { $out['records'][29]['post']['post_content'] = $release_css_result( $out['records'][29]['post']['post_content'] ); }
    if ( $cleaned['astra'] ) { $out['options']['astra-settings'] = $release_astra( $old['options']['astra-settings'] ); }
    return $out;
};
$release_private_input = (string) getenv( 'WWM_BACKUP_DIR' );
$release_private = realpath( $release_private_input ); $release_webroot = realpath( ABSPATH );
$release_private_ok = ! is_link( $release_private_input ) && $release_private_input === $release_private && str_starts_with( $release_private_input, '/' ) && $release_private && $release_webroot && is_dir( $release_private ) && is_writable( $release_private ) && $release_private !== $release_webroot && ! str_starts_with( $release_private . '/', rtrim( $release_webroot, '/' ) . '/' );
$release_file_unchanged = static function ( $path, $bytes ) use ( $release_fail ) {
    clearstatcache( true, $path );
    if ( is_link( $path ) || ! is_file( $path ) || ( fileperms( $path ) & 0777 ) !== 0600 || file_get_contents( $path ) !== $bytes ) { $release_fail( 'Private snapshot/journal changed; no further writes or success marker.' ); }
};
$release_saved_bytes = array();
$release_save = static function ( $path, $snapshot ) use ( $release_json, $release_fail, &$release_saved_bytes ) {
    $owned = array_key_exists( $path, $release_saved_bytes );
    $unchanged = static function () use ( $owned, $path, &$release_saved_bytes ) {
        clearstatcache( true, $path );
        return $owned ? ( ! is_link( $path ) && is_file( $path ) && ( fileperms( $path ) & 0777 ) === 0600 && file_get_contents( $path ) === $release_saved_bytes[$path] ) : ( ! file_exists( $path ) && ! is_link( $path ) );
    };
    if ( ! $unchanged() ) { $release_fail( 'Snapshot path already exists or owned journal bytes/permissions changed; refuse replacement.' ); }
    $bytes = $release_json( $snapshot ); $tmp = $path . '.tmp-' . bin2hex( random_bytes( 4 ) ); $handle = fopen( $tmp, 'x' );
    if ( ! $handle ) { $release_fail( 'Snapshot creation failed.' ); }
    try { if ( ! chmod( $tmp, 0600 ) || fwrite( $handle, $bytes ) !== strlen( $bytes ) || ! fflush( $handle ) ) { $release_fail( 'Snapshot write failed.' ); } }
    finally { fclose( $handle ); }
    if ( file_get_contents( $tmp ) !== $bytes || ! $unchanged() ) { unlink( $tmp ); $release_fail( 'Snapshot journal changed before replacement.' ); }
    // link is an atomic no-overwrite first publication. Later replacements are
    // allowed only for this invocation's exact verified private journal bytes.
    $published = $owned ? rename( $tmp, $path ) : link( $tmp, $path );
    if ( ! $owned && is_file( $tmp ) ) { unlink( $tmp ); }
    clearstatcache( true, $path );
    if ( ! $published || is_link( $path ) || file_get_contents( $path ) !== $bytes || ( fileperms( $path ) & 0777 ) !== 0600 ) { $release_fail( 'Snapshot save/read-back failed.' ); }
    $release_saved_bytes[$path] = $bytes;
};
$release_refresh = static function () use ( $release_fail ) {
    if ( ! isset( wpcode()->cache ) || ! method_exists( wpcode()->cache, 'cache_all_loaded_snippets' ) ) { $release_fail( 'WPCode cache refresh unavailable.' ); }
    wpcode()->cache->cache_all_loaded_snippets();
};
$release_css_set = static function ( $from, $to ) use ( $wpdb, $release_fail ) {
    try { $rows = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content=%s WHERE ID=%d AND BINARY post_content=BINARY %s", $to, 29, $from ) ); }
    finally { wp_cache_delete( 29, 'posts' ); wp_cache_delete( 29, 'post_meta' ); wp_cache_set_posts_last_changed(); }
    if ( $rows !== 1 ) { $release_fail( 'CSS compare-and-swap did not update exactly one row.' ); }
};
$release_astra_set = static function ( $from, $to ) use ( $wpdb, $release_fail ) {
    try { $rows = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value=%s WHERE option_id=%d AND BINARY option_name=BINARY %s AND BINARY autoload=BINARY %s AND BINARY option_value=BINARY %s", $to['option_value'], (int) $from['option_id'], 'astra-settings', $from['autoload'], $from['option_value'] ) ); }
    finally { wp_cache_delete( 'astra-settings', 'options' ); wp_cache_delete( 'alloptions', 'options' ); wp_cache_delete( 'notoptions', 'options' ); }
    if ( $rows !== 1 ) { $release_fail( 'Astra compare-and-swap did not update exactly one row.' ); }
};
$release_marker = static function ( $path, $snapshot, $state, $current ) use ( $release_hash, $release_home, $release_manifest_hash, $release_marker_fields, $release_json, $release_file_unchanged ) {
    $release_file_unchanged( $path, $release_json( $snapshot ) );
    WP_CLI::log( 'RELEASE-SNAPSHOT ' . json_encode( $release_marker_fields( array( 'schema' => 1, 'bundle' => 'peek-visitor-release-v1', 'home' => $release_home, 'state' => $state, 'snapshot' => $path, 'snapshot_sha256' => hash_file( 'sha256', $path ), 'manifest_sha256' => $release_manifest_hash, 'state_sha256' => $release_hash( $current ), 'ids' => $snapshot['ids'] ) ), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
};

// Each reverse prefix has an exact complete state. A stopped rollback may resume
// only at one of these prefixes. Unknown edits are never adopted or overwritten.
$release_reverse = static function ( $old, $start, $ids, $file_guard = null, $read_only = false ) use ( $release_capture, $release_expected, $release_equal, $release_hash, $release_roles, $release_order, $release_css_set, $release_astra_set, $release_refresh, $release_fail ) {
    $owned = $start['owned'];
    $cleaned = array( 'css' => $start['records'][29]['post']['post_content'] !== $old['records'][29]['post']['post_content'], 'astra' => $start['options']['astra-settings'] !== $old['options']['astra-settings'] );
    $states = array( $start ); $actions = array();
    $remove = static function ( $role ) use ( &$owned, &$states, &$actions, &$cleaned, $old, $release_expected ) {
        if ( $owned[$role] !== null ) { $id = (int) $owned[$role]['post']['ID']; $owned[$role] = null; $actions[] = array( 'delete', $id ); $states[] = $release_expected( $old, $owned, $cleaned ); }
    };
    $remove( 'core' );
    foreach ( array( 'css', 'astra' ) as $store ) { if ( $cleaned[$store] ) { $cleaned[$store] = false; $actions[] = array( $store, null ); $states[] = $release_expected( $old, $owned, $cleaned ); } }
    foreach ( array_reverse( $release_order ) as $role ) { if ( $role !== 'core' ) { $remove( $role ); } }
    $release_equal( end( $states ), $old, 'Rollback plan final original state' );
    $current = $release_capture( $ids ); $position = null;
    foreach ( $states as $i => $state ) { if ( $release_hash( $current ) === $release_hash( $state ) ) { $position = $i; break; } }
    if ( $position === null ) { $release_fail( 'State is not an exact recognized rollback prefix; nothing overwritten.' ); }
    if ( $read_only ) { if ( $file_guard ) { $file_guard(); } return $current; }
    for ( $i = $position; $i < count( $actions ); $i++ ) {
        $release_equal( $release_capture( $ids ), $states[$i], 'State before rollback step' );
        if ( $file_guard ) { $file_guard(); }
        list( $action, $id ) = $actions[$i];
        if ( $action === 'delete' ) { $deleted = wp_delete_post( $id, true ); if ( ! $deleted || is_wp_error( $deleted ) ) { $release_fail( 'Owned snippet deletion failed; retain snapshot for exact-prefix retry.' ); } }
        elseif ( $action === 'css' ) { $release_css_set( $states[$i]['records'][29]['post']['post_content'], $old['records'][29]['post']['post_content'] ); }
        else { $release_astra_set( $states[$i]['options']['astra-settings'], $old['options']['astra-settings'] ); }
        $release_equal( $release_capture( $ids ), $states[$i + 1], 'State after rollback step' );
    }
    if ( $file_guard ) { $file_guard(); }
    $release_refresh();
    $current = $release_capture( $ids ); $release_equal( $current, $old, 'Exact original state after rollback/cache refresh' );
    if ( $file_guard ) { $file_guard(); }
    return $current;
};

$release_path = null; $release_snapshot = null;
try {
    if ( isset( $args[1] ) ) {
        $release_path = realpath( $args[1] );
        if ( ! $release_private_ok || ! $release_path || $args[1] !== $release_path || is_link( $args[1] ) || dirname( $release_path ) !== $release_private || ! preg_match( '/^peek-release-before-\d{8}-\d{6}-[a-f0-9]{8}\.json$/D', basename( $release_path ) ) || ! is_file( $release_path ) || ( fileperms( $release_path ) & 0777 ) !== 0600 ) { $release_fail( 'Snapshot must be its exact private 0600 file in WWM_BACKUP_DIR.' ); }
        $release_snapshot_bytes = file_get_contents( $release_path );
        $release_expected_snapshot_hash = (string) getenv( 'TS_PEEK_RELEASE_EXPECT_SNAPSHOT_SHA256' );
        if ( ( $release_incident || ( $release_live && in_array( $release_mode, array( 'apply', 'rollback' ), true ) ) ) && $release_expected_snapshot_hash === '' ) { $release_fail( 'Live snapshot-dependent writes and incident checks require the caller-preserved exact journal hash.' ); }
        if ( $release_expected_snapshot_hash !== '' && ( ! preg_match( '/^[a-f0-9]{64}$/D', $release_expected_snapshot_hash ) || hash( 'sha256', $release_snapshot_bytes ) !== $release_expected_snapshot_hash ) ) { $release_fail( 'Snapshot differs from caller-preserved exact bytes.' ); }
        $release_snapshot = json_decode( $release_snapshot_bytes, true, 512, JSON_THROW_ON_ERROR );
        $s = $release_snapshot;
        if ( ( $s['schema'] ?? null ) !== 1 || ( $s['bundle'] ?? null ) !== 'peek-visitor-release-v1' || ( $s['home'] ?? null ) !== $release_home || ( $s['manifest_sha256'] ?? null ) !== $release_manifest_hash || ( $s['profile_sha256'] ?? null ) !== $release_profile_hash || ! is_bool( $s['complete'] ?? null ) || $release_hash( $s['old'] ?? null ) !== ( $s['old_sha256'] ?? null ) ) { $release_fail( 'Snapshot bundle/site/manifest/completion/original hash differs.' ); }
        $release_incomplete = ! $s['complete'];
        if ( $release_incomplete && ! in_array( $release_mode, array( 'rollback', 'recovery-check' ), true ) ) { $release_fail( 'Incomplete journals support exact recovery rollback only.' ); }
        $target_key = $release_incomplete ? 'journal' : 'new';
        if ( $release_hash( $s[$target_key] ?? null ) !== ( $s[$target_key . '_sha256'] ?? null ) ) { $release_fail( 'Snapshot recorded state hash differs.' ); }
        $release_baseline( $s['old'] );
        if ( ! is_array( $s['ids'] ?? null ) || array_diff( array_keys( $s['ids'] ), $release_roles ) || count( array_unique( $s['ids'], SORT_REGULAR ) ) !== count( $s['ids'] ) || ( ! $release_incomplete && count( $s['ids'] ) !== 5 ) ) { $release_fail( 'Snapshot role IDs are missing, foreign or duplicated.' ); }
        $target = $s[$target_key]; $gap = false; $uncorrected = false; $draft_seen = false; $published = 0;
        foreach ( $release_order as $role ) {
            $record = $target['owned'][$role] ?? null;
            if ( $record === null ) { if ( isset( $s['ids'][$role] ) ) { $release_fail( 'Snapshot ID has no owned record.' ); } $gap = true; continue; }
            if ( $gap || $uncorrected || ! isset( $s['ids'][$role] ) ) { $release_fail( 'Snapshot creation/correction order differs.' ); }
            $id = $s['ids'][$role];
            if ( ! is_int( $id ) || $id < 1 || isset( $s['old']['witnesses']['posts'][$id] ) || isset( $s['old']['records'][$id] ) || ( $record['post']['ID'] ?? null ) !== (string) $id ) { $release_fail( 'Snapshot owned ID was not newly created.' ); }
            $status = $record['post']['post_status'] ?? null; $code = $record['post']['post_content'] ?? null;
            if ( ! in_array( $status, $release_incomplete ? array( 'draft', 'publish' ) : array( 'publish' ), true ) || ! in_array( $code, $status === 'draft' ? array( $release_codes[$role], wp_unslash( $release_codes[$role] ) ) : array( $release_codes[$role] ), true ) ) { $release_fail( 'Snapshot frozen code/activation phase differs.' ); }
            $initial_factory = $release_incomplete && $status === 'draft' && ! in_array( '_wpcode_shortcode_attributes', array_column( $record['meta'], 'meta_key' ), true );
            // An exact-code four-key draft may have skipped correction (raw)
            // or completed it (entity). No other phase accepts both forms.
            $saved_guid = $status === 'publish' || ( ! $initial_factory && $code === $release_codes[$role] && ( $record['post']['guid'] ?? null ) === $release_guid( $id, true ) );
            $release_owned_ok( $record, $role, $status, $code, $initial_factory, $saved_guid );
            // A three-key factory draft cannot be followed by another role or
            // cleanup/publication, even if its initial code needed no unslashing.
            $uncorrected = $initial_factory || $code !== $release_codes[$role];
            if ( $status === 'draft' ) { $draft_seen = true; } else { if ( $draft_seen ) { $release_fail( 'Snapshot activation order differs.' ); } $published++; }
        }
        $cleaned = array( 'css' => $target['records'][29]['post']['post_content'] !== $s['old']['records'][29]['post']['post_content'], 'astra' => $release_hash( $target['options']['astra-settings'] ) !== $release_hash( $s['old']['options']['astra-settings'] ) );
        if ( ( $cleaned['astra'] && ! $cleaned['css'] ) || ( ( $cleaned['css'] || $published ) && ( count( $s['ids'] ) !== 5 || $uncorrected ) ) || ( $published && ! $cleaned['astra'] ) || ( ! $release_incomplete && ( ! $cleaned['css'] || ! $cleaned['astra'] ) ) ) { $release_fail( 'Snapshot cleanup/activation phase differs.' ); }
        $release_equal( $target, $release_expected( $s['old'], $target['owned'], $cleaned ), 'Snapshot permitted recorded transition' );
        if ( $release_incident ) {
            // Only after the immutable original snapshot passes every original
            // semantic/ownership check may this one proved external delta be
            // projected onto expected states. Never alter $s or actual capture.
            if ( $release_incomplete ) { $release_fail( 'LIVE30 receipt requires its completed original journal.' ); }
            $adapter = require __DIR__ . '/incident-receipt.php';
            $bound = $adapter['read']( __DIR__ ); $receipt = $bound['receipt'];
            if ( basename( $release_path ) !== $receipt['snapshot']['file'] ) { $release_fail( 'LIVE30 actual journal filename differs.' ); }
            $sql_path = $release_private . '/' . $receipt['sql']['file'];
            $incident_guard = static function () use ( $release_path, $release_snapshot_bytes, $release_file_unchanged, $bound, $sql_path, $receipt, $release_fail ) {
                $release_file_unchanged( $release_path, $release_snapshot_bytes );
                foreach ( $bound['paths'] as $name => $file ) {
                    clearstatcache( true, $file );
                    if ( is_link( $file ) || ! is_file( $file ) || realpath( $file ) !== $file || file_get_contents( $file ) !== $bound['bytes'][$name] ) { $release_fail( 'LIVE30 bundled receipt binding changed.' ); }
                }
                clearstatcache( true, $sql_path );
                if ( is_link( $sql_path ) || ! is_file( $sql_path ) || realpath( $sql_path ) !== $sql_path || ( fileperms( $sql_path ) & 0777 ) !== 0600 || filesize( $sql_path ) !== $receipt['sql']['bytes'] || hash_file( 'sha256', $sql_path ) !== $receipt['sql']['sha256'] ) { $release_fail( 'LIVE30 original private SQL differs.' ); }
            };
            $incident_guard();
            $projection = $adapter['validate']( $bound['bytes']['receipt'], $bound['sha256'], $release_snapshot_bytes, $s, $release_manifest_hash, $release_profile_hash, hash_file( 'sha256', $sql_path ) );
            // Full-state witness equality is retained. These extra raw checks
            // establish the proved cache identities without disclosing values.
            $cache_rows = $release_rows( "SELECT * FROM {$wpdb->postmeta} WHERE meta_id IN (4701,4702,5089,5090,5091) ORDER BY meta_id" );
            if ( array_column( $cache_rows, 'meta_id' ) !== array( '5089', '5090', '5091' ) ) { $release_fail( 'LIVE30 exact five-row cache presence differs.' ); }
            foreach ( $cache_rows as $row ) {
                $proof = $receipt['added'][(int) $row['meta_id']];
                if ( $row['post_id'] !== (string) $proof['post_id'] || $row['meta_key'] !== $proof['meta_key'] || ! is_string( $row['meta_value'] ) || strlen( $row['meta_value'] ) !== $proof['value_bytes'] || hash( 'sha256', $row['meta_value'] ) !== $proof['value_sha256'] || $release_hash( $row ) !== $proof['row_sha256'] ) { $release_fail( 'LIVE30 exact generated cache row differs.' ); }
            }
            if ( $release_mode === 'verify' ) {
                $release_current = $release_capture( $s['ids'] );
                $release_equal( $release_current, $projection[$release_original ? 'old' : 'new'], 'LIVE30 release state with exact proved cache delta' );
                $incident_state = $release_original ? 'restored' : 'applied';
            } else {
                // The existing reverse planner still checks every complete raw
                // state before/after each owned write; only expected cache
                // witnesses differ. Cache rows themselves are never written.
                $release_current = $release_reverse( $projection['old'], $projection['new'], $s['ids'], $incident_guard, $release_mode === 'recovery-check' );
                if ( $release_original ) { $release_equal( $release_current, $projection['old'], 'LIVE30 owned release recovery with cache retained' ); }
                $incident_state = $release_mode === 'rollback' || $release_original ? 'restored' : 'recoverable';
            }
            $incident_guard();
            WP_CLI::log( 'RELEASE-INCIDENT ' . json_encode( $release_marker_fields( array( 'schema' => 1, 'bundle' => 'peek-visitor-release-v1', 'home' => $release_home, 'state' => $incident_state, 'snapshot' => $release_path, 'snapshot_sha256' => hash( 'sha256', $release_snapshot_bytes ), 'manifest_sha256' => $release_manifest_hash, 'state_sha256' => $release_hash( $release_current ), 'complete' => true, 'ids' => $s['ids'], 'receipt_sha256' => $bound['sha256'], 'sql_sha256' => $receipt['sql']['sha256'], 'cache_delta_retained' => true ) ), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
            WP_CLI::success( 'LIVE30 ' . $incident_state . ': exact release state with the proved external cache delta retained; historical database identity is not claimed. Original journal unchanged.' ); return;
        }
        $release_current = $release_capture( $s['ids'] );
        if ( $release_mode === 'recovery-check' ) {
            $release_current = $release_reverse( $s['old'], $target, $s['ids'], static fn() => $release_file_unchanged( $release_path, $release_json( $s ) ), true );
            if ( $release_original ) { $release_equal( $release_current, $s['old'], 'Recovery exact original state' ); }
            WP_CLI::log( 'RELEASE-RECOVERY ' . json_encode( $release_marker_fields( array( 'schema' => 1, 'bundle' => 'peek-visitor-release-v1', 'home' => $release_home, 'state' => $release_original ? 'original' : 'recoverable', 'snapshot' => $release_path, 'snapshot_sha256' => hash_file( 'sha256', $release_path ), 'manifest_sha256' => $release_manifest_hash, 'state_sha256' => $release_hash( $release_current ), 'complete' => $s['complete'], 'ids' => $s['ids'] ) ), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
            WP_CLI::success( 'Recognized exact recovery state; no database, cache or snapshot writes.' ); return;
        }
        if ( $release_mode === 'verify' || $release_mode === 'apply' ) {
            $release_state = $release_original ? 'original' : 'applied';
            $release_equal( $release_current, $s[$release_original ? 'old' : 'new'], 'Exact ' . $release_state . ' state' );
            $release_marker( $release_path, $s, $release_state, $release_current );
            WP_CLI::success( 'Verified ' . $release_state . '; no database, cache or snapshot writes.' ); return;
        }
        $release_current = $release_reverse( $s['old'], $target, $s['ids'], static fn() => $release_file_unchanged( $release_path, $release_json( $s ) ) );
        if ( $release_incomplete ) { WP_CLI::success( 'Exact original state recovered from incomplete journal; no release marker. Journal unchanged.' ); return; }
        $release_marker( $release_path, $s, 'original', $release_current );
        WP_CLI::success( 'Exact original state restored; completed snapshot unchanged.' ); return;
    }
    $release_current = $release_capture(); $release_baseline( $release_current );
    if ( $release_mode === 'preflight' ) {
        WP_CLI::log( 'RELEASE-PREFLIGHT ' . json_encode( $release_marker_fields( array( 'schema' => 1, 'bundle' => 'peek-visitor-release-v1', 'home' => $release_home, 'manifest_sha256' => $release_manifest_hash, 'state' => 'ready', 'protected_state_sha256' => $release_hash( $release_current ) ) ), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
        WP_CLI::success( 'Original visitor baseline ready; nothing changed.' ); return;
    }
    if ( ! $release_private_ok ) { $release_fail( 'Existing writable private WWM_BACKUP_DIR outside the webroot required.' ); }
} catch ( Throwable $release_error ) { WP_CLI::error( $release_error->getMessage() . ' No further writes.' ); }

$release_old = $release_current; $release_ids = array(); $release_owned = $release_old['owned']; $release_cleaned = array( 'css' => false, 'astra' => false );
$release_path_request = (string) getenv( 'TS_PEEK_RELEASE_NEW_JOURNAL' );
if ( $release_live && $release_path_request === '' ) { WP_CLI::error( 'First live apply requires its preselected private recovery journal; nothing changed.' ); }
$release_path = $release_path_request !== '' ? $release_path_request : $release_private . '/peek-release-before-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
if ( dirname( $release_path ) !== $release_private || ! preg_match( '/^peek-release-before-\d{8}-\d{6}-[a-f0-9]{8}\.json$/D', basename( $release_path ) ) || file_exists( $release_path ) || is_link( $release_path ) ) { WP_CLI::error( 'New journal must be an unused exact canonical private path; nothing changed.' ); }
$release_snapshot = array( 'schema' => 1, 'bundle' => 'peek-visitor-release-v1', 'home' => $release_home, 'manifest_sha256' => $release_manifest_hash, 'created' => gmdate( 'c' ), 'complete' => false, 'ids' => array(), 'old' => $release_old, 'old_sha256' => $release_hash( $release_old ), 'journal' => $release_old, 'journal_sha256' => $release_hash( $release_old ) );
$release_snapshot = $release_marker_fields( $release_snapshot );
try { $release_save( $release_path, $release_snapshot ); $release_equal( $release_capture(), $release_old, 'State after initial private snapshot' ); }
catch ( Throwable $release_error ) { WP_CLI::error( $release_error->getMessage() . ' Nothing changed.' ); }

// Only accept a save's documented fields; all other post fields/meta/terms stay
// byte-for-byte equal. Dates are limited to the actual call's local/GMT clocks.
$release_changed_record = static function ( $before, $after, $changes, $clocks ) use ( $release_equal, $release_fail, $release_guid ) {
    $expected = $before;
    foreach ( $changes as $key => $value ) { $expected['post'][$key] = $value; }
    $expected['post']['guid'] = $release_guid( (int) $before['post']['ID'], true );
    if ( ( $changes['post_status'] ?? null ) === 'publish' && $before['post']['post_name'] === '' ) {
        // Core creates a draft without a slug, then assigns its deterministic
        // title slug when publishing. An existing slug is never normalized.
        $expected['post']['post_name'] = sanitize_title( $before['post']['post_title'] );
    }
    if ( ( $changes['post_status'] ?? null ) === 'publish' && $before['post']['post_date_gmt'] === '0000-00-00 00:00:00' ) {
        $expected['post']['post_date_gmt'] = get_gmt_from_date( $before['post']['post_date'] );
    }
    foreach ( array( 'post_modified', 'post_modified_gmt' ) as $i => $key ) {
        if ( ! in_array( $after['post'][$key] ?? null, array( $clocks[$i], current_time( 'mysql', $i === 1 ) ), true ) ) { $release_fail( 'Unexpected save date.' ); }
        $expected['post'][$key] = $after['post'][$key];
    }
    $release_equal( $after, $expected, 'Owned save permitted fields' );
};
$release_last = $release_old;
$release_step = static function ( $action, $accept ) use ( &$release_last, &$release_ids, &$release_snapshot, $release_path, $release_capture, $release_equal, $release_hash, $release_save, $release_file_unchanged, &$release_saved_bytes ) {
    $release_equal( $release_capture( $release_ids ), $release_last, 'State before apply step' );
    $release_file_unchanged( $release_path, $release_saved_bytes[$release_path] );
    $error = null; $result = null;
    try { $result = $action(); } catch ( Throwable $caught ) { $error = $caught; }
    $actual = $release_capture( $release_ids );
    if ( $release_hash( $actual ) !== $release_hash( $release_last ) ) {
        $accept( $actual, $result );
        $release_last = $actual;
        $release_snapshot['ids'] = $release_ids; $release_snapshot['journal'] = $actual; $release_snapshot['journal_sha256'] = $release_hash( $actual );
        $release_save( $release_path, $release_snapshot );
    } elseif ( $error === null ) { throw new RuntimeException( 'Apply step did not produce its required state transition.' ); }
    if ( $error !== null ) { throw $error; }
};
try {
    // Draft all five records. WPCode's first save unslashes PHP; capture that
    // exact known draft, then correct it while still inactive.
    foreach ( $release_order as $release_role ) {
        $role = $release_role;
        $release_step( static function () use ( $role, $release_titles, $release_notes, $release_codes, $release_fail, $release_wpcode_save ) {
            $snippet = new WPCode_Snippet( array( 'title' => $release_titles[$role], 'code' => $release_codes[$role], 'code_type' => 'php', 'location' => 'everywhere', 'auto_insert' => 1, 'priority' => 10, 'active' => false, 'note' => $release_notes[$role] ) );
            $id = $release_wpcode_save( static fn() => $snippet->save() ); if ( ! is_numeric( $id ) || (int) $id < 1 ) { $release_fail( 'Inactive snippet creation failed.' ); } return (int) $id;
        }, static function ( $actual, $result ) use ( $role, &$release_owned, &$release_ids, $release_codes, $release_owned_ok, $release_old, &$release_cleaned, $release_expected, $release_equal, $release_fail ) {
            $record = $actual['owned'][$role]; $id = (int) ( $record['post']['ID'] ?? 0 );
            $code = $record['post']['post_content'] ?? null;
            if ( ! in_array( $code, array( $release_codes[$role], wp_unslash( $release_codes[$role] ) ), true ) || ( $result !== null && $result !== $id ) || isset( $release_old['witnesses']['posts'][$id] ) || isset( $release_old['records'][$id] ) || in_array( $id, $release_ids, true ) ) { $release_fail( 'Created draft is not an exact newly owned record.' ); }
            $release_owned_ok( $record, $role, 'draft', $code, true );
            $owned = $release_owned; $owned[$role] = $record;
            $release_equal( $actual, $release_expected( $release_old, $owned, $release_cleaned ), 'Inactive creation full state' );
            $release_owned = $owned; $release_ids[$role] = $id;
        } );
        // Actual copy2 proved WPCode's inactive factory omits this metadata.
        // Normalize only our just-journalled exact three-key draft. A unique
        // insert must return its new meta ID; an unreturned row is never adopted.
        $normalization_meta_id = null;
        $release_step( static function () use ( $role, &$release_ids, &$normalization_meta_id, $release_fail ) {
            $normalization_meta_id = add_post_meta( $release_ids[$role], '_wpcode_shortcode_attributes', array(), true );
            if ( ! is_int( $normalization_meta_id ) || $normalization_meta_id < 1 ) { $release_fail( 'Unique attribute initialization did not return its new metadata ID.' ); }
            return $normalization_meta_id;
        }, static function ( $actual, $result ) use ( $role, &$release_owned, &$release_ids, &$normalization_meta_id, $release_owned_ok, $release_old, &$release_cleaned, $release_expected, $release_equal, $release_fail ) {
            if ( ! is_int( $normalization_meta_id ) || $normalization_meta_id < 1 || $result !== $normalization_meta_id ) { $release_fail( 'Unreturned attribute row is not an owned initialization result.' ); }
            $before = $release_owned[$role];
            if ( $normalization_meta_id <= max( array_map( 'intval', array_column( $before['meta'], 'meta_id' ) ) ) ) { $release_fail( 'Initialization metadata ID is not newly allocated.' ); }
            $expected = $before;
            $expected['meta'][] = array( 'meta_id' => (string) $normalization_meta_id, 'meta_key' => '_wpcode_shortcode_attributes', 'meta_value' => 'a:0:{}', 'post_id' => (string) $release_ids[$role] );
            $record = $actual['owned'][$role];
            $release_equal( $record, $expected, 'Attribute initialization preserves original raw rows, post and relationships' );
            $release_owned_ok( $record, $role, 'draft', $before['post']['post_content'] );
            $owned = $release_owned; $owned[$role] = $record;
            $release_equal( $actual, $release_expected( $release_old, $owned, $release_cleaned ), 'Attribute initialization full state' );
            $release_owned = $owned;
        } );
        if ( $release_owned[$role]['post']['post_content'] !== $release_codes[$role] ) {
            $clocks = array( current_time( 'mysql' ), current_time( 'mysql', true ) );
            $release_step( static function () use ( $role, &$release_ids, $release_codes, $release_fail, $release_wpcode_save ) {
                $id = $release_wpcode_save( static fn() => wp_update_post( wp_slash( array( 'ID' => $release_ids[$role], 'post_content' => $release_codes[$role], 'edit_date' => true ) ), true ) );
                if ( is_wp_error( $id ) || $id !== $release_ids[$role] ) { $release_fail( 'Inactive code correction failed.' ); } return $id;
            }, static function ( $actual ) use ( $role, &$release_owned, $release_codes, $release_changed_record, $clocks, $release_old, &$release_cleaned, $release_expected, $release_equal ) {
                $record = $actual['owned'][$role]; $release_changed_record( $release_owned[$role], $record, array( 'post_content' => $release_codes[$role] ), $clocks );
                $owned = $release_owned; $owned[$role] = $record; $release_equal( $actual, $release_expected( $release_old, $owned, $release_cleaned ), 'Corrected draft full state' ); $release_owned = $owned;
            } );
        }
    }
    foreach ( array( 'css', 'astra' ) as $store ) {
        $release_step( static function () use ( $store, $release_old, $release_expected, &$release_owned, $release_css_set, $release_astra_set, $release_astra ) {
            if ( $store === 'css' ) { $next = $release_expected( $release_old, $release_owned, array( 'css' => true, 'astra' => false ) ); $release_css_set( $release_old['records'][29]['post']['post_content'], $next['records'][29]['post']['post_content'] ); }
            else { $release_astra_set( $release_old['options']['astra-settings'], $release_astra( $release_old['options']['astra-settings'] ) ); }
            return true;
        }, static function ( $actual ) use ( $store, &$release_cleaned, $release_old, &$release_owned, $release_expected, $release_equal ) {
            $cleaned = $release_cleaned; $cleaned[$store] = true; $release_equal( $actual, $release_expected( $release_old, $release_owned, $cleaned ), 'Cleanup full state' ); $release_cleaned = $cleaned;
        } );
    }
    // Page data activates first; core is the last activation. The existing row
    // and native guide remain throughout and on any failed/rolled-back release.
    foreach ( $release_order as $role ) {
        $clocks = array( current_time( 'mysql' ), current_time( 'mysql', true ) );
        $release_step( static function () use ( $role, &$release_ids, $release_fail, $release_wpcode_save ) {
            $id = $release_wpcode_save( static fn() => wp_update_post( wp_slash( array( 'ID' => $release_ids[$role], 'post_status' => 'publish', 'edit_date' => true ) ), true ) );
            if ( is_wp_error( $id ) || $id !== $release_ids[$role] ) { $release_fail( 'Activation failed.' ); } return $id;
        }, static function ( $actual ) use ( $role, &$release_owned, $release_changed_record, $clocks, $release_owned_ok, $release_codes, $release_old, &$release_cleaned, $release_expected, $release_equal ) {
            $record = $actual['owned'][$role]; $release_changed_record( $release_owned[$role], $record, array( 'post_status' => 'publish' ), $clocks ); $release_owned_ok( $record, $role, 'publish', $release_codes[$role], false, true );
            $owned = $release_owned; $owned[$role] = $record; $release_equal( $actual, $release_expected( $release_old, $owned, $release_cleaned ), 'Activation full state and shared counts' ); $release_owned = $owned;
        } );
    }
    $release_file_unchanged( $release_path, $release_saved_bytes[$release_path] );
    $release_refresh(); $release_equal( $release_capture( $release_ids ), $release_last, 'Final applied state/cache refresh' );
    $release_snapshot['ids'] = $release_ids; $release_snapshot['new'] = $release_last; $release_snapshot['new_sha256'] = $release_hash( $release_last ); $release_snapshot['complete'] = true;
    unset( $release_snapshot['journal'], $release_snapshot['journal_sha256'] );
    $release_save( $release_path, $release_snapshot );
    $release_equal( $release_capture( $release_ids ), $release_last, 'Final state after complete snapshot' );
} catch ( Throwable $release_error ) {
    try {
        $release_equal( $release_capture( $release_ids ), $release_last, 'Recognized state before apply recovery' );
        $release_reverse( $release_old, $release_last, $release_ids );
    } catch ( Throwable $release_recovery ) { WP_CLI::error( $release_error->getMessage() . '; exact recovery stopped: ' . $release_recovery->getMessage() . ' Snapshot: ' . $release_path ); }
    WP_CLI::error( $release_error->getMessage() . '; exact original state restored. Snapshot: ' . $release_path );
}
$release_marker( $release_path, $release_snapshot, 'applied', $release_last );
WP_CLI::success( 'Applied exact profile-bound visitor release. Snapshot: ' . $release_path );
