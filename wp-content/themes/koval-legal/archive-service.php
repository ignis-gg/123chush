<?php
/**
 * "Послуги" catalog — 8 categories, ~47 cards. Content comes from
 * inc/services-catalog.php (koval_legal_services_catalog()); this
 * template renders it into the tab-filter + accordion-group + live-search
 * markup that assets/js/main.js binds to (data-filter, #svcSearch,
 * .svc-group-toggle, data-search attribute).
 *
 * RECOVERY REBUILD (2026-09-03) — markup reconstructed from the static
 * export of this exact site (koval-legal-demo.pages.dev).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$koval_categories = koval_legal_services_catalog();
?>
<main id="main">
	<div class="archive-head">
		<div class="wrap">
			<div class="eyebrow on-dark">Послуги</div>
			<h1>Наші ключові напрями</h1>
			<p>Повний юридичний супровід під ключ: консультація, підготовка документів і представництво в державних органах — з фіксованою вартістю та строком.</p>
			<div class="svc-search">
				<input type="text" id="svcSearch" placeholder="Що вам потрібно? Наприклад: апостиль, дублікат свідоцтва, шлюб з іноземцем">
			</div>
		</div>
	</div>

	<?php koval_legal_breadcrumbs(); ?>

	<div class="archive-body">
		<div class="wrap">

			<div class="svc-tabs" id="svcTabs">
				<button class="svc-tab is-active" data-filter="all" type="button">Усі послуги</button>
				<?php foreach ( $koval_categories as $cat ) : ?>
					<button class="svc-tab" data-filter="<?php echo esc_attr( $cat['slug'] ); ?>" type="button"><?php echo esc_html( $cat['label'] ); ?></button>
				<?php endforeach; ?>
			</div>

			<div class="svc-intro">
				<div class="eyebrow">Напрями</div>
				<h2>Оберіть свою ситуацію</h2>
			</div>

			<nav class="svc-mini-nav" id="svcMiniNav" hidden>
				<?php foreach ( $koval_categories as $cat ) : ?>
					<a href="#group-<?php echo esc_attr( $cat['slug'] ); ?>"><?php echo esc_html( $cat['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<div id="svcGroups">
				<?php foreach ( $koval_categories as $cat ) : ?>
					<div class="svc-group" id="group-<?php echo esc_attr( $cat['slug'] ); ?>" data-group="<?php echo esc_attr( $cat['slug'] ); ?>">
						<button class="svc-group-toggle" type="button" aria-expanded="false" aria-controls="svc-grid-<?php echo esc_attr( $cat['slug'] ); ?>">
							<span class="svc-group-icon svc-icon-<?php echo esc_attr( $cat['slug'] ); ?>"></span>
							<h3 class="svc-group-head"><?php echo esc_html( $cat['label'] ); ?></h3>
							<span class="svc-group-count">(<?php echo count( $cat['cards'] ); ?>)</span>
							<span class="svc-group-chevron">▾</span>
						</button>
						<div class="svc-grid" id="svc-grid-<?php echo esc_attr( $cat['slug'] ); ?>" hidden>
							<?php foreach ( $cat['cards'] as $card ) :
								$href = ! empty( $card['permalink'] )
									? get_permalink( $card['permalink'] )
									: ( $cat['price_anchor'] ? home_url( '/tsiny/#' . $cat['price_anchor'] ) : home_url( '/tsiny/' ) );
								$search_blob = strtolower( $card['name'] . ' ' . $card['desc'] );
								?>
								<div class="svc-card" data-search="<?php echo esc_attr( $search_blob ); ?>">
									<?php if ( ! empty( $card['popular'] ) ) : ?>
										<span class="svc-badge-popular">Часто замовляють</span>
									<?php endif; ?>
									<h4><a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $card['name'] ); ?></a></h4>
									<p><?php echo esc_html( $card['desc'] ); ?></p>
									<?php if ( $card['price'] || $card['duration'] ) : ?>
										<div class="svc-meta">
											<?php if ( $card['price'] ) : ?><span>Вартість <b><?php echo esc_html( $card['price'] ); ?></b></span><?php endif; ?>
											<?php if ( $card['duration'] ) : ?><span>Строк <b><?php echo esc_html( $card['duration'] ); ?></b></span><?php endif; ?>
										</div>
									<?php endif; ?>
									<a href="<?php echo esc_url( $href ); ?>" class="service-link">Детальніше →</a>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if ( $cat['mini_cta'] ) : ?>
							<a href="#contact-form" class="svc-mini-cta"><?php echo esc_html( $cat['mini_cta'] ); ?></a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
	</div>

	<?php echo koval_legal_render_cta_section(); ?>
</main>
<?php
get_footer();
