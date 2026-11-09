<?php
/** Read-only observations of the original Rail page before baseline warming.
 * record | compare FILE [cold-assets]
 * cold-assets classifies only this page's _uag_page_assets read-side change.
 * It never changes WordPress, rewrites an observation, or rebases an install snapshot.
 */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 'Run with WP-CLI.' ); }
$ri_observe_args = isset( $args ) && is_array( $args ) ? $args : array();
$ri_observe_mode = $ri_observe_args[0] ?? '';
if ( ! ( ( 'record' === $ri_observe_mode && count( $ri_observe_args ) === 1 ) || ( 'compare' === $ri_observe_mode && ( count( $ri_observe_args ) === 2 || ( count( $ri_observe_args ) === 3 && $ri_observe_args[2] === 'cold-assets' ) ) ) ) ) { WP_CLI::error( 'Use record or compare FILE [cold-assets].' ); }
$args = array( 'preflight' );
require __DIR__ . '/install-rail-input.php';
if ( 'ready' !== $state || ! $matches( $current, $old_content ) ) { WP_CLI::error( 'Observation requires the exact original Rail page; nothing changed.' ); }
$ri_observe_dir = getenv( 'WWM_BACKUP_DIR' ); $ri_observe_private = $ri_observe_dir ? realpath( $ri_observe_dir ) : false; $ri_observe_webroot = realpath( ABSPATH );
if ( ! $ri_observe_private || '/' !== $ri_observe_dir[0] || ! is_dir( $ri_observe_private ) || ! is_writable( $ri_observe_private ) || ! $ri_observe_webroot || $ri_observe_private === $ri_observe_webroot || 0 === strpos( $ri_observe_private . '/', rtrim( $ri_observe_webroot, '/' ) . '/' ) ) { WP_CLI::error( 'Observation requires an existing writable WWM_BACKUP_DIR outside the web root.' ); }
$ri_observe_report = static function ( $label, $record ) {
	$values = $record['meta']['_uag_page_assets'] ?? array(); $exists = array_key_exists( 0, $values ); $value = $values[0] ?? null;
	WP_CLI::log( 'Rail observation ' . $label . ' content_sha256=' . hash( 'sha256', $record['post']['post_content'] ) . ' full_state_sha256=' . hash( 'sha256', wp_json_encode( $record ) ) . ' _uag_page_assets[0] type=' . ( $exists ? gettype( $value ) : 'absent' ) . ' bytes=' . ( is_string( $value ) ? strlen( $value ) : '-' ) . ' byte_sha256=' . ( is_string( $value ) ? hash( 'sha256', $value ) : '-' ) . ' serialize_sha256=' . ( $exists ? hash( 'sha256', serialize( $value ) ) : '-' ) );
};
if ( 'record' === $ri_observe_mode ) {
	$ri_observe_file = $ri_observe_private . '/rail-input-observe-' . gmdate( 'Ymd-His' ) . '-' . bin2hex( random_bytes( 4 ) ) . '.json';
	$ri_observe_record = array( 'bundle' => 'rail-input-observe-v1', 'home' => $home, 'created' => gmdate( 'c' ), 'payloads' => $manifest, 'state' => $current, 'state_sha256' => hash( 'sha256', wp_json_encode( $current ) ) );
	$ri_observe_json = wp_json_encode( $ri_observe_record );
	if ( false === $ri_observe_json ) { WP_CLI::error( 'Observation encoding failed; WordPress unchanged.' ); }
	$ri_observe_mask = umask( 0077 ); $ri_observe_handle = fopen( $ri_observe_file, 'x' ); umask( $ri_observe_mask );
	if ( ! $ri_observe_handle ) { WP_CLI::error( 'Private observation could not be created; WordPress unchanged.' ); }
	$ri_observe_written = fwrite( $ri_observe_handle, $ri_observe_json ); fclose( $ri_observe_handle );
	if ( $ri_observe_written !== strlen( $ri_observe_json ) || ! chmod( $ri_observe_file, 0600 ) || file_get_contents( $ri_observe_file ) !== $ri_observe_json ) { WP_CLI::error( 'Private observation save/read-back failed; WordPress unchanged.' ); }
	$ri_observe_report( 'record', $current );
	WP_CLI::success( 'Original Rail state recorded read-only. Observation: ' . $ri_observe_file ); return;
}
$ri_observe_path = is_string( $ri_observe_args[1] ) && $ri_observe_args[1] ? realpath( $ri_observe_args[1] ) : false;
if ( ! $ri_observe_path || ! is_file( $ri_observe_path ) || 0 !== strpos( $ri_observe_path, $ri_observe_private . '/' ) || ! preg_match( '/^rail-input-observe-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$/D', basename( $ri_observe_path ) ) || ( fileperms( $ri_observe_path ) & 0777 ) !== 0600 ) { WP_CLI::error( 'Compare requires a private 0600 Rail observation inside WWM_BACKUP_DIR.' ); }
$ri_observe_saved = json_decode( file_get_contents( $ri_observe_path ), true ); $ri_observe_before = $ri_observe_saved['state'] ?? null;
$ri_observe_valid = is_array( $ri_observe_saved ) && ( $ri_observe_saved['bundle'] ?? '' ) === 'rail-input-observe-v1' && ( $ri_observe_saved['home'] ?? '' ) === $home && ( $ri_observe_saved['payloads'] ?? null ) === $manifest && $matches( $ri_observe_before, $old_content ) && hash( 'sha256', wp_json_encode( $ri_observe_before ) ) === ( $ri_observe_saved['state_sha256'] ?? '' );
if ( ! $ri_observe_valid ) { WP_CLI::error( 'Observation invalid, damaged or from another site/page/payload; nothing changed.' ); }
$ri_observe_report( 'before', $ri_observe_before ); $ri_observe_report( 'current', $current );
$ri_observe_equal = $current === $ri_observe_before;
if ( ( $ri_observe_args[2] ?? '' ) === 'cold-assets' ) {
	$ri_observe_without_before = $ri_observe_before; $ri_observe_without_current = $current;
	unset( $ri_observe_without_before['meta']['_uag_page_assets'], $ri_observe_without_current['meta']['_uag_page_assets'] );
	if ( $ri_observe_without_before !== $ri_observe_without_current ) { WP_CLI::error( 'Cold observation changed beyond _uag_page_assets; nothing changed or rebased.' ); }
	WP_CLI::success( $ri_observe_equal ? 'Original Rail state unchanged after the baseline read; nothing written.' : 'Only _uag_page_assets changed while the exact original Rail content stayed unchanged; classified read-only, nothing written or rebased.' ); return;
}
if ( ! $ri_observe_equal ) { WP_CLI::error( 'Original Rail full state is not stable; nothing changed or rebased.' ); }
WP_CLI::success( 'Original Rail full state is exactly stable; nothing written. full_state_sha256=' . hash( 'sha256', wp_json_encode( $current ) ) );
