<?php
/**
 * WebP <picture> support. A .webp sibling of an original JPEG/PNG is served
 * via <source>, with the original file kept as the <img> fallback — safer
 * than replacing the file extension outright (see the fake-.webp incident
 * in project history: a bad file with a .webp extension broke rendering
 * because <picture> had no working fallback to fall back to).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Given an original upload URL, returns its .webp sibling URL if that file
 * actually exists on disk, or false otherwise. Never assumes — always
 * checks the real file, so a missing/half-generated .webp can't break output.
 */
function koval_webp_sibling( $url ) {
	if ( ! preg_match( '/\.(jpe?g|png)$/i', $url ) ) {
		return false;
	}
	// Scheme-agnostic: content_url() may report http while the stored URL is https (or vice versa).
	$no_scheme = str_replace( array( 'http://', 'https://' ), '', $url );
	$content_no_scheme = str_replace( array( 'http://', 'https://' ), '', content_url() );
	$path = WP_CONTENT_DIR . str_replace( $content_no_scheme, '', $no_scheme );
	$webp_path = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $path );
	if ( ! file_exists( $webp_path ) ) {
		return false;
	}
	return preg_replace( '/\.(jpe?g|png)$/i', '.webp', $url );
}

/**
 * Rewrites a srcset attribute value ("url1 300w, url2 768w, ...") into its
 * webp equivalent. Returns false (not a partial srcset) unless EVERY entry
 * has a real .webp sibling — a <source> with some entries silently missing
 * is worse than no <source> at all.
 */
function koval_webp_srcset( $srcset ) {
	$parts = array_map( 'trim', explode( ',', $srcset ) );
	$out = array();
	foreach ( $parts as $part ) {
		if ( ! preg_match( '/^(\S+)(\s+.+)?$/', $part, $m ) ) {
			return false;
		}
		$webp_url = koval_webp_sibling( $m[1] );
		if ( ! $webp_url ) {
			return false;
		}
		$out[] = $webp_url . ( $m[2] ?? '' );
	}
	return implode( ', ', $out );
}

/**
 * Wraps a plain <img ...> tag in <picture><source type="image/webp">...
 * Falls back to the untouched original tag if src/srcset don't all have
 * webp siblings, or if the string isn't a single <img> tag as expected.
 */
function koval_wrap_webp_picture( $img_html ) {
	if ( ! preg_match( '/<img\s[^>]*>/i', $img_html, $img_match ) ) {
		return $img_html;
	}
	$img_tag = $img_match[0];

	if ( ! preg_match( '/\ssrc="([^"]+)"/i', $img_tag, $src_match ) ) {
		return $img_html;
	}
	$webp_src = koval_webp_sibling( $src_match[1] );
	if ( ! $webp_src ) {
		return $img_html;
	}

	if ( preg_match( '/\ssrcset="([^"]+)"/i', $img_tag, $srcset_match ) ) {
		$webp_srcset = koval_webp_srcset( $srcset_match[1] );
		if ( ! $webp_srcset ) {
			return $img_html; // partial srcset coverage — skip <picture>, keep the safe original.
		}
		$source = '<source type="image/webp" srcset="' . esc_attr( $webp_srcset ) . '">';
	} else {
		$source = '<source type="image/webp" srcset="' . esc_attr( $webp_src ) . '">';
	}

	return str_replace( $img_tag, '<picture>' . $source . $img_tag . '</picture>', $img_html );
}
add_filter( 'post_thumbnail_html', 'koval_wrap_webp_picture' );
