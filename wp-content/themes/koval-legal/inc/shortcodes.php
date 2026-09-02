<?php
/**
 * Theme shortcodes.
 * RECOVERY REBUILD (2026-09-02) — koval_map and koval_contact_form are
 * reconstructed from session context; koval_legal_consultation_form() and
 * the lead-capture wiring (nonce, koval_lead CPT, wp_mail) referenced below
 * are NOT fully recovered — this is a simplified stand-in form for now.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [koval_contact_form]
 */
function koval_legal_contact_form_shortcode() {
	ob_start();
	?>
	<div class="form-card" id="contact-form">
		<h3>Заявка на консультацію</h3>
		<p>Заповніть форму — юрист зв'яжеться протягом 30 хвилин.</p>
		<?php koval_legal_consultation_form(); ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'koval_contact_form', 'koval_legal_contact_form_shortcode' );

/**
 * Minimal consultation form. The original had nonce verification, a
 * koval_lead CPT entry per submission, and wp_mail() notification — that
 * wiring is not recovered yet, this just renders the fields.
 */
function koval_legal_consultation_form() {
	?>
	<form class="consultation-form" method="post" action="">
		<?php wp_nonce_field( 'koval_lead_submit', 'koval_lead_nonce' ); ?>
		<div class="form-row">
			<label>Ім'я *<input type="text" name="koval_name" placeholder="Як до вас звертатись" required></label>
			<label>Телефон *<input type="tel" name="koval_phone" placeholder="+380" required></label>
		</div>
		<label>Email<input type="email" name="koval_email" placeholder="you@mail.com"></label>
		<label>Коментар<textarea name="koval_comment" placeholder="Коротко опишіть вашу ситуацію"></textarea></label>
		<label class="consent"><input type="checkbox" name="koval_consent" required> Приймаю умови обробки персональних даних згідно з <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Політикою конфіденційності</a></label>
		<button type="submit" class="btn btn-wine">Отримати консультацію →</button>
	</form>
	<?php
}

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
