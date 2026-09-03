<?php
/**
 * Dynamic homepage sections woven around post 7's static Gutenberg content
 * in front-page.php: services grid, testimonials, FAQ, and the CTA form
 * section. Markup reconstructed 2026-09-03 from the static export of this
 * exact site (koval-legal-demo.pages.dev) — real class names, real form
 * field names/nonce action. Data (category cards, testimonial/FAQ posts)
 * comes live from the DB, not from the static snapshot.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function koval_legal_render_services_grid() {
	$categories = array_values( array_filter( koval_legal_catalog_categories(), function ( $c ) {
		return ! empty( $c['show_on_homepage'] );
	} ) );
	$archive    = get_post_type_archive_link( 'service' );
	$num        = 1;
	ob_start();
	?>
	<section class="services" id="services">
		<div class="wrap">
			<div class="section-head">
				<div>
					<div class="eyebrow">Послуги</div>
					<h2>Наші ключові напрями</h2>
					<p>Прозорі ціни, фіксовані строки, договір і чіткі зобов'язання сторін.</p>
				</div>
				<a href="<?php echo esc_url( $archive ); ?>" class="service-link">Усі послуги →</a>
			</div>
			<div class="services-grid">
				<?php foreach ( array_slice( $categories, 0, 6 ) as $cat ) : ?>
					<div class="service-card">
						<span class="service-num"><?php echo esc_html( str_pad( $num++, 2, '0', STR_PAD_LEFT ) ); ?></span>
						<h3><a href="<?php echo esc_url( $archive . '#group-' . $cat['slug'] ); ?>"><?php echo esc_html( $cat['label'] ); ?></a></h3>
						<p><?php echo esc_html( $cat['description'] ); ?></p>
						<div class="service-meta">
							<span>Кілька послуг у цьому напрямі →</span>
						</div>
						<a href="<?php echo esc_url( $archive . '#group-' . $cat['slug'] ); ?>" class="service-link">Детальніше →</a>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="services-more">
				<a href="<?php echo esc_url( $archive ); ?>" class="btn btn-ghost">Переглянути всі послуги →</a>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function koval_legal_render_testimonials() {
	$testimonials = get_posts( array( 'post_type' => 'testimonial', 'posts_per_page' => 3, 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( empty( $testimonials ) ) {
		return '';
	}
	ob_start();
	?>
	<section class="testimonials">
		<div class="wrap">
			<div class="eyebrow">Відгуки</div>
			<h2>Що кажуть клієнти</h2>
			<div class="test-grid">
				<?php foreach ( $testimonials as $t ) :
					$city = koval_legal_field( 'testimonial_city', $t->ID );
					?>
					<div class="test-card">
						<p>«<?php echo esc_html( $t->post_content ); ?>»</p>
						<div class="test-who">
							<span class="test-name"><?php echo esc_html( $t->post_title ); ?><?php if ( $city ) : ?> <span>· <?php echo esc_html( $city ); ?></span><?php endif; ?></span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function koval_legal_render_faq() {
	$items = get_posts( array( 'post_type' => 'faq_item', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
	if ( empty( $items ) ) {
		return '';
	}
	ob_start();
	?>
	<section class="faq">
		<div class="wrap">
			<div class="eyebrow">Питання</div>
			<h2>Питання щодо послуг</h2>
			<div class="faq-list" id="faqList">
				<?php foreach ( $items as $item ) : ?>
					<div class="faq-item">
						<button class="faq-q"><?php echo esc_html( $item->post_title ); ?><span class="plus">+</span></button>
						<div class="faq-a"><?php echo wp_kses_post( wpautop( $item->post_content ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * The CTA / consultation-form section (id="contact-form") — photo + form
 * side by side. Used site-wide (homepage, Про нас, /poslugy/ catalog, and
 * every service landing) so every page's consultation form looks and
 * behaves identically. $locked_service pre-fills the hidden "service"
 * field on the lead so submissions are traceable to the page they came
 * from; defaults to 'Головна сторінка' to keep every pre-existing caller
 * (front-page.php, page.php, archive-service.php) behaving exactly as
 * before without needing to touch them.
 */
function koval_legal_render_cta_section( $locked_service = 'Головна сторінка' ) {
	ob_start();
	?>
	<section class="cta-section" id="contact-form">
		<div class="wrap">
			<div class="cta-grid">
				<div class="cta-left">
					<div class="eyebrow">Готові розпочати?</div>
					<h2>Перша консультація — безкоштовно</h2>
					<p>Юрист відповість протягом 30 хвилин у робочий час і оцінить вашу ситуацію без зобов'язань.</p>
					<?php
					$koval_cta_photo = function_exists( 'get_field' ) ? get_field( 'cta_photo', 'option' ) : '';
					if ( ! $koval_cta_photo ) {
						$koval_cta_photo = content_url( '/uploads/2026/08/contract.jpg' ); // fallback until set in Налаштування сайту.
					}
					?>
					<div class="cta-photo">
						<img src="<?php echo esc_url( $koval_cta_photo ); ?>" alt="Підготовка документів" loading="eager">
					</div>
					<div class="cta-disclaimer">Заповнюючи форму, ви звертаєтесь до приватної юридичної компанії за консультаційними послугами — не до державного органу.</div>
				</div>
				<div class="form-card">
					<h3>Заявка на консультацію</h3>
					<p>Залишіть контакти — підберемо оптимальний варіант супроводу саме для вашої ситуації.</p>
					<?php koval_legal_consultation_form( $locked_service ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
