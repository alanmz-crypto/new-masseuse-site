<?php
// Exact deployed v4 -> rails without new summary data; untouched localhost baseline.
$allowed_host = 'localhost:8090';
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) || 'http://' . $allowed_host !== untrailingslashit( home_url() ) || 'local' !== wp_get_environment_type() ) { throw new \RuntimeException( 'Isolated localhost:8090 rehearsal only.' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || count( $args ) > ( 'rollback' === $mode ? 2 : 1 ) ) { WP_CLI::error( 'Use preflight|apply, or rollback FILE, for this title-rail rehearsal.' ); }
if ( 'rollback' === $mode ) {
    $file = $args[1] ?? '';
    $snapshot = is_string( $file ) && is_file( $file ) && is_readable( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
    if ( ! is_array( $snapshot ) || ( $snapshot['bundle'] ?? '' ) !== 'peek-core-title-rail-v1' || ( $snapshot['component'] ?? '' ) !== 'core-title-rail' || ( $snapshot['slug'] ?? null ) !== '' ) { WP_CLI::error( 'Rollback snapshot must belong to this title-rail upgrade; nothing changed.' ); }
    // The shared installer verifies private path, complete states, hashes and all page data.
} else {
    $args = array( $mode, 'core-title-rail' );
}
require dirname( __DIR__ ) . '/install-peek-nav.php';
