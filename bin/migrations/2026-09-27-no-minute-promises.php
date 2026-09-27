<?php
/**
 * Removes the "we'll call back within 30 minutes" promise from DB-stored
 * copy (user decision 2026-09-27): a promise in minutes can't be kept in the
 * evening/at weekends, and it's a sales manager who calls back, not a
 * lawyer. Same wording rule as the Google Ads landings ("оперативно").
 * Theme-side copies (footer popup, form card, CTA fallback) are changed in
 * code in the same commit.
 *
 *  - kontakty: page excerpt (hero lead)
 *  - pro-nas:  "about-contact-lead" paragraph in post_content
 *  - option options_cta_lead (ACF "cta_lead", the form section lead)
 *
 * Pages are looked up by slug (dev/prod IDs can differ). post_content /
 * post_excerpt are written via $wpdb: wp_update_post() under WP-CLI runs
 * kses and would strip the inline markup (docs/known-issues.md).
 * Idempotent — a second run reports "already clean".
 *
 * Run: wp eval-file bin/migrations/2026-09-27-no-minute-promises.php
 */

global $wpdb;

$koval_changes = array(
	array(
		'slug'  => 'kontakty',
		'field' => 'post_excerpt',
		'from'  => "Приходьте в офіс або залиште заявку — юрист зв'яжеться з вами протягом 30 хвилин.",
		'to'    => "Приходьте в офіс або залиште заявку — фахівець оперативно зв'яжеться з вами.",
	),
	array(
		'slug'  => 'pro-nas',
		'field' => 'post_content',
		'from'  => 'Залиште заявку у формі нижче або напишіть у чат на сайті — відповідаємо у робочий час протягом 30 хвилин.',
		'to'    => 'Залиште заявку у формі нижче або напишіть у чат на сайті — відповідаємо оперативно в робочий час.',
	),
);

foreach ( $koval_changes as $c ) {
	$page = get_page_by_path( $c['slug'], OBJECT, 'page' );
	if ( ! $page ) {
		echo "NOT FOUND: {$c['slug']}\n";
		continue;
	}
	$current = $page->{$c['field']};
	if ( false === strpos( $current, $c['from'] ) ) {
		echo ( false !== strpos( $current, $c['to'] ) ? 'already clean' : 'TEXT NOT FOUND' ) . ": {$c['slug']} {$c['field']}\n";
		continue;
	}
	$wpdb->update( $wpdb->posts, array( $c['field'] => str_replace( $c['from'], $c['to'], $current ) ), array( 'ID' => $page->ID ) );
	clean_post_cache( $page->ID );
	echo "UPDATED: {$c['slug']} {$c['field']} ({$page->ID})\n";
}

$koval_lead_from = "Юрист відповість протягом 30 хвилин у робочий час і оцінить вашу ситуацію без зобов'язань.";
$koval_lead_to   = "Фахівець оперативно зв'яжеться з вами в робочий час і оцінить вашу ситуацію без зобов'язань.";
$koval_lead      = get_option( 'options_cta_lead' );
if ( $koval_lead === $koval_lead_from ) {
	update_option( 'options_cta_lead', $koval_lead_to );
	echo "UPDATED: option options_cta_lead\n";
} elseif ( $koval_lead === $koval_lead_to ) {
	echo "already clean: option options_cta_lead\n";
} else {
	echo 'option options_cta_lead has other text, not touched: ' . $koval_lead . "\n";
}
