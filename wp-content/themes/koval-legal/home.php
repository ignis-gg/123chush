<?php
/**
 * Blog listing (the "posts page" — page_for_posts option points at the
 * "Блог" page, so WordPress uses home.php here instead of page.php).
 *
 * RECOVERY REBUILD (2026-09-03) — markup reconstructed from the static
 * export of this exact site (koval-legal-demo.pages.dev).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$koval_categories = get_categories( array( 'hide_empty' => false, 'exclude' => array( 1 ) ) ); // drop "Uncategorized"
$koval_cat_order  = array( 'dokumenty-dracs', 'legalizatsiya-apostyl', 'simeyne-pravo', 'biznes-fop', 'sudovi-pytannya' );
usort( $koval_categories, fn( $a, $b ) => array_search( $a->slug, $koval_cat_order ) <=> array_search( $b->slug, $koval_cat_order ) );
?>
<main id="main">
	<div class="archive-head">
		<div class="wrap">
			<div class="eyebrow on-dark">Блог</div>
			<h1>Юридичні статті та роз'яснення</h1>
			<p>Пояснюємо процедури, зміни в законодавстві та відповідаємо на питання, які найчастіше чуємо від клієнтів.</p>
		</div>
	</div>

	<div class="breadcrumbs">
		<div class="wrap">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
			<span class="sep">›</span>
			<span>Блог</span>
		</div>
	</div>

	<div class="archive-body">
		<div class="wrap">

			<div class="blog-filter-row">
				<div class="svc-tabs">
					<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="svc-tab is-active">Усі статті</a>
					<?php foreach ( $koval_categories as $cat ) : ?>
						<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>" class="svc-tab"><?php echo esc_html( $cat->name ); ?></a>
					<?php endforeach; ?>
				</div>
				<form class="svc-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<input type="hidden" name="post_type" value="post">
					<label class="sr-only" for="blogSearch">Пошук по блогу</label>
					<input type="text" id="blogSearch" name="s" placeholder="Пошук по блогу">
				</form>
			</div>

			<div class="blog-grid">
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
					$koval_cats = get_the_category();
					?>
					<article class="blog-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="blog-card-thumb"><?php the_post_thumbnail( 'medium_large', array( 'loading' => 'eager', 'alt' => koval_legal_thumbnail_alt() ) ); ?></div>
						<?php endif; ?>
						<div class="blog-meta">
							<?php if ( ! empty( $koval_cats ) ) : ?><span class="blog-badge"><?php echo esc_html( $koval_cats[0]->name ); ?></span><?php endif; ?>
							<span class="blog-date"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?> · <?php echo esc_html( koval_legal_reading_time() ); ?> хв читання</span>
						</div>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<a href="<?php the_permalink(); ?>" class="service-link">Читати →</a>
					</article>
				<?php endwhile; else : ?>
					<p>Поки що немає статей.</p>
				<?php endif; ?>
			</div>

			<?php the_posts_pagination(); ?>

		</div>
	</div>

	<section class="cta-strip">
		<div class="wrap">
			<p>Не знайшли відповідь на своє питання? Юрист відповість особисто.</p>
			<a href="<?php echo esc_url( home_url( '/#contact-form' ) ); ?>" class="btn btn-wine">Отримати консультацію →</a>
		</div>
	</section>
</main>
<?php
get_footer();
