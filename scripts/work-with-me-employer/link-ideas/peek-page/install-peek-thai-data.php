<?php
// Rehearsal adapter: localhost:8090 only; modes 'preflight', 'apply', 'rollback'.
// No duplicate installer logic and no package preparation before this baseline.
$allowed_host = 'localhost:8090';
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) || 'http://' . $allowed_host !== untrailingslashit( home_url() ) || 'local' !== wp_get_environment_type() ) { throw new \RuntimeException( 'Isolated localhost:8090 rehearsal only.' ); }
$args = isset( $args ) && is_array( $args ) ? $args : array();
if ( 'rollback' !== ( $args[0] ?? 'preflight' ) ) { $args = array( $args[0] ?? 'preflight', 'page', 'thai-massage' ); }
require dirname( __DIR__ ) . '/install-peek-nav.php';
