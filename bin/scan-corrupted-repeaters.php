<?php
$repeater_fields = array( 'scenarios', 'advantages_steps', 'advantages_docs', 'advantages_terms', 'compare_rows', 'testimonials', 'price_cards', 'process_steps', 'faq_items' );
$posts = get_posts( array( 'post_type' => 'service', 'posts_per_page' => -1, 'post_status' => 'any' ) );
$found = false;
foreach ( $posts as $post ) {
	foreach ( $repeater_fields as $field ) {
		$value = get_field( $field, $post->ID );
		if ( $value !== null && $value !== false && ! is_array( $value ) ) {
			echo "CORRUPT: post {$post->ID} ({$post->post_title}) field '{$field}' is " . gettype( $value ) . "\n";
			$found = true;
		}
	}
}
if ( ! $found ) {
	echo "No other corrupted repeater fields found.\n";
}
