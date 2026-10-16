<?php
/** Read-only comparison and public-page checks for the localhost Thai Solitude site. */

if ( 'http://localhost:8090' !== home_url() || 'astra' !== get_stylesheet() ) {
	WP_CLI::error( 'This verifier is for the localhost Thai Solitude practice site only.' );
}

$cli_args = isset( $args ) && is_array( $args ) ? $args : array();
$allow_legacy_content = in_array( 'allow-legacy-content', $cli_args, true );
foreach ( array( 298, 296, 299, 1091, 1107, 301 ) as $id ) {
	$page = get_post( $id );
	$source = __DIR__ . '/page-' . $id . '.html';
	if ( ! $page || 'page' !== $page->post_type || ! is_file( $source ) ) {
		WP_CLI::error( 'Page or packaged output missing for ID ' . $id );
	}
	$current = rtrim( $page->post_content, "\r\n" );
	if ( $allow_legacy_content ) {
		$current = preg_replace( '/,"nonces":\{[^{}]*\}/', '', $current );
		if ( 296 === $id ) {
			$current = preg_replace( '/,"authorName":"[^"]+","authorLink":"http:\/\/localhost:8090\/wp-admin\/profile\.php"/', '', $current );
		}
		if ( in_array( $id, array( 1091, 1107 ), true ) ) {
			$current = preg_replace( '/[ \t]+$/m', '', $current );
		}
	}
	$expected = rtrim( file_get_contents( $source ), "\r\n" );
	if ( $current !== $expected && ( ! $allow_legacy_content || $current !== stripslashes( $expected ) ) ) {
		WP_CLI::error( 'Packaged output differs from localhost page ID ' . $id );
	}
	WP_CLI::log( 'Page ' . $id . ': ' . ( $allow_legacy_content ? 'legacy-normalized' : 'exact' ) . ' content match' );
}

$qa_pages = array(
	296  => 'Home',
	298  => 'About',
	299  => 'Services',
	301  => 'Contact',
	1091 => 'Deep Tissue Massage',
	1107 => 'Therapeutic Massage',
	1160 => 'Thai Massage',
	1165 => 'Work With Me',
);

$license_line = 'Butsaba Dusrai, LMT - Oregon License LMT-29905';
$forbidden_phrases = array(
	'heal',
	'healing',
	'proof',
	'measurable',
	'effective',
	'effectiveness',
	'pain relief',
	'relieve pain',
	'pain management',
	'sciatica',
	'headache',
	'headaches',
	'chronic',
	'adhesions',
	'corrective',
	'root of the restriction',
	'accelerate',
	'restore balance',
	'nervous system',
	'circulation',
	'anxiety',
	'cancer',
	'neurological',
	'disabilities',
	'prevention',
	'lasting',
	'long-term wellness',
	'overall health',
	'real results',
	'studio',
	'our',
	'we',
	'us',
	'registered therapist',
	'registered massage therapist',
	'licensed therapist',
);
$phone_pattern      = '/(?<!\d)(?:\+?1[ .-]*)?(?:\(\d{3}\)[ .-]*|\d{3}[ .-]+)\d{3}[ .-]+\d{4}(?!\d)/';
$site_phone_numbers = array();
$verification_errors = array();
$rendered_page_count = 0;

if ( ! class_exists( 'DOMDocument' ) ) {
	WP_CLI::error( 'PHP DOM support is required to check rendered local pages.' );
}

foreach ( $qa_pages as $id => $label ) {
	$page = get_post( $id );
	if ( ! $page || 'page' !== $page->post_type ) {
		$verification_errors[] = 'Page missing for public-page checks: ' . $label . ' (ID ' . $id . ')';
		continue;
	}

	$permalink = get_permalink( $id );
	$path      = wp_parse_url( $permalink, PHP_URL_PATH );
	$query     = wp_parse_url( $permalink, PHP_URL_QUERY );
	if ( ! is_string( $path ) ) {
		$verification_errors[] = 'Could not determine local page path for ' . $label . ' (ID ' . $id . ')';
		continue;
	}
	$request_url = 'http://wordpress' . ( '' === $path ? '/' : $path );
	if ( is_string( $query ) && '' !== $query ) {
		$request_url .= '?' . $query;
	}
	$response = wp_remote_get(
		$request_url,
		array(
			'headers'     => array( 'Host' => 'localhost:8090' ),
			'timeout'     => 15,
			'redirection' => 0,
		)
	);
	if ( is_wp_error( $response ) ) {
		$verification_errors[] = 'Could not read the rendered local page for ' . $label . ': ' . $response->get_error_message();
		continue;
	}
	$status = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status ) {
		$verification_errors[] = 'Rendered local page returned HTTP ' . $status . ' for ' . $label . ' (ID ' . $id . ')';
		continue;
	}
	$html = wp_remote_retrieve_body( $response );

	$document                = new DOMDocument();
	$previous_libxml_settings = libxml_use_internal_errors( true );
	$loaded                  = $document->loadHTML( $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_libxml_settings );
	if ( ! $loaded ) {
		$verification_errors[] = 'Could not parse the rendered local page for ' . $label . ' (ID ' . $id . ')';
		continue;
	}

	$xpath       = new DOMXPath( $document );
	$noise_nodes = $xpath->query( '//script | //style' );
	foreach ( $noise_nodes as $node ) {
		$node->parentNode->removeChild( $node );
	}
	$body_nodes = $xpath->query( '//body' );
	if ( ! $body_nodes || 0 === $body_nodes->length ) {
		$verification_errors[] = 'Rendered local page has no body for ' . $label . ' (ID ' . $id . ')';
		continue;
	}
	++$rendered_page_count;
	$body_text       = trim( $body_nodes->item( 0 )->textContent );
	$search_surfaces = array( $body_text );
	$title_nodes     = $xpath->query( '//head/title' );
	foreach ( $title_nodes as $node ) {
		$search_surfaces[] = $node->textContent;
	}
	$meta_nodes = $xpath->query( '//head/meta[@content]' );
	foreach ( $meta_nodes as $node ) {
		$search_surfaces[] = $node->getAttribute( 'content' );
	}
	$alt_nodes = $xpath->query( '//body//img[@alt]' );
	foreach ( $alt_nodes as $node ) {
		$search_surfaces[] = $node->getAttribute( 'alt' );
	}

	if ( false === strpos( $body_text, $license_line ) ) {
		$verification_errors[] = 'License line is missing from the rendered ' . $label . ' page (ID ' . $id . ')';
	}

	$surface_text = implode( "\n", $search_surfaces );
	foreach ( $forbidden_phrases as $phrase ) {
		$phrase_pattern = '/(?<![\p{L}\p{N}])' . str_replace( ' ', '\\s+', preg_quote( $phrase, '/' ) ) . '(?![\p{L}\p{N}])/iu';
		if ( preg_match( $phrase_pattern, $surface_text ) ) {
			$verification_errors[] = 'Avoided wording found on ' . $label . ' (ID ' . $id . '): ' . $phrase;
		}
	}

	$has_coming_soon = false;
	$class_nodes     = $xpath->query( '//*[@class]' );
	foreach ( $class_nodes as $node ) {
		$classes = preg_split( '/\s+/', trim( $node->getAttribute( 'class' ) ) );
		if ( in_array( 'ts-coming-soon', $classes, true ) ) {
			$has_coming_soon = true;
			break;
		}
	}
	if ( ! $has_coming_soon ) {
		$verification_errors[] = 'Coming-soon notice is missing from ' . $label . ' (ID ' . $id . ')';
	}

	if ( preg_match_all( $phone_pattern, $surface_text, $phone_matches ) ) {
		foreach ( $phone_matches[0] as $phone_match ) {
			$digits = preg_replace( '/\D+/', '', $phone_match );
			if ( 11 === strlen( $digits ) && '1' === substr( $digits, 0, 1 ) ) {
				$digits = substr( $digits, 1 );
			}
			$site_phone_numbers[ $digits ] = true;
		}
	}

	$link_nodes = $xpath->query( '//body//a[@href]' );
	foreach ( $link_nodes as $node ) {
		$href = $node->getAttribute( 'href' );
		if ( 0 === stripos( $href, 'tel:' ) ) {
			$digits = preg_replace( '/\D+/', '', substr( $href, 4 ) );
			if ( 11 === strlen( $digits ) && '1' === substr( $digits, 0, 1 ) ) {
				$digits = substr( $digits, 1 );
			}
			if ( 10 === strlen( $digits ) ) {
				$site_phone_numbers[ $digits ] = true;
			}
		}
	}

	WP_CLI::log( $label . ': license line, wording, and coming-soon notice checked' );
}


if ( 8 !== $rendered_page_count ) {
	$verification_errors[] = 'Phone number could not be checked across all eight pages; only ' . $rendered_page_count . ' pages were read.';
} elseif ( 1 !== count( $site_phone_numbers ) || ! isset( $site_phone_numbers['3125394328'] ) ) {
	$found_numbers = empty( $site_phone_numbers ) ? 'none' : implode( ', ', array_keys( $site_phone_numbers ) );
	$verification_errors[] = 'The eight pages must use only phone number 312-539-4328; found: ' . $found_numbers;
}

if ( ! empty( $verification_errors ) ) {
	WP_CLI::error( 'Local page checks found ' . count( $verification_errors ) . " issue(s):\n- " . implode( "\n- ", $verification_errors ) );
}

WP_CLI::success( 'All six packaged pages match localhost; all eight rendered pages pass the content checks.' );
