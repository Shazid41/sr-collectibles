<?php
/**
 * FH Collectibles theme bootstrap.
 *
 * @package fh-collectibles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FH_THEME_VERSION', '1.0.0' );

/**
 * Theme setup.
 */
function fh_theme_setup() {
	load_theme_textdomain( 'fh-collectibles', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 192,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'fh-collectibles' ),
		)
	);

	add_image_size( 'fh-product', 720, 540, true );
	add_image_size( 'fh-hero', 1200, 800, false );

	// WooCommerce.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'fh_theme_setup' );

/**
 * Front-end assets.
 */
function fh_theme_assets() {
	wp_enqueue_style( 'fh-fonts', get_theme_file_uri( 'assets/css/fonts.css' ), array(), FH_THEME_VERSION );
	wp_enqueue_style( 'fh-main', get_stylesheet_uri(), array( 'fh-fonts' ), (string) filemtime( get_stylesheet_directory() . '/style.css' ) );

	if ( fh_has_woocommerce() ) {
		wp_enqueue_script( 'wc-add-to-cart' );
		wp_enqueue_script( 'wc-cart-fragments' );
	}

	wp_enqueue_script( 'fh-main', get_theme_file_uri( 'assets/js/main.js' ), array(), (string) filemtime( get_theme_file_path( 'assets/js/main.js' ) ), true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'fh_theme_assets' );

/**
 * Preload hero slide photos so slide 2 is decoded before autoplay reveals it.
 */
function fh_preload_hero_images() {
	if ( ! is_front_page() ) {
		return;
	}
	foreach ( array( 'hero-burgundy.jpg', 'hero-onyx.jpg' ) as $fh_hero_img ) {
		printf(
			'<link rel="preload" as="image" href="%s">' . "\n",
			esc_url( get_theme_file_uri( 'assets/images/' . $fh_hero_img ) )
		);
	}
}
add_action( 'wp_head', 'fh_preload_hero_images', 2 );

/**
 * Brand monogram used in the header/footer.
 */
function fh_brand_monogram() {
	return apply_filters( 'fh_brand_monogram', 'SR' );
}

/**
 * URL for a WooCommerce product category (falls back to the shop).
 */
function fh_category_url( $slug ) {
	if ( fh_has_woocommerce() ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			return get_term_link( $term );
		}
		$shop_id = wc_get_page_id( 'shop' );
		if ( $shop_id > 0 ) {
			return get_permalink( $shop_id );
		}
	}
	return fh_section_url( 'products' );
}

/**
 * Whether WooCommerce is active.
 */
function fh_has_woocommerce() {
	return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_cart_url' );
}

/**
 * URL for a homepage section anchor that works on any page.
 */
function fh_section_url( $anchor ) {
	if ( is_front_page() ) {
		return '#' . $anchor;
	}
	return home_url( '/#' . $anchor );
}

/**
 * Cart badge markup (also used as a WooCommerce cart fragment).
 */
function fh_cart_count_html() {
	$count = 0;
	if ( fh_has_woocommerce() && function_exists( 'WC' ) && WC()->cart ) {
		$count = (int) WC()->cart->get_cart_contents_count();
	}
	$classes = 'fh-cart-count' . ( $count > 0 ? '' : ' fh-cart-count--empty' );

	return '<span class="' . esc_attr( $classes ) . '">' . esc_html( $count ) . '</span>';
}

/**
 * Refresh the cart badge via WooCommerce fragments.
 */
function fh_cart_fragment( $fragments ) {
	$fragments['span.fh-cart-count'] = fh_cart_count_html();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'fh_cart_fragment' );

/**
 * Primary navigation fallback that matches the design.
 */
function fh_primary_menu_fallback() {
	$items = array(
		'home'       => __( 'Home', 'fh-collectibles' ),
		'products'   => __( 'Products', 'fh-collectibles' ),
		'features'   => __( 'Features', 'fh-collectibles' ),
		'categories' => __( 'Categories', 'fh-collectibles' ),
	);
	echo '<ul>';
	foreach ( $items as $anchor => $label ) {
		printf(
			'<li><a href="%1$s" data-fh-section="%2$s"%3$s>%4$s</a></li>',
			esc_url( fh_section_url( $anchor ) ),
			esc_attr( $anchor ),
			( is_front_page() && 'home' === $anchor ) ? ' class="is-active"' : '',
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Render a WooCommerce add-to-cart button for a simple product.
 */
function fh_add_to_cart_button( $product, $label = '', $extra_class = 'fh-btn fh-btn--dark' ) {
	$label = $label ? $label : __( 'Add to Cart', 'fh-collectibles' );

	if ( ! $product instanceof WC_Product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		printf(
			'<a href="%1$s" class="fh-btn %2$s is-disabled" aria-disabled="true">%3$s</a>',
			esc_url( $product ? $product->get_permalink() : '#' ),
			esc_attr( $extra_class ),
			esc_html( $label )
		);
		return;
	}

	printf(
		'<a href="%1$s" data-quantity="1" class="fh-add-to-cart add_to_cart_button ajax_add_to_cart product_type_simple %2$s" data-product_id="%3$d" data-product_sku="%4$s" aria-label="%5$s" rel="nofollow">%5$s</a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( $extra_class ),
		(int) $product->get_id(),
		esc_attr( $product->get_sku() ),
		esc_html( $label )
	);
}

/**
 * Split a product name into card title + variant line ("Hot Wheels Keychain — Midnight Onyx Edition").
 */
function fh_split_product_name( $name ) {
	$name   = trim( (string) $name );
	$parts  = array_map( 'trim', explode( '—', $name, 2 ) );

	if ( count( $parts ) === 2 ) {
		return array( $parts[0], $parts[1] );
	}

	return array( $name, '' );
}

/**
 * Products shown in the homepage grid.
 */
function fh_home_products() {
	$products = array();

	if ( fh_has_woocommerce() && function_exists( 'wc_get_products' ) ) {
		$products = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => 8,
				'orderby'  => 'menu_order',
				'order'    => 'ASC',
				'paginate' => false,
			)
		);
	}

	return $products;
}

/**
 * Newsletter subscribe form handler.
 */
function fh_handle_newsletter() {
	$home = home_url( '/#newsletter' );

	$nonce = isset( $_POST['fh_newsletter_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['fh_newsletter_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'fh_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'fh_sub', 'invalid', $home ) );
		exit;
	}

	// Honeypot: silently accept bots.
	if ( ! empty( $_POST['fh_hp'] ) ) {
		wp_safe_redirect( add_query_arg( 'fh_sub', 'success', $home ) );
		exit;
	}

	$email = isset( $_POST['fh_email'] ) ? sanitize_email( wp_unslash( $_POST['fh_email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'fh_sub', 'invalid', $home ) );
		exit;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'fh_subscriber',
			'title'          => $email,
			'posts_per_page' => 1,
			'post_status'    => 'private',
			'fields'         => 'ids',
		)
	);

	if ( empty( $existing ) ) {
		wp_insert_post(
			array(
				'post_type'   => 'fh_subscriber',
				'post_title'  => $email,
				'post_status' => 'private',
			)
		);
	}

	wp_safe_redirect( add_query_arg( 'fh_sub', 'success', $home ) );
	exit;
}
add_action( 'admin_post_fh_subscribe', 'fh_handle_newsletter' );
add_action( 'admin_post_nopriv_fh_subscribe', 'fh_handle_newsletter' );

/**
 * Hidden subscriber post type (visible in admin).
 */
function fh_register_subscriber_cpt() {
	register_post_type(
		'fh_subscriber',
		array(
			'labels'              => array(
				'name'          => __( 'Subscribers', 'fh-collectibles' ),
				'singular_name' => __( 'Subscriber', 'fh-collectibles' ),
			),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 58,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'        => true,
			'supports'            => array( 'title', 'custom-fields' ),
		)
	);
}
add_action( 'init', 'fh_register_subscriber_cpt' );

/**
 * Body classes.
 */
function fh_body_classes( $classes ) {
	$classes[] = 'fh-theme';
	return $classes;
}
add_filter( 'body_class', 'fh_body_classes' );

/**
 * Keep WooCommerce wrappers out of our own markup.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

