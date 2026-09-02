<?php
/**
 * Plugin Name: Tunnel URL Override
 * Description: Allows viewing the site through a temporary tunnel (e.g. loca.lt, ngrok, trycloudflare) without changing the stored siteurl/home used by DDEV.
 */

if ( isset( $_SERVER['HTTP_HOST'] ) ) {
	$host = $_SERVER['HTTP_HOST'];

	if ( preg_match( '/\.(loca\.lt|ngrok-free\.app|ngrok-free\.dev|ngrok\.io|ngrok\.dev|trycloudflare\.com)$/', $host ) ) {
		$tunnel_url = 'https://' . $host;

		add_filter( 'option_home', fn() => $tunnel_url );
		add_filter( 'option_siteurl', fn() => $tunnel_url );

		/**
		 * option_home/option_siteurl only cover URLs generated through
		 * get_option('home'|'siteurl'). Theme/plugin asset URLs go through
		 * content_url()/plugins_url(), which read the WP_CONTENT_URL /
		 * WP_PLUGIN_URL *constants* directly (defined from WP_HOME in
		 * wp-config-ddev.php) and never touch those two filters — so
		 * enqueued CSS/JS and baked-in <img src> from post_content keep
		 * pointing at the unreachable *.ddev.site host.
		 *
		 * Gotcha found 2026-09-02: WP_HOME/WP_SITEURL are NOT reliably
		 * defined on every request path — traffic that reaches PHP-FPM
		 * through DDEV's raw forwarded port (which is what ngrok/tunnel
		 * tools actually connect to, bypassing the traefik-routed
		 * law-firm.ddev.site vhost) hits a request context where those
		 * constants are undefined. So don't gate the rewrite on
		 * defined('WP_HOME') — match the local dev domain by pattern
		 * instead, which works regardless of which path served the request.
		 */
		add_action( 'init', function() use ( $tunnel_url ) {
			ob_start( function( $html ) use ( $tunnel_url ) {
				$html = preg_replace( '#https?://[a-z0-9.-]+\.ddev\.site#i', $tunnel_url, $html );
				return preg_replace( "#(?<=[\"'(])//[a-z0-9.-]+\.ddev\.site#i", substr( $tunnel_url, 8 ), $html );
			} );
		}, 0 );
	}
}
