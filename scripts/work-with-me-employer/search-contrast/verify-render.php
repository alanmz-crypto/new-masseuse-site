<?php
// Read-only exact Additional CSS check; HTML style framing whitespace is ignored.
if ( PHP_SAPI !== 'cli' || count( $argv ) !== 4 || ! in_array( $argv[3], array( 'applied', 'original' ), true ) ) { fwrite( STDERR, "Use verify-render.php HTML SNAPSHOT applied|original\n" ); exit( 1 ); }
$snapshot = json_decode( file_get_contents( $argv[2] ), true );
$which = $argv[3];
if ( ! is_array( $snapshot ) || ( $snapshot['bundle'] ?? '' ) !== 'search-contrast-v1' || ! is_string( $snapshot[$which] ?? null ) || hash( 'sha256', $snapshot[$which] ) !== ( $snapshot[$which . '_sha256'] ?? null ) ) { fwrite( STDERR, "Snapshot CSS is missing or damaged.\n" ); exit( 1 ); }
$html = file_get_contents( $argv[1] );
if ( preg_match_all( '~<style\b[^>]*\bid=["\x27]wp-custom-css["\x27][^>]*>(.*?)</style\s*>~is', $html, $matches ) !== 1 || trim( $matches[1][0], " \t\n\r\f" ) !== trim( $snapshot[$which], " \t\n\r\f" ) ) { fwrite( STDERR, "Served Additional CSS differs from the exact snapshot (outside style framing whitespace).\n" ); exit( 1 ); }
$count = 'applied' === $which ? 1 : 0;
if ( substr_count( $matches[1][0], 'ts-search-contrast:start' ) !== $count || substr_count( $matches[1][0], 'ts-search-contrast:end' ) !== $count || ! str_contains( $html, 'LMT-29905' ) ) { fwrite( STDERR, "Search CSS scope or retained license markup differs.\n" ); exit( 1 ); }
echo 'Exact served ' . $which . ' Additional CSS verified; css_sha256=' . $snapshot[$which . '_sha256'] . ".\n";
