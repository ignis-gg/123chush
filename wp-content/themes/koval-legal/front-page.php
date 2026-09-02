<?php
/**
 * Homepage. Post 7's post_content (untouched by the incident, still in the
 * DB with all its Gutenberg blocks) holds 5 top-level sections in order:
 * hero, advantages, guarantee-section, stats, process. This template
 * renders those blocks via render_block() and weaves the dynamic sections
 * (services grid, testimonials, FAQ, CTA form) in between them at the
 * exact positions confirmed against the static export of this site:
 * hero -> [services] -> advantages -> guarantee -> stats -> [testimonials]
 * -> process -> [faq] -> [cta].
 *
 * RECOVERY REBUILD (2026-09-03).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
the_post();

$blocks = parse_blocks( get_the_content() );
// Keep only real blocks (parse_blocks() can yield empty whitespace blocks
// between comments).
$blocks = array_values( array_filter( $blocks, fn( $b ) => ! empty( $b['blockName'] ) ) );
?>
<main id="main">
	<?php
	echo render_block( $blocks[0] ?? null );      // hero
	echo koval_legal_render_services_grid();
	echo render_block( $blocks[1] ?? null );      // advantages
	echo render_block( $blocks[2] ?? null );      // guarantee-section
	echo render_block( $blocks[3] ?? null );      // stats
	echo koval_legal_render_testimonials();
	echo render_block( $blocks[4] ?? null );      // process
	echo koval_legal_render_faq();
	echo koval_legal_render_cta_section();
	?>
</main>
<?php
get_footer();
