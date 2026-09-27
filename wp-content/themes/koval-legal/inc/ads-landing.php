<?php
/**
 * Google Ads landing template (2026-09-27) — the 6 pages from
 * mocup/TZ_dyzajn_shablon_ads_landing.md (approved design: Claude Design
 * prototype linked there).
 *
 * The page's single job is to get the visitor's phone number to the sales
 * manager: every CTA opens the Binotel GetCall "Передзвоніть мені" window,
 * no form and no chat buttons (the sitewide Binotel chat widget stays). A
 * service post opts in via the "ads_landing" ACF toggle (not an ID list —
 * dev and prod IDs differ, see koval_legal_criminal_record_ids()).
 *
 * Text that is the same on every landing (steps, stats, hours, final CTA)
 * lives in template-parts/ads-landing.php; everything page-specific is an
 * ACF field below so an editor can change it without touching code.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Binotel GetCall widget id (footer.php loads widget xzcao5s8l0chc86m3rgm,
 * whose config registers itself as BinotelGetCall['88044']).
 */
const KOVAL_BINOTEL_GETCALL_ID = '88044';

function koval_legal_is_ads_landing( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();
	if ( ! $post_id || 'service' !== get_post_type( $post_id ) || ! function_exists( 'get_field' ) ) {
		return false;
	}
	return (bool) get_field( 'ads_landing', $post_id );
}

/**
 * Line icons (24×24, stroke) for the "Яка у вас ситуація?" rows. Keys are
 * the ACF select values; never government symbols (Google Ads policy).
 */
function koval_legal_ads_icons() {
	$doc = '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>';
	return array(
		'doc_x'     => array( 'Документ втрачено / пошкоджено', $doc . '<path d="m9.5 12.5 5 5M14.5 12.5l-5 5"/>' ),
		'doc_lines' => array( 'Витяг / документ з текстом', $doc . '<path d="M8 13h8M8 17h5"/>' ),
		'doc'       => array( 'Документ', $doc ),
		'family'    => array( 'Родина / діти', '<circle cx="9" cy="7" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17.5" cy="9.5" r="2.3"/><path d="M16 14.3c3 .2 5 2.5 5 5.7"/>' ),
		'globe'     => array( 'За кордоном', '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>' ),
		'pen'       => array( 'Помилка / виправлення', '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>' ),
		'question'  => array( 'Інше / не знаю', '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6"/><circle cx="12" cy="17" r=".6" fill="currentColor"/>' ),
		'scales'    => array( 'Суд / спір', '<path d="M12 3v18M7 21h10M5 7h14"/><path d="m5 7-3 6a3 3 0 0 0 6 0z"/><path d="m19 7-3 6a3 3 0 0 0 6 0z"/>' ),
		'handshake' => array( 'Згода / домовленість', '<path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/>' ),
		'stamp'     => array( 'Апостиль / легалізація', '<path d="M5 22h14"/><path d="M19.27 13.73A2.5 2.5 0 0 0 17.5 13h-11A2.5 2.5 0 0 0 4 15.5V17a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1.5c0-.66-.26-1.3-.73-1.77Z"/><path d="M14 13V8.5C14 7 15 7 15 5a3 3 0 0 0-6 0c0 2 1 2 1 3.5V13"/>' ),
		'plane'     => array( 'Віза / поїздка', '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>' ),
		'briefcase' => array( 'Робота', '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>' ),
		'home'      => array( 'ВНЖ / проживання', '<path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>' ),
		'translate' => array( 'Переклад', '<path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/>' ),
		'rings'     => array( 'Шлюб', '<circle cx="9" cy="14" r="5"/><circle cx="15" cy="14" r="5"/><path d="m10 4 2 3 2-3"/>' ),
		'undo'      => array( 'Повернути попереднє', '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>' ),
		'child'     => array( 'Дитина', '<circle cx="12" cy="5.5" r="2.5"/><path d="M12 8v7M8 11h8M9.5 21l2.5-6 2.5 6"/>' ),
		'users'     => array( 'Двоє людей / подвійне', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>' ),
	);
}

/**
 * Inline SVG. $name is either a key of koval_legal_ads_icons() or one of
 * the fixed UI icons below.
 */
function koval_legal_ads_svg( $name, $size = 20, $stroke = 1.9 ) {
	$ui = array(
		'phone'   => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
		'check'   => '<path d="M20 6 9 17l-5-5"/>',
		'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'info'    => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><circle cx="12" cy="7.8" r=".6" fill="currentColor"/>',
		'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'chevron' => '<path d="m9 6 6 6-6 6"/>',
		'plus'    => '<path d="M12 5v14M5 12h14"/>',
	);
	$icons = koval_legal_ads_icons();
	if ( isset( $ui[ $name ] ) ) {
		$paths = $ui[ $name ];
	} elseif ( isset( $icons[ $name ] ) ) {
		$paths = $icons[ $name ][1];
	} else {
		$paths = $icons['doc'][1];
	}
	return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . esc_attr( $stroke ) . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}

/**
 * Page-scoped assets: only the 6 landings load them, the rest of the site
 * is untouched.
 */
add_action( 'wp_enqueue_scripts', 'koval_legal_ads_landing_assets' );
function koval_legal_ads_landing_assets() {
	if ( ! is_singular( 'service' ) || ! koval_legal_is_ads_landing() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	wp_enqueue_style( 'koval-ads-landing', $uri . '/assets/css/ads-landing.css', array( 'koval-legal-style' ), filemtime( $dir . '/assets/css/ads-landing.css' ) );
	wp_enqueue_script( 'koval-ads-landing', $uri . '/assets/js/ads-landing.js', array(), filemtime( $dir . '/assets/js/ads-landing.js' ), true );
	wp_localize_script( 'koval-ads-landing', 'kovalAdsLanding', array( 'getcallId' => KOVAL_BINOTEL_GETCALL_ID ) );
}

/**
 * Binotel on these pages: every page button opens GetCall; its own floating
 * phone button stays hidden (the chat widget hides it sitewide too). The
 * chat widget loads as on every page (footer.php); on phones its launcher
 * is lifted above the sticky call bar (assets/css/ads-landing.css).
 * onReady flags the page so our buttons know the window can be opened.
 */
function koval_legal_ads_binotel_settings_script() {
	return '<script>window.BinotelGetCallSettings=Object.assign(window.BinotelGetCallSettings||{},{phoneButtonDisplay:0,onReady:function(){document.documentElement.classList.add("binotel-gc-ready");}});</script>';
}

add_filter( 'body_class', function ( $classes ) {
	if ( is_singular( 'service' ) && koval_legal_is_ads_landing() ) {
		$classes[] = 'ads-landing';
	}
	return $classes;
} );

/**
 * ACF fields — everything page-specific. Registered as local PHP like the
 * other groups (inc/acf-fields.php) so the structure lives in git.
 */
add_action( 'acf/init', 'koval_legal_register_ads_landing_fields' );
function koval_legal_register_ads_landing_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$icon_choices = array();
	foreach ( koval_legal_ads_icons() as $key => $icon ) {
		$icon_choices[ $key ] = $icon[0];
	}
	$shown = array(
		array(
			array(
				'field'    => 'field_koval_ads_landing',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	acf_add_local_field_group( array(
		'key'        => 'group_koval_ads_landing',
		'title'      => 'Ads-лендинг (Google Ads)',
		'location'   => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'service',
				),
			),
		),
		'menu_order' => -1,
		'position'   => 'normal',
		'style'      => 'default',
		'fields'     => array(
			array(
				'key'          => 'field_koval_ads_landing',
				'label'        => 'Шаблон Ads-лендингу',
				'name'         => 'ads_landing',
				'type'         => 'true_false',
				'ui'           => 1,
				'instructions' => 'Увімкнено — сторінка показується за шаблоном Ads-лендингу (усі кнопки відкривають вікно «Передзвоніть мені» Binotel). Заголовок H1 — назва запису. Постійні блоки (кроки, цифри, години роботи, фінальний блок) однакові для всіх лендингів і в коді; тут — тільки те, що відрізняється.',
			),
			array(
				'key'               => 'field_koval_ads_variant',
				'label'             => 'Варіант оформлення',
				'name'              => 'ads_variant',
				'type'              => 'select',
				'choices'           => array(
					'standard' => 'Стандартний',
					'delicate' => 'Делікатний (свідоцтво про смерть — спокійний, без яскравих акцентів)',
				),
				'default_value'     => 'standard',
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_eyebrow',
				'label'             => 'Надзаголовок',
				'name'              => 'ads_eyebrow',
				'type'              => 'text',
				'placeholder'       => 'Консультація · ДРАЦС',
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_lead',
				'label'             => 'Лід під заголовком',
				'name'              => 'ads_lead',
				'type'              => 'textarea',
				'rows'              => 3,
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_chip',
				'label'             => 'Третя плашка в першому екрані',
				'name'              => 'ads_chip',
				'type'              => 'text',
				'placeholder'       => 'Консультуємо українців по всьому світу',
				'instructions'      => 'Перші дві плашки постійні. У делікатному варіанті плашки інші й це поле не показується.',
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_hero_image',
				'label'             => 'Фото першого екрана (лише десктоп)',
				'name'              => 'ads_hero_image',
				'type'              => 'image',
				'return_format'     => 'id',
				'preview_size'      => 'medium',
				'instructions'      => 'Людина спокійно розмовляє по телефону. Без державної символіки, паспортів, свідоцтв. На мобільному не показується (швидкість).',
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_notice',
				'label'             => 'Плашка «Ми не державний орган»',
				'name'              => 'ads_notice',
				'type'              => 'textarea',
				'rows'              => 4,
				'instructions'      => "Обов'язкова (правила Google Ads). Перше речення виділяється жирним.",
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_situations',
				'label'             => 'Ситуації («Яка у вас ситуація?»)',
				'name'              => 'ads_situations',
				'type'              => 'repeater',
				'layout'            => 'table',
				'button_label'      => 'Додати ситуацію',
				'instructions'      => 'Кожен рядок — кнопка «Передзвоніть мені». Останнім краще лишити «Інше — не знаю, з чого почати» з іконкою «Інше / не знаю».',
				'conditional_logic' => $shown,
				'sub_fields'        => array(
					array( 'key' => 'field_koval_ads_sit_icon', 'label' => 'Іконка', 'name' => 'icon', 'type' => 'select', 'choices' => $icon_choices, 'default_value' => 'doc' ),
					array( 'key' => 'field_koval_ads_sit_text', 'label' => 'Текст', 'name' => 'text', 'type' => 'text' ),
				),
			),
			array(
				'key'               => 'field_koval_ads_step3',
				'label'             => 'Підпис 3-го кроку «Ви знаєте, як діяти далі»',
				'name'              => 'ads_step3',
				'type'              => 'text',
				'placeholder'       => 'Консультуємо щодо порядку — свідоцтво видає орган ДРАЦС',
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_steps_image',
				'label'             => 'Фото в блоці «Як це працює»',
				'name'              => 'ads_steps_image',
				'type'              => 'image',
				'return_format'     => 'id',
				'preview_size'      => 'medium',
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_reviews',
				'label'             => 'Відгуки',
				'name'              => 'ads_reviews',
				'type'              => 'repeater',
				'layout'            => 'block',
				'button_label'      => 'Додати відгук',
				'instructions'      => 'Лише РЕАЛЬНІ відгуки клієнтів. Поки порожньо — блок на сторінці не показується.',
				'conditional_logic' => $shown,
				'sub_fields'        => array(
					array( 'key' => 'field_koval_ads_rev_text', 'label' => 'Текст', 'name' => 'text', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_koval_ads_rev_name', 'label' => "Ім'я, місто", 'name' => 'name', 'type' => 'text' ),
					array( 'key' => 'field_koval_ads_rev_source', 'label' => 'Джерело (напр. Google)', 'name' => 'source', 'type' => 'text' ),
				),
			),
			array(
				'key'               => 'field_koval_ads_faq',
				'label'             => 'Часті запитання',
				'name'              => 'ads_faq',
				'type'              => 'repeater',
				'layout'            => 'block',
				'button_label'      => 'Додати питання',
				'instructions'      => "Відповіді — 1–2 речення, без інструкцій «як зробити самому». Питання «Ви державний орган чи приватна компанія?» обов'язкове.",
				'conditional_logic' => $shown,
				'sub_fields'        => array(
					array( 'key' => 'field_koval_ads_faq_q', 'label' => 'Питання', 'name' => 'question', 'type' => 'text' ),
					array( 'key' => 'field_koval_ads_faq_a', 'label' => 'Відповідь', 'name' => 'answer', 'type' => 'textarea', 'rows' => 2 ),
				),
			),
			array(
				'key'               => 'field_koval_ads_final_note',
				'label'             => 'Рядок під фінальною кнопкою',
				'name'              => 'ads_final_note',
				'type'              => 'text',
				'placeholder'       => 'Консультуємо щодо порядку звернення; свідоцтво видає орган ДРАЦС.',
				'conditional_logic' => $shown,
			),
			array(
				'key'               => 'field_koval_ads_related',
				'label'             => '«Також може знадобитися»',
				'name'              => 'ads_related',
				'type'              => 'relationship',
				'post_type'         => array( 'service', 'post' ),
				'return_format'     => 'id',
				'max'               => 3,
				'conditional_logic' => $shown,
			),
		),
	) );
}
