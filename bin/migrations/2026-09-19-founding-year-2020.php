<?php
/**
 * Fix wrong founding year: the site said "з 1998 року" / "1998—2026" /
 * "рік заснування об'єднання: 1998" everywhere, but the company was
 * officially registered in 2020 (user correction, 2026-09-19) — the
 * "15+ років на ринку" marketing stat is unrelated and left untouched.
 *
 * Touches: front page (7) hero seal SVG + stats block, About page (21)
 * founder bio + company-history paragraph + hero_lead ACF field.
 * Idempotent: str_replace is a no-op once "1998" is gone.
 *
 * Run: wp eval-file bin/migrations/2026-09-19-founding-year-2020.php
 */

function fy2020_replace_post_content( $post_id, $pairs ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		echo "SKIP: post {$post_id} not found\n";
		return;
	}
	$content = $post->post_content;
	foreach ( $pairs as $old => $new ) {
		$content = str_replace( $old, $new, $content );
	}
	if ( $content !== $post->post_content ) {
		wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ) );
		echo "UPDATED: post {$post_id} post_content\n";
	} else {
		echo "NO-OP: post {$post_id} post_content already clean\n";
	}
}

function fy2020_replace_meta( $post_id, $meta_key, $pairs ) {
	$value = get_post_meta( $post_id, $meta_key, true );
	if ( ! is_string( $value ) || '' === $value ) {
		echo "SKIP: post {$post_id} meta {$meta_key} empty/missing\n";
		return;
	}
	$new_value = $value;
	foreach ( $pairs as $old => $new ) {
		$new_value = str_replace( $old, $new, $new_value );
	}
	if ( $new_value !== $value ) {
		update_post_meta( $post_id, $meta_key, $new_value );
		echo "UPDATED: post {$post_id} meta {$meta_key}\n";
	} else {
		echo "NO-OP: post {$post_id} meta {$meta_key} already clean\n";
	}
}

// Front page (7): hero seal SVG year range + "рік заснування" stat.
fy2020_replace_post_content( 7, array(
	'1998—2026' => '2020—2026',
	'<p class="stat-num">1998</p>' => '<p class="stat-num">2020</p>',
) );

// About page (21): founder bio + company-history paragraph.
fy2020_replace_post_content( 21, array(
	'Надає юридичні консультації з 1998 року.' => 'Надає юридичні консультації з 2020 року.',
	'Історія компанії почалась у 1998 році' => 'Історія компанії почалась у 2020 році',
) );

// About page (21): hero_lead ACF field (used by koval_render_page_hero()).
fy2020_replace_meta( 21, 'hero_lead', array(
	'яка працює з 1998 року' => 'яка працює з 2020 року',
) );

echo "Done.\n";
