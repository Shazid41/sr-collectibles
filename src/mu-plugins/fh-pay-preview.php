<?php
/**
 * Plugin Name: FH Pay Preview Seeder
 * Description: Seeds one demo product into the cart when visiting checkout with ?fh-pay-preview=1, so the SR Secure Pay popup can be previewed with an empty cart.
 * Version:     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_loaded',
	function () {
		if ( ! isset( $_GET['fh-pay-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}
		if ( ! empty( WC()->cart->cart_contents ) ) {
			return;
		}
		$product_id = 13;
		if ( 'product' === get_post_type( $product_id ) ) {
			WC()->cart->add_to_cart( $product_id, 1 );
		}
	}
);
