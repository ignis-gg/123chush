<?php
/**
 * UA/RU language layer (2026-10-03). Polylang owns the content side
 * (a separate RU copy of every page/service/post, /ru/ URLs, hreflang,
 * per-language menus); this file covers what the theme itself prints.
 *
 *  - koval_t( 'Український текст' ) — the Ukrainian string is the key; on
 *    RU pages it returns the translation from inc/i18n-ru.php, otherwise
 *    (or if a string has no translation yet) the Ukrainian text as is.
 *  - koval_page_url( 'pro-nas' ) / koval_home_url() — links to fixed
 *    pages that follow the current language.
 *  - koval_source_slug() — the Ukrainian slug of a post, so code that
 *    picks a layout by slug (service-lists.php, page.php) treats the RU
 *    copy exactly like its Ukrainian original.
 *  - koval_language_switcher() — the UA | RU switch in the header.
 *
 * Everything degrades to plain Ukrainian when Polylang is inactive.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function koval_lang() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	return $lang ? $lang : 'uk';
}

function koval_is_ru() {
	return 'ru' === koval_lang();
}

function koval_t( $uk ) {
	static $dict = null;
	if ( ! koval_is_ru() ) {
		return $uk;
	}
	if ( null === $dict ) {
		$dict = require __DIR__ . '/i18n-ru.php';
	}
	return $dict[ $uk ] ?? $uk;
}

function koval_home_url( $fragment = '' ) {
	$url = function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
	return $url . $fragment;
}

/**
 * Translation of a post (any language) into the current language — or
 * the post itself when there is no translation.
 */
function koval_translated_id( $post_id, $lang = null ) {
	if ( ! $post_id || ! function_exists( 'pll_get_post' ) ) {
		return $post_id;
	}
	$tr = pll_get_post( $post_id, $lang ? $lang : koval_lang() );
	return $tr ? $tr : $post_id;
}

/**
 * Permalink of a fixed Ukrainian page (by its UA slug) in the current
 * language: koval_page_url( 'kontakty' ) → /kontakty/ or /ru/svyaz-s-nami/.
 */
function koval_page_url( $ua_slug, $fragment = '' ) {
	$page = get_page_by_path( $ua_slug );
	if ( ! $page ) {
		return home_url( '/' . $ua_slug . '/' ) . $fragment;
	}
	return get_permalink( koval_translated_id( $page->ID ) ) . $fragment;
}

function koval_blog_url() {
	return get_permalink( koval_translated_id( (int) get_option( 'page_for_posts' ) ) );
}

function koval_privacy_url() {
	$id = (int) get_option( 'wp_page_for_privacy_policy' );
	return $id ? get_permalink( koval_translated_id( $id ) ) : get_privacy_policy_url();
}

/** Ukrainian original's ID of a post (itself if it is Ukrainian/untranslated). */
function koval_source_id( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	return (int) koval_translated_id( $post_id, 'uk' );
}

/** Ukrainian original's slug — the stable key for slug-based layout lists. */
function koval_source_slug( $post_id = null ) {
	return urldecode( (string) get_post_field( 'post_name', koval_source_id( $post_id ) ) );
}

/**
 * Header UA | RU switch. Links to the translation of the current page;
 * a page without a translation links to the other language's homepage
 * (Polylang's hide_if_no_translation is off on purpose, so the switch
 * never disappears).
 */
function koval_language_switcher( $extra_class = '' ) {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}
	$langs = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'force_home' => 0 ) );
	if ( empty( $langs ) || count( $langs ) < 2 ) {
		return '';
	}
	$labels = array( 'uk' => 'UA', 'ru' => 'RU' );
	$names  = array( 'uk' => 'Українська', 'ru' => 'Русский' );
	$out    = '<div class="lang-switch' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' ) . '" role="group" aria-label="' . esc_attr( koval_t( 'Мова сайту' ) ) . '">';
	foreach ( array( 'uk', 'ru' ) as $slug ) {
		if ( empty( $langs[ $slug ] ) ) {
			continue;
		}
		$l = $langs[ $slug ];
		if ( ! empty( $l['current_lang'] ) ) {
			$out .= '<span class="lang-switch__item is-current" aria-current="true" lang="' . esc_attr( $l['locale'] ? str_replace( '_', '-', $l['locale'] ) : $slug ) . '" title="' . esc_attr( $names[ $slug ] ) . '">' . esc_html( $labels[ $slug ] ) . '</span>';
		} else {
			$out .= '<a class="lang-switch__item" href="' . esc_url( $l['url'] ) . '" hreflang="' . esc_attr( $slug ) . '" lang="' . esc_attr( $slug ) . '" title="' . esc_attr( $names[ $slug ] ) . '" data-lang="' . esc_attr( $slug ) . '">' . esc_html( $labels[ $slug ] ) . '</a>';
		}
	}
	$out .= '</div>';
	return $out;
}

/**
 * Strings that front-end JS prints (Viber hint, Ads-landing toast).
 */
add_action( 'wp_enqueue_scripts', function () {
	$strings = array(
		'close'       => koval_t( 'Закрити' ),
		'viberHint'   => koval_t( 'Якщо Viber не відкрився — напишіть нам у Viber на номер' ),
		'copyNumber'  => koval_t( 'Скопіювати номер' ),
		'copied'      => koval_t( 'Скопійовано ✓' ),
		'callbackErr' => koval_t( 'Не вдалося відкрити вікно зворотного дзвінка. Оновіть сторінку й спробуйте ще раз.' ),
	);
	wp_add_inline_script( 'koval-legal-main', 'window.KOVAL_I18N=' . wp_json_encode( $strings, JSON_UNESCAPED_UNICODE ) . ';', 'before' );
}, 20 );

/**
 * Leads from the RU version: mark the language in the lead's "service"
 * label so the call centre sees which version the visitor used.
 */
function koval_lead_service_label( $label ) {
	return koval_is_ru() && '' !== $label ? $label . ' [RU]' : $label;
}

/**
 * Rank Math builds the /poslugy/ archive title/description from the CPT
 * label ("Послуги"), which isn't per-language — give the RU catalog its
 * own.
 */
add_filter( 'rank_math/frontend/title', function ( $title ) {
	return is_post_type_archive( 'service' ) && koval_is_ru() ? 'Услуги - KOVAL Legal Group' : $title;
} );
add_filter( 'rank_math/opengraph/facebook/og_title', function ( $title ) {
	return is_post_type_archive( 'service' ) && koval_is_ru() ? 'Услуги - KOVAL Legal Group' : $title;
} );
foreach ( array( 'rank_math/frontend/description', 'rank_math/opengraph/facebook/og_description' ) as $koval_hook ) {
	add_filter( $koval_hook, function ( $desc ) {
		return is_post_type_archive( 'service' ) && koval_is_ru()
			? 'Каталог услуг KOVAL Legal Group: консультации по апостилю и легализации документов, семейным, образовательным и судебным вопросам — с фиксированной стоимостью и сроками.'
			: $desc;
	} );
}

/**
 * Blog category tabs (home.php, category.php): the current language's
 * categories, without "Uncategorized" (ID 1 and its RU twin), in the
 * fixed editorial order — keyed by the Ukrainian slug so the RU tabs
 * (slugs "…-ru") follow it too.
 */
function koval_blog_categories() {
	$order   = array( 'dokumenty-dracs', 'legalizatsiya-apostyl', 'simeyne-pravo', 'biznes-fop', 'sudovi-pytannya' );
	$exclude = array( 1 );
	if ( function_exists( 'pll_get_term' ) && pll_get_term( 1, 'ru' ) ) {
		$exclude[] = (int) pll_get_term( 1, 'ru' );
	}
	$cats = get_categories( array( 'hide_empty' => false, 'exclude' => $exclude ) );
	$key  = function ( $cat ) use ( $order ) {
		$slug = $cat->slug;
		if ( function_exists( 'pll_get_term' ) ) {
			$ua = pll_get_term( $cat->term_id, 'uk' );
			if ( $ua && (int) $ua !== (int) $cat->term_id ) {
				$slug = get_term( $ua )->slug;
			}
		}
		$i = array_search( $slug, $order, true );
		return false === $i ? PHP_INT_MAX : $i;
	};
	usort( $cats, function ( $a, $b ) use ( $key ) {
		return $key( $a ) <=> $key( $b );
	} );
	return $cats;
}
