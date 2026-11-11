<?php
// Exact deployed v7 -> reviewed neighbours fix without page-data changes; untouched localhost baseline.
$allowed_host = 'localhost:8090';
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) || 'http://' . $allowed_host !== untrailingslashit( home_url() ) || 'local' !== wp_get_environment_type() ) { throw new \RuntimeException( 'Isolated localhost:8090 rehearsal only.' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || count( $args ) > ( 'rollback' === $mode ? 2 : 1 ) ) { WP_CLI::error( 'Use preflight|apply, or rollback FILE, for this neighbours rehearsal.' ); }
if ( 'rollback' === $mode ) {
    $file = $args[1] ?? '';
    $snapshot = is_string( $file ) && is_file( $file ) && is_readable( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
    if ( ! is_array( $snapshot ) || ( $snapshot['bundle'] ?? '' ) !== 'peek-core-neighbours-v1' || ( $snapshot['component'] ?? '' ) !== 'core-neighbours' || ( $snapshot['slug'] ?? null ) !== '' ) { WP_CLI::error( 'Rollback snapshot must belong to this neighbours upgrade; nothing changed.' ); }
    // The shared installer verifies private path, complete states, hashes and all page data.
} else {
    $args = array( $mode, 'core-neighbours' );
}
require dirname( __DIR__ ) . '/install-peek-nav.php';
