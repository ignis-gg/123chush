<?php
/**
 * Single blog post ('post' type). Markup reconstructed 2026-09-03 from the
 * static export of this exact site (koval-legal-demo.pages.dev): hero with
 * category/title/meta line, breadcrumbs with category, featured image,
 * content, a fixed author box (Олег Коваль — the site has one author),
 * share buttons, a "related service" card driven by the ACF
 * `related_service` field (a post-object picker to the service CPT), and
 * a related-posts strip (other posts, same category preferred).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
the_post();
$koval_cats = get_the_category();
$koval_cat  = ! empty( $koval_cats ) ? $koval_cats[0] : null;
?>
<main id="main">
	<div class="single-hero">
		<div class="wrap">
			<?php if ( $koval_cat ) : ?><div class="eyebrow on-dark"><?php echo esc_html( $koval_cat->name ); ?></div><?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<p class="article-meta">
				<?php echo esc_html( koval_t( 'Дата публікації' ) ); ?> <?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?>
				<?php if ( get_the_modified_date( 'Y-m-d' ) !== get_the_date( 'Y-m-d' ) ) : ?>
					· <?php echo esc_html( koval_t( 'Оновлено' ) ); ?> <?php echo esc_html( get_the_modified_date( 'd.m.Y' ) ); ?>
				<?php endif; ?>
				· <?php echo esc_html( koval_legal_reading_time() ); ?> <?php echo esc_html( koval_t( 'хв читання' ) ); ?>
				· <?php echo esc_html( koval_t( 'Автор' ) ); ?>: <?php echo esc_html( koval_t( 'Олег Коваль' ) ); ?>
			</p>
		</div>
	</div>

	<div class="breadcrumbs">
		<div class="wrap">
			<a href="<?php echo esc_url( koval_home_url() ); ?>"><?php echo esc_html( koval_t( 'Головна' ) ); ?></a>
			<span class="sep">›</span>
			<a href="<?php echo esc_url( koval_blog_url() ); ?>"><?php echo esc_html( koval_t( 'Блог' ) ); ?></a>
			<?php if ( $koval_cat ) : ?>
				<span class="sep">›</span>
				<a href="<?php echo esc_url( get_category_link( $koval_cat ) ); ?>"><?php echo esc_html( $koval_cat->name ); ?></a>
			<?php endif; ?>
			<span class="sep">›</span>
			<span><?php the_title(); ?></span>
		</div>
	</div>

	<div class="single-body">
		<div class="wrap article-wrap">
			<div class="single-content">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="single-thumb"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => koval_legal_thumbnail_alt() ) ); ?></div>
				<?php endif; ?>

				<button type="button" class="print-article-btn" onclick="window.print()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z" stroke-linecap="round" stroke-linejoin="round"/></svg> <?php echo esc_html( koval_t( 'Друкувати статтю' ) ); ?></button>

				<?php the_content(); ?>

				<div class="author-box">
					<div class="author-label"><?php echo esc_html( koval_t( 'Автор статті' ) ); ?></div>
					<span class="author-name"><?php echo esc_html( koval_t( 'Олег Коваль' ) ); ?></span>
					<span class="author-role"><?php echo esc_html( koval_t( 'Засновник та керівник KOVAL Legal Group' ) ); ?></span>
					<p class="author-bio"><?php echo esc_html( koval_t( 'Надає юридичні консультації з 2020 року, спеціалізується на документах ДРАЦС, судових питаннях та легалізації документів.' ) ); ?></p>
				</div>

				<?php
				$koval_related_service = koval_legal_field( 'related_service' );
				if ( $koval_related_service ) :
					$koval_related_id = is_object( $koval_related_service ) ? $koval_related_service->ID : $koval_related_service;
					$koval_related_title = get_the_title( $koval_related_id );
					if ( $koval_related_title ) :
						?>
						<div class="related-service-card">
							<p><?php echo esc_html( sprintf( koval_t( 'Потрібна допомога з «%s»?' ), $koval_related_title ) ); ?></p>
							<a href="<?php echo esc_url( get_permalink( $koval_related_id ) ); ?>" class="btn btn-wine btn-sm"><?php echo esc_html( koval_t( 'Детальніше про послугу →' ) ); ?></a>
						</div>
						<?php
					endif;
				endif;
				?>
			</div>
		</div>
	</div>

	<?php
	$koval_related_args = array(
		'post_type'      => 'post',
		'posts_per_page' => 2,
		'post__not_in'   => array( get_the_ID() ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( $koval_cat ) {
		$koval_related_args['category__in'] = array( $koval_cat->term_id );
	}
	$koval_related = get_posts( $koval_related_args );
	if ( ! empty( $koval_related ) ) :
		?>
		<div class="archive-body related-posts">
			<div class="wrap">
				<h2 class="related-posts-title"><?php echo esc_html( koval_t( 'Читайте також' ) ); ?></h2>
				<div class="blog-grid">
					<?php foreach ( $koval_related as $koval_post ) :
						setup_postdata( $koval_post );
						$koval_rel_cats = get_the_category( $koval_post->ID );
						?>
						<article class="blog-card">
							<?php if ( has_post_thumbnail( $koval_post->ID ) ) : ?>
								<div class="blog-card-thumb"><?php echo get_the_post_thumbnail( $koval_post->ID, 'medium_large', array( 'loading' => 'lazy' ) ); ?></div>
							<?php endif; ?>
							<div class="blog-meta">
								<?php if ( ! empty( $koval_rel_cats ) ) : ?><span class="blog-badge"><?php echo esc_html( $koval_rel_cats[0]->name ); ?></span><?php endif; ?>
								<span class="blog-date"><?php echo esc_html( get_the_date( 'd.m.Y', $koval_post ) ); ?> · <?php echo esc_html( koval_legal_reading_time( $koval_post->ID ) ); ?> <?php echo esc_html( koval_t( 'хв читання' ) ); ?></span>
							</div>
							<h3><a href="<?php echo esc_url( get_permalink( $koval_post ) ); ?>"><?php echo esc_html( get_the_title( $koval_post ) ); ?></a></h3>
							<p><?php echo esc_html( get_the_excerpt( $koval_post ) ); ?></p>
							<a href="<?php echo esc_url( get_permalink( $koval_post ) ); ?>" class="service-link"><?php echo esc_html( koval_t( 'Читати →' ) ); ?></a>
						</article>
					<?php endforeach; wp_reset_postdata(); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
