<?php
/**
 * Exports the Ukrainian source of every public post/page/service/FAQ/
 * testimonial + service/blog category terms into JSON files for the
 * Russian translation (RU version of the site, 2026-10-03).
 *
 * One file per post: bin/migrations/assets/ru/src/<type>-<ID>.json with
 * the fields that are actually rendered on the front end:
 *  - title, excerpt
 *  - content — only where it is rendered (pages, blog posts, FAQ,
 *    testimonials); services render from ACF fields, their post_content
 *    is an unrendered legacy fallback and is skipped
 *  - meta — every non-underscore meta key whose value holds Cyrillic text
 *    (ACF fields, Rank Math title/description), unserialized
 * The translated copies (same shape + ru_slug) live in assets/ru/tr/ and
 * are imported by 2026-10-03-ru-import.php.
 *
 * Run: wp eval-file bin/migrations/ru-export-source.php
 */

$koval_dir = __DIR__ . '/assets/ru/src';
if ( ! is_dir( $koval_dir ) ) {
	mkdir( $koval_dir, 0775, true );
}

$koval_skip_meta = array( 'rank_math_internal_links_processed' );
$koval_count     = 0;

foreach ( get_posts( array(
	'post_type'      => array( 'page', 'post', 'service', 'faq_item', 'testimonial' ),
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'lang'           => '', // all languages, if Polylang is active.
	'orderby'        => 'ID',
	'order'          => 'ASC',
) ) as $koval_post ) {
	if ( function_exists( 'pll_get_post_language' ) && 'ru' === pll_get_post_language( $koval_post->ID ) ) {
		continue;
	}
	$koval_item = array(
		'id'      => $koval_post->ID,
		'type'    => $koval_post->post_type,
		'slug'    => urldecode( $koval_post->post_name ),
		'title'   => $koval_post->post_title,
		'excerpt' => $koval_post->post_excerpt,
	);
	if ( 'service' !== $koval_post->post_type ) {
		$koval_item['content'] = $koval_post->post_content;
	}
	$koval_meta = array();
	foreach ( get_post_meta( $koval_post->ID ) as $koval_key => $koval_values ) {
		if ( '_' === $koval_key[0] || in_array( $koval_key, $koval_skip_meta, true ) ) {
			continue;
		}
		if ( ! preg_match( '/\p{Cyrillic}/u', $koval_values[0] ) ) {
			continue;
		}
		$koval_meta[ $koval_key ] = maybe_unserialize( $koval_values[0] );
	}
	$koval_item['meta'] = $koval_meta;
	file_put_contents(
		$koval_dir . '/' . $koval_post->post_type . '-' . $koval_post->ID . '.json',
		wp_json_encode( $koval_item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n"
	);
	$koval_count++;
}

// Terms: service categories (catalog groups) + blog categories.
$koval_terms = array();
foreach ( array( 'service_category', 'category' ) as $koval_tax ) {
	foreach ( get_terms( array( 'taxonomy' => $koval_tax, 'hide_empty' => false, 'lang' => '' ) ) as $koval_term ) {
		if ( function_exists( 'pll_get_term_language' ) && 'ru' === pll_get_term_language( $koval_term->term_id ) ) {
			continue;
		}
		$koval_tmeta = array();
		foreach ( get_term_meta( $koval_term->term_id ) as $koval_key => $koval_values ) {
			if ( '_' !== $koval_key[0] && preg_match( '/\p{Cyrillic}/u', $koval_values[0] ) ) {
				$koval_tmeta[ $koval_key ] = maybe_unserialize( $koval_values[0] );
			}
		}
		$koval_terms[] = array(
			'taxonomy'    => $koval_tax,
			'id'          => $koval_term->term_id,
			'slug'        => $koval_term->slug,
			'name'        => $koval_term->name,
			'description' => $koval_term->description,
			'meta'        => $koval_tmeta,
		);
	}
}
file_put_contents( $koval_dir . '/terms.json', wp_json_encode( $koval_terms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n" );

// ACF options (site-wide CTA block, legalization disclaimer).
$koval_options = array();
global $wpdb;
foreach ( $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'options\\_%' ORDER BY option_name" ) as $koval_row ) {
	if ( preg_match( '/\p{Cyrillic}/u', $koval_row->option_value ) ) {
		$koval_options[ $koval_row->option_name ] = $koval_row->option_value;
	}
}
file_put_contents( $koval_dir . '/options.json', wp_json_encode( $koval_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n" );

WP_CLI::success( "Exported $koval_count posts, " . count( $koval_terms ) . ' terms, ' . count( $koval_options ) . " options to $koval_dir" );
