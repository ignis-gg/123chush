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
				<p class="footer-partner-text">Result Law Company — консультування іноземців в Україні</p>
			</div>
		</div>

		<div class="footer-bottom">
			<div>© <?php echo esc_html( date( 'Y' ) ); ?> Koval Legal Group. Усі права захищені.</div>
		</div>

		<div class="footer-disclaimer">KOVAL Legal Group — приватна юридична компанія, яка надає консультаційні та інформаційні послуги. Ми не є державним органом, не входимо до структури ДРАЦС, Мін'юсту, МЗС чи МОН України та не видаємо офіційні документи самостійно.</div>
	</div>
</footer>

<div class="koval-popup-overlay" id="koval-thanks-popup" hidden>
	<div class="koval-popup" role="dialog" aria-modal="true" aria-labelledby="koval-thanks-title">
		<button type="button" class="koval-popup-close" id="koval-thanks-close" aria-label="Закрити">&times;</button>
		<div class="koval-popup-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
		<h3 id="koval-thanks-title">Дякуємо за вашу заявку!</h3>
		<p>Ми зв'яжемось з вами найближчим часом — зазвичай протягом 30 хвилин у робочий час.</p>
		<button type="button" class="btn btn-wine" id="koval-thanks-ok">Добре</button>
	</div>
</div>

<script src="https://widgets.binotel.com/getcall/widgets/xzcao5s8l0chc86m3rgm.js" async></script>
<script src="https://widgets.binotel.com/chat/widgets/SBkjqYQDD5PrzD5ErAjC.js" async></script>

<?php wp_footer(); ?>
</body>
</html>
