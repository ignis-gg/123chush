<?php
/**
 * JSON-LD structured data. One combined @graph per page, output in wp_head:
 * Organization/LegalService always; Service + FAQPage on service posts (both
 * the 46 rich landings and the 6 pillar hubs use the same faq_items shape);
 * BlogPosting on the 4 real blog posts.
 *
 * Deliberately no Offer/price schema: service_price is free text ("від 3 500
 * грн"), not a clean number+currency pair — inventing structured pricing
 * from that risks Google surfacing a wrong price, which is worse than no
 * price markup at all.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function koval_schema_organization() {
	$phone   = get_theme_mod( 'company_phone', '+380 97 192 07 26' );
	$email   = get_theme_mod( 'company_email', 'callcenter.via.klg@gmail.com' );
	$address = get_theme_mod( 'company_address', "м. Київ, вул. Іоанна Павла ІІ, 23/35, під'їзд 1, офіс 1" );

	$same_as = array_values( array_filter( array(
		function_exists( 'get_field' ) ? get_field( 'facebook_url', 'option' ) : '',
		function_exists( 'get_field' ) ? get_field( 'instagram_url', 'option' ) : '',
		function_exists( 'get_field' ) ? get_field( 'youtube_url', 'option' ) : '',
		function_exists( 'get_field' ) ? get_field( 'telegram_url', 'option' ) : '',
	) ) );

	return array(
		'@type'        => 'LegalService',
		'@id'          => home_url( '/#organization' ),
		'name'         => 'KOVAL Legal Group',
		'url'          => home_url( '/' ),
		'telephone'    => $phone,
		'email'        => $email,
		'foundingDate' => '1998',
		'address'      => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $address,
			'addressLocality' => 'Київ',
			'addressCountry'  => 'UA',
		),
		'areaServed'   => 'UA',
		'sameAs'       => $same_as,
	);
}

function koval_schema_faq_entities( $items ) {
	if ( empty( $items ) ) {
		return array();
	}
	$entities = array();
	foreach ( $items as $item ) {
		if ( empty( $item['question'] ) || empty( $item['answer'] ) ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $item['question'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $item['answer'] ),
			),
		);
	}
	return $entities;
}

function koval_schema_service_node( $post_id ) {
	$title = get_post_meta( $post_id, 'rank_math_title', true );
	$desc  = get_post_meta( $post_id, 'rank_math_description', true );

	return array(
		'@type'       => 'Service',
		'name'        => $title ?: get_the_title( $post_id ),
		'description' => $desc ?: wp_strip_all_tags( get_the_excerpt( $post_id ) ),
		'serviceType' => get_the_title( $post_id ),
		'provider'    => array( '@id' => home_url( '/#organization' ) ),
		'areaServed'  => 'UA',
		'url'         => get_permalink( $post_id ),
	);
}

function koval_schema_blogposting_node( $post_id ) {
	$node = array(
		'@type'            => 'BlogPosting',
		'headline'         => get_the_title( $post_id ),
		'description'      => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
		'datePublished'    => get_the_date( 'c', $post_id ),
		'dateModified'     => get_the_modified_date( 'c', $post_id ),
		'url'              => get_permalink( $post_id ),
		'mainEntityOfPage' => get_permalink( $post_id ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => 'Олег Коваль',
		),
		'publisher'        => array( '@id' => home_url( '/#organization' ) ),
	);
	if ( has_post_thumbnail( $post_id ) ) {
		$node['image'] = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'large' );
	}
	return $node;
}

function koval_schema_output() {
	$graph = array( koval_schema_organization() );

	if ( is_singular( 'service' ) ) {
		$post_id = get_the_ID();
		$graph[] = koval_schema_service_node( $post_id );

		$faq_items = get_field( 'faq_items', $post_id );
		if ( empty( $faq_items ) ) {
			$faq_items = get_field( 'pillar_faq_items', $post_id );
		}
		$entities = koval_schema_faq_entities( $faq_items );
		if ( $entities ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => $entities,
			);
		}
	} elseif ( is_singular( 'post' ) ) {
		$graph[] = koval_schema_blogposting_node( get_the_ID() );
	}

	echo '<script type="application/ld+json">' . wp_json_encode( array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'koval_schema_output' );
