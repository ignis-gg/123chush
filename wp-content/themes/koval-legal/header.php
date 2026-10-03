<?php
/**
 * RECOVERY REBUILD (2026-09-03) — markup reconstructed from a static export
 * of this exact site (koval-legal-demo.pages.dev), which preserved the
 * original rendered HTML even though the source PHP was lost. Menu items
 * are pulled dynamically via wp_nav_menu(); the fallback mirrors the
 * static export's structure for when no menu is assigned yet.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php echo esc_html( koval_t( 'Перейти до контенту' ) ); ?></a>

<div class="topbar">
	<div class="wrap"><?php echo esc_html( koval_t( "Ми — дочірня компанія юридичного об'єднання «Шлях до мрії О.К.» з 15+ роками досвіду" ) ); ?></div>
</div>

<header id="site-header">
	<div class="wrap">
		<a href="<?php echo esc_url( koval_home_url() ); ?>" class="logo">
			<span class="logo-row"><span class="lg-koval">KOVAL</span><span class="lg-legal">Legal Group</span></span>
			<span class="lg-sub"><?php echo esc_html( koval_t( 'Юридична компанія · Київ' ) ); ?></span>
		</a>

		<nav class="main-nav" aria-label="<?php echo esc_attr( koval_t( 'Головна навігація' ) ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul>%3$s</ul>',
				'fallback_cb'    => 'koval_legal_default_menu',
			) );
			?>
		</nav>

		<div class="header-right">
			<?php echo koval_language_switcher(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
			<a href="#contact-form" class="btn btn-wine btn-sm"><?php echo esc_html( koval_t( 'Консультація' ) ); ?></a>
			<button class="menu-toggle" aria-label="<?php echo esc_attr( koval_t( 'Меню' ) ); ?>" aria-expanded="false" aria-controls="mobile-nav"><span></span><span></span><span></span></button>
		</div>
	</div>

	<div class="mobile-nav" id="mobile-nav">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '<ul>%3$s</ul>',
			'fallback_cb'    => 'koval_legal_default_menu',
		) );
		?>
	</div>
</header>
