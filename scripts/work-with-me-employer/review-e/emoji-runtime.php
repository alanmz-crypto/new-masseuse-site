<?php
// Bundle E: installed WordPress 7.1.3 frontend emoji output only.
// Preserve the admin editor, email and RSS emoji callbacks.
add_action( 'init', function () {
	if ( is_admin() ) { return; }
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_footer_scripts', '_print_emoji_detection_script', 10 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles', 10 );
	remove_action( 'wp_print_styles', 'print_emoji_styles', 10 );
	remove_action( 'embed_head', 'print_emoji_detection_script', 10 );
	remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles', 10 );
	// Core can register its footer callback when detection runs after init.
	add_action( 'wp_print_footer_scripts', function () {
		remove_action( 'wp_print_footer_scripts', '_print_emoji_detection_script', 10 );
	}, 0 );
}, 0 );
