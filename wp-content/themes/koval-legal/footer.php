<?php
/**
 * RECOVERY REBUILD (2026-09-03, corrected) — this replaces an earlier,
 * incorrect reconstruction written before the static-export reference was
 * found. That first version used <footer class="site-footer"> instead of
 * the real <footer id="site-footer">, so style.css's #site-footer dark-
 * background rule never matched and the footer rendered white — caught by
 * the user visually testing the live tunnel. Markup below is copied from
 * the static export of this exact site.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$koval_address = get_theme_mod( 'company_address', "м. Київ, вул. Іоанна Павла ІІ, 23/35, під'їзд 1, офіс 1" );
$koval_phone   = get_theme_mod( 'company_phone', '+380 97 192 07 26' );
$koval_email   = get_theme_mod( 'company_email', 'callcenter.via.klg@gmail.com' );

// Соцмережі/месенджери — раніше прибиті в HTML, тепер "Налаштування сайту"
// (inc/acf-fields.php group_koval_site_settings); фолбек = поточні значення.
$koval_telegram  = function_exists( 'get_field' ) ? get_field( 'telegram_url', 'option' ) : '';
$koval_telegram  = $koval_telegram ?: 'https://t.me/shlyakh_do_mriyi';
$koval_whatsapp  = function_exists( 'get_field' ) ? get_field( 'whatsapp_url', 'option' ) : '';
$koval_whatsapp  = $koval_whatsapp ?: 'https://wa.me/380971920726';
$koval_facebook  = function_exists( 'get_field' ) ? get_field( 'facebook_url', 'option' ) : '';
$koval_facebook  = $koval_facebook ?: 'https://www.facebook.com/kovallegalgroup';
$koval_instagram = function_exists( 'get_field' ) ? get_field( 'instagram_url', 'option' ) : '';
$koval_instagram = $koval_instagram ?: 'https://www.instagram.com/kovallegalgroup';
$koval_youtube   = function_exists( 'get_field' ) ? get_field( 'youtube_url', 'option' ) : '';
$koval_youtube   = $koval_youtube ?: 'https://www.youtube.com/@kovallegalgroup';
?>
<footer id="site-footer">
	<div class="wrap">
		<div class="footer-top">
			<div>
				<div class="footer-logo"><span class="lg-koval">KOVAL</span> <span class="lg-legal">Legal Group</span></div>
				<p>Дочірня компанія юридичного об'єднання «Шлях до мрії О.К.»</p>
				<div class="footer-contact">
					<div class="footer-contact-row">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.3"/></svg>
						<span><?php echo esc_html( $koval_address ); ?></span>
					</div>
					<div class="footer-contact-row">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
						<span>Пн–Пт, 09:00–18:00</span>
					</div>
					<div class="footer-contact-row">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2z"/></svg>
						<span><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $koval_phone ) ); ?>"><?php echo esc_html( $koval_phone ); ?></a></span>
					</div>
					<div class="footer-contact-row">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m3 6 9 6 9-6"/></svg>
						<span><a href="mailto:<?php echo esc_attr( $koval_email ); ?>"><?php echo esc_html( $koval_email ); ?></a></span>
					</div>
				</div>
			</div>

			<div class="footer-col">
				<h5>Послуги</h5>
				<?php
				$koval_footer_services = get_posts( array( 'post_type' => 'service', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'ASC' ) );
				foreach ( $koval_footer_services as $s ) :
					?>
					<a href="<?php echo esc_url( get_permalink( $s ) ); ?>" class="footer-nav-link"><?php echo esc_html( $s->post_title ); ?></a>
				<?php endforeach; ?>
			</div>

			<div class="footer-col">
				<h5>Компанія</h5>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/pro-nas/' ) ); ?>">Про нас</a></li>
					<li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">Блог</a></li>
					<li><a href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>">Контакти</a></li>
					<li><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Політика конфіденційності</a></li>
				</ul>
			</div>

			<div class="footer-col">
				<h5>Партнери</h5>
				<p class="footer-partner-text">Result Law Company — супровід іноземців в Україні</p>
				<h6>Написати в месенджер</h6>
				<div class="icon-row">
					<a href="<?php echo esc_url( $koval_telegram ); ?>" class="icon-circle" aria-label="Telegram"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M21.9 4.3 18.6 20c-.2 1-1 1.3-1.9.8l-5.3-3.9-2.6 2.5c-.3.3-.5.5-1 .5l.4-5.4L18 6.4c.5-.4-.1-.6-.7-.2L6.5 13.2l-5.3-1.7c-1.1-.4-1.1-1.1.3-1.6L20.6 3.1c1-.3 1.8.2 1.3 1.2z"/></svg></a>
					<a href="<?php echo esc_url( $koval_whatsapp ); ?>" class="icon-circle" aria-label="WhatsApp"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.39 1.26 4.81L2 22l5.44-1.36a9.9 9.9 0 0 0 4.6 1.14h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.14c-.24.68-1.19 1.25-1.95 1.4-.52.11-1.2.2-3.5-.75-2.94-1.22-4.83-4.2-4.98-4.4-.14-.19-1.19-1.58-1.19-3.02 0-1.43.75-2.14 1.02-2.43.24-.27.55-.34.73-.34h.53c.17 0 .4-.03.62.48.24.55.8 1.9.87 2.04.07.14.11.31.02.5-.09.19-.14.31-.28.47-.14.16-.29.36-.42.48-.14.13-.28.28-.12.55.16.27.71 1.17 1.53 1.9 1.05.94 1.94 1.24 2.21 1.38.27.14.43.12.6-.07.16-.19.7-.81.88-1.09.18-.27.36-.23.6-.14.24.09 1.55.73 1.82.87.27.14.45.2.51.32.07.11.07.65-.17 1.33z"/></svg></a>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $koval_phone ) ); ?>" class="icon-circle" aria-label="Viber"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.9 2 3 5.3 3 9.6c0 2.6 1.5 4.9 3.9 6.3-.1.9-.5 2.4-1.4 3.6-.1.2 0 .4.2.4 1.9-.3 3.4-1.2 4.3-1.9.6.1 1.3.2 2 .2 5.1 0 9-3.3 9-7.6S17.1 2 12 2zm3.9 11.1c-.2.4-.9.8-1.3.9-.3.1-.7.1-2.1-.4-1.8-.7-3-2.4-3.1-2.5-.1-.1-.7-1-.7-1.8 0-.9.5-1.3.6-1.5.2-.2.4-.2.5-.2h.4c.1 0 .3 0 .4.3.2.4.5 1.2.6 1.3.1.1.1.2 0 .3-.1.1-.1.2-.2.3-.1.1-.2.2-.3.3-.1.1-.2.2-.1.4.1.2.5.8 1.1 1.3.7.6 1.3.8 1.5.9.2.1.3.1.4-.1.1-.1.4-.5.6-.7.1-.2.3-.1.4-.1.2.1 1 .5 1.2.6.2.1.3.1.3.2 0 .1 0 .4-.2.7z"/></svg></a>
				</div>
				<h6>Ми в соцмережах</h6>
				<div class="icon-row">
					<a href="<?php echo esc_url( $koval_facebook ); ?>" class="icon-circle" aria-label="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-8h2.7l.4-3.2h-3.1V7.7c0-.9.3-1.6 1.6-1.6h1.7V3.2C16.5 3.1 15.4 3 14.2 3c-2.6 0-4.4 1.6-4.4 4.5v2.3H7v3.2h2.8v8h3.7z"/></svg></a>
					<a href="<?php echo esc_url( $koval_instagram ); ?>" class="icon-circle" aria-label="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg></a>
					<a href="<?php echo esc_url( $koval_youtube ); ?>" class="icon-circle" aria-label="YouTube"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9.5v5l4.5-2.5z" fill="currentColor" stroke="none"/></svg></a>
				</div>
			</div>
		</div>

		<div class="footer-bottom">
			<div>© <?php echo esc_html( date( 'Y' ) ); ?> Koval Legal Group. Усі права захищені.</div>
			<div>Зв'язатись інакше: <?php echo esc_html( $koval_phone ); ?></div>
		</div>

		<div class="footer-disclaimer">KOVAL Legal Group — приватна юридична компанія, яка надає консультаційні та представницькі послуги. Ми не є державним органом, не входимо до структури ДРАЦС, Мін'юсту, МЗС чи МОН України та не видаємо офіційні документи самостійно.</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
