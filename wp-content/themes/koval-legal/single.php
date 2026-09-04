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
				Дата публікації <?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?>
				<?php if ( get_the_modified_date( 'Y-m-d' ) !== get_the_date( 'Y-m-d' ) ) : ?>
					· Оновлено <?php echo esc_html( get_the_modified_date( 'd.m.Y' ) ); ?>
				<?php endif; ?>
				· <?php echo esc_html( koval_legal_reading_time() ); ?> хв читання
				· Автор: Олег Коваль
			</p>
		</div>
	</div>

	<div class="breadcrumbs">
		<div class="wrap">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Головна</a>
			<span class="sep">›</span>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">Блог</a>
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

				<button type="button" class="print-article-btn" onclick="window.print()"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z" stroke-linecap="round" stroke-linejoin="round"/></svg> Друкувати статтю</button>

				<?php the_content(); ?>

				<div class="author-box">
					<div class="author-label">Автор статті</div>
					<span class="author-name">Олег Коваль</span>
					<span class="author-role">Засновник та керівник KOVAL Legal Group</span>
					<p class="author-bio">Особисто веде справи клієнтів з 1998 року, спеціалізується на документах ДРАЦС, судовому представництві та легалізації документів.</p>
				</div>

				<?php
				$koval_share_url  = rawurlencode( get_permalink() );
				$koval_share_text = rawurlencode( get_the_title() );
				?>
				<div class="share-buttons">
					<span class="share-buttons-label">Поділитися:</span>
					<a href="https://t.me/share/url?url=<?php echo esc_attr( $koval_share_url ); ?>&text=<?php echo esc_attr( $koval_share_text ); ?>" class="icon-circle" aria-label="Поділитися в Telegram" target="_blank" rel="noopener"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M21.9 4.3 18.6 20c-.2 1-1 1.3-1.9.8l-5.3-3.9-2.6 2.5c-.3.3-.5.5-1 .5l.4-5.4L18 6.4c.5-.4-.1-.6-.7-.2L6.5 13.2l-5.3-1.7c-1.1-.4-1.1-1.1.3-1.6L20.6 3.1c1-.3 1.8.2 1.3 1.2z"/></svg></a>
					<a href="https://wa.me/?text=<?php echo esc_attr( $koval_share_text ); ?>%20<?php echo esc_attr( $koval_share_url ); ?>" class="icon-circle" aria-label="Поділитися в WhatsApp" target="_blank" rel="noopener"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.39 1.26 4.81L2 22l5.44-1.36a9.9 9.9 0 0 0 4.6 1.14h.01c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2zm5.8 14.14c-.24.68-1.19 1.25-1.95 1.4-.52.11-1.2.2-3.5-.75-2.94-1.22-4.83-4.2-4.98-4.4-.14-.19-1.19-1.58-1.19-3.02 0-1.43.75-2.14 1.02-2.43.24-.27.55-.34.73-.34h.53c.17 0 .4-.03.62.48.24.55.8 1.9.87 2.04.07.14.11.31.02.5-.09.19-.14.31-.28.47-.14.16-.29.36-.42.48-.14.13-.28.28-.12.55.16.27.71 1.17 1.53 1.9 1.05.94 1.94 1.24 2.21 1.38.27.14.43.12.6-.07.16-.19.7-.81.88-1.09.18-.27.36-.23.6-.14.24.09 1.55.73 1.82.87.27.14.45.2.51.32.07.11.07.65-.17 1.33z"/></svg></a>
				</div>

				<?php
				$koval_related_service = koval_legal_field( 'related_service' );
				if ( $koval_related_service ) :
					$koval_related_id = is_object( $koval_related_service ) ? $koval_related_service->ID : $koval_related_service;
					$koval_related_title = get_the_title( $koval_related_id );
					if ( $koval_related_title ) :
						?>
						<div class="related-service-card">
							<p>Потрібна допомога з «<?php echo esc_html( $koval_related_title ); ?>»?</p>
							<a href="<?php echo esc_url( get_permalink( $koval_related_id ) ); ?>" class="btn btn-wine btn-sm">Детальніше про послугу →</a>
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
				<h2 class="related-posts-title">Читайте також</h2>
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
								<span class="blog-date"><?php echo esc_html( get_the_date( 'd.m.Y', $koval_post ) ); ?> · <?php echo esc_html( koval_legal_reading_time( $koval_post->ID ) ); ?> хв читання</span>
							</div>
							<h3><a href="<?php echo esc_url( get_permalink( $koval_post ) ); ?>"><?php echo esc_html( get_the_title( $koval_post ) ); ?></a></h3>
							<p><?php echo esc_html( get_the_excerpt( $koval_post ) ); ?></p>
							<a href="<?php echo esc_url( get_permalink( $koval_post ) ); ?>" class="service-link">Читати →</a>
						</article>
					<?php endforeach; wp_reset_postdata(); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
