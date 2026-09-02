<?php
/**
 * RECOVERY REBUILD (2026-09-02) — simplified stand-in for the CTA section
 * template part called from single-service.php via get_template_part().
 * $args (locked_service, response_text, show_messengers) come from the
 * caller's third get_template_part() argument.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$locked_service  = $args['locked_service'] ?? '';
$response_text   = $args['response_text'] ?? '';
$show_messengers = $args['show_messengers'] ?? false;
?>
<section class="cta-section">
	<div class="wrap">
		<h2>Отримати консультацію</h2>
		<?php if ( $response_text ) : ?><p><?php echo esc_html( $response_text ); ?></p><?php endif; ?>
		<?php echo do_shortcode( '[koval_contact_form]' ); ?>
		<?php if ( $show_messengers ) : ?>
			<div class="cta-messengers">
				<p>Або напишіть нам напряму:</p>
				<a href="https://t.me/shlyakh_do_mriyi" target="_blank" rel="noopener">Telegram</a>
			</div>
		<?php endif; ?>
	</div>
</section>
