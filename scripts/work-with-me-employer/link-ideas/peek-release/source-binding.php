<?php
/** Read-only binding to the reviewed, shipped Spectra cache-invalidation code. */
$release_spectra_paths = array(
    'ultimate-addons-for-gutenberg.php',
    'classes/class-uagb-helper.php',
    'classes/class-uagb-rest-api.php',
    'classes/class-uagb-loader.php',
    'classes/class-uagb-post-assets.php',
    'classes/class-uagb-front-assets.php',
);
$release_spectra = $release_manifest['spectra'] ?? array();
$release_spectra_files = $release_spectra['files'] ?? array();
if ( ( $release_spectra['version'] ?? null ) !== '2.20.6' || ( $release_spectra['source_commit'] ?? null ) !== 'd1852cd27c9ac93e8fd7606261c2a023c5c253da' || ( $release_spectra['official_package_url'] ?? null ) !== 'https://downloads.wordpress.org/plugin/ultimate-addons-for-gutenberg.2.20.6.zip' || ! preg_match( '/^[a-f0-9]{64}$/D', $release_spectra['official_package_sha256'] ?? '' ) || ! is_array( $release_spectra_files ) || array_diff( array_keys( $release_spectra_files ), $release_spectra_paths ) || array_diff( $release_spectra_paths, array_keys( $release_spectra_files ) ) ) { $release_fail( 'Reviewed Spectra source profile is missing or malformed.' ); }
$release_spectra_root = defined( 'WP_PLUGIN_DIR' ) ? rtrim( WP_PLUGIN_DIR, '/\\' ) . '/ultimate-addons-for-gutenberg' : '';
$release_spectra_real = $release_spectra_root !== '' ? realpath( $release_spectra_root ) : false;
$release_spectra_root_ok = is_string( $release_spectra_real ) && $release_spectra_real === $release_spectra_root && ! is_link( $release_spectra_root );
$release_spectra_ok = $release_spectra_root_ok;
$release_spectra_diagnostic = array( 'files' => array(), 'header_version' => null, 'runtime_version' => null, 'loaded' => array(), 'save_post_priority_10' => false );
foreach ( $release_spectra_paths as $release_spectra_path ) {
    $file = $release_spectra_root . '/' . $release_spectra_path;
    $regular = $release_spectra_root_ok && is_file( $file ) && ! is_link( $file ) && realpath( $file ) === $file;
    $actual = $regular ? hash_file( 'sha256', $file ) : false;
    $release_spectra_diagnostic['files'][$release_spectra_path] = is_string( $actual ) ? $actual : 'missing-or-noncanonical';
    $expected = $release_spectra_files[$release_spectra_path];
    if ( ! is_string( $expected ) || ! preg_match( '/^[a-f0-9]{64}$/D', $expected ) || ! is_string( $actual ) || ! hash_equals( $expected, $actual ) ) { $release_spectra_ok = false; }
}
$release_spectra_main = $release_spectra_root . '/ultimate-addons-for-gutenberg.php';
$release_spectra_header = is_file( $release_spectra_main ) ? file_get_contents( $release_spectra_main, false, null, 0, 8192 ) : '';
if ( is_string( $release_spectra_header ) && preg_match( '/^[ \t\/*#@]*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)[ \t]*\r?$/mi', $release_spectra_header, $matches ) ) { $release_spectra_diagnostic['header_version'] = $matches[1]; }
$release_spectra_runtime = defined( 'UAGB_VER' ) ? UAGB_VER : null;
if ( is_string( $release_spectra_runtime ) && preg_match( '/^[0-9]+\.[0-9]+\.[0-9]+$/D', $release_spectra_runtime ) ) { $release_spectra_diagnostic['runtime_version'] = $release_spectra_runtime; }
$release_spectra_diagnostic['loaded']['main'] = in_array( $release_spectra_main, array_map( 'realpath', get_included_files() ), true );
foreach ( array( 'UAGB_Helper' => 'classes/class-uagb-helper.php', 'UAGB_Rest_API' => 'classes/class-uagb-rest-api.php', 'UAGB_Loader' => 'classes/class-uagb-loader.php', 'UAGB_Post_Assets' => 'classes/class-uagb-post-assets.php', 'UAGB_Front_Assets' => 'classes/class-uagb-front-assets.php' ) as $class => $file ) {
    $loaded = class_exists( $class, false );
    $matches = $loaded && ( new ReflectionClass( $class ) )->getFileName() === $release_spectra_root . '/' . $file;
    $release_spectra_diagnostic['loaded'][$class] = $loaded ? $matches : 'not-loaded';
    if ( ( $loaded && ! $matches ) || ( ! $loaded && in_array( $class, array( 'UAGB_Helper', 'UAGB_Loader' ), true ) ) ) { $release_spectra_ok = false; }
}
$release_spectra_diagnostic['save_post_priority_10'] = has_action( 'save_post', array( 'UAGB_Helper', 'delete_page_assets' ) ) === 10;
$release_spectra_ok = $release_spectra_ok && $release_spectra_diagnostic['header_version'] === '2.20.6' && $release_spectra_diagnostic['runtime_version'] === '2.20.6' && $release_spectra_diagnostic['loaded']['main'] && $release_spectra_diagnostic['save_post_priority_10'];
// Only public file identities and booleans: no source bytes, database values,
// filesystem roots, or arbitrary plugin/version strings enter the report.
WP_CLI::log( 'RELEASE-SPECTRA-SOURCE ' . json_encode( $release_spectra_diagnostic, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR ) );
if ( ! $release_spectra_ok ) { $release_fail( 'Installed/loaded Spectra differs from the reviewed shipped source.' ); }
