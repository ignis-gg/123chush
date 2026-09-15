<?php
/**
 * Idempotent cleanup of stale (unused — ACF fields already render instead,
 * see single-service.php) post_content HTML on 3 legalization posts that
 * still said "подаємо документ" (active representation) instead of the
 * consultation-only framing already used everywhere else. Same class of
 * leftover as post 143's pre-ACF HTML (documented in
 * docs/next-session.md) — doesn't affect the live page, cleaned up for
 * Google Ads text-audit hygiene per explicit user request 2026-09-15.
 *
 * Run: wp eval-file bin/migrations/2026-09-15-legalization-stale-content-cleanup.php
 */

$replacements = array(
	99 => array(
		'Подаємо документ на апостилювання у відповідний орган' => 'Консультуємо щодо подання документа на апостилювання; подання здійснює клієнт особисто',
		'Консультуємо, як підготувати та подаємо документ на апостилювання</p>' => 'Консультуємо, як підготувати документ; подання здійснює клієнт особисто</p>',
	),
	111 => array(
		'Подаємо документ на апостилювання у відповідний орган' => 'Консультуємо щодо подання документа на апостилювання; подання здійснює клієнт особисто',
		'Консультуємо, як підготувати та подаємо документ на апостилювання в Мін\'юсті' => 'Консультуємо, як підготувати документ; подання здійснює клієнт особисто в Мін\'юсті',
	),
	112 => array(
		'Консультуємо, як підготувати та подаємо документ на засвідчення в Міністерстві юстиції' => 'Консультуємо, як підготувати документ; подання здійснює клієнт особисто в Міністерстві юстиції',
		'Подаємо документ у Мін\'юсті та за потреби в наступних інстанціях' => 'Консультуємо щодо подання документа у Мін\'юсті та за потреби в наступних інстанціях; подання здійснює клієнт особисто',
	),
);

foreach ( $replacements as $post_id => $pairs ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		echo "SKIP $post_id: not found\n";
		continue;
	}
	$content = $post->post_content;
	$changed  = 0;
	foreach ( $pairs as $from => $to ) {
		if ( strpos( $content, $from ) !== false ) {
			$content = str_replace( $from, $to, $content );
			$changed++;
		}
	}
	if ( $changed ) {
		wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ) );
		echo "UPDATED $post_id: $changed replacement(s)\n";
	} else {
		echo "NOCHANGE $post_id: already clean (idempotent)\n";
	}
}
