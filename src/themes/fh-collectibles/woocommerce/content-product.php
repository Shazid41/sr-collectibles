<?php
/**
 * Product card override for the WooCommerce shop loop.
 *
 * @package fh-collectibles
 */

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

set_query_var( 'fh_product', $product );
get_template_part( 'template-parts/product-card' );
