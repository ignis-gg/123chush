<?php
/**
 * Rich/pillar service pages are fully authored through the ACF fields
 * registered in inc/acf-fields.php; the classic content editor still holds
 * the old hand-authored HTML as a non-destructive fallback (see the ACF-
 * empty branch in single-service.php), but leaving it visible on the edit
 * screen tricks an editor into typing into a box that the front end never
 * reads. Hide it for exactly the posts whose rendering ignores it.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'koval_legal_hide_editor_for_acf_posts' );
function koval_legal_hide_editor_for_acf_posts() {
	global $pagenow;
	if ( 'post.php' !== $pagenow || empty( $_GET['post'] ) ) {
		return;
	}
	$post_id = (int) $_GET['post'];
	if ( 'service' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( in_array( $post_id, koval_legal_acf_content_ids(), true ) ) {
		remove_post_type_support( 'service', 'editor' );
	}
}
