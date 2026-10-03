<?php
/**
 * RU version of the site, step 1 of 2 — Polylang setup (2026-10-03).
 *
 *  - languages: Українська (uk, default) + Русский (ru)
 *  - URLs: language from the directory; the default language has no
 *    prefix, so every existing UA URL stays exactly as it is; RU lives
 *    under /ru/ (RU front page = /ru/). No browser-language redirect: the Ukrainian version must
 *    open by default (language law + Google Ads landings keep their URLs).
 *  - translatable: posts, pages, services, FAQ, testimonials, blog and
 *    service categories. Media is shared (not translated). Leads
 *    (koval_lead) stay language-less.
 *  - every existing post/term gets the Ukrainian language.
 *
 * Idempotent. Needs the polylang plugin active.
 * Run: wp eval-file bin/migrations/2026-10-03-ru-polylang-setup.php
 * Step 2: 2026-10-03-ru-import.php
 */

if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	WP_CLI::error( 'Polylang is not active.' );
}

$koval_model = PLL()->model;

$koval_wanted = array(
	array( 'name' => 'Українська', 'slug' => 'uk', 'locale' => 'uk', 'rtl' => false, 'term_group' => 0, 'flag' => 'ua' ),
	array( 'name' => 'Русский', 'slug' => 'ru', 'locale' => 'ru_RU', 'rtl' => false, 'term_group' => 1, 'flag' => 'ru', 'no_default_cat' => true ),
);
foreach ( $koval_wanted as $koval_lang ) {
	if ( $koval_model->get_language( $koval_lang['slug'] ) ) {
		WP_CLI::log( "Language {$koval_lang['slug']} already exists." );
		continue;
	}
	$koval_result = $koval_model->languages->add( $koval_lang );
	if ( is_wp_error( $koval_result ) ) {
		WP_CLI::error( "Adding {$koval_lang['slug']}: " . $koval_result->get_error_message() );
	}
	WP_CLI::log( "Language {$koval_lang['slug']} added." );
}
$koval_model->clean_languages_cache();

$koval_options = PLL()->options;
foreach ( array(
	'force_lang'    => 1,
	'hide_default'  => true,
	'rewrite'       => true,
	'redirect_lang' => true, // RU front page is /ru/, not /ru/glavnaya/.
	'browser'       => false,
	'media_support' => false,
	'post_types'    => array( 'service', 'faq_item', 'testimonial' ),
	'taxonomies'    => array( 'service_category' ),
	'sync'          => array(),
) as $koval_key => $koval_value ) {
	$koval_err = $koval_options->set( $koval_key, $koval_value );
	if ( $koval_err->has_errors() ) {
		WP_CLI::warning( "Option $koval_key: " . $koval_err->get_error_message() );
	}
}
// Polylang takes over menu locations per language — without this the
// existing menu disappears (header falls back to the hardcoded list).
// The RU menu is added by the import step.
$koval_theme_mods = get_option( 'theme_mods_' . get_stylesheet(), array() );
$koval_nav_menus  = $koval_options->get( 'nav_menus' );
foreach ( (array) ( $koval_theme_mods['nav_menu_locations'] ?? array() ) as $koval_location => $koval_menu_id ) {
	if ( $koval_menu_id && empty( $koval_nav_menus[ get_stylesheet() ][ $koval_location ]['uk'] ) ) {
		$koval_nav_menus[ get_stylesheet() ][ $koval_location ]['uk'] = (int) $koval_menu_id;
	}
}
$koval_err = $koval_options->set( 'nav_menus', $koval_nav_menus );
if ( $koval_err->has_errors() ) {
	WP_CLI::warning( 'nav_menus: ' . $koval_err->get_error_message() );
}

if ( 'uk' !== $koval_options->get( 'default_lang' ) ) {
	$koval_err = $koval_model->languages->update_default( 'uk' );
	if ( $koval_err->has_errors() ) {
		WP_CLI::error( 'Default language: ' . $koval_err->get_error_message() );
	}
}
$koval_options->save();

// Polylang only knows about the CPTs/taxonomies from the options above
// once they're saved — re-read them before the mass assignment.
$koval_model->clean_languages_cache();
$koval_model->set_language_in_mass( $koval_model->get_language( 'uk' ) );

$koval_left = $koval_model->get_objects_with_no_lang( 10 );
WP_CLI::log( 'Objects still without language: ' . wp_json_encode( $koval_left ) );

flush_rewrite_rules( false );
WP_CLI::success( 'Polylang configured: ' . wp_json_encode( get_option( 'polylang' ) ) );
