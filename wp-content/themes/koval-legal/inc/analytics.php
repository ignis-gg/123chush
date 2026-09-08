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

/**
 * Direct "Тег Google" gtag.js install — separate from koval_analytics_gtm_head()
 * above. 2026-09-08: found via live network-request check that loading a
 * GT- ("Google tag") ID through the classic gtm.js/dataLayer.push loader
 * (the gtm_id mechanism) loads the script (200) but fires NO collect hit
 * at all — that loader expects a real GTM container with tags/triggers
 * published inside it, which an auto-generated Google tag container
 * doesn't have. The gtag.js loader below is what Google's own "Тег
 * Google" install page actually prescribes, and is what actually sends
 * data (confirmed live: google-analytics.com/g/collect fires after this).
 */
function koval_analytics_google_tag() {
	$tag_id = function_exists( 'get_field' ) ? trim( (string) get_field( 'google_tag_id', 'option' ) ) : '';
	if ( ! $tag_id ) {
		return;
	}
	?>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $tag_id ); ?>"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', '<?php echo esc_js( $tag_id ); ?>');
	</script>
	<?php
}
add_action( 'wp_head', 'koval_analytics_google_tag' );

/**
 * Server-validated Lead signal for koval_analytics_ga4_events() below —
 * checked/consumed exactly once per request (the transient set in
 * shortcodes.php is single-use). Factored out as its own function (rather
 * than inlined) to match the koval-group.pp.ua fork's analytics.php,
 * where it's shared with a Meta Pixel Lead event too — this project has
 * no Meta Pixel event tracking yet, but keeping the same shape makes it a
 * one-line add if that ever changes.
 */
function koval_analytics_consume_lead_token() {
	static $result = null;
	if ( null !== $result ) {
		return $result;
	}
	$result = false;
	if ( isset( $_GET['koval_sent'], $_GET['lt'] ) && '1' === $_GET['koval_sent'] ) {
		$token         = sanitize_text_field( wp_unslash( $_GET['lt'] ) );
		$transient_key = 'koval_lead_' . $token;
		if ( get_transient( $transient_key ) ) {
			delete_transient( $transient_key );
			$result = true;
		}
	}
	return $result;
}

/**
 * GA4 conversion tracking — two events, both markable as "key events" in
 * GA4 (Admin → Events) so they show up as conversions:
 *
 * - contact_click: delegated click listener, catches ANY link anywhere on
 *   the site whose href looks like a phone/Viber/WhatsApp/Telegram
 *   contact method (footer icons, header CTA, the floating call button —
 *   one listener covers all of them, including ones added later, no
 *   per-button wiring needed). `method` comes from the aria-label when
 *   present (so the Viber icon, which happens to use a tel: href, is
 *   labeled correctly instead of as "phone"), falling back to a pattern
 *   match on the href otherwise.
 * - generate_lead: fired only on the server-validated, single-use `lt`
 *   token set by koval_legal_handle_consultation_submit() (shortcodes.php)
 *   right after a real, nonce+honeypot-validated form submit — see
 *   koval_analytics_consume_lead_token() above. `koval_sent=1` alone is
 *   not proof of a fresh submission (a refresh/bookmark/cache replay
 *   could carry it), so this alone gates the event.
 */
function koval_analytics_ga4_events() {
	$tag_id = function_exists( 'get_field' ) ? trim( (string) get_field( 'google_tag_id', 'option' ) ) : '';
	if ( ! $tag_id ) {
		return;
	}

	$fire_lead = koval_analytics_consume_lead_token();
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		document.addEventListener('click', function (e) {
			if (typeof gtag !== 'function') return;
			var link = e.target.closest && e.target.closest('a[href]');
			if (!link) return;
			var href = link.getAttribute('href') || '';
			var label = (link.getAttribute('aria-label') || '').toLowerCase();
			var method = null;
			if (label.indexOf('viber') !== -1) {
				method = 'viber';
			} else if (label.indexOf('telegram') !== -1) {
				method = 'telegram';
			} else if (label.indexOf('whatsapp') !== -1) {
				method = 'whatsapp';
			} else if (/^tel:/i.test(href)) {
				method = 'phone';
			} else if (/wa\.me|whatsapp/i.test(href)) {
				method = 'whatsapp';
			} else if (/t\.me|telegram/i.test(href)) {
				method = 'telegram';
			}
			if (!method) return;
			gtag('event', 'contact_click', { method: method, link_url: href });
		});
		<?php if ( $fire_lead ) : ?>
		if (typeof gtag === 'function') {
			gtag('event', 'generate_lead');
		}
		<?php endif; ?>
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'koval_analytics_ga4_events' );

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
