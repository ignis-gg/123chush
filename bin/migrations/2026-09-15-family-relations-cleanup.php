<?php
/**
 * Fix remaining active-representation phrasing on 3 "Сімейні відносини"
 * posts, found 2026-09-15 doing a full live-browser pass of that category
 * (same audit method as the legalization-category pass earlier this
 * session). Unlike the legalization case, this was never fixed on ddev
 * either — not a prod/ddev drift, a genuine content gap the 2026-09-14
 * informational-positioning migration missed for these 3 posts.
 *
 * - Post 102 (Шлюб з іноземцем): advantages_steps step 3/4 claimed "ми
 *   подаємо заяву" and "присутність на кожному етапі" (active
 *   representation / physical accompaniment).
 * - Posts 137, 138: process_steps step III/IV still had the generic
 *   placeholder "Виконуємо необхідні дії відповідно до вашого запиту" /
 *   "Передаємо вам готовий результат" (same stale phrase found on 14
 *   legalization posts earlier today).
 *
 * Run: wp eval-file bin/migrations/2026-09-15-family-relations-cleanup.php
 * Safe to re-run (idempotent — direct value overwrite, no-op if already set).
 */

$updates = array(
	102 => array(
		'advantages_steps' => array(
			array(
				'heading'     => 'Аналіз документів іноземного партнера',
				'description' => 'Перевіряємо, які документи потрібні саме для громадянина цієї країни',
			),
			array(
				'heading'     => 'Підготовка та легалізація документів',
				'description' => 'Переклад, за потреби — апостиль чи консульська легалізація документів іноземця',
			),
			array(
				'heading'     => 'Подання заяви в ДРАЦС',
				'description' => "Консультуємо щодо подання спільної заяви на реєстрацію шлюбу; подання здійснює заявник особисто.",
			),
			array(
				'heading'     => 'Консультація реєстрації',
				'description' => 'Консультуємо на кожному етапі до отримання свідоцтва про шлюб.',
			),
		),
	),
	137 => array(
		'process_steps' => array(
			array(
				'heading'     => "Консультація щодо формату реєстрації",
				'description' => "З'ясовуємо деталі вашого випадку та перевіряємо документи",
			),
			array(
				'heading'     => 'Договір та оплата',
				'description' => 'Фіксуємо вартість і строк виконання',
			),
			array(
				'heading'     => 'Підготовка й подання заяви',
				'description' => "Консультуємо щодо підготовки та подання заяви; подання здійснює заявник особисто.",
			),
			array(
				'heading'     => 'Реєстрація шлюбу в обраному форматі',
				'description' => "Реєстрацію проводить орган ДРАЦС; ми на зв'язку для консультації, якщо вона потрібна.",
			),
		),
	),
	138 => array(
		'process_steps' => array(
			array(
				'heading'     => 'Консультація щодо умов договору',
				'description' => "З'ясовуємо деталі вашого випадку та перевіряємо документи",
			),
			array(
				'heading'     => 'Договір та оплата',
				'description' => 'Фіксуємо вартість і строк виконання',
			),
			array(
				'heading'     => 'Розробка тексту договору',
				'description' => 'Консультуємо щодо формулювання умов договору відповідно до вашого запиту.',
			),
			array(
				'heading'     => 'Нотаріальне посвідчення',
				'description' => "Договір посвідчує нотаріус; ми на зв'язку для консультації, якщо вона потрібна.",
			),
		),
	),
);

foreach ( $updates as $post_id => $fields ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		echo "SKIP $post_id: not found\n";
		continue;
	}
	$changed = 0;
	foreach ( $fields as $key => $new_val ) {
		$current = get_post_meta( $post_id, $key, true );
		if ( $current !== $new_val ) {
			update_post_meta( $post_id, $key, $new_val );
			$changed++;
		}
	}
	echo ( $changed ? 'UPDATED' : 'NOCHANGE' ) . " $post_id ({$post->post_title}): $changed field(s)\n";
}
