<?php
/**
 * Removes every remaining direct contact channel from page content — only
 * the Binotel widgets stay (explicit user request 2026-09-27, "убрать надо
 * вообще все контакты, оставить только бинотел виджеты"; continues the
 * footer/floating-button cleanup of 2026-09-18). Office address and working
 * hours are kept, same as in the footer. The privacy policy (post 3) is
 * deliberately NOT touched — the data controller's contacts are legally
 * required there.
 *
 *  - golovna:   hero "Написати в Telegram" button
 *  - kontakty:  email, phone, "Напишіть нам" + Telegram/WhatsApp/Viber
 *               buttons, @shlyakh_do_mriyi card; excerpt reworded
 *  - pro-nas:   phone, email, messenger icons; lead reworded
 *  - services:  stale pre-ACF "Не знайшли відповідь? ... Telegram" block in
 *               post_content (not rendered — ACF wins — but cleaned too)
 *  - options:   ACF social/messenger URLs (fields removed from the theme)
 *
 * Pages are looked up by slug, not ID (dev/prod IDs can differ). Writes go
 * through $wpdb directly: wp_update_post() under WP-CLI (no user →
 * no unfiltered_html) would run kses and strip the inline <svg>s elsewhere
 * in these posts. Idempotent — a second run reports "already clean".
 *
 * Run: wp eval-file bin/migrations/2026-09-27-remove-contacts.php
 */

global $wpdb;

$koval_pages = array(
	'golovna'  => array(
		'content' => array(
			"\n<!-- wp:button {\"className\":\"btn-outline-dark\"} -->\n<div class=\"wp-block-button btn-outline-dark\"><a class=\"wp-block-button__link wp-element-button\" href=\"https://t.me/shlyakh_do_mriyi\">Написати в Telegram</a></div>\n<!-- /wp:button -->" => '',
		),
	),
	'kontakty' => array(
		'content' => array(
			"\n<!-- wp:list-item -->\n<li><a href=\"mailto:callcenter.via.klg@gmail.com\">callcenter.via.klg@gmail.com</a></li>\n<!-- /wp:list-item -->" => '',
			"\n<!-- wp:list-item -->\n<li>Зв'язатись інакше: <a href=\"tel:+380971920726\">+380 97 192 07 26</a></li>\n<!-- /wp:list-item -->" => '',
			"\n<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Напишіть нам</h3>\n<!-- /wp:heading -->\n" => '',
			'#\n<!-- wp:html -->\n<div class="messenger-row">.*?</div>\n<!-- /wp:html -->\n#s' => '',
			'#\n<!-- wp:html -->\n<div class="messenger-card">.*?\n</div>\n<!-- /wp:html -->\n#s' => '',
		),
		'excerpt' => array(
			'Приходьте в офіс або пишіть у зручний месенджер.' => 'Приходьте в офіс або залиште заявку — юрист зв\'яжеться з вами протягом 30 хвилин.',
		),
	),
	'pro-nas'  => array(
		'content' => array(
			'Зателефонуйте або напишіть нам напряму — відповідаємо у робочий час протягом 30 хвилин.' => 'Залиште заявку у формі нижче або напишіть у чат на сайті — відповідаємо у робочий час протягом 30 хвилин.',
			"\n\t<div><a href=\"tel:+380971920726\">📞 +380 97 192 07 26</a></div>" => '',
			"\n\t<div><a href=\"mailto:callcenter.via.klg@gmail.com\">✉️ callcenter.via.klg@gmail.com</a></div>" => '',
			'#\n<!-- wp:html -->\n<div class="about-contact-messengers">.*?</div>\n<!-- /wp:html -->\n#s' => '',
		),
	),
);

/** Applies literal or regex (keys starting with '#') replacements; returns [text, applied, missing]. */
function koval_apply_pairs( $text, $pairs ) {
	$applied = 0;
	$missing = array();
	foreach ( $pairs as $from => $to ) {
		if ( '#' === $from[0] ) {
			$new = preg_replace( $from, $to, $text, 1, $n );
		} else {
			$n   = substr_count( $text, $from );
			$new = str_replace( $from, $to, $text );
		}
		if ( $n ) {
			$text = $new;
			$applied++;
		} else {
			$missing[] = mb_substr( trim( $from ), 0, 60 );
		}
	}
	return array( $text, $applied, $missing );
}

foreach ( $koval_pages as $slug => $spec ) {
	$post = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $post ) {
		echo "SKIP page '$slug': not found\n";
		continue;
	}
	$data = array();
	$log  = array();
	foreach ( array( 'content' => 'post_content', 'excerpt' => 'post_excerpt' ) as $key => $col ) {
		if ( empty( $spec[ $key ] ) ) {
			continue;
		}
		list( $new, $applied, $missing ) = koval_apply_pairs( $post->$col, $spec[ $key ] );
		if ( $new !== $post->$col ) {
			$data[ $col ] = $new;
		}
		$log[] = "$key $applied/" . count( $spec[ $key ] ) . ( $missing ? ' (not found: ' . implode( ' | ', $missing ) . ')' : '' );
	}
	if ( $data ) {
		$wpdb->update( $wpdb->posts, $data, array( 'ID' => $post->ID ) );
		clean_post_cache( $post->ID );
	}
	echo ( $data ? 'UPDATED' : 'already clean' ) . " page '$slug' (ID {$post->ID}): " . implode( ', ', $log ) . "\n";
}

$koval_services = get_posts( array( 'post_type' => 'service', 'post_status' => 'any', 'numberposts' => -1 ) );
$koval_cleaned  = 0;
foreach ( $koval_services as $post ) {
	$new = preg_replace( '#\n?<div class="faq-more">.*?</a>\s*</div>#s', '', $post->post_content, -1, $n );
	if ( $n ) {
		$wpdb->update( $wpdb->posts, array( 'post_content' => $new ), array( 'ID' => $post->ID ) );
		clean_post_cache( $post->ID );
		$koval_cleaned++;
	}
}
echo "services: faq-more Telegram block removed from $koval_cleaned of " . count( $koval_services ) . " posts\n";

foreach ( array( 'telegram_url', 'whatsapp_url', 'facebook_url', 'instagram_url', 'youtube_url' ) as $name ) {
	$had = ( false !== get_option( "options_$name" ) );
	delete_option( "options_$name" );
	delete_option( "_options_$name" );
	echo ( $had ? 'deleted' : 'absent ' ) . " option options_$name\n";
}

// Final check — anything still pointing at a messenger or the old contacts
// in published pages/services (privacy policy excluded on purpose).
$koval_left = $wpdb->get_col(
	"SELECT CONCAT(ID, ' ', post_name) FROM {$wpdb->posts}
	 WHERE post_status = 'publish' AND post_type IN ('page','service','post') AND post_name <> 'privacy-policy'
	 AND CONCAT(post_content, ' ', post_excerpt) REGEXP 't\\\\.me/|wa\\\\.me|viber:|mailto:|tel:|Telegram|WhatsApp|Viber|месенджер'"
);
echo 'remaining mentions: ' . ( $koval_left ? implode( ', ', $koval_left ) : 'none' ) . "\n";
