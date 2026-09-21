<?php
/**
 * Rewrites the "compare" section (compare_heading/compare_lead/compare_rows)
 * on the "Довідка про несудимість" page for both dev and prod — was framed
 * as "this can be done yourself" (copied verbatim from the sitewide
 * template on posts 361/141), which undercuts the sale. Reframed to lead
 * with the risk/uncertainty clients actually run into, paired with how
 * KOVAL's consultation prevents it — same ✕/✓ table mechanism, no code
 * changes, just content.
 */

$post_id = (int) ( $args[0] ?? 0 );
if ( ! $post_id ) {
	echo "ERROR: pass the post ID as argv[1]\n";
	exit( 1 );
}

$post = get_post( $post_id );
if ( ! $post || 'service' !== $post->post_type ) {
	echo "ERROR: post {$post_id} not found or not a service\n";
	exit( 1 );
}

update_field( 'compare_heading', 'Чому клієнти звертаються саме до нас', $post_id );
update_field( 'compare_lead', "Отримання довідки про несудимість здається простим, поки не зіткнешся з деталями — ми проходимо через це щотижня і знаємо, де саме клієнти втрачають час.", $post_id );
update_field( 'compare_rows', array(
	array(
		'self_text'  => 'Незрозуміло, який спосіб звернення підходить саме у вашому випадку',
		'koval_text' => 'Одразу підказуємо оптимальний варіант під вашу ситуацію',
	),
	array(
		'self_text'  => 'Ризик зібрати не той пакет документів і отримати відмову',
		'koval_text' => 'Перевіряємо комплект документів заздалегідь',
	),
	array(
		'self_text'  => 'Невизначеність, скільки часу і кроків це займе',
		'koval_text' => 'Даємо чіткий план дій і супроводжуємо до результату',
	),
), $post_id );

echo "UPDATED: post {$post_id}\n";
