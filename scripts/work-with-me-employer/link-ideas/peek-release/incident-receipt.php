<?php
// Pure, one-incident expected-state adapter. No database or filesystem writes.
// The original journal must pass the installer's full semantic validation first.
$incident_require = static function ( $ok, $label ) {
    if ( ! $ok ) { throw new RuntimeException( 'LIVE30 receipt refused: ' . $label . '.' ); }
};
$incident_keys = static function ( $value, $keys ) use ( $incident_require ) {
    $incident_require( is_array( $value ), 'object shape' );
    $actual = array_map( 'strval', array_keys( $value ) ); $expected = array_map( 'strval', $keys );
    sort( $actual, SORT_STRING ); sort( $expected, SORT_STRING );
    $incident_require( $actual === $expected, 'exact fields' );
};
$incident_canonical = static function ( $value ) use ( &$incident_canonical ) {
    if ( is_array( $value ) ) {
        if ( ! array_is_list( $value ) ) { ksort( $value, SORT_STRING ); }
        foreach ( $value as &$item ) { $item = $incident_canonical( $item ); } unset( $item );
    }
    return $value;
};
$incident_hash = static fn( $value ) => hash( 'sha256', json_encode( $incident_canonical( $value ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_THROW_ON_ERROR ) );
$incident_sha = static fn( $value ) => is_string( $value ) && preg_match( '/^[a-f0-9]{64}$/D', $value ) === 1;
$incident_shape = static function ( $r ) use ( $incident_require, $incident_keys, $incident_sha ) {
    $incident_keys( $r, array( 'schema', 'incident', 'bundle', 'home', 'manifest_sha256', 'profile_sha256', 'snapshot', 'sql', 'ids', 'original_applied_sha256', 'projected_applied_sha256', 'removed', 'added', 'proof_sha256' ) );
    $incident_require( $r['schema'] === 1 && $r['incident'] === 'live30-assets-20261111' && $r['bundle'] === 'peek-visitor-release-v1' && $r['home'] === 'http://thaisolitude.com', 'incident identity' );
    foreach ( array( 'manifest_sha256', 'profile_sha256', 'original_applied_sha256', 'projected_applied_sha256', 'proof_sha256' ) as $key ) { $incident_require( $incident_sha( $r[$key] ), 'digest format' ); }
    foreach ( array( 'snapshot' => '/^peek-release-before-\d{8}-\d{6}-[a-f0-9]{8}\.json$/D', 'sql' => '/^live-before-peek-release-\d{8}-\d{6}-[0-9]+\.sql$/D' ) as $key => $pattern ) {
        $incident_keys( $r[$key], array( 'file', 'sha256', 'bytes' ) );
        $incident_require( is_string( $r[$key]['file'] ) && preg_match( $pattern, $r[$key]['file'] ) === 1 && $incident_sha( $r[$key]['sha256'] ) && is_int( $r[$key]['bytes'] ) && $r[$key]['bytes'] > 0, 'private evidence binding' );
    }
    $incident_keys( $r['ids'], array( 'core', 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ) );
    foreach ( $r['ids'] as $id ) { $incident_require( is_int( $id ) && $id > 0, 'owned ID type' ); }
    $incident_require( count( array_unique( $r['ids'], SORT_REGULAR ) ) === 5, 'distinct owned IDs' );
    foreach ( array( 'removed' => array( 4701 => 1255, 4702 => 1257 ), 'added' => array( 5089 => 1254, 5090 => 1255, 5091 => 1257 ) ) as $kind => $identities ) {
        $incident_keys( $r[$kind], array_keys( $identities ) );
        foreach ( $identities as $id => $post_id ) {
            $row = $r[$kind][$id];
            $incident_keys( $row, array( 'post_id', 'meta_key', 'row_sha256', 'value_sha256', 'value_bytes' ) );
            $incident_require( $row['post_id'] === $post_id && $row['meta_key'] === '_uag_page_assets' && $incident_sha( $row['row_sha256'] ) && $incident_sha( $row['value_sha256'] ) && is_int( $row['value_bytes'] ) && $row['value_bytes'] > 0 && ! in_array( $post_id, $r['ids'], true ), 'fixed external cache row' );
        }
    }
};
$incident_project = static function ( $state, $r ) use ( $incident_require, $incident_shape ) {
    $incident_shape( $r );
    $incident_require( is_array( $state['witnesses']['postmeta'] ?? null ), 'postmeta witnesses' );
    foreach ( $r['removed'] as $id => $row ) {
        $incident_require( ( $state['witnesses']['postmeta'][$id] ?? null ) === $row['row_sha256'], 'original removed-row witness' );
        unset( $state['witnesses']['postmeta'][$id] );
    }
    foreach ( $r['added'] as $id => $row ) {
        $incident_require( ! array_key_exists( $id, $state['witnesses']['postmeta'] ), 'new witness must be absent originally' );
        $state['witnesses']['postmeta'][$id] = $row['row_sha256'];
    }
    return $state;
};
$incident_validate = static function ( $bytes, $expected_sha, $snapshot_bytes, $s, $manifest_sha, $profile_sha, $sql_sha ) use ( $incident_require, $incident_sha, $incident_shape, $incident_project, $incident_hash ) {
    $incident_require( $incident_sha( $expected_sha ) && is_string( $bytes ) && hash( 'sha256', $bytes ) === $expected_sha, 'reviewed receipt bytes' );
    $r = json_decode( $bytes, true, 512, JSON_THROW_ON_ERROR ); $incident_shape( $r );
    $incident_require( $r['manifest_sha256'] === $manifest_sha && $r['profile_sha256'] === $profile_sha && $r['sql']['sha256'] === $sql_sha, 'source/profile/SQL identity' );
    $incident_require( is_string( $snapshot_bytes ) && strlen( $snapshot_bytes ) === $r['snapshot']['bytes'] && hash( 'sha256', $snapshot_bytes ) === $r['snapshot']['sha256'], 'immutable actual journal bytes' );
    $incident_require( json_decode( $snapshot_bytes, true, 512, JSON_THROW_ON_ERROR ) === $s, 'parsed actual journal' );
    $incident_require( ( $s['schema'] ?? null ) === 1 && ( $s['complete'] ?? null ) === true && ( $s['home'] ?? null ) === $r['home'] && ( $s['bundle'] ?? null ) === $r['bundle'] && ( $s['manifest_sha256'] ?? null ) === $manifest_sha && ( $s['profile_sha256'] ?? null ) === $profile_sha && $incident_hash( $s['ids'] ?? null ) === $incident_hash( $r['ids'] ), 'complete original journal identity' );
    $incident_require( $incident_hash( $s['old'] ?? null ) === ( $s['old_sha256'] ?? null ) && $incident_hash( $s['new'] ?? null ) === ( $s['new_sha256'] ?? null ) && $s['new_sha256'] === $r['original_applied_sha256'], 'original state hashes' );
    $old = $incident_project( $s['old'], $r ); $new = $incident_project( $s['new'], $r );
    $incident_require( $incident_hash( $new ) === $r['projected_applied_sha256'], 'exact five-row projection' );
    return array( 'receipt' => $r, 'old' => $old, 'new' => $new );
};
$incident_read = static function ( $directory ) use ( $incident_require, $incident_keys, $incident_sha, $incident_shape ) {
    $paths = array( 'binding' => $directory . '/incident-live30-binding.json', 'receipt' => $directory . '/incident-live30.json' );
    $bytes = array();
    foreach ( $paths as $name => $path ) {
        $incident_require( is_file( $path ) && ! is_link( $path ) && realpath( $path ) === $path, 'canonical bundled receipt files' );
        $bytes[$name] = file_get_contents( $path );
    }
    $binding = json_decode( $bytes['binding'], true, 512, JSON_THROW_ON_ERROR );
    $incident_keys( $binding, array( 'schema', 'incident', 'receipt_sha256' ) );
    $incident_require( $binding['schema'] === 1 && $binding['incident'] === 'live30-assets-20261111' && $incident_sha( $binding['receipt_sha256'] ), 'production proof binding is disabled or invalid' );
    $incident_require( hash( 'sha256', $bytes['receipt'] ) === $binding['receipt_sha256'], 'pinned bundled receipt' );
    $r = json_decode( $bytes['receipt'], true, 512, JSON_THROW_ON_ERROR ); $incident_shape( $r );
    return array( 'paths' => $paths, 'bytes' => $bytes, 'sha256' => $binding['receipt_sha256'], 'receipt' => $r );
};
return array( 'project' => $incident_project, 'validate' => $incident_validate, 'read' => $incident_read );
