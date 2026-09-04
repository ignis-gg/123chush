<?php
/**
 * GTM / Meta Pixel — code is wired up now, IDs aren't known yet. Both stay
 * silent (no snippet output at all, not even an empty container) until a
 * real ID is entered on the "Налаштування сайту" options page (Аналітика
 * tab). When the client gets real IDs, someone just pastes them in
 * wp-admin — nothing in this file needs to change.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function koval_analytics_gtm_head() {
	$gtm_id = function_exists( 'get_field' ) ? trim( (string) get_field( 'gtm_id', 'option' ) ) : '';
	if ( ! $gtm_id ) {
		return;
	}
	?>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');</script>
	<?php
}
add_action( 'wp_head', 'koval_analytics_gtm_head', 1 );

function koval_analytics_gtm_body_open() {
	$gtm_id = function_exists( 'get_field' ) ? trim( (string) get_field( 'gtm_id', 'option' ) ) : '';
	if ( ! $gtm_id ) {
		return;
	}
	?>
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<?php
}
add_action( 'wp_body_open', 'koval_analytics_gtm_body_open' );

function koval_analytics_meta_pixel() {
	$pixel_id = function_exists( 'get_field' ) ? trim( (string) get_field( 'meta_pixel_id', 'option' ) ) : '';
	if ( ! $pixel_id ) {
		return;
	}
	?>
	<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?php echo esc_js( $pixel_id ); ?>');fbq('track','PageView');</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $pixel_id ); ?>&ev=PageView&noscript=1"></noscript>
	<?php
}
add_action( 'wp_head', 'koval_analytics_meta_pixel' );
