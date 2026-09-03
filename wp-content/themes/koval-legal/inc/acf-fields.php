<?php
/**
 * ACF field groups for the "service" CPT rich landing template — lets a
 * non-technical editor change every block (situations, steps, compare
 * table, testimonials, price, process, FAQ) through labeled admin forms
 * instead of touching raw HTML in post_content. Registered as local PHP
 * (not DB-stored field groups) so the structure lives in git like the
 * rest of the theme.
 *
 * single-service.php reads these fields to build the page; a one-time
 * migration script (bin/migrate-to-acf.php, run once) populated them from
 * the existing hand-authored HTML for all current rich landings.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'koval_legal_register_acf_fields' );

function koval_legal_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$icon_choices = array(
		'grad'    => 'Диплом / освіта',
		'work'    => 'Портфель / бізнес',
		'clock'   => 'Годинник / строки',
		'docq'    => 'Документ зі знаком питання',
		'box'     => 'Архівна коробка',
		'stamp'   => 'Печатка / сертифікація',
		'idcard'  => "Паспорт / посвідчення",
		'person'  => 'Особа / консультація',
		'docedit' => 'Документ з олівцем',
		'scales'  => 'Терези (суд)',
		'globe'   => 'Глобус / за кордоном',
	);

	acf_add_local_field_group( array(
		'key'      => 'group_koval_service_content',
		'title'    => 'Контент лендингу послуги',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'service',
				),
			),
		),
		'menu_order' => 0,
		'position'   => 'normal',
		'style'      => 'default',
		'fields'     => array(

			array(
				'key'     => 'field_koval_service_editor_notice',
				'label'   => '',
				'name'    => 'koval_service_editor_notice',
				'type'    => 'message',
				'message' => 'Весь контент цієї сторінки редагується тут, у полях нижче (розкладені по вкладках). Стандартний редактор WordPress для цієї сторінки не використовується.',
			),
			array(
				'key'   => 'field_koval_hero_tab',
				'label' => 'Верхній блок сторінки',
				'type'  => 'tab',
			),
			array(
				'key'          => 'field_koval_cta_text',
				'label'        => 'Текст кнопки (CTA)',
				'name'         => 'service_cta_text',
				'type'         => 'text',
				'instructions' => 'Напр.: «Дізнатись вартість для мого випадку →»',
			),
			array(
				'key'          => 'field_koval_location',
				'label'        => 'Локація / контекст',
				'name'         => 'service_location',
				'type'         => 'text',
				'instructions' => 'Четвертий пункт у рядку швидких фактів під заголовком, напр. «Подання у м. Київ»',
			),

			array(
				'key'   => 'field_koval_scenarios_tab',
				'label' => 'Кому актуально',
				'type'  => 'tab',
			),
			array(
				'key'          => 'field_koval_scenarios',
				'label'        => 'Ситуації (картки)',
				'name'         => 'scenarios',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати ситуацію',
				'sub_fields'   => array(
					array(
						'key'     => 'field_koval_scenario_icon',
						'label'   => 'Іконка',
						'name'    => 'icon',
						'type'    => 'select',
						'choices' => $icon_choices,
					),
					array(
						'key'   => 'field_koval_scenario_heading',
						'label' => 'Заголовок',
						'name'  => 'heading',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_koval_scenario_consequence',
						'label' => 'Уточнення / наслідок',
						'name'  => 'consequence',
						'type'  => 'text',
					),
				),
			),

			array(
				'key'   => 'field_koval_advantages_tab',
				'label' => 'Що входить',
				'type'  => 'tab',
			),
			array(
				'key'   => 'field_koval_advantages_lead',
				'label' => 'Вступний рядок',
				'name'  => 'advantages_lead',
				'type'  => 'text',
			),
			array(
				'key'          => 'field_koval_advantages_steps',
				'label'        => 'Кроки',
				'name'         => 'advantages_steps',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати крок',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_step_heading', 'label' => 'Заголовок', 'name' => 'heading', 'type' => 'text' ),
					array( 'key' => 'field_koval_step_desc', 'label' => 'Опис', 'name' => 'description', 'type' => 'text' ),
				),
			),
			array(
				'key'          => 'field_koval_advantages_docs',
				'label'        => 'Документи від вас (список)',
				'name'         => 'advantages_docs',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Додати пункт',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_doc_item', 'label' => 'Пункт', 'name' => 'item', 'type' => 'text' ),
				),
			),
			array(
				'key'          => 'field_koval_advantages_terms',
				'label'        => 'Строки виконання (список)',
				'name'         => 'advantages_terms',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Додати пункт',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_term_item', 'label' => 'Пункт', 'name' => 'item', 'type' => 'text' ),
				),
			),

			array(
				'key'   => 'field_koval_compare_tab',
				'label' => 'Чому ми',
				'type'  => 'tab',
			),
			array(
				'key'   => 'field_koval_compare_heading',
				'label' => 'Заголовок блоку',
				'name'  => 'compare_heading',
				'type'  => 'text',
			),
			array(
				'key'   => 'field_koval_compare_lead',
				'label' => 'Вступний рядок',
				'name'  => 'compare_lead',
				'type'  => 'text',
			),
			array(
				'key'          => 'field_koval_compare_rows',
				'label'        => 'Порівняльна таблиця',
				'name'         => 'compare_rows',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Додати рядок',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_compare_self', 'label' => 'Самостійно', 'name' => 'self_text', 'type' => 'text' ),
					array( 'key' => 'field_koval_compare_koval', 'label' => 'З KOVAL', 'name' => 'koval_text', 'type' => 'text' ),
				),
			),

			array(
				'key'   => 'field_koval_testimonials_tab',
				'label' => 'Відгуки',
				'type'  => 'tab',
			),
			array(
				'key'          => 'field_koval_testimonials',
				'label'        => 'Відгуки (до 3, показуються в ряд)',
				'name'         => 'testimonials',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати відгук',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_testi_text', 'label' => 'Текст відгуку', 'name' => 'text', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_koval_testi_cite', 'label' => "Ім'я, місто", 'name' => 'cite', 'type' => 'text' ),
					array(
						'key'          => 'field_koval_testi_placeholder',
						'label'        => 'Це заглушка?',
						'name'         => 'is_placeholder',
						'type'         => 'true_false',
						'ui'           => 1,
						'default_value' => 1,
						'instructions' => 'Увімкнено = це ілюстративний, не справжній відгук клієнта. Вимкніть, коли заміните на реальний.',
					),
				),
			),

			array(
				'key'   => 'field_koval_price_tab',
				'label' => 'Ціна',
				'type'  => 'tab',
			),
			array(
				'key'          => 'field_koval_price_cards',
				'label'        => 'Тарифні картки (зазвичай одна; кілька — якщо є варіанти на кшталт ФОП/ТОВ)',
				'name'         => 'price_cards',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати картку',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_price_title', 'label' => 'Назва', 'name' => 'title', 'type' => 'text' ),
					array( 'key' => 'field_koval_price_value', 'label' => 'Ціна', 'name' => 'value', 'type' => 'text' ),
					array( 'key' => 'field_koval_price_desc', 'label' => 'Опис', 'name' => 'description', 'type' => 'text' ),
					array( 'key' => 'field_koval_price_term', 'label' => 'Строк', 'name' => 'term', 'type' => 'text' ),
					array(
						'key'          => 'field_koval_price_link',
						'label'        => 'Посилання на іншу послугу (необов\'язково)',
						'name'         => 'link_url',
						'type'         => 'url',
						'instructions' => 'Заповніть, якщо ця картка веде на окрему сторінку послуги (напр. крос-продаж), залиште порожнім для основної ціни цієї сторінки.',
					),
					array(
						'key'          => 'field_koval_price_featured',
						'label'        => 'Виділити карткою з акцентом',
						'name'         => 'featured',
						'type'         => 'true_false',
						'ui'           => 1,
						'default_value' => 1,
					),
				),
			),
			array(
				'key'   => 'field_koval_price_legend',
				'label' => 'Дисклеймер під ціною',
				'name'  => 'price_legend',
				'type'  => 'textarea',
				'rows'  => 2,
			),

			array(
				'key'   => 'field_koval_process_tab',
				'label' => 'Процес',
				'type'  => 'tab',
			),
			array(
				'key'          => 'field_koval_process_steps',
				'label'        => 'Кроки процесу (I, II, III...)',
				'name'         => 'process_steps',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати крок',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_process_heading', 'label' => 'Заголовок', 'name' => 'heading', 'type' => 'text' ),
					array( 'key' => 'field_koval_process_desc', 'label' => 'Опис', 'name' => 'description', 'type' => 'text' ),
				),
			),

			array(
				'key'   => 'field_koval_faq_tab',
				'label' => 'FAQ',
				'type'  => 'tab',
			),
			array(
				'key'          => 'field_koval_faq_items',
				'label'        => 'Питання та відповіді',
				'name'         => 'faq_items',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати питання',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_faq_q', 'label' => 'Питання', 'name' => 'question', 'type' => 'text' ),
					array( 'key' => 'field_koval_faq_a', 'label' => 'Відповідь', 'name' => 'answer', 'type' => 'textarea', 'rows' => 3 ),
				),
			),
		),
	) );

	acf_add_local_field_group( array(
		'key'      => 'group_koval_pillar_content',
		'title'    => 'Контент pillar-сторінки (категорії)',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'service',
				),
			),
		),
		'menu_order' => 1,
		'position'   => 'normal',
		'fields'     => array(
			array(
				'key'     => 'field_koval_pillar_editor_notice',
				'label'   => '',
				'name'    => 'koval_pillar_editor_notice',
				'type'    => 'message',
				'message' => 'Весь контент цієї сторінки редагується тут, у полях нижче. Стандартний редактор WordPress для цієї сторінки не використовується.',
			),
			array(
				'key'          => 'field_koval_pillar_crosslinks',
				'label'        => 'Посилання на суміжні послуги (рядок посилань зверху)',
				'name'         => 'pillar_crosslinks',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Додати посилання',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_xlink_label', 'label' => 'Текст посилання', 'name' => 'label', 'type' => 'text' ),
					array( 'key' => 'field_koval_xlink_url', 'label' => 'URL', 'name' => 'url', 'type' => 'url' ),
				),
			),
			array(
				'key'          => 'field_koval_pillar_groups',
				'label'        => 'Групи карток послуг',
				'name'         => 'pillar_card_groups',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати групу',
				'instructions' => 'Якщо на сторінці одна група без підзаголовка (більшість випадків) — залиште назву групи порожньою.',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_group_heading', 'label' => 'Назва групи (необов\'язково)', 'name' => 'heading', 'type' => 'text' ),
					array(
						'key'          => 'field_koval_group_cards',
						'label'        => 'Картки',
						'name'         => 'cards',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => 'Додати картку',
						'sub_fields'   => array(
							array( 'key' => 'field_koval_gc_name', 'label' => 'Назва послуги', 'name' => 'name', 'type' => 'text' ),
							array( 'key' => 'field_koval_gc_desc', 'label' => 'Опис', 'name' => 'description', 'type' => 'text' ),
							array( 'key' => 'field_koval_gc_price', 'label' => 'Ціна (необов\'язково)', 'name' => 'price', 'type' => 'text' ),
							array( 'key' => 'field_koval_gc_duration', 'label' => 'Строк (необов\'язково)', 'name' => 'duration', 'type' => 'text' ),
							array( 'key' => 'field_koval_gc_link', 'label' => 'Посилання', 'name' => 'link_url', 'type' => 'url' ),
						),
					),
				),
			),
			array(
				'key'          => 'field_koval_pillar_faq',
				'label'        => 'FAQ напряму',
				'name'         => 'pillar_faq_items',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Додати питання',
				'sub_fields'   => array(
					array( 'key' => 'field_koval_pfaq_q', 'label' => 'Питання', 'name' => 'question', 'type' => 'text' ),
					array( 'key' => 'field_koval_pfaq_a', 'label' => 'Відповідь', 'name' => 'answer', 'type' => 'textarea', 'rows' => 3 ),
				),
			),
		),
	) );

	// Ціна / строк — раніше звичайні custom fields (потрібно було знати
	// точну назву поля через приховану панель "Довільні поля"); тепер
	// підписані поля, показуються для будь-якої послуги (не лише
	// rich/pillar), бо їх також показує проста бічна панель послуги.
	acf_add_local_field_group( array(
		'key'      => 'group_koval_service_price_duration',
		'title'    => 'Ціна та строк послуги',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'service',
				),
			),
		),
		'menu_order' => 2,
		'position'   => 'side',
		'fields'     => array(
			array(
				'key'          => 'field_koval_service_price',
				'label'        => 'Вартість',
				'name'         => 'service_price',
				'type'         => 'text',
				'instructions' => 'Напр.: «від 3 900 грн»',
			),
			array(
				'key'          => 'field_koval_service_duration',
				'label'        => 'Строк виконання',
				'name'         => 'service_duration',
				'type'         => 'text',
				'instructions' => 'Напр.: «від 1-2 робочих днів»',
			),
		),
	) );

	// Як картка послуги виглядає в каталозі /poslugy/ та на кроках "Оберіть
	// категорію" — окремий короткий опис (не той самий лід/excerpt, що на
	// самій сторінці послуги: у каталозі текст навмисно коротший).
	acf_add_local_field_group( array(
		'key'      => 'group_koval_service_catalog_card',
		'title'    => 'Картка в каталозі /poslugy/',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'service',
				),
			),
		),
		'menu_order' => 3,
		'position'   => 'side',
		'fields'     => array(
			array(
				'key'          => 'field_koval_catalog_title',
				'label'        => 'Назва картки в каталозі',
				'name'         => 'catalog_title',
				'type'         => 'text',
				'instructions' => 'Коротка назва для картки в каталозі — окремо від заголовка сторінки, який може бути довшим (для пошукових систем). Порожнє поле — картка покаже заголовок сторінки.',
			),
			array(
				'key'          => 'field_koval_catalog_short_desc',
				'label'        => 'Короткий опис для картки',
				'name'         => 'catalog_short_description',
				'type'         => 'textarea',
				'rows'         => 3,
				'instructions' => 'Показується в каталозі послуг під назвою картки.',
			),
			array(
				'key'          => 'field_koval_catalog_popular',
				'label'        => '«Часто замовляють»',
				'name'         => 'catalog_popular',
				'type'         => 'true_false',
				'ui'           => 1,
				'instructions' => 'Додає помітний бейдж на картці в каталозі.',
			),
		),
	) );

	// Категорії каталогу /poslugy/ — до якої категорії належить послуга,
	// обирається прямо на сторінці редагування послуги (стандартний UI
	// категорій WordPress). Ці поля — на самій категорії: короткий заклик
	// до дії внизу групи карток і чи показувати категорію на головній.
	acf_add_local_field_group( array(
		'key'      => 'group_koval_service_category_meta',
		'title'    => 'Налаштування категорії',
		'location' => array(
			array(
				array(
					'param'    => 'taxonomy',
					'operator' => '==',
					'value'    => 'service_category',
				),
			),
		),
		'fields' => array(
			array(
				'key'          => 'field_koval_cat_mini_cta',
				'label'        => 'Текст заклику в кінці групи (необов\'язково)',
				'name'         => 'category_mini_cta',
				'type'         => 'text',
				'instructions' => 'Напр.: «Не знайшли потрібний документ? Залишити заявку →»',
			),
			array(
				'key'          => 'field_koval_cat_show_home',
				'label'        => 'Показувати на головній сторінці',
				'name'         => 'category_show_on_homepage',
				'type'         => 'true_false',
				'ui'           => 1,
				'default_value' => 1,
			),
			array(
				'key'          => 'field_koval_cat_sort',
				'label'        => 'Порядок показу (менше число — вище)',
				'name'         => 'category_sort_order',
				'type'         => 'number',
				'instructions' => 'Визначає порядок вкладок і груп на сторінці /poslugy/ та порядок плиток на головній.',
			),
		),
	) );

	// Про нас / Контакти / Ціни show a big hero H1+lead above the_content()
	// (page.php's "full-width" branch). Кontakty/tsiny already read it from
	// the page's own title/excerpt — these fields let an editor override
	// that per-page with a punchier headline without changing the page's
	// actual title (used for the browser tab and breadcrumb). Empty =
	// falls back to title/excerpt, so leaving them blank changes nothing.
	acf_add_local_field_group( array(
		'key'      => 'group_koval_page_hero',
		'title'    => 'Заголовок і лід зверху сторінки',
		'location' => array(
			array( array( 'param' => 'page', 'operator' => '==', 'value' => 21 ) ), // Про нас.
			array( array( 'param' => 'page', 'operator' => '==', 'value' => 22 ) ), // Контакти.
			array( array( 'param' => 'page', 'operator' => '==', 'value' => 44 ) ), // Ціни.
		),
		'fields'   => array(
			array(
				'key'          => 'field_koval_hero_h1',
				'label'        => 'Заголовок (H1)',
				'name'         => 'hero_h1',
				'type'         => 'text',
				'instructions' => "Порожньо — покаже звичайний заголовок сторінки.",
			),
			array(
				'key'          => 'field_koval_hero_lead',
				'label'        => 'Підзаголовок під H1',
				'name'         => 'hero_lead',
				'type'         => 'textarea',
				'rows'         => 2,
				'instructions' => "Порожньо — покаже короткий опис (excerpt) сторінки.",
			),
		),
	) );

	// Місто клієнта у відгуку — та сама причина: раніше звичайний custom
	// field без підпису, легко було помилитись у назві поля.
	acf_add_local_field_group( array(
		'key'      => 'group_koval_testimonial_meta',
		'title'    => 'Місто клієнта',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'testimonial',
				),
			),
		),
		'position' => 'side',
		'fields'   => array(
			array(
				'key'          => 'field_koval_testimonial_city',
				'label'        => 'Місто',
				'name'         => 'testimonial_city',
				'type'         => 'text',
				'instructions' => "Показується поруч з ім'ям клієнта, напр. «Київ». Необов'язково.",
			),
		),
	) );

	// Site-wide settings (photo, currently-hardcoded texts) as an ACF
	// Options page — one place, no code, for things that used to live only
	// in PHP.
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array(
			'page_title' => 'Налаштування сайту',
			'menu_title' => 'Налаштування сайту',
			'menu_slug'  => 'koval-site-settings',
			'capability' => 'manage_options',
			'icon_url'   => 'dashicons-admin-generic',
		) );
	}

	acf_add_local_field_group( array(
		'key'      => 'group_koval_site_settings',
		'title'    => 'Наскрізні налаштування',
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'koval-site-settings',
				),
			),
		),
		'fields' => array(
			array(
				'key'          => 'field_koval_cta_photo',
				'label'        => 'Фото на формі заявки',
				'name'         => 'cta_photo',
				'type'         => 'image',
				'return_format' => 'url',
				'instructions' => "Показується поруч із формою заявки на консультацію на всіх сторінках.",
			),
		),
	) );
}
