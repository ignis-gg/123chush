<?php
/**
 * Page template. Про нас / Контакти / Ціни get a full-width "page-hero +
 * the_content()" layout (whitelisted by slug, same pattern as
 * single-service.php's rich-service whitelist); everything else
 * (privacy-policy, umovy-garantii, sample-page) gets a plain narrow
 * column.
 *
 * RECOVERY REBUILD (2026-09-03) — hero kicker/H1/lead text reconstructed
 * from the static export of this exact site. Kontakty/tsiny use the
 * page's own title+excerpt for H1/lead (confirmed matching the static
 * export exactly); "pro-nas" has a distinct hardcoded H1/lead that does
 * NOT match its post_title/excerpt in the DB — copied verbatim from the
 * static export since it can't be derived from post fields.
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
		$koval_h1     = get_the_title();
		$koval_lead   = get_the_excerpt();

		if ( is_page( 'pro-nas' ) ) {
			$koval_kicker = 'Про нас';
			$koval_h1     = 'Хто ми і чому нам довіряють';
			$koval_lead   = "Ми — юридична компанія повного циклу, що працює з 1998 року. За цей час напрацювали досвід у роботі з ДРАЦС, судами, консульствами та державними реєстрами — і продовжуємо ним ділитися з кожним клієнтом особисто.";
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
