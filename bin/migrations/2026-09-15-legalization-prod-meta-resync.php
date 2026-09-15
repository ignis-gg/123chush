<?php
/**
 * Sync 14 legalization-category posts' postmeta (ACF fields) on PROD to
 * match the already-corrected values on ddev. Found 2026-09-15 doing a
 * full live-browser pass of the "Легалізація документів" category per
 * explicit user request: prod's `process_steps` step III still said
 * "Виконуємо необхідні дії відповідно до вашого запиту" (active
 * representation — Google Ads non-compliant) on all 14 posts below, even
 * though the 2026-09-14 informational-positioning migration had already
 * fixed this field on ddev. Two posts (152, 155) also carry a grammar
 * bug from that migration's broad "супровід→консультація" replace
 * mangling the genitive "супровідних" into "консультаціяних" — also
 * already fixed on ddev, not on prod. Root cause: the prod deploy of
 * that migration didn't fully land on these posts (never diagnosed why
 * — cPanel Terminal is flaky for this project, see known-issues.md).
 *
 * This is a snapshot sync, not a phrase-replace: `legalization-meta-
 * snapshot.php` (sibling file, gitignored scratch data) holds the exact
 * current ACF postmeta arrays for these 14 posts on ddev, captured
 * 2026-09-15. Idempotent — no-op if a value already matches.
 *
 * Run on prod: wp eval-file bin/migrations/2026-09-15-legalization-prod-meta-resync.php
 */

$snapshot_path = __DIR__ . '/legalization-meta-snapshot.php';
if ( ! file_exists( $snapshot_path ) ) {
	echo "ABORT: snapshot file missing — upload legalization-meta-snapshot.php alongside this script first.\n";
	return;
}
$snapshot = include $snapshot_path;

foreach ( $snapshot as $post_id => $fields ) {
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
	echo ( $changed ? "UPDATED" : "NOCHANGE" ) . " $post_id ({$post->post_title}): $changed field(s)\n";
}
