<?php
/**
 * Real SMTP delivery for wp_mail() (consultation-form leads), via
 * koval-legal.pp.ua's own mailbox — replaces the bare PHP mail() fallback,
 * which has no SPF/DKIM and is likely to land in spam on a real domain
 * (see docs/migration-status.md, blocker "Корпоративная почта на домене",
 * resolved 2026-09-07).
 *
 * This only affects the SENDING identity/authentication — the form's
 * recipient (`company_email` theme_mod) stays callcenter.via.klg@gmail.com,
 * unchanged.
 *
 * Config lives entirely in wp-config.php constants (see
 * wp-config-production.php in the repo root) — this file is inert (falls
 * through to WordPress's normal wp_mail() behavior) unless all of
 * KOVAL_SMTP_HOST/PORT/USER/PASS are defined, so it's a no-op on dev/ddev
 * where those constants are never set.
 *
 * Settings as provided by the client (cPanel "Configure Mail Client",
 * Secure SSL/TLS): host mail.koval-legal.pp.ua, SMTP port 465 (implicit
 * SSL, not STARTTLS), username manager@koval-legal.pp.ua. Password is
 * deliberately NOT in this file or in git history — see the placeholder
 * in wp-config-production.php, filled in only on the real server.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( ! defined( 'KOVAL_SMTP_HOST' ) || ! defined( 'KOVAL_SMTP_PORT' )
		|| ! defined( 'KOVAL_SMTP_USER' ) || ! defined( 'KOVAL_SMTP_PASS' )
		|| ! KOVAL_SMTP_PASS ) {
		return; // Not configured (e.g. local/ddev) — let wp_mail() use its normal fallback.
	}

	$phpmailer->isSMTP();
	$phpmailer->Host       = KOVAL_SMTP_HOST;
	$phpmailer->Port       = KOVAL_SMTP_PORT;
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = KOVAL_SMTP_USER;
	$phpmailer->Password   = KOVAL_SMTP_PASS;
	// Port 465 in the client's cPanel settings is implicit SSL (SMTPS), not
	// STARTTLS on 587 — SMTPSecure must be 'ssl', not 'tls', or the
	// connection fails outright.
	$phpmailer->SMTPSecure = defined( 'KOVAL_SMTP_SECURE' ) ? KOVAL_SMTP_SECURE : 'ssl';
	$phpmailer->From       = KOVAL_SMTP_USER;
	$phpmailer->FromName   = defined( 'KOVAL_SMTP_FROM_NAME' ) ? KOVAL_SMTP_FROM_NAME : 'KOVAL Legal Group';
} );

// WordPress's default From: address (wordpress@<host>) doesn't match the
// authenticated SMTP user above, which some mail servers reject outright
// or flag as spoofing. Force it to the real mailbox whenever SMTP is
// actually configured.
add_filter( 'wp_mail_from', function ( $email ) {
	return defined( 'KOVAL_SMTP_USER' ) && KOVAL_SMTP_USER ? KOVAL_SMTP_USER : $email;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
	return defined( 'KOVAL_SMTP_FROM_NAME' ) ? KOVAL_SMTP_FROM_NAME : 'KOVAL Legal Group';
} );
