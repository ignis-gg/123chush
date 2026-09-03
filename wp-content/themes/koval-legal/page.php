<?php
/**
 * Page template. Про нас / Контакти / Ціни get a full-width "page-hero +
 * the_content()" layout (whitelisted by slug, same pattern as
 * single-service.php's rich-service whitelist); everything else
 * (privacy-policy, umovy-garantii, sample-page) gets a plain narrow
 * column.
 *
 * Hero H1/lead prefer the hero_h1/hero_lead ACF fields (inc/acf-fields.php
 * group_koval_page_hero) so an editor can set page-hero copy independently
 * of the page's actual title/excerpt (which still drive the browser tab
 * title and breadcrumb) — falls back to get_the_title()/get_the_excerpt()
 * when empty, which is exactly what all three pages showed before these
 * fields existed.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
the_post();

$koval_full_width_slugs = array( 'pro-nas', 'kontakty', 'tsiny' );
$koval_is_full          = in_array( get_post_field( 'post_name' ), $koval_full_width_slugs, true );
?>
<main id="main">
	<?php if ( $koval_is_full ) : ?>

		<?php
		$koval_kicker = 'Юридична компанія · Київ';
		$koval_h1     = get_field( 'hero_h1' ) ?: get_the_title();
		$koval_lead   = get_field( 'hero_lead' ) ?: get_the_excerpt();

		if ( is_page( 'pro-nas' ) ) {
			$koval_kicker = 'Про нас';
		}
		?>
		<div class="page-hero">
			<div class="wrap">
				<div class="kicker"><?php echo esc_html( $koval_kicker ); ?></div>
				<h1><?php echo esc_html( $koval_h1 ); ?></h1>
				<?php if ( $koval_lead ) : ?><p><?php echo esc_html( $koval_lead ); ?></p><?php endif; ?>
			</div>
		</div>

		<?php koval_legal_breadcrumbs(); ?>

		<?php
		remove_filter( 'the_content', 'wpautop' );
		the_content();
		add_filter( 'the_content', 'wpautop' );
		?>

		<?php if ( is_page( array( 'pro-nas', 'kontakty' ) ) ) : ?>
			<?php echo koval_legal_render_cta_section(); ?>
		<?php endif; ?>

	<?php else : ?>

		<div class="single-body">
			<div class="wrap single-grid">
				<div class="single-content">
					<h1><?php the_title(); ?></h1>
					<?php the_content(); ?>
				</div>
			</div>
		</div>

	<?php endif; ?>
</main>
<?php
get_footer();
