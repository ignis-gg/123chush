<?php
/**
 * RECOVERY REBUILD (2026-09-02) — simplified reconstruction, not the
 * original footer.php (lost). Address/contact block approximated from
 * earlier session context.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$koval_address = get_theme_mod( 'company_address', "м. Київ, вул. Іоанна Павла ІІ, 23/35, під'їзд 1, офіс 1" );
$koval_phone   = get_theme_mod( 'company_phone', '+380 97 192 07 26' );
?>
<footer class="site-footer">
	<div class="wrap footer-grid">
		<div class="footer-about">
			<strong>KOVAL</strong> Legal Group
			<p>Дочірня компанія юридичного об'єднання «Шлях до мрії О.К.»</p>
			<p><?php echo esc_html( $koval_address ); ?></p>
			<p><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $koval_phone ) ); ?>"><?php echo esc_html( $koval_phone ); ?></a></p>
		</div>
		<div class="footer-col">
			<h4>Послуги</h4>
			<?php
			$koval_footer_services = get_posts( array( 'post_type' => 'service', 'posts_per_page' => 5 ) );
			echo '<ul>';
			foreach ( $koval_footer_services as $s ) {
				echo '<li><a href="' . esc_url( get_permalink( $s ) ) . '">' . esc_html( $s->post_title ) . '</a></li>';
			}
			echo '</ul>';
			?>
		</div>
		<div class="footer-col">
			<h4>Компанія</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/pro-nas/' ) ); ?>">Про нас</a></li>
				<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Блог</a></li>
				<li><a href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>">Контакти</a></li>
				<li><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Політика конфіденційності</a></li>
			</ul>
		</div>
	</div>
	<div class="wrap footer-bottom">
		<p>© <?php echo esc_html( date( 'Y' ) ); ?> Koval Legal Group. Усі права захищені.</p>
		<p>KOVAL Legal Group — приватна юридична компанія. Ми не є державним органом і не входимо до структури ДРАЦС, Мін'юсту, МЗС чи МОН України та не видаємо офіційні документи самостійно.</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
