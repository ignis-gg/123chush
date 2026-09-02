<?php
/**
 * RECOVERY REBUILD (2026-09-02) — generic fallback template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main id="main">
	<div class="wrap">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
		<?php endwhile; else : ?>
			<p>Нічого не знайдено.</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
