<?php
// Reproduce SiteGround's logical/physical uploads distinction on a throwaway.
// This file is not uploaded by the staging push script.
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WP_CLI' ) ) { exit( 1 ); }
if ( untrailingslashit( home_url() ) !== 'http://localhost:8090' || wp_get_environment_type() !== 'local' ) { WP_CLI::error( 'Isolated localhost rehearsal only.' ); }
$uploads = wp_upload_dir( null, false, false );
$logical = ABSPATH . 'wp-content/uploads'; $physical = ABSPATH . 'wp-content/e-rehearsal-uploads';
if ( wp_normalize_path( $uploads['basedir'] ) !== wp_normalize_path( $logical ) ) { WP_CLI::error( 'Unexpected isolated uploads location.' ); }
if ( ! is_link( $logical ) ) {
	if ( file_exists( $physical ) || ! is_dir( $logical ) || ! rename( $logical, $physical ) || ! symlink( 'e-rehearsal-uploads', $logical ) ) { WP_CLI::error( 'Cannot prepare isolated symlinked uploads.' ); }
}
$relative = '2026/11/therapeutic-red-400.webp';
if ( realpath( $logical ) !== realpath( $physical ) || _wp_relative_upload_path( $logical . '/' . $relative ) !== $relative || ! str_starts_with( _wp_relative_upload_path( $physical . '/' . $relative ), '/' ) ) { WP_CLI::error( 'Isolated symlink scenario does not match the observed host distinction.' ); }
WP_CLI::success( 'Isolated symlinked uploads ready; WordPress logical path stays relative, physical path does not.' );
