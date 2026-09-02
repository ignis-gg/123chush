<?php
/**
 * Theme shortcodes.
 * RECOVERY REBUILD — koval_map, koval_contact_form, and the consultation
 * form (fields, nonce action, admin-post handler) reconstructed 2026-09-03
 * from the static export of this exact site (koval-legal-demo.pages.dev),
 * which preserved the real rendered form markup and field names even
 * though the PHP source was lost.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [koval_contact_form]
 */
function koval_legal_contact_form_shortcode( $atts = array() ) {
	$atts = shortcode_atts( array( 'service' => '' ), $atts );
	ob_start();
	?>
	<div class="form-card" id="contact-form">
		<h3>Заявка на консультацію</h3>
		<p>Заповніть форму — юрист зв'яжеться протягом 30 хвилин.</p>
		<?php koval_legal_consultation_form( $atts['service'] ); ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'koval_contact_form', 'koval_legal_contact_form_shortcode' );

/**
 * Consultation form. $locked_service pre-fills a hidden "service" field so
 * leads carry which page they came from (homepage vs a specific service).
 */
function koval_legal_consultation_form( $locked_service = '' ) {
	?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="koval_legal_consultation">
		<?php wp_nonce_field( 'koval_legal_contact', 'koval_legal_contact_nonce' ); ?>
		<input type="text" name="website" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;" aria-hidden="true">

		<div class="form-row">
			<div class="field"><label for="koval-name">Ім'я *</label><input id="koval-name" type="text" name="name" placeholder="Як до вас звертатись" required></div>
			<div class="field"><label for="koval-phone">Телефон *</label><input id="koval-phone" type="tel" name="phone" placeholder="+380" required></div>
		</div>
		<div class="form-row form-row-single">
			<div class="field"><label for="koval-email">Email</label><input id="koval-email" type="email" name="email" placeholder="you@mail.com"></div>
			<?php if ( $locked_service ) : ?><input type="hidden" name="service" value="<?php echo esc_attr( $locked_service ); ?>"><?php endif; ?>
		</div>
		<div class="field"><label for="koval-comment">Коментар</label><textarea id="koval-comment" name="comment" placeholder="Коротко опишіть вашу ситуацію"></textarea></div>
		<label class="consent"><input type="checkbox" name="consent" value="1" required> Приймаю умови обробки персональних даних згідно з <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" target="_blank" rel="noopener">Політикою конфіденційності</a> *</label>
		<button type="submit" class="btn btn-wine">Отримати консультацію</button>
	</form>
	<?php
}

/**
 * admin-post.php handler for the form above: nonce check, honeypot check,
 * koval_lead CPT entry, wp_mail() notification (falls back to
 * admin_email if company_email theme_mod isn't set, same as before).
 */
function koval_legal_handle_consultation_submit() {
	if ( ! isset( $_POST['koval_legal_contact_nonce'] ) || ! wp_verify_nonce( $_POST['koval_legal_contact_nonce'], 'koval_legal_contact' ) ) {
		wp_die( 'Security check failed.' );
	}
	$referer = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	if ( ! empty( $_POST['website'] ) ) {
		// Honeypot tripped — silently pretend success.
		wp_safe_redirect( $referer . '#contact-form' );
		exit;
	}

	$name    = sanitize_text_field( $_POST['name'] ?? '' );
	$phone   = sanitize_text_field( $_POST['phone'] ?? '' );
	$email   = sanitize_email( $_POST['email'] ?? '' );
	$comment = sanitize_textarea_field( $_POST['comment'] ?? '' );
	$service = sanitize_text_field( $_POST['service'] ?? '' );

	$lead_id = wp_insert_post( array(
		'post_type'    => 'koval_lead',
		'post_title'   => $name . ' — ' . $phone,
		'post_status'  => 'publish',
		'post_content' => $comment,
	) );
	if ( $lead_id && ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, 'lead_phone', $phone );
		update_post_meta( $lead_id, 'lead_email', $email );
		update_post_meta( $lead_id, 'lead_service', $service );
	}

	$to = get_theme_mod( 'company_email' );
	if ( ! $to ) {
		$to = get_option( 'admin_email' );
	}
	$subject = 'Нова заявка з сайту' . ( $service ? ' — ' . $service : '' );
	$body    = "Ім'я: $name\nТелефон: $phone\nEmail: $email\nПослуга: $service\nКоментар: $comment";
	wp_mail( $to, $subject, $body );

	wp_safe_redirect( add_query_arg( 'koval_sent', '1', $referer ) . '#contact-form' );
	exit;
}
add_action( 'admin_post_koval_legal_consultation', 'koval_legal_handle_consultation_submit' );
add_action( 'admin_post_nopriv_koval_legal_consultation', 'koval_legal_handle_consultation_submit' );

/**
 * [koval_map] — Google Maps embed for the office address. No API key
 * required (uses the public /maps?...&output=embed endpoint).
 */
function koval_legal_map_shortcode() {
	$address = get_theme_mod( 'company_address', "м. Київ, вул. Іоанна Павла ІІ, 23/35, під'їзд 1, офіс 1" );

	/**
	 * Plain-text geocoding of the address alone landed the pin roughly
	 * 1-2 blocks off the real office — and worse, at that ambiguous
	 * point Google matched a *different* nearby business entirely
	 * ("Шлях до Мрії") instead of ours. There are multiple Google
	 * Business listings registered around this address (Koval Legal
	 * Group itself is also mismatched on Maps — TZ section 7, needs a
	 * Google Business Profile Manager fix, out of scope for the site).
	 * "Result Law Company" is the client-verified listing that
	 * correctly resolves to the exact office (confirmed 2026-09-02
	 * against a live Google Maps screenshot: 3.7★, 6 reviews, вул.
	 * Іоанна Павла II, 23/35). Querying by that business name + street
	 * pins the same correct spot reliably. $address itself stays clean
	 * for the human-readable text used in the footer/contacts block —
	 * only the map query uses the business name.
	 */
	$src = 'https://www.google.com/maps?q=' . rawurlencode( "Result Law Company, вулиця Іоанна Павла II, 23/35, Київ" ) . '&output=embed&hl=uk';

	ob_start();
	?>
	<div class="map-frame">
		<iframe src="<?php echo esc_url( $src ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e( 'Карта — офіс KOVAL Legal Group', 'koval-legal' ); ?>"></iframe>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'koval_map', 'koval_legal_map_shortcode' );
