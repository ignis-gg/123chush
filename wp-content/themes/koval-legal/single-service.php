<?php
/**
 * Single "service" — title, description (editor), price + term (ACF).
 *
 * Most services use the simple layout below (narrow content column + a
 * price/duration sidebar). A service can opt into the full high-conversion
 * landing layout instead — enhanced hero (lead + price/duration pills + CTA)
 * and full-width block sections authored directly in the editor — by adding
 * its ID to $koval_rich_services. Same whitelist pattern as page.php's
 * full-width branch for Про нас / Контакти / Ціни.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$koval_rich_services = array(
	9   => ' — юридичний супровід під ключ', // Зміна ПІБ.
	99  => ' — швидко і без черг', // Апостиль на диплом або атестат.
	100 => ' — без походів і повторних відмов', // Дублікат свідоцтва ДРАЦС.
	101 => '', // Легалізація диплома для роботи чи навчання за кордоном.
	102 => ' — без бюрократичних складнощів', // Шлюб з іноземцем в Україні.
	103 => '', // Поновлення актового запису ДРАЦС.
	111 => '', // Апостиль в Мін'юсті.
	112 => '', // Легалізація документів в Мін'юсті.
);

while ( have_posts() ) :
	the_post();
	$price      = koval_legal_field( 'service_price' );
	$duration   = koval_legal_field( 'service_duration' );
	$koval_rich = array_key_exists( get_the_ID(), $koval_rich_services );
	?>
	<main id="main">
		<div class="single-hero">
			<div class="wrap">
				<div class="eyebrow on-dark">Послуга</div>
				<h1><?php echo esc_html( $koval_rich ? get_the_title() . $koval_rich_services[ get_the_ID() ] : get_the_title() ); ?></h1>

				<?php if ( $koval_rich ) : ?>
					<?php if ( has_excerpt() ) : ?>
						<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<div class="hero-ctas">
						<a href="#contact-form" class="btn btn-wine">Дізнатись вартість для мого випадку →</a>
					</div>

					<?php if ( $price || $duration ) : ?>
						<ul class="trust-row">
							<?php if ( $price ) : ?><li>Вартість <?php echo esc_html( $price ); ?></li><?php endif; ?>
							<?php if ( $duration ) : ?><li>Строк <?php echo esc_html( $duration ); ?></li><?php endif; ?>
							<li>Подання у м. Київ</li>
							<li>15+ років досвіду</li>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<?php koval_legal_breadcrumbs(); ?>

		<?php if ( $koval_rich ) : ?>

			<?php
			// Content here is hand-authored, already-block-level HTML (sections,
			// tables, the FAQ accordion markup) — wpautop mangles it (it doesn't
			// recognise <button>/<span> as block-level and inserts stray <p>
			// tags around them), so it's switched off for just this render.
			remove_filter( 'the_content', 'wpautop' );
			the_content();
			add_filter( 'the_content', 'wpautop' );
			?>

		<?php else : ?>

			<div class="single-body">
				<div class="wrap single-grid">
					<div class="single-content">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="single-thumb"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'alt' => koval_legal_thumbnail_alt() ) ); ?></div>
						<?php endif; ?>
						<?php the_content(); ?>
					</div>
					<aside class="single-side">
						<dl>
							<?php if ( $price ) : ?>
								<div><dt>Вартість</dt><dd><?php echo esc_html( $price ); ?></dd></div>
							<?php endif; ?>
							<?php if ( $duration ) : ?>
								<div><dt>Строк виконання</dt><dd><?php echo esc_html( $duration ); ?></dd></div>
							<?php endif; ?>
						</dl>
						<a href="#contact-form" class="btn btn-wine" style="width:100%;justify-content:center;">Отримати консультацію →</a>
						<p class="cta-disclaimer" style="margin-top:18px;">Вартість орієнтовна і фіксується в договорі до початку роботи.</p>
					</aside>
				</div>
			</div>

		<?php endif; ?>

		<?php
		get_template_part( 'template-parts/section', 'cta', array(
			'locked_service' => get_the_title(),
			'response_text'  => $koval_rich ? "Юрист відповість протягом робочого дня і оцінить вашу ситуацію без зобов'язань." : '',
			'show_messengers' => $koval_rich,
		) );
		?>

		<?php if ( $koval_rich ) : ?>
			<div class="sticky-cta">
				<a href="#contact-form" class="btn btn-wine">Отримати консультацію</a>
				<a class="sticky-call" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', get_theme_mod( 'company_phone', '+380 97 192 07 26' ) ) ); ?>" aria-label="Зателефонувати">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				</a>
			</div>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
