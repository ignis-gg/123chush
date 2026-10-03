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
	// Keyed by the Ukrainian slug (was post IDs until 2026-10-03 — dev and
	// prod IDs differed for the later pages, and the RU copies have their
	// own IDs; look a post up with koval_source_slug()). The value is a
	// suffix appended to the H1, in Ukrainian — koval_t() translates it.
	return array(
		'apostyl-na-dyplom-abo-atestat' => ' — швидко і без черг', // Апостиль на диплом або атестат.
		'lehalizatsiya-dyploma-dlya-roboty-chy-navchannya-za-kordonom' => '', // Легалізація диплома для роботи чи навчання за кордоном.
		'shlyub-z-inozemtsem-v-ukrayini' => ' — без бюрократичних складнощів', // Шлюб з іноземцем в Україні.
		'apostyl-v-minyusti' => '', // Апостиль в Мін'юсті.
		'lehalizatsiya-dokumentiv-v-minyusti' => '', // Легалізація документів в Мін'юсті.
		'wes-canada' => '', // WES Canada.
		'rozluchennya-cherez-drats' => '', // Розлучення через ДРАЦС.
		'nakaz-pro-styagnennya-alimentiv' => '', // Наказ про стягнення аліментів.
		// ТЗ "32 сторінки" (2026-09-03), хвилі 1-6:
		'reyestratsiya-shlyubu' => '', // Реєстрація шлюбу між громадянами України.
		'shlyubnyy-kontrakt' => '', // Шлюбний договір.
		'dublikat-dyploma-atestata' => '', // Витребування дублікатів освітніх документів.
		'dovidka-pro-fakt-navchannya' => '', // Довідка про факт навчання.
		'osvitni-dokumenty-z-kordonu' => '', // Витребування освітніх документів з-за кордону.
		'podannya-pozovu-do-sudu' => '', // Подання позовів до суду.
		'kopiya-sudovogo-rishennya' => '', // Витребування копій судових рішень.
		'advokatskyy-zapyt' => '', // Адвокатські запити (АдвЗП) в суди.
		'apostyl-na-dokumenty-drats' => '', // Апостиль на документи ДРАЦС.
		'podviynyy-apostyl' => '', // Подвійний апостиль.
		'terminovyy-apostyl' => '', // Терміновий апостиль документів.
		'apostyl-v-mon' => '', // Апостиль в МОН.
		'apostyl-v-mzs' => '', // Апостиль в МЗС.
		'apostyl-za-kordonom' => '', // Апостиль за кордоном.
		'konsultatsiya-gaazka-konventsiya' => '', // Консультація щодо Гаазької конвенції.
		'konsulska-legalizatsiya-v-ukrayini' => '', // Консульська легалізація документів в Україні.
		'legalizatsiya-za-kordonom' => '', // Легалізація документів за кордоном.
		'legalizatsiya-v-mzs' => '', // Легалізація документів в МЗС.
		'legalizatsiya-dovirenosti' => '', // Легалізація довіреності в Україні.
		'legalizatsiya-svidotstva-pro-shlyub' => '', // Легалізація свідоцтва про шлюб.
		'legalizatsiya-svidotstva-pro-rozluchennya' => '', // Легалізація свідоцтва про розлучення.
		'legalizatsiya-inozemnyh-dokumentiv' => '', // Консульська легалізація іноземних документів в Україні.
		'dovidky' => '', // Довідки (загальний лендінг, без переліку конкретних видів) — 2026-09-08.
		'dovidka-pro-nesudymist' => '', // Довідка про несудимість — 2026-09-21, див. koval_legal_criminal_record_slugs().
		// 100, 103, 118, 133, 134, 135, 136, 157, 159, 162, 163 — retired
		// 2026-09-06 (Google Ads compliance, ІПН/ДРАЦС group + 4 legalization-
		// of-svidotstvo pages), see docs/google-ads-gov-services-classification.md.
	);
}

/**
 * Pillar (category hub) pages — compact layout: H1 + lead only (no
 * quick-facts pills, no hero CTA button), then cross-links + grouped
 * service cards + short FAQ.
 */
function koval_legal_pillar_services() {
	return array(
		'legalizatsiya-dokumentiv' => '', // Легалізація документів.
		'simeyni-vidnosyny' => '', // Сімейні відносини.
		'osvitni-dokumenty' => '', // Освітні документи.
		'sudovi-poslugy' => '', // Суд.
		// 121 (Документи ДРАЦС), 124 (ІПН) — retired 2026-09-06, no
		// surviving children to hub.
	);
}

/**
 * Whether a post's content is fully authored through ACF fields (rich
 * landings + pillar pages, in any language) — post_content on these still
 * holds the old hand-authored HTML as a non-destructive fallback (or is
 * empty on the RU copies), but it's not what an editor should touch, so
 * the admin edit screen hides the raw editor for exactly these.
 */
function koval_legal_is_acf_content( $post_id ) {
	$slug = koval_source_slug( $post_id );
	return array_key_exists( $slug, koval_legal_rich_services() ) || array_key_exists( $slug, koval_legal_pillar_services() );
}

/**
 * The 22 apostille/legalization landing pages (service_category term
 * "legalization") — these get a prominent not-a-government-body /
 * not-guaranteed-issuance notice injected right under the hero (see
 * koval_render_legal_notice() in inc/acf-render.php), per the 2026-09-04
 * Google Ads exclusion-request prep pass.
 */
function koval_legal_legalization_group_slugs() {
	// 157, 159, 162, 163 removed 2026-09-06 (Google Ads compliance).
	return array(
		'apostyl-na-dyplom-abo-atestat',
		'podviynyy-apostyl',
		'terminovyy-apostyl',
		'apostyl-v-mon',
		'apostyl-v-mzs',
		'apostyl-v-minyusti',
		'apostyl-za-kordonom',
		'konsultatsiya-gaazka-konventsiya',
		'konsulska-legalizatsiya-v-ukrayini',
		'legalizatsiya-inozemnyh-dokumentiv',
		'legalizatsiya-za-kordonom',
		'lehalizatsiya-dyploma-dlya-roboty-chy-navchannya-za-kordonom',
		'legalizatsiya-dovirenosti',
		'legalizatsiya-svidotstva-pro-shlyub',
		'legalizatsiya-svidotstva-pro-rozluchennya',
		'lehalizatsiya-dokumentiv-v-minyusti',
		'legalizatsiya-v-mzs',
	);
}

/**
 * "Довідка про несудимість" — same elevated-risk notice treatment as the
 * legalization group above (prominent private-company / not-a-government-
 * body disclaimer injected under the hero), because this document type is
 * a direct, literal match for Google Ads' "Criminal background checks"
 * restricted category (see docs/google-ads-gov-services-classification.md,
 * page 149 was retired 2026-09-05 for exactly this reason). Re-added
 * 2026-09-21 after KOVAL received Google certification to advertise this
 * as a consultation/informational service specifically — wording on the
 * page and this notice both keep the consultation-only framing (KOVAL
 * does not issue the certificate, only consults on how to obtain it).
 */
function koval_legal_criminal_record_slugs() {
	return array( 'dovidka-pro-nesudymist' );
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

		// The Ukrainian term's slug even on RU pages: it drives the
		// #group-<slug> anchors and the .svc-icon-<slug> CSS icons.
		$source_term = function_exists( 'pll_get_term' ) ? pll_get_term( $term->term_id, 'uk' ) : 0;
		$categories[] = array(
			'slug'             => $source_term && $source_term !== $term->term_id ? get_term( $source_term )->slug : $term->slug,
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
