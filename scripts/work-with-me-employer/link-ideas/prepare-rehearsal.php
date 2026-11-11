<?php
// Core migration's prerequisite, on the isolated copy only, before its baseline.
// The separate peek-page/ wrapper rehearses data creation itself without this hook.
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) || 'http://localhost:8090' !== untrailingslashit( home_url() ) || 'local' !== wp_get_environment_type() ) { throw new \RuntimeException( 'Isolated localhost rehearsal only.' ); }
$args = array( 'apply', 'page', 'thai-massage' );
require __DIR__ . '/install-peek-nav.php';
