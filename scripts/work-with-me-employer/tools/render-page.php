<?php
// Render ONE public page through WordPress in the command line, like a front-end request. usage: wp eval-file render-page.php <request-path> <output-file>
// Runs under whatever PHP the caller launched WP-CLI with. Writes only the output file.
// WordPress turns deprecation reporting off after it loads unless WP_DEBUG is on (normal for production). The test wants to SEE them, so reporting is set to everything here.
error_reporting( -1 );
$args = isset( $args ) && is_array( $args ) ? $args : array();
if ( count( $args ) < 2 ) { WP_CLI::error( 'usage: render-page.php <path> <outfile>' ); }
list( $path, $outfile ) = $args;
$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
$_SERVER['HTTP_HOST'] = $host; $_SERVER['SERVER_NAME'] = $host; unset( $_SERVER['HTTPS'] ); $_SERVER['SERVER_PORT'] = '80';   // the site's home option is http://, so a plain-http request avoids WordPress's canonical redirect
$_SERVER['REQUEST_METHOD'] = 'GET'; $_SERVER['REQUEST_URI'] = $path; $_SERVER['QUERY_STRING'] = ''; $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SCRIPT_NAME'] = '/index.php'; $_SERVER['PHP_SELF'] = '/index.php'; $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (php-runtime-render-test)';
add_filter( 'wp_using_themes', '__return_true' );
ob_start();
wp();
require ABSPATH . WPINC . '/template-loader.php';
$html = ob_get_clean();
if ( '' === trim( (string) $html ) ) { WP_CLI::error( 'Empty output for ' . $path . ' (a redirect or an early exit?)' ); }
file_put_contents( $outfile, $html );
echo strlen( $html ), " bytes, status ", http_response_code() ?: 200, "\n";

// ref: ../php-runtime-test.sh — uploads and runs this under each PHP version; ../php-compat-scan.sh — the compile-time companion
