<?php
// Discreet "jump" links (2 to 4) directly under the header of every page that has sections (accessibility; Ryan 2026-10-18). Remove this snippet to take them away.
// Entries: label, kind, match. kind h = a heading whose text STARTS with match; cls = first element whose class contains match; blk = first wp-block-group that mentions match;
// id = an element that already has this id; faq = the accordion item whose title is match.
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || is_feed() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$page = get_queried_object();
	if ( ! $page || false !== strpos( $content, 'ts-jump' ) ) {
		return $content;
	}
	$plans = array(
		'home'                => array( array( 'Learn about Thai Solitude', 'h', 'Learn About Thai Solitude' ), array( 'The massage I offer', 'h', 'The Massage I Offer' ), array( 'How I work', 'h', 'How I Work' ) ),
		'about'               => array( array( 'The person behind Thai Solitude', 'h', 'The Person Behind' ), array( 'About Thai Solitude', 'h', 'Learn About Thai Solitude' ), array( 'From Chicago to Portland', 'h', 'From Chicago to Portland' ) ),
		'services'            => array( array( 'Corporate & group', 'h', 'Corporate' ), array( 'Chair massage', 'h', 'Chair Massage' ), array( 'Thai massage', 'h', 'Thai Massage' ), array( 'What to expect', 'h', 'What to Expect' ) ),
		'thai-massage'        => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'What to expect', 'h', 'What To Expect' ) ),
		'therapeutic-massage' => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'Who can benefit', 'h', 'Who Can Benefit' ), array( 'What to expect', 'h', 'What To Expect' ) ),
		'deep-tissue-massage' => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'What to expect', 'h', 'What To Expect' ) ),
		'chair-massage'       => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'Who can benefit', 'h', 'Who Can Benefit' ), array( 'What to expect', 'h', 'What To Expect' ) ),
		'contact'             => array( array( 'Contact details', 'blk', 'Phone' ), array( 'Corporate quote', 'id', 'corporate-quote' ), array( 'For employers', 'id', 'employers' ) ),
		'privacy-policy'      => array( array( 'Online privacy', 'faq', 'Online privacy' ), array( 'Client privacy', 'faq', 'Client privacy' ) ),
		'accessibility'       => array( array( 'What has been checked', 'h', 'What has been checked' ), array( 'Known limitations', 'h', 'Known limitations' ), array( 'Feedback', 'h', 'Feedback' ) ),
	);
	if ( ! isset( $plans[ $page->post_name ] ) ) {
		return $content;
	}
	$links    = array();
	$has_faq  = false;
	foreach ( array_slice( $plans[ $page->post_name ], 0, 4 ) as $item ) {
		list( $label, $kind, $match ) = $item;
		$q = preg_quote( $match, '#' );
		if ( 'h' === $kind ) {
			$pattern = '#(<h[1-6]\b[^>]*>)(?=\s*(?:<[^>]+>\s*)*' . $q . ')#i';
		} elseif ( 'cls' === $kind ) {
			$pattern = '#(<div\b[^>]*class="[^"]*' . $q . '[^"]*"[^>]*>)#i';
		} elseif ( 'blk' === $kind ) {
			$pattern = '#(<div\b[^>]*class="[^"]*wp-block-group[^"]*"[^>]*>)(?=.{0,900}?' . $q . ')#is';
		} else {
			$pattern = '#(<[a-z0-9]+\b[^>]*\sid="' . $q . '"[^>]*>)#i';
		}
		if ( 'faq' === $kind ) {
			// Step by step (a single regular expression over the long accordion markup can hit PHP's regex limits): find each accordion item and the question title inside it.
			$m      = null;
			$offset = 0;
			while ( preg_match( '#<div\b[^>]*class="[^"]*uagb-faq-item[^"]*"[^>]*>#i', $content, $d, PREG_OFFSET_CAPTURE, $offset ) ) {
				$end  = $d[0][1] + strlen( $d[0][0] );
				$next = preg_match( '#<div\b[^>]*uagb-faq-item#i', $content, $nx, PREG_OFFSET_CAPTURE, $end ) ? $nx[0][1] : strlen( $content );
				if ( preg_match( '#class="uagb-question"[^>]*>\s*' . $q . '#i', substr( $content, $end, $next - $end ) ) ) {
					$m = array( $d[0], $d[0] );
					break;
				}
				$offset = $end;
			}
			if ( ! $m ) {
				continue;
			}
		} elseif ( ! preg_match( $pattern, $content, $m, PREG_OFFSET_CAPTURE ) ) {
			continue;
		}
		$tag = $m[1][0];
		if ( preg_match( '/\sid\s*=\s*["\']([^"\']+)["\']/i', $tag, $im ) ) {
			$id = $im[1];
		} else {
			$id      = 'ts-' . sanitize_title( $label );
			$new_tag = preg_replace( '/^<([a-z0-9]+)/i', '<$1 id="' . esc_attr( $id ) . '"', $tag, 1 );
			$content = substr_replace( $content, $new_tag, $m[1][1], strlen( $tag ) );
		}
		$has_faq = $has_faq || 'faq' === $kind;
		$links[] = '<li><a href="#' . esc_attr( $id ) . '">' . esc_html( $label ) . '</a></li>';
	}
	if ( count( $links ) < 2 ) {
		return $content;
	}
	if ( $has_faq ) {
		// A jump link to an accordion panel also opens it, then scrolls it into view once the other panel has slid shut.
		add_action( 'wp_footer', function () {
			echo '<script>document.addEventListener("click",function(e){var a=e.target.closest&&e.target.closest(".ts-jump a");if(!a){return;}var t=document.getElementById(a.getAttribute("href").slice(1));if(t&&t.classList.contains("uagb-faq-item")&&!t.classList.contains("uagb-faq-item-active")){var b=t.querySelector(".uagb-faq-questions-button");if(b){setTimeout(function(){b.click();},0);setTimeout(function(){t.scrollIntoView();},700);}}});</script>';
		}, 99 );
	}
	return '<nav class="ts-jump" aria-label="On this page"><ul class="ts-jump__list">' . implode( '', $links ) . '</ul></nav>' . $content;
}, 20 );
