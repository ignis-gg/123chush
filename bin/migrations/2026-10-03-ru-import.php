<?php
/**
 * RU version of the site, step 2 of 2 — creates/updates the Russian copy
 * of every page, service, blog post, FAQ item, testimonial and category
 * from the translations in assets/ru/tr/*.json (made from assets/ru/src,
 * see assets/ru/TRANSLATION-GUIDE.md), links each copy to its Ukrainian
 * original in Polylang, and builds the RU header menu.
 *
 *  - the UA original is found by post type + Ukrainian slug (not by ID);
 *    an existing RU translation is updated in place, so the script is
 *    idempotent and re-running it after editing a tr/*.json refreshes
 *    the RU page
 *  - meta: everything is copied from the UA post (ACF field references,
 *    thumbnails, toggles, sort order), then the translated fields are put
 *    on top; post IDs (ads_related, related_service) point at the RU copies
 *  - services render from ACF, so their RU post_content stays empty (the
 *    UA post_content there is an unrendered legacy fallback)
 *  - internal links in content and URL fields are rewritten to the RU
 *    pages in a second pass, once every RU page exists
 *
 * Needs step 1 (2026-10-03-ru-polylang-setup.php) first.
 * Run: wp eval-file bin/migrations/2026-10-03-ru-import.php
 */

if ( ! function_exists( 'pll_set_post_language' ) ) {
	WP_CLI::error( 'Polylang is not active — run 2026-10-03-ru-polylang-setup.php first.' );
}
if ( ! PLL()->model->get_language( 'ru' ) ) {
	WP_CLI::error( 'No "ru" language — run 2026-10-03-ru-polylang-setup.php first.' );
}

// No user under WP-CLI → no unfiltered_html → kses would strip the inline
// <svg>/<button> markup from page content (see docs/known-issues.md).
kses_remove_filters();

$koval_dir      = __DIR__ . '/assets/ru';
$koval_skip     = array( '_edit_lock', '_edit_last', 'rank_math_internal_links_processed', '_pingme', '_encloseme' );
$koval_id_meta  = array( 'ads_related', 'related_service' );
$koval_pairs    = array(); // UA ID => RU ID.
$koval_warnings = array();

/* ------------------------------------------------------------------ terms */

$koval_terms = json_decode( file_get_contents( $koval_dir . '/tr/terms.json' ), true );
foreach ( $koval_terms as $koval_t ) {
	$koval_ua = get_term_by( 'slug', $koval_t['slug'], $koval_t['taxonomy'] );
	if ( ! $koval_ua ) {
		$koval_warnings[] = "term {$koval_t['taxonomy']}/{$koval_t['slug']} not found — skipped";
		continue;
	}
	$koval_ru_id = pll_get_term( $koval_ua->term_id, 'ru' );
	$koval_args  = array(
		'name'        => $koval_t['name'],
		'description' => $koval_t['description'],
		'slug'        => $koval_t['slug'] . '-ru',
	);
	if ( $koval_ru_id ) {
		wp_update_term( $koval_ru_id, $koval_t['taxonomy'], $koval_args );
	} else {
		$koval_res = wp_insert_term( $koval_t['name'], $koval_t['taxonomy'], $koval_args );
		if ( is_wp_error( $koval_res ) ) {
			$koval_warnings[] = "term {$koval_t['slug']}: " . $koval_res->get_error_message();
			continue;
		}
		$koval_ru_id = $koval_res['term_id'];
		pll_set_term_language( $koval_ru_id, 'ru' );
		pll_save_term_translations( array( 'uk' => $koval_ua->term_id, 'ru' => $koval_ru_id ) );
	}
	foreach ( get_term_meta( $koval_ua->term_id ) as $koval_key => $koval_values ) {
		update_term_meta( $koval_ru_id, $koval_key, maybe_unserialize( $koval_values[0] ) );
	}
	foreach ( (array) $koval_t['meta'] as $koval_key => $koval_value ) {
		update_term_meta( $koval_ru_id, $koval_key, $koval_value );
	}
}
WP_CLI::log( 'Terms: ' . count( $koval_terms ) . ' processed.' );

/* ------------------------------------------------------------------ posts */

$koval_files = glob( $koval_dir . '/tr/*-*.json' );
$koval_items = array();
foreach ( $koval_files as $koval_file ) {
	$koval_item = json_decode( file_get_contents( $koval_file ), true );
	if ( ! $koval_item || empty( $koval_item['type'] ) ) {
		WP_CLI::error( 'Bad JSON: ' . basename( $koval_file ) );
	}
	$koval_ua = get_page_by_path( $koval_item['slug'], OBJECT, $koval_item['type'] );
	if ( ! $koval_ua || 'publish' !== $koval_ua->post_status ) {
		$koval_warnings[] = "{$koval_item['type']}/{$koval_item['slug']} not found on this site — skipped";
		continue;
	}
	if ( 'uk' !== pll_get_post_language( $koval_ua->ID ) ) {
		pll_set_post_language( $koval_ua->ID, 'uk' );
	}
	$koval_items[ $koval_ua->ID ] = $koval_item;
}

// Pass 1: create/update the RU posts (pages sorted parents-first).
uasort( $koval_items, function ( $a, $b ) {
	return strcmp( $a['type'], $b['type'] );
} );
foreach ( $koval_items as $koval_ua_id => $koval_item ) {
	$koval_ua    = get_post( $koval_ua_id );
	$koval_ru_id = pll_get_post( $koval_ua_id, 'ru' );
	if ( ! $koval_ru_id ) {
		// A copy left by an interrupted earlier run: adopt it, don't duplicate.
		$koval_prev = get_page_by_path( $koval_item['ru_slug'], OBJECT, $koval_ua->post_type );
		if ( $koval_prev && (int) $koval_prev->ID !== (int) $koval_ua_id && ! array_key_exists( $koval_prev->ID, $koval_items ) ) {
			$koval_ru_id = (int) $koval_prev->ID;
		}
	}
	$koval_data  = array(
		'post_type'      => $koval_ua->post_type,
		'post_status'    => 'publish',
		'post_title'     => $koval_item['title'],
		'post_excerpt'   => $koval_item['excerpt'],
		'post_content'   => 'service' === $koval_ua->post_type ? '' : ( $koval_item['content'] ?? '' ),
		'post_name'      => $koval_item['ru_slug'],
		'menu_order'     => $koval_ua->menu_order,
		'post_author'    => $koval_ua->post_author,
		'post_date'      => $koval_ua->post_date,
		'post_date_gmt'  => $koval_ua->post_date_gmt,
		'comment_status' => $koval_ua->comment_status,
		'ping_status'    => $koval_ua->ping_status,
		'post_parent'    => $koval_ua->post_parent ? (int) pll_get_post( $koval_ua->post_parent, 'ru' ) : 0,
	);
	if ( $koval_ru_id ) {
		$koval_data['ID'] = $koval_ru_id;
		$koval_res        = wp_update_post( wp_slash( $koval_data ), true );
	} else {
		$koval_res = wp_insert_post( wp_slash( $koval_data ), true );
	}
	if ( is_wp_error( $koval_res ) ) {
		WP_CLI::error( "{$koval_item['type']}/{$koval_item['slug']}: " . $koval_res->get_error_message() );
	}
	$koval_ru_id = (int) $koval_res;
	// Always: Polylang gives a newly inserted post the default language (uk).
	pll_set_post_language( $koval_ru_id, 'ru' );
	pll_save_post_translations( array( 'uk' => $koval_ua_id, 'ru' => $koval_ru_id ) );
	$koval_pairs[ $koval_ua_id ] = $koval_ru_id;

	$koval_actual_slug = urldecode( get_post_field( 'post_name', $koval_ru_id ) );
	if ( $koval_actual_slug !== $koval_item['ru_slug'] ) {
		$koval_warnings[] = "{$koval_item['type']}/{$koval_item['slug']}: RU slug became $koval_actual_slug (wanted {$koval_item['ru_slug']})";
	}

	// Meta: UA copy first, translated fields on top.
	foreach ( get_post_meta( $koval_ua_id ) as $koval_key => $koval_values ) {
		if ( in_array( $koval_key, $koval_skip, true ) || isset( $koval_item['meta'][ $koval_key ] ) ) {
			continue;
		}
		update_post_meta( $koval_ru_id, $koval_key, wp_slash( maybe_unserialize( $koval_values[0] ) ) );
	}
	foreach ( (array) $koval_item['meta'] as $koval_key => $koval_value ) {
		update_post_meta( $koval_ru_id, $koval_key, wp_slash( $koval_value ) );
	}

	// Taxonomies: the RU translations of the UA post's terms.
	foreach ( get_object_taxonomies( $koval_ua->post_type ) as $koval_tax ) {
		if ( ! pll_is_translated_taxonomy( $koval_tax ) ) {
			continue;
		}
		$koval_ru_terms = array();
		foreach ( wp_get_object_terms( $koval_ua_id, $koval_tax, array( 'fields' => 'ids' ) ) as $koval_term_id ) {
			$koval_tr = pll_get_term( $koval_term_id, 'ru' );
			if ( $koval_tr ) {
				$koval_ru_terms[] = (int) $koval_tr;
			}
		}
		wp_set_object_terms( $koval_ru_id, $koval_ru_terms, $koval_tax );
	}
}
WP_CLI::log( 'Posts: ' . count( $koval_pairs ) . ' RU copies created/updated.' );

/* ------------------------------------------------ pass 2: links and IDs */

// wp eval-file runs this file inside a function — share with the helpers
// below through $GLOBALS explicitly.
$GLOBALS['koval_home']    = untrailingslashit( home_url() );
$GLOBALS['koval_ru_lang'] = PLL()->model->get_language( 'ru' );
$GLOBALS['koval_pairs']   = $koval_pairs;

/** Maps one UA URL (absolute on this site, or root-relative) to its RU twin. */
function koval_ru_map_url( $url ) {
	$koval_home    = $GLOBALS['koval_home'];
	$koval_ru_lang = $GLOBALS['koval_ru_lang'];
	$koval_pairs   = $GLOBALS['koval_pairs'];
	$is_relative = 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' );
	if ( ! $is_relative && 0 !== strpos( $url, $koval_home ) ) {
		return $url; // external, mailto:, tel:, #anchor…
	}
	if ( false !== strpos( $url, '/wp-content/' ) || false !== strpos( $url, '/ru/' ) ) {
		return $url;
	}
	$fragment = '';
	if ( false !== ( $hash = strpos( $url, '#' ) ) ) {
		$fragment = substr( $url, $hash );
		$url      = substr( $url, 0, $hash );
	}
	$absolute = $is_relative ? $koval_home . $url : $url;
	$post_id  = url_to_postid( $absolute );
	if ( $post_id && isset( $koval_pairs[ $post_id ] ) ) {
		$mapped = get_permalink( $koval_pairs[ $post_id ] );
	} elseif ( $post_id ) {
		return ( $is_relative ? $url : $absolute ) . $fragment; // no RU copy — keep UA.
	} else {
		$mapped = PLL()->links_model->add_language_to_link( $absolute, $koval_ru_lang ); // home, /poslugy/ archive.
	}
	if ( $is_relative ) {
		$mapped = substr( $mapped, strlen( $koval_home ) );
	}
	return $mapped . $fragment;
}

function koval_ru_map_value( $value, $key = '' ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			$value[ $k ] = koval_ru_map_value( $v, is_string( $k ) ? $k : $key );
		}
		return $value;
	}
	if ( ! is_string( $value ) || '' === $value ) {
		return $value;
	}
	if ( preg_match( '#^(https?://|/)[^\s<>"]*$#', $value ) ) {
		return koval_ru_map_url( $value );
	}
	if ( false !== strpos( $value, 'href=' ) ) {
		return preg_replace_callback( '#href=(["\'])([^"\']+)\1#', function ( $m ) {
			return 'href=' . $m[1] . koval_ru_map_url( $m[2] ) . $m[1];
		}, $value );
	}
	return $value;
}

$koval_links = 0;
foreach ( $koval_pairs as $koval_ua_id => $koval_ru_id ) {
	$koval_ru = get_post( $koval_ru_id );
	$koval_new_content = koval_ru_map_value( $koval_ru->post_content );
	if ( $koval_new_content !== $koval_ru->post_content ) {
		wp_update_post( wp_slash( array( 'ID' => $koval_ru_id, 'post_content' => $koval_new_content ) ) );
		$koval_links++;
	}
	foreach ( get_post_meta( $koval_ru_id ) as $koval_key => $koval_values ) {
		if ( '_' === $koval_key[0] ) {
			continue;
		}
		$koval_old = maybe_unserialize( $koval_values[0] );
		if ( in_array( $koval_key, $koval_id_meta, true ) ) {
			$koval_new = is_array( $koval_old )
				? array_map( function ( $id ) {
					$tr = pll_get_post( (int) $id, 'ru' );
					return $tr ? (string) $tr : $id;
				}, $koval_old )
				: ( pll_get_post( (int) $koval_old, 'ru' ) ? (string) pll_get_post( (int) $koval_old, 'ru' ) : $koval_old );
		} else {
			$koval_new = koval_ru_map_value( $koval_old );
		}
		if ( $koval_new !== $koval_old ) {
			update_post_meta( $koval_ru_id, $koval_key, wp_slash( $koval_new ) );
			$koval_links++;
		}
	}
}
WP_CLI::log( "Links/IDs remapped in $koval_links fields." );

/* ------------------------------------------------------------- RU menu */

$koval_menu_titles = array(
	'Головна' => 'Главная',
	'Послуги' => 'Услуги',
	'Про нас' => 'О нас',
	'Блог'    => 'Блог',
	'Контакти' => 'Контакты',
	'Ціни'    => 'Цены',
);
$koval_nav = PLL()->options->get( 'nav_menus' );
foreach ( (array) ( $koval_nav[ get_stylesheet() ] ?? array() ) as $koval_location => $koval_by_lang ) {
	if ( empty( $koval_by_lang['uk'] ) ) {
		continue;
	}
	$koval_ua_menu = wp_get_nav_menu_object( $koval_by_lang['uk'] );
	$koval_ru_menu = ! empty( $koval_by_lang['ru'] ) ? wp_get_nav_menu_object( $koval_by_lang['ru'] ) : null;
	if ( ! $koval_ru_menu ) {
		$koval_ru_menu_id = wp_create_nav_menu( $koval_ua_menu->name . ' (RU)' );
		if ( is_wp_error( $koval_ru_menu_id ) ) {
			$koval_ru_menu = wp_get_nav_menu_object( $koval_ua_menu->name . ' (RU)' );
			$koval_ru_menu_id = $koval_ru_menu ? $koval_ru_menu->term_id : 0;
		}
	} else {
		$koval_ru_menu_id = $koval_ru_menu->term_id;
	}
	if ( ! $koval_ru_menu_id ) {
		$koval_warnings[] = "menu for $koval_location not created";
		continue;
	}
	foreach ( (array) wp_get_nav_menu_items( $koval_ru_menu_id ) as $koval_old_item ) {
		wp_delete_post( $koval_old_item->ID, true ); // rebuilt from the UA menu each run.
	}
	foreach ( wp_get_nav_menu_items( $koval_ua_menu->term_id ) as $koval_mi ) {
		$koval_title = $koval_menu_titles[ $koval_mi->title ] ?? $koval_mi->title;
		$koval_args  = array(
			'menu-item-title'    => $koval_title,
			'menu-item-status'   => 'publish',
			'menu-item-position' => $koval_mi->menu_order,
		);
		if ( 'post_type' === $koval_mi->type && isset( $koval_pairs[ (int) $koval_mi->object_id ] ) ) {
			$koval_args['menu-item-type']      = 'post_type';
			$koval_args['menu-item-object']    = $koval_mi->object;
			$koval_args['menu-item-object-id'] = $koval_pairs[ (int) $koval_mi->object_id ];
		} else {
			$koval_args['menu-item-type'] = 'custom';
			$koval_args['menu-item-url']  = koval_ru_map_url( $koval_mi->url );
		}
		wp_update_nav_menu_item( $koval_ru_menu_id, 0, $koval_args );
	}
	$koval_nav[ get_stylesheet() ][ $koval_location ]['ru'] = (int) $koval_ru_menu_id;
}
PLL()->options->set( 'nav_menus', $koval_nav );
PLL()->options->save();

/* ---------------------------------------------------------------- caches */

// Language objects cache the translated front/blog page IDs.
PLL()->model->clean_languages_cache();
flush_rewrite_rules( false );
if ( class_exists( '\RankMath\Sitemap\Cache' ) ) {
	\RankMath\Sitemap\Cache::invalidate_storage();
}
wp_cache_flush();

foreach ( $koval_warnings as $koval_w ) {
	WP_CLI::warning( $koval_w );
}
WP_CLI::success( count( $koval_pairs ) . ' RU posts linked. Front page RU: ' . get_permalink( pll_get_post( (int) get_option( 'page_on_front' ), 'ru' ) ) );
