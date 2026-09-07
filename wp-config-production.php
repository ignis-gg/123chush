<?php
/**
 * Production wp-config.php TEMPLATE — KOVAL Legal Group (main project).
 *
 * This is NOT a live config file. Before deploying:
 *   1. Fill in the 4 DB_* placeholders below with the real hosting values
 *      (cPanel → MySQL Databases gives you these after creating the DB).
 *   2. Rename this file to `wp-config.php` and place it in the site root
 *      (same level as `wp-load.php`) on the koval-legal.pp.ua addon domain.
 *   3. Do NOT reuse these AUTH/SECURE/LOGGED_IN/NONCE keys anywhere else —
 *      they were freshly generated for this deploy from
 *      https://api.wordpress.org/secret-key/1.1/salt/ specifically for
 *      this file, not copied from the dev environment (which has its own
 *      separate, gitignored wp-config.php with different salts).
 *
 * See docs/migration-status.md → "Этап B (перенос) — механика" for the
 * full step-by-step (DB import, uploads, domain search-replace,
 * permalinks, verification).
 */

// ---- Database — fill these in with real hosting values ----
define( 'DB_NAME', 'REPLACE_WITH_DB_NAME' );
define( 'DB_USER', 'REPLACE_WITH_DB_USER' );
define( 'DB_PASSWORD', 'REPLACE_WITH_DB_PASSWORD' );
// Most shared hosts (including cPanel/Hostpro) use 'localhost'. Change only
// if your host's control panel tells you a different DB host.
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wp_'; // Matches the shipped DB dump — do not change unless you also edit the dump.

// ---- Authentication unique keys and salts ----
// Freshly generated for this deploy (api.wordpress.org/secret-key/1.1/salt/),
// 2026-09-07 — do not reuse elsewhere, do not copy from dev.
define('AUTH_KEY',         'k9lZ:Z,]J]A9jdh(&:+-->,-9~OvHYgfR5Nd^^.6Lv-kdL&9zn)Uimf&_B;z#^$U');
define('SECURE_AUTH_KEY',  'Fk_ WlKX{$gnDy*N=4Y+4vCDJ-MV|/38e*57|ma1%+!U-x8*u3KRkU}?0?R(b;^g');
define('LOGGED_IN_KEY',    '`g`6bMu^Utq:cH8)y{yU3F}`[|A.+ #i(kz``Y^|^8^ ;5f7Y,.#m-z@+(juuY-/');
define('NONCE_KEY',        'Nc7va3~j#UxJEryjCc1/nf,#4-PHzL6d^Hh~_lJI>}xgF%l+*F -[O17]gZGipDu');
define('AUTH_SALT',        'Qn 0r;J_F_Cd/|,+q;dS_S`vE:qaOS~SZz,f5Y{Q!z3NneExSA.Q3JH_ND=qW*R#');
define('SECURE_AUTH_SALT', 'w>-0ZzxY^+cVbci<4N{e+R4ax/F;}knsm-w8,%h(UCn?vqw.3_e>)<R-N@ch|r1d');
define('LOGGED_IN_SALT',   '1Zee;?vgwce1EZ|bYV);7KYwO>8^)#VG>v?&+3p|yTLJK|Ve7vn4s*TtklieA.|7');
define('NONCE_SALT',       ' .E$V-x:|W70]S5uxu9o[_ %@B+}.Pk-;eCh]!w@Q+#ZEg+GkLhjr8WS;mIi+]c]');

// ---- Production hardening ----
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'DISALLOW_FILE_EDIT', true );
define( 'FORCE_SSL_ADMIN', true );

// WP_HOME / WP_SITEURL are deliberately NOT hardcoded here — WordPress
// auto-detects them from the request once the real domain is live and SSL
// is configured. Only add them explicitly if you need to force a specific
// scheme/host (e.g. redirect www -> non-www) after the domain is finalized.

// ---- SMTP (real mailbox delivery for the consultation-form leads) ----
// Read by wp-content/mu-plugins/smtp-mailer.php. This is the SENDING
// identity only (authenticates + improves deliverability/SPF-DKIM) — the
// form's RECIPIENT stays whatever `company_email` theme_mod already is
// (callcenter.via.klg@gmail.com, confirmed working, no change needed).
// Host/port/username below are the real values from the client's cPanel
// mail settings (koval-legal.pp.ua) once the mailbox is created — not
// secret. The password is deliberately left blank here: fill it in
// directly on the real server only, never commit the real value to git.
define( 'KOVAL_SMTP_HOST', 'mail.koval-legal.pp.ua' );
define( 'KOVAL_SMTP_PORT', 465 ); // Implicit SSL (SMTPS), matches the koval-group.pp.ua setup — confirm same on this mailbox.
define( 'KOVAL_SMTP_SECURE', 'ssl' );
define( 'KOVAL_SMTP_USER', 'manager@koval-legal.pp.ua' );
define( 'KOVAL_SMTP_PASS', 'REPLACE_WITH_MAILBOX_PASSWORD' ); // Fill in on the real server only.
define( 'KOVAL_SMTP_FROM_NAME', 'KOVAL Legal Group' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
