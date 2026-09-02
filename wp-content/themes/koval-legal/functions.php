<?php
/**
 * KOVAL Legal theme — functions.php
 *
 * RECOVERY REBUILD (2026-09-02): the original functions.php was lost when
 * the project directory was accidentally deleted. This is a best-effort
 * reconstruction of what the theme needs to function, based on observed
 * behavior (URLs, ACF field names, template calls) from earlier in the
 * session — NOT a byte-for-byte recovery of the original file. If anything
 * behaves differently than before, this file is the first place to check.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KOVAL_LEGAL_VERSION', '1.0-recovery' );

/**
 * Theme setup.
 */
function koval_legal_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo' );

	register_nav_menus( array(
		'primary' => 'Головне меню',
		'footer'  => 'Меню в футері',
	) );
}
add_action( 'after_setup_theme', 'koval_legal_setup' );

/**
 * Enqueue styles/scripts — cache-busted with filemtime() so edits don't get
 * stuck behind browser cache.
 */
function koval_legal_enqueue_assets() {
	$style_path = get_stylesheet_directory() . '/style.css';
	wp_enqueue_style( 'koval-legal-style', get_stylesheet_uri(), array(), file_exists( $style_path ) ? filemtime( $style_path ) : KOVAL_LEGAL_VERSION );

	$js_path = get_stylesheet_directory() . '/assets/js/main.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script( 'koval-legal-main', get_stylesheet_directory_uri() . '/assets/js/main.js', array(), filemtime( $js_path ), true );
	}
}
add_action( 'wp_enqueue_scripts', 'koval_legal_enqueue_assets' );

/**
 * Custom post types.
 * Reconstructed from observed URLs/behavior — archive slugs and supports
 * are best-effort, verify against real usage once the site is back up.
 */
function koval_legal_register_post_types() {
	register_post_type( 'service', array(
		'label'        => 'Послуги',
		'public'       => true,
		'has_archive'  => 'poslugy',
		'rewrite'      => array( 'slug' => 'poslugy' ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		'menu_icon'    => 'dashicons-portfolio',
		'show_in_rest' => true,
	) );

	register_post_type( 'testimonial', array(
		'label'        => 'Відгуки',
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'supports'     => array( 'title', 'editor' ),
		'menu_icon'    => 'dashicons-testimonial',
	) );

	register_post_type( 'faq_item', array(
		'label'        => 'FAQ',
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'supports'     => array( 'title', 'editor', 'page-attributes' ),
		'menu_icon'    => 'dashicons-editor-help',
	) );

	register_post_type( 'case_study', array(
		'label'        => 'Кейси',
		'public'       => true,
		'has_archive'  => 'keysy',
		'rewrite'      => array( 'slug' => 'keysy' ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'menu_icon'    => 'dashicons-portfolio',
	) );

	register_post_type( 'koval_lead', array(
		'label'        => 'Заявки',
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'supports'     => array( 'title' ),
		'menu_icon'    => 'dashicons-email-alt',
		'capability_type' => 'post',
	) );

	// case_study was explicitly retired sitewide — keep the CPT (content
	// stays manageable in wp-admin) but 301 any front-end URL to the
	// homepage and drop it from the core XML sitemap.
	add_action( 'template_redirect', function () {
		if ( is_singular( 'case_study' ) || is_post_type_archive( 'case_study' ) ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	} );
	add_filter( 'wp_sitemaps_post_types', function ( $post_types ) {
		unset( $post_types['case_study'] );
		return $post_types;
	} );
}
add_action( 'init', 'koval_legal_register_post_types' );

/**
 * Small helpers used across templates.
 */
function koval_legal_field( $field_name, $post_id = null ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $field_name, $post_id );
	}
	return get_post_meta( $post_id ?: get_the_ID(), $field_name, true );
}

function koval_legal_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	echo '<nav class="breadcrumbs"><div class="wrap">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">Головна</a>';
	if ( is_singular( 'service' ) ) {
		$archive_link = get_post_type_archive_link( 'service' );
		if ( $archive_link ) {
			echo ' <span class="sep">/</span> <a href="' . esc_url( $archive_link ) . '">Послуги</a>';
		}
	}
	echo ' <span class="sep">/</span> <span class="current">' . esc_html( get_the_title() ) . '</span>';
	echo '</div></nav>';
}

function koval_legal_thumbnail_alt() {
	$alt = get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true );
	return $alt ? $alt : get_the_title();
}

/**
 * Contacts theme_mods used by the map/footer shortcodes.
 */
function koval_legal_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'koval_legal_contacts', array(
		'title' => 'Контакти компанії',
	) );

	$wp_customize->add_setting( 'company_address', array( 'default' => "м. Київ, вул. Іоанна Павла ІІ, 23/35, під'їзд 1, офіс 1" ) );
	$wp_customize->add_control( 'company_address', array( 'label' => 'Адреса', 'section' => 'koval_legal_contacts', 'type' => 'text' ) );

	$wp_customize->add_setting( 'company_phone', array( 'default' => '+380 97 192 07 26' ) );
	$wp_customize->add_control( 'company_phone', array( 'label' => 'Телефон', 'section' => 'koval_legal_contacts', 'type' => 'text' ) );

	$wp_customize->add_setting( 'company_email', array( 'default' => '' ) );
	$wp_customize->add_control( 'company_email', array( 'label' => 'Email', 'section' => 'koval_legal_contacts', 'type' => 'text' ) );
}
add_action( 'customize_register', 'koval_legal_customize_register' );

function koval_legal_default_menu() {
	$archive = get_post_type_archive_link( 'service' );
	echo '<ul>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Головна</a></li>';
	if ( $archive ) {
		echo '<li><a href="' . esc_url( $archive ) . '">Послуги</a></li>';
	}
	echo '<li><a href="' . esc_url( home_url( '/pro-nas/' ) ) . '">Про нас</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/blog/' ) ) . '">Блог</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/kontakty/' ) ) . '">Контакти</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/tsiny/' ) ) . '">Ціни</a></li>';
	echo '</ul>';
}

require get_theme_file_path( 'inc/shortcodes.php' );
require get_theme_file_path( 'inc/services-catalog.php' );
require get_theme_file_path( 'inc/homepage-sections.php' );
