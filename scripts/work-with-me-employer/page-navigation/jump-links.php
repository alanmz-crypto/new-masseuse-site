<?php
// One server-rendered section map for inline links and the native section guide.
// Only rendered IDs/navigation are added; stored page blocks and wording stay intact.
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || is_feed() || ! is_page() || ! in_the_loop() || ! is_main_query() ) { return $content; }
	$page = get_queried_object();
	if ( ! $page || false !== strpos( $content, 'ts-jump' ) ) { return $content; }
	// label, match kind, target text/ID, optional stable ID for a newly linked heading.
	// Legacy h/blk/id/faq plans retain their coverage; exact matches serve the five expanded pages.
	$plans = array(
		'home' => array( array( 'Learn about Thai Solitude', 'h', 'Learn About Thai Solitude' ), array( 'The massage I offer', 'h', 'The Massage I Offer' ), array( 'How I work', 'h', 'How I Work' ) ),
		'about' => array( array( 'The person behind Thai Solitude', 'h', 'The Person Behind' ), array( 'About Thai Solitude', 'h', 'Learn About Thai Solitude' ), array( 'From Chicago to Portland', 'h', 'From Chicago to Portland' ) ),
		'services' => array(
			array( 'Chair massage', 'exact', 'Chair Massage' ),
			array( 'Corporate & group', 'exact', 'Corporate & Group Massage' ),
			array( 'Therapeutic massage', 'exact', 'Therapeutic Massage', 'ts-therapeutic-massage' ),
			array( 'Deep tissue massage', 'exact', 'Deep Tissue Massage' ),
			array( 'Thai massage', 'exact', 'Thai Massage' ),
			array( 'Your session', 'exact', 'Your Session', 'ts-your-session' ),
			array( 'What to expect', 'exact', 'What to Expect During a Massage Session' ),
		),
		'thai-massage' => array(
			array( 'What it is', 'exact', 'What Is Thai Massage?' ),
			array( 'Common reasons', 'exact', 'Common Reasons Clients Choose Thai Massage' ),
			array( 'What to expect', 'exact', 'What To Expect During Your Appointment' ),
			array( 'Questions', 'exact', 'Questions About Thai Massage', 'ts-guide-questions' ),
			array( 'What Research Says', 'exact', 'What the Research Says About Thai Massage', 'ts-guide-research' ),
		),
		'therapeutic-massage' => array(
			array( 'What it is', 'exact', 'What Is Therapeutic Massage?' ),
			array( 'Common reasons', 'exact', 'Common Reasons Clients Choose Therapeutic Massage' ),
			array( 'Who can benefit', 'exact', 'Who Can Benefit From Therapeutic Massage?' ),
			array( 'Questions', 'exact', 'Questions About Therapeutic Massage', 'ts-guide-questions' ),
			array( 'What to expect', 'exact', 'What To Expect During Your Appointment' ),
			array( 'What Research Says', 'exact', 'What the Research Says About Therapeutic Massage', 'ts-guide-research' ),
		),
		'deep-tissue-massage' => array(
			array( 'What it is', 'exact', 'What Is Deep Tissue Massage?' ),
			array( 'Common reasons', 'exact', 'Common Reasons Clients Choose Deep Tissue Massage' ),
			array( 'What to expect', 'exact', 'What To Expect During Your Appointment' ),
			array( 'Questions', 'exact', 'Questions About Deep Tissue Massage', 'ts-guide-questions' ),
			array( 'What Research Says', 'exact', 'What the Research Says About Deep Tissue Massage', 'ts-guide-research' ),
		),
		'chair-massage' => array(
			array( 'What it is', 'exact', 'What Is Chair Massage?' ),
			array( 'Common reasons', 'exact', 'Common Reasons Clients Choose Chair Massage' ),
			array( 'Who can benefit', 'exact', 'Who Can Benefit From Chair Massage?' ),
			array( 'Questions', 'exact', 'Questions About Chair Massage', 'ts-guide-questions' ),
			array( 'What to expect', 'exact', 'What To Expect During Your Appointment' ),
			array( 'What Research Says', 'exact', 'What the Research Says About Chair Massage', 'ts-guide-research' ),
		),
		'contact' => array( array( 'Contact details', 'blk', 'Phone' ), array( 'Corporate quote', 'id', 'corporate-quote' ), array( 'For employers', 'id', 'employers' ) ),
		'privacy-policy' => array( array( 'Online privacy', 'faq', 'Online privacy' ), array( 'Client privacy', 'faq', 'Client privacy' ) ),
		'accessibility' => array( array( 'What has been checked', 'h', 'What has been checked' ), array( 'Known limitations', 'h', 'Known limitations' ), array( 'Feedback', 'h', 'Feedback' ) ),
	);
	if ( ! isset( $plans[ $page->post_name ] ) ) { return $content; }

	// Mask comments and raw-text elements for inspection, preserving byte offsets.
	// Never serialize parsed HTML: only reviewed opening tags receive an ID below.
	$search = preg_replace_callback( '#<!--.*?-->|<(script|style)\b[^>]*>.*?</\1\s*>#is', static function ( $m ) { return str_repeat( ' ', strlen( $m[0] ) ); }, $content );
	if ( null === $search ) { return $content; }
	$normalize = static function ( $text ) {
		return trim( preg_replace( '/[\s\x{00A0}]+/u', ' ', html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
	};
	$attribute = static function ( $tag, $name ) {
		// Consume whole quoted values so text such as title='id="example"' is not an ID.
		preg_match_all( '/\s+([^\s=\/>]+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+)))?/i', $tag, $attributes, PREG_SET_ORDER );
		$values = array();
		foreach ( $attributes as $attribute ) {
			if ( 0 !== strcasecmp( $attribute[1], $name ) ) { continue; }
			$value = ( $attribute[2] ?? '' ) !== '' ? $attribute[2] : ( ( $attribute[3] ?? '' ) !== '' ? $attribute[3] : ( $attribute[4] ?? '' ) );
			$values[] = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}
		return count( $values ) > 1 ? false : ( $values[0] ?? null );
	};
	$has_class = static function ( $tag, $class ) use ( $attribute ) {
		$value = $attribute( $tag, 'class' );
		return is_string( $value ) && in_array( $class, preg_split( '/\s+/', trim( $value ) ), true );
	};
	$tag_pattern = '<[a-z][a-z0-9:-]*\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>';
	preg_match_all( '#' . $tag_pattern . '#i', $search, $tags, PREG_OFFSET_CAPTURE );
	$ids = array();
	foreach ( $tags[0] as $tag ) {
		$id = $attribute( $tag[0], 'id' );
		if ( is_string( $id ) && '' !== $id ) { $ids[ $id ][] = $tag; }
	}
	preg_match_all( '#(<(h[1-6])\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>)(.*?)</\2\s*>#is', $search, $headings, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
	$resolved = array();
	foreach ( $plans[ $page->post_name ] as $item ) {
		list( $label, $kind, $match ) = $item;
		$candidates = array();
		if ( 'exact' === $kind || 'h' === $kind ) {
			foreach ( $headings as $heading ) {
				$text = $normalize( $heading[3][0] );
				if ( 'exact' === $kind ? $text === $match : 0 === strncasecmp( $text, $match, strlen( $match ) ) ) { $candidates[] = $heading[1]; }
			}
		} elseif ( 'id' === $kind ) {
			$candidates = $ids[ $match ] ?? array();
		} elseif ( 'blk' === $kind ) {
			foreach ( $tags[0] as $tag ) {
				$after = substr( $search, $tag[1] + strlen( $tag[0] ), 900 );
				// Preserve the legacy Contact target: the first group before Phone.
				// WordPress may render nested group wrappers around the same card;
				// those are not competing destinations and must not remove this link.
				if ( preg_match( '/^<div\b/i', $tag[0] ) && $has_class( $tag[0], 'wp-block-group' ) && false !== stripos( $after, $match ) ) { $candidates[] = $tag; break; }
			}
		} elseif ( 'faq' === $kind ) {
			$items = array_values( array_filter( $tags[0], static function ( $tag ) use ( $has_class ) { return $has_class( $tag[0], 'uagb-faq-item' ); } ) );
			foreach ( $items as $index => $tag ) {
				$start = $tag[1] + strlen( $tag[0] );
				$end = isset( $items[ $index + 1 ] ) ? $items[ $index + 1 ][1] : strlen( $search );
				$chunk = substr( $search, $start, $end - $start );
				preg_match_all( '#(<([a-z][a-z0-9]*)\b(?=[^>]*\sclass\s*=\s*["\'][^"\']*\buagb-question\b)[^>]*>)(.*?)</\2\s*>#is', $chunk, $questions, PREG_SET_ORDER );
				foreach ( $questions as $question ) {
					if ( $has_class( $question[1], 'uagb-question' ) && $normalize( $question[3] ) === $match ) { $candidates[] = $tag; break; }
				}
			}
		}
		// A changed or ambiguous heading must not silently link to a different section.
		if ( 1 !== count( $candidates ) ) { continue; }
		$tag = $candidates[0];
		if ( isset( $resolved[ $tag[1] ] ) ) { continue; }
		$id = $attribute( $tag[0], 'id' );
		if ( false === $id || ( is_string( $id ) && ( '' === $id || count( $ids[ $id ] ?? array() ) !== 1 ) ) ) { continue; }
		$resolved[ $tag[1] ] = array( 'label' => $label, 'kind' => $kind, 'tag' => $tag[0], 'id' => $id, 'base' => $item[3] ?? ( 'ts-' . sanitize_title( $label ) ) );
	}
	if ( count( $resolved ) < 2 ) { return $content; }
	ksort( $resolved, SORT_NUMERIC );
	$links = array();
	$edits = array();
	$has_faq = false;
	foreach ( $resolved as $offset => $entry ) {
		$id = $entry['id'];
		if ( null === $id ) {
			$id = $entry['base'];
			$suffix = 2;
			while ( isset( $ids[ $id ] ) ) { $id = $entry['base'] . '-' . $suffix++; }
			$ids[ $id ] = array( array( $entry['tag'], $offset ) );
			$edits[ $offset ] = preg_replace( '/^<([a-z0-9]+)/i', '<$1 id="' . esc_attr( $id ) . '"', $entry['tag'], 1 );
		}
		$links[] = '<li><a href="#' . esc_attr( rawurlencode( $id ) ) . '">' . esc_html( $entry['label'] ) . '</a></li>';
		$has_faq = $has_faq || 'faq' === $entry['kind'];
	}
	// Reverse offsets keep every original byte outside the added ID attributes intact.
	krsort( $edits, SORT_NUMERIC );
	foreach ( $edits as $offset => $tag ) { $content = substr_replace( $content, $tag, $offset, strlen( $resolved[ $offset ]['tag'] ) ); }
	if ( $has_faq ) {
		// Preserve the existing Privacy Policy accordion-opening behavior.
		add_action( 'wp_footer', function () {
			echo '<script>document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest(".ts-jump a");if(!a){return;}var t=document.getElementById(decodeURIComponent(a.getAttribute("href").slice(1)));if(t&&t.classList.contains("uagb-faq-item")&&!t.classList.contains("uagb-faq-item-active")){var b=t.querySelector(".uagb-faq-questions-button");if(b){setTimeout(function(){b.click();},0);setTimeout(function(){t.scrollIntoView();},700);}}});</script>';
		}, 99 );
	}
	return '<nav class="ts-jump" aria-label="On this page"><ul class="ts-jump__list">' . implode( '', $links ) . '</ul></nav>' . $content;
}, 20 );
