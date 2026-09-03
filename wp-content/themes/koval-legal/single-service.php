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

// ID lists live in inc/service-lists.php — shared with inc/acf-admin-ux.php
// so the "which posts are ACF-driven" answer never drifts between them.
$koval_rich_services   = koval_legal_rich_services();
$koval_pillar_services = koval_legal_pillar_services();

while ( have_posts() ) :
	the_post();
	$price        = koval_legal_field( 'service_price' );
	$duration     = koval_legal_field( 'service_duration' );
	$cta_text     = koval_legal_field( 'service_cta_text' );
	$location     = koval_legal_field( 'service_location' );
	$koval_rich   = array_key_exists( get_the_ID(), $koval_rich_services );
	$koval_pillar = array_key_exists( get_the_ID(), $koval_pillar_services );
	?>
	<main id="main">
		<div class="single-hero">
			<div class="wrap">
				<div class="eyebrow on-dark"><?php echo esc_html( $koval_pillar ? 'Напрям' : 'Послуга' ); ?></div>
				<h1><?php echo esc_html( $koval_rich ? get_the_title() . $koval_rich_services[ get_the_ID() ] : get_the_title() ); ?></h1>

				<?php if ( $koval_rich || $koval_pillar ) : ?>
					<?php if ( has_excerpt() ) : ?>
						<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<?php if ( $koval_rich ) : ?>
						<div class="hero-ctas">
							<a href="#contact-form" class="btn btn-wine"><?php echo esc_html( $cta_text ? $cta_text : 'Дізнатись вартість для мого випадку →' ); ?></a>
						</div>

						<?php if ( $price || $duration ) : ?>
							<ul class="trust-row">
								<?php if ( $price ) : ?><li>Вартість <?php echo esc_html( $price ); ?></li><?php endif; ?>
								<?php if ( $duration ) : ?><li>Строк <?php echo esc_html( $duration ); ?></li><?php endif; ?>
								<li><?php echo esc_html( $location ? $location : 'Подання у м. Київ' ); ?></li>
								<li>15+ років досвіду</li>
							</ul>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<?php koval_legal_breadcrumbs(); ?>

		<?php if ( $koval_rich || $koval_pillar ) : ?>

			<?php
			// Content prefers ACF fields (inc/acf-render.php) an editor can
			// fill in through the admin without touching HTML; posts not yet
			// migrated fall back to the old hand-authored HTML in
			// post_content so nothing breaks mid-migration.
			if ( $koval_pillar && function_exists( 'koval_legal_render_pillar_acf' ) ) {
					$koval_acf_html = koval_legal_render_pillar_acf( get_the_ID() );
				} else {
					$koval_acf_html = function_exists( 'koval_legal_render_service_acf' ) ? koval_legal_render_service_acf( get_the_ID() ) : '';
				}
			echo '<div class="svc-body">';
			if ( $koval_acf_html ) {
				// the_content() normally runs post_content through
				// wptexturize() (straight ' " -> curly “smart” quotes) —
				// apply it here too so ACF-sourced text matches typographically.
				echo wptexturize( $koval_acf_html );
			} else {
				// Hand-authored HTML (sections, tables, FAQ accordion markup)
				// — wpautop mangles it (doesn't recognise <button>/<span> as
				// block-level, inserts stray <p> tags), so it's off just here.
				remove_filter( 'the_content', 'wpautop' );
				the_content();
				add_filter( 'the_content', 'wpautop' );
			}
			echo '</div>';
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

		<?php echo koval_legal_render_cta_section( get_the_title() ); ?>

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
