<?php
/**
 * Renders the rich-landing body sections from ACF fields (inc/acf-fields.php)
 * instead of reading hand-authored HTML out of post_content. Markup/classes
 * are unchanged from the old hand-authored version so the 2026-09-03 visual
 * refresh CSS (scoped to .svc-body) keeps applying without modification.
 *
 * koval_legal_render_service_acf( $post_id ) returns '' if the post has no
 * ACF data yet (scenarios repeater empty) — single-service.php falls back
 * to the legacy the_content() render in that case, so un-migrated posts
 * keep working exactly as before.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$GLOBALS['koval_icon_svgs'] = array(
	'grad'    => '<path d="M12 3 2 8l10 5 10-5-10-5z"/><path d="M6 10.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5"/>',
	'work'    => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/>',
	'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
	'docq'    => '<path d="M6 4h9l4 4v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/><path d="M14 3v6h6"/><path d="M10.5 13a1.5 1.5 0 1 1 2 1.4c-.6.3-1 .8-1 1.4v.2"/><circle cx="11.5" cy="18" r=".5" fill="currentColor" stroke="none"/>',
	'box'     => '<rect x="3" y="4" width="18" height="5" rx="1"/><path d="M5 9v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"/><path d="M10 13h4"/>',
	'stamp'   => '<circle cx="12" cy="9" r="6"/><path d="M9 14.5 7 21l5-3 5 3-2-6.5"/>',
	'idcard'  => '<rect x="3" y="5" width="18" height="12" rx="4"/><path d="M8 17l-2 3v-3"/>',
	'person'  => '<circle cx="12" cy="8" r="3"/><path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>',
	'docedit' => '<path d="M6 4h9l4 4v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/><path d="M14 3v6h6"/><path d="M12 11v4"/><circle cx="12" cy="17.3" r=".5" fill="currentColor" stroke="none"/>',
	'scales'  => '<path d="M12 3v18M8 21h8"/><path d="M5 7h6M13 7h6"/><path d="M5 7 2 13a3 3 0 0 0 6 0z"/><path d="M19 7l-3 6a3 3 0 0 0 6 0z"/>',
	'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 4 6 4 9s-1.5 6.5-4 9c-2.5-2.5-4-6-4-9s1.5-6.5 4-9z"/>',
);

/**
 * Escapes only the characters that could break HTML structure (<, >),
 * leaving straight quotes/apostrophes and & alone — matches how
 * post_content is treated: trusted editor input, not user-submitted, and
 * the caller runs the whole assembled HTML through wptexturize() (which
 * needs to see literal ' / " to turn them into “smart” quotes; esc_html()
 * would have already turned them into &#039; entities first, and
 * wptexturize doesn't recognise those as text to convert).
 */
function koval_text( $s ) {
	return str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), (string) $s );
}

function koval_icon_svg( $key, $size = 20 ) {
	$paths = $GLOBALS['koval_icon_svgs'][ $key ] ?? $GLOBALS['koval_icon_svgs']['docq'];
	return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
}

function koval_guarantee_html() {
	return '<section class="guarantee-section"><div class="wrap"><div class="eyebrow">' . koval_t( 'Гарантія' ) . '</div><div class="guarantee-card">'
	. '<div class="guarantee-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>'
	. '<div><h3>' . koval_t( 'Ваш ризик — під нашим контролем' ) . '</h3>'
	. '<p>' . koval_t( 'Строк виконання фіксуємо в договорі. Якщо виникають затримки з боку державного органу або інші обставини, що не залежать від нас, одразу повідомляємо про це та узгоджуємо подальші дії. Якщо причиною відмови стала помилка з нашого боку — беремо виправлення на себе.' ) . '</p>'
	. '<a class="guarantee-link" href="' . esc_url( koval_page_url( 'umovy-garantii' ) ) . '">' . koval_t( 'Умови співпраці →' ) . '</a></div></div></div></section>';
}

function koval_render_scenarios( $scenarios ) {
	if ( empty( $scenarios ) ) {
		return '';
	}
	$out = '<section class="services" style="padding:60px 0 64px;"><div class="wrap">'
		. '<div class="eyebrow">' . koval_t( 'Кому актуально' ) . '</div><h2 class="section-h2">' . koval_t( 'Впізнаєте себе?' ) . '</h2>'
		. '<p class="section-lead">' . koval_t( 'Ось найчастіші ситуації, з якими до нас звертаються:' ) . '</p><div class="scenario-grid">';
	foreach ( $scenarios as $s ) {
		$out .= '<div class="scenario-card"><div class="scenario-icon">' . koval_icon_svg( $s['icon'] ) . '</div>'
			. '<h4>' . koval_text( $s['heading'] ) . '</h4><p class="consequence">' . koval_text( $s['consequence'] ) . '</p></div>';
	}
	$out .= '</div></div></section>';
	return $out;
}

function koval_render_advantages( $lead, $steps, $docs, $terms ) {
	if ( empty( $steps ) ) {
		return '';
	}
	$out = '<section class="advantages"><div class="wrap">'
		. '<div class="eyebrow">' . koval_t( 'Що входить' ) . '</div><h2 class="section-h2">' . koval_t( 'Що входить у послугу' ) . '</h2>'
		. '<p class="section-lead">' . koval_text( $lead ) . '</p><div class="stepper-grid">';
	$n = 1;
	foreach ( $steps as $s ) {
		$out .= '<div class="stepper-card"><div class="stepper-num">' . $n . '</div><h4>' . koval_text( $s['heading'] ) . '</h4><p>' . koval_text( $s['description'] ) . '</p></div>';
		$n++;
	}
	$out .= '</div><div class="info-cols"><div class="addon-panel"><h3>' . koval_t( 'Документи від вас' ) . '</h3><ul class="check-list">';
	foreach ( (array) $docs as $d ) {
		$out .= '<li>' . koval_text( $d['item'] ) . '</li>';
	}
	$out .= '</ul></div><div class="addon-panel"><h3>' . koval_t( 'Строки виконання' ) . '</h3><ul class="check-list">';
	foreach ( (array) $terms as $t ) {
		$out .= '<li>' . koval_text( $t['item'] ) . '</li>';
	}
	$out .= '</ul></div></div></div></section>';
	return $out;
}

function koval_render_compare( $heading, $lead, $rows ) {
	if ( empty( $rows ) ) {
		return '';
	}
	$out = '<section class="cases"><div class="wrap">'
		. '<div class="eyebrow">' . koval_t( 'Чому ми' ) . '</div><h2 class="section-h2">' . koval_text( $heading ) . '</h2><p class="section-lead">' . koval_text( $lead ) . '</p>'
		. '<div class="compare-table"><div class="compare-head"><div>' . koval_t( 'Самостійно' ) . '</div><div>' . koval_t( 'З KOVAL Legal Group' ) . '</div></div>';
	foreach ( $rows as $r ) {
		$out .= '<div class="compare-row"><div><span class="x-icon">✕</span>' . koval_text( $r['self_text'] ) . '</div><div><span class="check-icon">✓</span>' . koval_text( $r['koval_text'] ) . '</div></div>';
	}
	$out .= '</div></div></section>';
	return $out;
}

function koval_render_testimonials( $items ) {
	if ( empty( $items ) ) {
		return '';
	}
	$out = '<section class="about-quote"><div class="wrap">';
	foreach ( $items as $t ) {
		if ( ! empty( $t['is_placeholder'] ) ) {
			$out .= "\n<!-- PLACEHOLDER TESTIMONIAL — AI-generated, replace with real client review before launch -->\n";
		}
		$out .= '<div class="about-quote-card"><p class="about-quote-mark">”</p><p>' . koval_text( $t['text'] ) . '</p><p class="cite">' . koval_text( $t['cite'] ) . '</p></div>';
	}
	$out .= '</div></section>';
	return $out;
}

function koval_render_price( $cards, $legend ) {
	if ( empty( $cards ) ) {
		return '';
	}
	$count      = count( $cards );
	$wrap_class = 1 === $count ? 'price-single' : 'pricing-cards cols-' . min( $count, 3 );
	$out = '<section class="price-section on-cream"><div class="wrap">'
		. '<div class="eyebrow">' . koval_t( 'Ціна' ) . '</div><h2 class="wp-block-heading">' . koval_t( 'Вартість послуги' ) . '</h2>'
		. '<div class="' . $wrap_class . '">';
	foreach ( $cards as $c ) {
		$featured = ! empty( $c['featured'] ) ? ' featured' : '';
		$title    = ! empty( $c['link_url'] ) ? '<a href="' . esc_url( $c['link_url'] ) . '">' . koval_text( $c['title'] ) . '</a>' : koval_text( $c['title'] );
		$out .= '<div class="price-card' . $featured . '"><h3>' . $title . '</h3>'
			. '<div class="price-value">' . koval_text( $c['value'] ) . '</div>'
			. '<p class="price-desc">' . koval_text( $c['description'] ) . '</p>'
			. '<p class="price-term">' . koval_text( $c['term'] ) . '</p>';
		if ( ! empty( $c['link_url'] ) ) {
			$out .= '<a href="' . esc_url( $c['link_url'] ) . '" class="service-link">' . koval_t( 'Детальніше →' ) . '</a>';
		}
		$out .= '</div>';
	}
	$out .= '</div>';
	if ( $legend ) {
		$out .= '<p class="price-legend">' . koval_text( $legend ) . '</p>';
	}
	$out .= '</div></section>';
	return $out;
}

function koval_render_process( $steps ) {
	if ( empty( $steps ) ) {
		return '';
	}
	$roman = array( 'I', 'II', 'III', 'IV', 'V', 'VI' );
	$out   = '<section class="process"><div class="wrap"><div class="eyebrow">' . koval_t( 'Процес' ) . '</div><h2>' . koval_t( 'Як відбувається процедура' ) . '</h2><div class="process-grid">';
	foreach ( $steps as $i => $s ) {
		$out .= '<div class="process-step"><p class="process-num">' . ( $roman[ $i ] ?? ( $i + 1 ) ) . '</p><h4>' . koval_text( $s['heading'] ) . '</h4><p>' . koval_text( $s['description'] ) . '</p></div>';
	}
	$out .= '</div></div></section>';
	return $out;
}

function koval_render_faq( $items, $heading = 'Питання щодо послуги' ) {
	if ( empty( $items ) ) {
		return '';
	}
	$heading = koval_t( $heading );
	static $koval_faq_counter = 0;
	$out = '<section class="faq"><div class="wrap"><div class="eyebrow">' . koval_t( 'Питання' ) . '</div><h2>' . koval_text( $heading ) . '</h2><div class="faq-list">';
	foreach ( $items as $q ) {
		$koval_faq_counter++;
		$panel_id = 'faq-a-' . $koval_faq_counter;
		$out .= '<div class="faq-item"><button class="faq-q" aria-expanded="false" aria-controls="' . esc_attr( $panel_id ) . '">' . koval_text( $q['question'] ) . '<span class="plus">+</span></button><div class="faq-a" id="' . esc_attr( $panel_id ) . '" role="region">' . '<p>' . koval_text( $q['answer'] ) . '</p></div></div>';
	}
	$out .= '</div></div></section>';
	return $out;
}

function koval_render_pillar_crosslinks( $links ) {
	if ( empty( $links ) ) {
		return '';
	}
	$out = '<p class="section-lead" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:8px;">';
	foreach ( $links as $l ) {
		$out .= '<a class="service-link" style="margin:0;" href="' . esc_url( $l['url'] ) . '">' . koval_text( $l['label'] ) . '</a>';
	}
	$out .= '</p>';
	return $out;
}

function koval_render_pillar_card( $c ) {
	$href = esc_url( $c['link_url'] );
	$out  = '<div class="svc-card"><h4><a href="' . $href . '">' . koval_text( $c['name'] ) . '</a></h4><p>' . koval_text( $c['description'] ) . '</p>';
	if ( ! empty( $c['price'] ) || ! empty( $c['duration'] ) ) {
		$out .= '<div class="svc-meta">';
		if ( ! empty( $c['price'] ) ) {
			$out .= '<span>' . koval_t( 'Вартість' ) . ' <b>' . koval_text( $c['price'] ) . '</b></span>';
		}
		if ( ! empty( $c['duration'] ) ) {
			$out .= '<span>' . koval_t( 'Строк' ) . ' <b>' . koval_text( $c['duration'] ) . '</b></span>';
		}
		$out .= '</div>';
	}
	$out .= '<a href="' . $href . '" class="service-link">' . koval_t( 'Детальніше →' ) . '</a></div>';
	return $out;
}

function koval_render_pillar_groups( $groups ) {
	if ( empty( $groups ) ) {
		return '';
	}
	$out = '';
	foreach ( $groups as $g ) {
		if ( ! empty( $g['heading'] ) ) {
			$out .= '<h3 class="section-h2" style="font-size:19px;margin:36px 0 14px;">' . koval_text( $g['heading'] ) . '</h3>';
		}
		$out .= '<div class="svc-grid" style="margin-top:8px;max-height:none;overflow:visible;">';
		foreach ( (array) $g['cards'] as $c ) {
			$out .= koval_render_pillar_card( $c );
		}
		$out .= '</div>';
	}
	return $out;
}

/**
 * @return string '' when the pillar post has no migrated ACF data yet
 *                (caller falls back to the_content()).
 */
function koval_legal_render_pillar_acf( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$groups = get_field( 'pillar_card_groups', $post_id );
	if ( empty( $groups ) ) {
		return '';
	}
	$inner = koval_render_pillar_crosslinks( get_field( 'pillar_crosslinks', $post_id ) ) . koval_render_pillar_groups( $groups );
	$html  = '<section style="padding:8px 0 20px;"><div class="wrap">' . $inner . '</div></section>';
	$html .= koval_render_faq( get_field( 'pillar_faq_items', $post_id ), 'Питання щодо напряму' );
	return $html;
}

/**
 * @return string '' when the post has no migrated ACF data yet (caller
 *                should fall back to the_content()).
 */
function koval_render_legal_notice( $post_id ) {
	$post_id             = (int) $post_id;
	$slug                = koval_source_slug( $post_id );
	$is_legalization     = in_array( $slug, koval_legal_legalization_group_slugs(), true );
	$is_criminal_record  = in_array( $slug, koval_legal_criminal_record_slugs(), true );
	if ( ! $is_legalization && ! $is_criminal_record ) {
		return '';
	}
	if ( $is_criminal_record ) {
		$text = koval_t( "KOVAL Legal Group — приватна юридична компанія. Ми не є Національною поліцією чи іншим державним органом, не видаємо довідку про несудимість і не проводимо перевірку на судимість. Надаємо винятково консультаційні та інформаційні послуги щодо порядку звернення — видачу довідки здійснює виключно уповноважений державний орган." );
	} else {
		$text = get_field( 'legalization_disclaimer', 'option' );
		if ( ! $text ) {
			$text = "KOVAL Legal Group — приватна юридична компанія. Ми не є державним органом, консульством чи офіційним провайдером легалізації чи апостилю, не видаємо і не гарантуємо видачу документа. Наші консультаційні та інформаційні послуги щодо підготовки документів не замінюють звернення до відповідного державного органу чи консульства — саме він ухвалює остаточне рішення щодо засвідчення чи видачі документа.";
		}
		$text = koval_t( $text );
	}
	return '<section style="padding:28px 0 0;"><div class="wrap"><div class="legal-notice"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><circle cx="12" cy="16.3" r=".6" fill="currentColor" stroke="none"/></svg><p><strong>' . koval_t( 'Важливо:' ) . '</strong> ' . koval_text( $text ) . '</p></div></div></section>';
}

function koval_legal_render_service_acf( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$scenarios = get_field( 'scenarios', $post_id );
	if ( empty( $scenarios ) ) {
		return '';
	}

	$html  = koval_render_legal_notice( $post_id );
	$html .= koval_render_scenarios( $scenarios );
	$html .= koval_render_advantages(
		get_field( 'advantages_lead', $post_id ),
		get_field( 'advantages_steps', $post_id ),
		get_field( 'advantages_docs', $post_id ),
		get_field( 'advantages_terms', $post_id )
	);
	$html .= koval_render_compare(
		get_field( 'compare_heading', $post_id ),
		get_field( 'compare_lead', $post_id ),
		get_field( 'compare_rows', $post_id )
	);
	$html .= koval_render_testimonials( get_field( 'testimonials', $post_id ) );
	$html .= koval_render_price( get_field( 'price_cards', $post_id ), get_field( 'price_legend', $post_id ) );
	$html .= koval_guarantee_html();
	$html .= koval_render_process( get_field( 'process_steps', $post_id ) );
	$html .= koval_render_faq( get_field( 'faq_items', $post_id ) );

	return $html;
}
