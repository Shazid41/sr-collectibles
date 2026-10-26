<?php
/**
 * Theme header.
 *
 * @package fh-collectibles
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="fh-skip-link" href="#fh-main"><?php esc_html_e( 'Skip to content', 'fh-collectibles' ); ?></a>

<header class="fh-header" id="fh-header">
	<div class="fh-container fh-header__inner">
		<div class="fh-header__left">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="fh-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — <?php esc_attr_e( 'Home', 'fh-collectibles' ); ?>"><?php echo esc_html( fh_brand_monogram() ); ?></a>
			<?php endif; ?>

			<nav class="fh-nav" aria-label="<?php esc_attr_e( 'Primary', 'fh-collectibles' ); ?>" data-fh-scrollspy>
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
				} else {
					fh_primary_menu_fallback();
				}
				?>
			</nav>
		</div>

		<div class="fh-header__actions">
			<form class="fh-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="fh-search-desktop"><?php esc_html_e( 'Search collectibles', 'fh-collectibles' ); ?></label>
				<input type="search" id="fh-search-desktop" name="s" placeholder="<?php esc_attr_e( 'Search collectibles...', 'fh-collectibles' ); ?>" value="<?php echo get_search_query(); ?>" />
				<?php if ( fh_has_woocommerce() ) : ?>
					<input type="hidden" name="post_type" value="product" />
				<?php endif; ?>
				<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'fh-collectibles' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
				</button>
			</form>

			<a class="fh-cart" href="<?php echo esc_url( fh_has_woocommerce() ? wc_get_cart_url() : home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Shopping cart', 'fh-collectibles' ); ?>" id="fh-cart-link">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
				<?php echo fh_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>

			<button class="fh-menu-toggle" type="button" aria-label="<?php esc_attr_e( 'Open menu', 'fh-collectibles' ); ?>" aria-expanded="false" aria-controls="fh-mobile-panel" id="fh-menu-toggle">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"></path></svg>
			</button>
		</div>
	</div>

	<div class="fh-mobile-panel" id="fh-mobile-panel">
		<form class="fh-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="fh-search-mobile"><?php esc_html_e( 'Search collectibles', 'fh-collectibles' ); ?></label>
			<input type="search" id="fh-search-mobile" name="s" placeholder="<?php esc_attr_e( 'Search collectibles...', 'fh-collectibles' ); ?>" value="<?php echo get_search_query(); ?>" />
			<?php if ( fh_has_woocommerce() ) : ?>
				<input type="hidden" name="post_type" value="product" />
			<?php endif; ?>
			<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'fh-collectibles' ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"></path></svg>
			</button>
		</form>
		<nav aria-label="<?php esc_attr_e( 'Primary mobile', 'fh-collectibles' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			} else {
				fh_primary_menu_fallback();
			}
			?>
		</nav>
	</div>
</header>
