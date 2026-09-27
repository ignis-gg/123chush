<?php
/**
 * Google Ads landing (see inc/ads-landing.php). One responsive markup for
 * mobile and desktop — layout differences are CSS only
 * (assets/css/ads-landing.css). Every [data-al-call] element opens the
 * Binotel "Передзвоніть мені" window (assets/js/ads-landing.js).
 *
 * Copy that is identical on every landing is written here; it follows the
 * approved TZ (mocup/TZ_dyzajn_shablon_ads_landing.md) — consultation-only
 * wording, no "how to do it yourself", "фахівець" (a sales manager calls
 * back, not a lawyer), no promises in minutes.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Repeaters are read defensively: a repeater once got saved as a
// var_export() string on post 138 and fataled the page (see
// bin/migrations/2026-09-21-fix-138-corrupted-fields.php) — a bad value
// here just hides the block instead.
$rows = function ( $value ) {
	return is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();
};

$post_id    = get_the_ID();
$delicate   = 'delicate' === get_field( 'ads_variant', $post_id );
$eyebrow    = (string) get_field( 'ads_eyebrow', $post_id );
$lead       = (string) get_field( 'ads_lead', $post_id );
$chip       = (string) get_field( 'ads_chip', $post_id );
$hero_img   = (int) get_field( 'ads_hero_image', $post_id );
$notice     = (string) get_field( 'ads_notice', $post_id );
$situations = $rows( get_field( 'ads_situations', $post_id ) );
$step3      = (string) get_field( 'ads_step3', $post_id );
$steps_img  = (int) get_field( 'ads_steps_image', $post_id );
$reviews    = $rows( get_field( 'ads_reviews', $post_id ) );
$faq        = $rows( get_field( 'ads_faq', $post_id ) );
$final_note = (string) get_field( 'ads_final_note', $post_id );
$related    = get_field( 'ads_related', $post_id );
$related    = is_array( $related ) ? $related : array();

// No exact hours here on purpose: the footer says "Пн–Пт, 09:00–18:00"
// while Binotel's schedule is 9:30–18:30 + Sat — two different numbers on
// one page would undermine trust. "Оперативно", never minutes (TZ §1.6).
$hours = $delicate
	? 'Залишили номер у вихідний чи ввечері — зателефонуємо наступного робочого дня.'
	: 'У робочі дні передзвонюємо оперативно. Залишили номер у вихідний чи ввечері — зателефонуємо наступного робочого дня.';

// Notice: first sentence bold, like the prototype.
$notice_html = '';
if ( $notice ) {
	$parts       = preg_split( '/(?<=\.)\s+/u', trim( $notice ), 2 );
	$notice_html = '<strong>' . esc_html( $parts[0] ) . '</strong>' . ( isset( $parts[1] ) ? ' ' . esc_html( $parts[1] ) : '' );
}

$call_btn = function ( $label, $class = 'al-btn al-btn--primary' ) {
	return '<button type="button" class="' . esc_attr( $class ) . '" data-al-call>' . koval_legal_ads_svg( 'phone', 20, 2 ) . '<span>' . esc_html( $label ) . '</span></button>';
};

/**
 * Desktop-only photo: the <source> matches ≥961px, below that the <img>
 * keeps a 1×1 placeholder so phones never download the photo (speed on
 * mobile Ads traffic). Eager, not lazy: on desktop it's above the fold.
 */
$desktop_picture = function ( $attachment_id, $class, $sizes ) {
	if ( ! $attachment_id ) {
		return '';
	}
	$srcset = wp_get_attachment_image_srcset( $attachment_id, 'large' );
	$src    = wp_get_attachment_image_url( $attachment_id, 'large' );
	$meta   = wp_get_attachment_metadata( $attachment_id );
	$alt    = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	return '<picture class="' . esc_attr( $class ) . '">'
		. '<source media="(min-width: 961px)" srcset="' . esc_attr( $srcset ? $srcset : $src ) . '" sizes="' . esc_attr( $sizes ) . '">'
		. '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="' . esc_attr( $alt ) . '" width="' . (int) ( $meta['width'] ?? 1200 ) . '" height="' . (int) ( $meta['height'] ?? 800 ) . '" loading="eager" decoding="async">'
		. '</picture>';
};
?>
<main id="main" class="al <?php echo $delicate ? 'al--delicate' : 'al--standard'; ?>">

	<section class="al-hero">
		<div class="wrap al-hero__grid">
			<div class="al-hero__text">
				<nav class="al-crumbs" aria-label="Хлібні крихти">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
					<span aria-hidden="true">›</span>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'service' ) ); ?>">Послуги</a>
					<span aria-hidden="true">›</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>
				<?php if ( $eyebrow ) : ?>
					<div class="al-eyebrow al-eyebrow--hero"><?php echo esc_html( $eyebrow ); ?></div>
				<?php endif; ?>
				<h1 class="al-hero__title"><?php the_title(); ?></h1>
				<?php if ( $lead ) : ?>
					<p class="al-hero__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>

				<ul class="al-chips">
					<?php if ( $delicate ) : ?>
						<li><?php echo koval_legal_ads_svg( 'check', 16, 2.2 ); ?>Перша консультація безкоштовна</li>
						<li><?php echo koval_legal_ads_svg( 'check', 16, 2.2 ); ?>Без зобов'язань</li>
					<?php else : ?>
						<li class="al-chips__main"><?php echo koval_legal_ads_svg( 'check', 20, 2.2 ); ?>Перша консультація — безкоштовно</li>
						<li class="al-chips__mobile"><?php echo koval_legal_ads_svg( 'check', 18, 2.2 ); ?>15+ років практики · 1000+ консультацій</li>
						<?php if ( $chip ) : ?>
							<li><?php echo koval_legal_ads_svg( 'check', 18, 2.2 ); ?><?php echo esc_html( $chip ); ?></li>
						<?php endif; ?>
					<?php endif; ?>
				</ul>

				<div class="al-hero__cta">
					<?php echo $call_btn( 'Передзвоніть мені' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( ! $delicate ) : ?>
						<span class="al-hero__cta-note">Безкоштовно · без зобов'язань</span>
					<?php endif; ?>
				</div>

				<p class="al-hours"><?php echo koval_legal_ads_svg( 'clock', 16, 2 ); ?><span><?php echo esc_html( $hours ); ?></span></p>
			</div>

			<?php if ( $hero_img ) : ?>
				<div class="al-hero__media" aria-hidden="true">
					<?php echo $desktop_picture( $hero_img, 'al-hero__photo', '(min-width: 1200px) 560px, 46vw' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( ! $delicate ) : ?>
						<div class="al-float al-float--a"><span class="al-float__num">1000+</span><span class="al-float__label">наданих<br>консультацій</span></div>
						<div class="al-float al-float--b"><span class="al-float__num">15+</span><span class="al-float__label">років<br>практики</span></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $notice_html ) : ?>
		<section class="al-notice">
			<div class="wrap">
				<div class="al-notice__box">
					<?php echo koval_legal_ads_svg( 'info', 22, 1.8 ); ?>
					<p><?php echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?></p>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $situations ) : ?>
		<section class="al-section al-sit">
			<div class="wrap">
				<?php if ( ! $delicate ) : ?>
					<div class="al-eyebrow">Ваша ситуація</div>
				<?php endif; ?>
				<h2 class="al-h2"><?php echo $delicate ? 'Чим можемо допомогти' : 'Яка у вас ситуація?'; ?></h2>
				<p class="al-sub"><?php echo $delicate ? 'Оберіть своє питання — і фахівець передзвонить саме з нього.' : 'Оберіть — і наш фахівець передзвонить саме з вашого питання.'; ?></p>
				<div class="al-sit__grid">
					<?php
					$last = count( $situations ) - 1;
					foreach ( $situations as $i => $sit ) :
						if ( empty( $sit['text'] ) ) {
							continue;
						}
						$is_other = ! $delicate && $i === $last && 'question' === ( $sit['icon'] ?? '' );
						?>
						<button type="button" class="al-sit__item<?php echo $is_other ? ' al-sit__item--other' : ''; ?>" data-al-call>
							<?php if ( ! $delicate ) : ?>
								<span class="al-ico"><?php echo koval_legal_ads_svg( $sit['icon'] ?? 'doc', 21, 1.8 ); ?></span>
							<?php endif; ?>
							<span class="al-sit__text"><?php echo esc_html( $sit['text'] ); ?></span>
							<span class="al-sit__go"><span class="al-sit__go-label">Передзвоніть мені</span><?php echo koval_legal_ads_svg( 'chevron', 18, 2 ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="al-section al-section--alt al-how">
		<div class="wrap al-how__grid">
			<?php if ( $steps_img && ! $delicate ) : // a busy, smiling call-centre photo doesn't fit the calm tone ?>
				<div class="al-how__media">
					<?php echo wp_get_attachment_image( $steps_img, 'large', false, array( 'class' => 'al-how__photo', 'loading' => 'lazy', 'sizes' => '(min-width: 961px) 560px, 100vw' ) ); ?>
				</div>
			<?php endif; ?>
			<div class="al-how__body">
				<?php if ( ! $delicate ) : ?>
					<div class="al-eyebrow">Як це працює</div>
				<?php endif; ?>
				<h2 class="al-h2"><?php echo $delicate ? 'Як це відбувається' : 'Усе починається з одного дзвінка'; ?></h2>
				<ol class="al-steps">
					<li><span class="al-steps__num">1</span><span class="al-steps__txt"><strong>Ви залишаєте номер</strong><span>Це займає менше хвилини</span></span></li>
					<li><span class="al-steps__num">2</span><span class="al-steps__txt"><strong>Фахівець передзвонює</strong><span><?php echo $delicate ? 'Спокійно уточнює деталі вашої ситуації' : 'Уточнює деталі вашої ситуації'; ?></span></span></li>
					<li><span class="al-steps__num">3</span><span class="al-steps__txt"><strong><?php echo $delicate ? 'Ви знаєте, що робити далі' : 'Ви знаєте, як діяти далі'; ?></strong><?php if ( $step3 ) : ?><span><?php echo esc_html( $step3 ); ?></span><?php endif; ?></span></li>
				</ol>
				<?php if ( $delicate ) : ?>
					<p class="al-quiet-stats">15+ років юридичної практики · 1000+ наданих консультацій</p>
				<?php else : ?>
					<?php echo $call_btn( 'Передзвоніть мені' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( ! $delicate ) : ?>
		<section class="al-stats">
			<div class="wrap al-stats__grid">
				<div class="al-stat"><span class="al-stat__num">15+</span><span class="al-stat__label">років юридичної практики</span></div>
				<div class="al-stat"><span class="al-stat__num">1000+</span><span class="al-stat__label">наданих консультацій</span></div>
				<div class="al-stat al-stat--world">
					<span class="al-ico al-ico--dark"><?php echo koval_legal_ads_svg( 'globe', 24, 1.7 ); ?></span>
					<p><strong>Консультуємо українців по всьому світу</strong> — тих, хто в Україні, і тих, хто виїхав за кордон.</p>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$reviews = array_filter( $reviews, function ( $r ) {
		return ! empty( $r['text'] );
	} );
	if ( $reviews && ! $delicate ) :
		?>
		<section class="al-section al-reviews">
			<div class="wrap">
				<div class="al-eyebrow">Відгуки</div>
				<h2 class="al-h2">Що кажуть клієнти</h2>
				<div class="al-reviews__track">
					<?php foreach ( $reviews as $r ) : ?>
						<figure class="al-review">
							<span class="al-review__mark" aria-hidden="true">”</span>
							<blockquote><?php echo esc_html( $r['text'] ); ?></blockquote>
							<figcaption><strong><?php echo esc_html( $r['name'] ?? '' ); ?></strong><?php if ( ! empty( $r['source'] ) ) : ?><span><?php echo esc_html( $r['source'] ); ?></span><?php endif; ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$faq = array_filter( $faq, function ( $f ) {
		return ! empty( $f['question'] ) && ! empty( $f['answer'] );
	} );
	if ( $faq ) :
		?>
		<section class="al-section <?php echo $delicate ? '' : 'al-section--alt'; ?> al-faq">
			<div class="wrap al-faq__grid">
				<div class="al-faq__head">
					<?php if ( ! $delicate ) : ?>
						<div class="al-eyebrow">Питання</div>
					<?php endif; ?>
					<h2 class="al-h2">Часті запитання</h2>
					<?php if ( ! $delicate ) : ?>
						<div class="al-faq__card">
							<p class="al-faq__card-title">Не знайшли відповідь?</p>
							<p>Залиште номер — фахівець передзвонить і відповість на ваше питання.</p>
							<?php echo $call_btn( 'Передзвоніть мені' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="al-faq__list">
					<?php foreach ( array_values( $faq ) as $i => $f ) : ?>
						<details class="al-faq__item"<?php echo 0 === $i ? ' open' : ''; ?>>
							<summary><span><?php echo esc_html( $f['question'] ); ?></span><span class="al-faq__icon" aria-hidden="true"><?php echo koval_legal_ads_svg( 'plus', 20, 2 ); ?></span></summary>
							<p><?php echo esc_html( $f['answer'] ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="al-final" id="contact-form">
		<div class="wrap al-final__grid">
			<div class="al-final__text">
				<h2>Залиште номер — ми передзвонимо</h2>
				<p>Фахівець уточнить деталі й підкаже, з чого почати. Перша консультація безкоштовна.</p>
				<p class="al-final__note"><?php echo esc_html( $final_note ); ?></p>
			</div>
			<?php echo $call_btn( 'Передзвоніть мені', 'al-btn al-btn--light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>

	<?php
	$related = array_filter( array_map( 'intval', $related ) );
	if ( $related ) :
		?>
		<section class="al-related">
			<div class="wrap">
				<h2 class="al-related__title">Також може знадобитися</h2>
				<div class="al-related__grid">
					<?php
					foreach ( $related as $rid ) :
						if ( 'publish' !== get_post_status( $rid ) ) {
							continue;
						}
						$rtitle = get_field( 'catalog_title', $rid );
						?>
						<a class="al-related__item" href="<?php echo esc_url( get_permalink( $rid ) ); ?>"><span><?php echo esc_html( $rtitle ? $rtitle : get_the_title( $rid ) ); ?></span><?php echo koval_legal_ads_svg( 'arrow', 18, 2 ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<div class="al-sticky" data-al-sticky hidden>
		<p class="al-sticky__note">Перша консультація безкоштовна</p>
		<?php echo $call_btn( 'Передзвоніть мені', 'al-btn al-btn--primary al-btn--sticky' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>

	<div class="al-toast" data-al-toast role="status" aria-live="polite" hidden></div>
</main>
