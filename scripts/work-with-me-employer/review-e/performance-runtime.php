<?php
// Bundle E: two existing normal Latin fonts, plus verified image attributes.
// No attachment lookup, image conversion, stored-block edit or database write.
add_action( 'wp_head', function () {
	static $printed = false;
	if ( is_admin() || is_feed() || $printed ) { return; }
	$printed = true;
	echo '<link rel="preload" href="https://fonts.gstatic.com/s/hankengrotesk/v12/ieVn2YZDLWuGJpnzaiwFXS9tYtpd59A.woff2" as="font" type="font/woff2" crossorigin="anonymous">' . "\n";
	echo '<link rel="preload" href="https://fonts.gstatic.com/s/newsreader/v26/cY9AfjOCX1hbuyalUrK4397yjA.woff2" as="font" type="font/woff2" crossorigin="anonymous">' . "\n";
}, 1 );

add_filter( 'wp_content_img_tag', function ( $image, $context, $attachment_id ) {
	if ( is_admin() || is_feed() || 'the_content' !== $context || ! class_exists( 'WP_HTML_Tag_Processor' ) ) { return $image; }
	$home = is_front_page();
	$services = is_page( 'services' );
	$work = is_page( 'work-with-me' );
	if ( ! $home && ! $services && ! $work && ! is_page( 'therapeutic-massage' ) ) { return $image; }
	static $images = array(
		'/wp-content/uploads/2026/06/ComfyUI_01040_curves.webp' => array( 512, 399, '/wp-content/uploads/2026/06/ComfyUI_01040_curves-300x234.webp' ),
		'/wp-content/uploads/2026/06/ComfyUI_01044_.webp' => array( 512, 287, '/wp-content/uploads/2026/06/ComfyUI_01044_-300x168.webp' ),
		'/wp-content/uploads/2026/06/00illaCNN512Curve.webp' => array( 512, 460, '/wp-content/uploads/2026/06/00illaCNN512Curve-300x270.webp' ),
		'/wp-content/uploads/2026/10/corporate-group-massage.webp' => array( 512, 416 ),
		'/wp-content/uploads/2026/09/butsaba_homepage.webp' => array( 360, 540 ),
		'/wp-content/uploads/2026/10/thai-massage-therapy-session-cutout.webp' => array( 1254, 1254 ),
	);
	$tag = new WP_HTML_Tag_Processor( $image );
	if ( ! $tag->next_tag( 'IMG' ) ) { return $image; }
	$src = $tag->get_attribute( 'src' );
	$path = is_string( $src ) ? wp_parse_url( $src, PHP_URL_PATH ) : null;
	if ( ! is_string( $path ) || ! isset( $images[ $path ] ) ) { return $image; }
	$meta = $images[ $path ];
	if ( null === $tag->get_attribute( 'width' ) && null === $tag->get_attribute( 'height' ) ) {
		$tag->set_attribute( 'width', (string) $meta[0] );
		$tag->set_attribute( 'height', (string) $meta[1] );
	}
	$missing = function ( $name ) use ( $tag ) { $value = $tag->get_attribute( $name ); return null === $value || ( is_string( $value ) && '' === trim( $value ) ); };
	$home_card = $home && isset( $meta[2] );
	if ( $home_card ) {
		if ( $missing( 'srcset' ) ) { $tag->set_attribute( 'srcset', $meta[2] . ' 300w, ' . $path . ' 512w' ); }
		if ( $missing( 'sizes' ) ) { $tag->set_attribute( 'sizes', 'auto, (max-width: 512px) 100vw, 512px' ); }
		if ( $missing( 'decoding' ) ) { $tag->set_attribute( 'decoding', 'async' ); }
	}
	$lazy = $home_card
		|| ( ( $home || $services ) && '/wp-content/uploads/2026/10/corporate-group-massage.webp' === $path )
		|| ( $work && '/wp-content/uploads/2026/10/thai-massage-therapy-session-cutout.webp' === $path );
	if ( $lazy && $missing( 'loading' ) ) { $tag->set_attribute( 'loading', 'lazy' ); }
	return $tag->get_updated_html();
}, 20, 3 );
