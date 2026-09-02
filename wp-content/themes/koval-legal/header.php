<?php
/**
 * RECOVERY REBUILD (2026-09-02) — simplified reconstruction, not the
 * original header.php (lost). Topbar/nav text approximated from earlier
 * session context (grep of the original disclaimer strings).
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

<div class="topbar">Ми — дочірня компанія юридичного об'єднання «Шлях до мрії О.К.» з 15+ роками досвіду</div>

<header class="site-header">
	<div class="wrap">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<strong>KOVAL</strong> Legal Group
			<span class="site-tagline">Юридична компанія · Київ</span>
		</a>
		<nav class="main-nav">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => function () {
					$archive = get_post_type_archive_link( 'service' );
					echo '<ul class="menu">';
					echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Головна</a></li>';
					if ( $archive ) {
						echo '<li><a href="' . esc_url( $archive ) . '">Послуги</a></li>';
					}
					echo '<li><a href="' . esc_url( home_url( '/pro-nas/' ) ) . '">Про нас</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/blog/' ) ) . '">Блог</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/kontakty/' ) ) . '">Контакти</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/tsiny/' ) ) . '">Ціни</a></li>';
					echo '</ul>';
				},
			) );
			?>
		</nav>
		<a href="#contact-form" class="btn btn-wine">Консультація</a>
	</div>
</header>
