<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The theme doesn't use emoji-as-image fallbacks anywhere, so the detection
// script/inline style/JSON blob WP core prints on every page load (canvas
// feature-testing + a sessionStorage write) has no upside here — every
// browser/OS this site's visitors run supports emoji natively.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', function ( $plugins ) {
	return array_diff( (array) $plugins, array( 'wpemoji' ) );
} );
