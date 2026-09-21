<?php
/**
 * Post 138 (шлюбний договір) has been throwing a fatal 500 on the live
 * page since at least 12-Sep-2026 (debug.log): several ACF repeater
 * fields were saved as a stringified var_export() dump instead of a real
 * repeater array — same corruption pattern already fixed for compare_rows
 * today. koval_render_price() does count($cards) on price_cards, which
 * fatals when $cards is a string. Rewriting all three affected fields
 * with their existing (already correct) content, just as real arrays.
 */

$post_id = 138;

update_field( 'advantages_steps', array(
	array( 'heading' => 'Консультація щодо майнових питань, які варто врегулювати', 'description' => '' ),
	array( 'heading' => 'Розробка індивідуальних умов договору', 'description' => '' ),
	array( 'heading' => 'Нотаріальне посвідчення договору', 'description' => '' ),
), $post_id );

update_field( 'price_cards', array(
	array(
		'title'       => 'Шлюбний договір',
		'value'       => 'від 19 000 грн',
		'description' => 'Шлюбний договір',
		'term'        => 'Строк: від 1 робочого дня',
		'link_url'    => '',
		'featured'    => true,
	),
), $post_id );

update_field( 'faq_items', array(
	array(
		'question' => 'Чи можна укласти договір уже в шлюбі, не тільки до реєстрації?',
		'answer'   => 'Так, законодавство це дозволяє.',
	),
	array(
		'question' => 'Що можна включити в шлюбний договір?',
		'answer'   => 'Питання майна, утримання, порядку розподілу активів у разі розлучення — конкретний перелік обговорюємо на консультації.',
	),
	array(
		'question' => 'Чи можна змінити договір пізніше?',
		'answer'   => 'Так, за згодою обох сторін договір можна змінити або розірвати.',
	),
	array(
		'question' => 'Ви державний орган чи приватна компанія?',
		'answer'   => 'Ми приватна юридична компанія, що надає консультаційні та інформаційні послуги з підготовки договору.',
	),
), $post_id );

echo "FIXED: post {$post_id} — advantages_steps, price_cards, faq_items rebuilt as arrays\n";
