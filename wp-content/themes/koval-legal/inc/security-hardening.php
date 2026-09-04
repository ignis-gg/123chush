<?php
/**
 * Code-level security hardening that doesn't depend on the final host or a
 * security plugin (that's Stage C, tied to the real cPanel/LiteSpeed setup).
 * Deliberately no CSP here — too easy to break something invisibly without
 * a browser to check against; that's flagged for a post-migration pass.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 4.1 — XML-RPC isn't used by this site (no Jetpack, no remote-publishing
// clients). Note: the commonly-cited `add_filter('xmlrpc_enabled',
// '__return_false')` alone does NOT actually block the endpoint — it only
// gates pingback-specific methods (confirmed by testing: a plain
// demo.sayHello call still succeeded with only that filter in place).
// The real fix is refusing the request itself before WP even routes it.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_action( 'init', function () {
	if ( false !== stripos( $_SERVER['REQUEST_URI'] ?? '', 'xmlrpc.php' ) ) {
		status_header( 403 );
		exit( 'XML-RPC is disabled on this site.' );
	}
}, 0 );

// 4.2 — REST API: /wp/v2/users lets anyone enumerate valid usernames/IDs
// without authenticating. Not needed by this theme's frontend (no author
// archives are linked/used), so block it for logged-out requests.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
} );

// 4.3 — baseline security headers. PHP-level (not .htaccess) on purpose:
// this project runs under nginx in ddev and Apache/LiteSpeed on the real
// VPS — a header() call works identically on both, an .htaccess rule
// would silently do nothing here and only start working after migration.
add_action( 'send_headers', function () {
	if ( is_admin() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
} );

// 4.4 — stop advertising the exact WP version in <head> and in the REST/RSS
// generator tag (a version-scanner's first move).
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
