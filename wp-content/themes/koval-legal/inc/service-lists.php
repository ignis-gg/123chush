<?php
/**
 * Single source of truth for "which service posts are fully ACF-driven
 * landing/pillar pages" — used by both single-service.php (which layout to
 * render) and inc/acf-admin-ux.php (which edit screens should hide the raw
 * HTML editor). Kept in one place so the two never drift apart.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function koval_legal_rich_services() {
	return array(
		99  => ' — швидко і без черг', // Апостиль на диплом або атестат.
		100 => ' — без походів і повторних відмов', // Дублікат свідоцтва ДРАЦС.
		101 => '', // Легалізація диплома для роботи чи навчання за кордоном.
		102 => ' — без бюрократичних складнощів', // Шлюб з іноземцем в Україні.
		103 => '', // Поновлення актового запису ДРАЦС.
		111 => '', // Апостиль в Мін'юсті.
		112 => '', // Легалізація документів в Мін'юсті.
		116 => '', // WES Canada.
		118 => '', // ІПН для іноземця.
		119 => '', // Розлучення через ДРАЦС.
		120 => '', // Наказ про стягнення аліментів.
		// ТЗ "32 сторінки" (2026-09-03), хвилі 1-6:
		133 => '', // ІПН для українця.
		134 => '', // Відмова від ІПН.
		135 => '', // Витяг з реєстру ДРАЦС.
		136 => '', // Внесення змін / виправлення помилок в актовому записі.
		137 => '', // Реєстрація шлюбу між громадянами України.
		138 => '', // Шлюбний контракт.
		140 => '', // Витребування дублікатів освітніх документів.
		141 => '', // Довідка про факт навчання.
		142 => '', // Витребування освітніх документів з-за кордону.
		143 => '', // Подання позовів до суду.
		144 => '', // Витребування копій судових рішень.
		145 => '', // Адвокатські запити (АдвЗП) в суди.
		146 => '', // Апостиль на документи ДРАЦС.
		147 => '', // Подвійний апостиль.
		148 => '', // Терміновий апостиль документів.
		150 => '', // Апостиль в МОН.
		151 => '', // Апостиль в МЗС.
		152 => '', // Апостиль за кордоном.
		153 => '', // Консультація щодо Гаазької конвенції.
		154 => '', // Консульська легалізація документів в Україні.
		155 => '', // Легалізація документів за кордоном.
		156 => '', // Легалізація документів в МЗС.
		157 => '', // Легалізація довідки про несудимість.
		158 => '', // Легалізація довіреності в Україні.
		159 => '', // Легалізація свідоцтва про народження.
		160 => '', // Легалізація свідоцтва про шлюб.
		161 => '', // Легалізація свідоцтва про розлучення.
		162 => '', // Легалізація свідоцтва про зміну ПІБ.
		163 => '', // Легалізація свідоцтва про смерть.
		164 => '', // Консульська легалізація іноземних документів в Україні.
	);
}

/**
 * Pillar (category hub) pages — compact layout: H1 + lead only (no
 * quick-facts pills, no hero CTA button), then cross-links + grouped
 * service cards + short FAQ.
 */
function koval_legal_pillar_services() {
	return array(
		121 => '', // Документи ДРАЦС.
		122 => '', // Легалізація документів.
		123 => '', // Сімейні відносини.
		124 => '', // ІПН.
		125 => '', // Освітні документи.
		126 => '', // Суд.
	);
}

/**
 * IDs whose content is fully authored through ACF fields (rich landings +
 * pillar pages) — post_content on these still holds the old hand-authored
 * HTML as a non-destructive fallback, but it's not what an editor should
 * touch, so the admin edit screen hides the raw editor for exactly these.
 */
function koval_legal_acf_content_ids() {
	return array_merge(
		array_keys( koval_legal_rich_services() ),
		array_keys( koval_legal_pillar_services() )
	);
}

/**
 * The 22 apostille/legalization landing pages (service_category term
 * "legalization") — these get a prominent not-a-government-body /
 * not-guaranteed-issuance notice injected right under the hero (see
 * koval_render_legal_notice() in inc/acf-render.php), per the 2026-09-04
 * Google Ads exclusion-request prep pass.
 */
function koval_legal_legalization_group_ids() {
	return array( 99, 147, 148, 150, 151, 111, 152, 153, 154, 164, 155, 157, 101, 158, 159, 160, 161, 162, 163, 112, 156 );
}

/**
 * The /poslugy/ catalog (archive-service.php) and homepage services grid
 * (inc/homepage-sections.php) used to read this shape — slug/label/
 * description/mini_cta + cards[] (name/desc/price/duration/permalink/
 * popular) — from a hardcoded PHP array. It's now built from the real
 * `service_category` taxonomy terms and the `service` posts assigned to
 * them, so both templates keep working unchanged: an editor adds a card
 * just by publishing a service post under a category, no code touched.
 */
function koval_legal_catalog_categories() {
	$terms = get_terms( array(
		'taxonomy'   => 'service_category',
		'hide_empty' => false,
		'orderby'    => 'meta_value_num',
		'meta_key'   => 'category_sort_order',
		'order'      => 'ASC',
	) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}

	// Fetch every service post in one query instead of one get_posts() per
	// term (was up to 8x redundant queries — see perf audit, 2026-09-05),
	// then group by term in PHP using the term cache the query already primes.
	$all_posts = get_posts( array(
		'post_type'      => 'service',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'service_category',
				'field'    => 'term_id',
				'terms'    => wp_list_pluck( $terms, 'term_id' ),
			),
		),
	) );

	$posts_by_term = array();
	foreach ( $all_posts as $post ) {
		$post_terms = get_the_terms( $post, 'service_category' );
		if ( is_wp_error( $post_terms ) || ! $post_terms ) {
			continue;
		}
		foreach ( $post_terms as $post_term ) {
			$posts_by_term[ $post_term->term_id ][] = $post;
		}
	}

	$categories = array();
	foreach ( $terms as $term ) {
		$cards = array();
		foreach ( $posts_by_term[ $term->term_id ] ?? array() as $post ) {
			$catalog_title = get_field( 'catalog_title', $post->ID );
			$cards[] = array(
				'name'      => $catalog_title ? $catalog_title : get_the_title( $post ),
				'desc'      => (string) get_field( 'catalog_short_description', $post->ID ),
				'price'     => (string) get_field( 'service_price', $post->ID ),
				'duration'  => (string) get_field( 'service_duration', $post->ID ),
				'permalink' => $post->ID,
				'popular'   => (bool) get_field( 'catalog_popular', $post->ID ),
			);
		}

		// Skip categories with zero cards — hide_empty=>false above keeps
		// admin-visible term management working even mid-edit, but an empty
		// tab/accordion on the public catalog is a dead end for visitors,
		// not a valid state to display (found 2026-09-05 after deleting the
		// only services in "Бізнес та реєстрація" and "Судовий захист водіїв").
		if ( empty( $cards ) ) {
			continue;
		}

		$categories[] = array(
			'slug'             => $term->slug,
			'label'            => $term->name,
			'description'      => $term->description,
			'price_anchor'     => '',
			'mini_cta'         => (string) get_field( 'category_mini_cta', 'service_category_' . $term->term_id ),
			'show_on_homepage' => (bool) get_field( 'category_show_on_homepage', 'service_category_' . $term->term_id ),
			'cards'            => $cards,
		);
	}

	return $categories;
}
