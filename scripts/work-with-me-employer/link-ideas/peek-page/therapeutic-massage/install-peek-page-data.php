<?php
// One page on a fresh staging39-source copy; no preparation before the baseline.
$allowed_host = 'localhost:8090';
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) || 'http://' . $allowed_host !== untrailingslashit( home_url() ) || 'local' !== wp_get_environment_type() ) { throw new \RuntimeException( 'Isolated localhost:8090 rehearsal only.' ); }
$peek_page_slug = 'therapeutic-massage';
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'preflight';
if ( ! in_array( $mode, array( 'preflight', 'apply', 'rollback' ), true ) || count( $args ) > ( 'rollback' === $mode ? 2 : 1 ) ) { WP_CLI::error( 'Use preflight|apply, or rollback FILE, for this page rehearsal.' ); }
if ( 'rollback' === $mode ) {
    $file = $args[1] ?? '';
    $snapshot = is_string( $file ) && is_file( $file ) && is_readable( $file ) ? json_decode( file_get_contents( $file ), true ) : null;
    if ( ! is_array( $snapshot ) || ( $snapshot['bundle'] ?? '' ) !== 'peek-template-v1' || ( $snapshot['component'] ?? '' ) !== 'page' || ( $snapshot['slug'] ?? '' ) !== $peek_page_slug ) { WP_CLI::error( 'Rollback snapshot must belong to this page rehearsal; nothing changed.' ); }
    // The shared installer checks private path, ownership, all hashes and exact state.
} else {
    $args = array( $mode, 'page', $peek_page_slug );
}
require dirname( __DIR__, 2 ) . '/install-peek-nav.php';
