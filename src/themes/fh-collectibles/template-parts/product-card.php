<?php
/**
 * WooCommerce product card — homepage grid & shop loops.
 *
 * @package fh-collectibles
 *
 * @var WC_Product $fh_product Product.
 * @var int        $fh_index   Zero-based position.
 */

$fh_product = get_query_var( 'fh_product' );
$fh_index   = (int) get_query_var( 'fh_index' );

if ( ! $fh_product instanceof WC_Product ) {
	return;
}

list( $fh_card_title, $fh_card_variant ) = fh_split_product_name( $fh_product->get_name() );

$fh_image_id = (int) $fh_product->get_image_id();
$fh_img_html = $fh_image_id
	? wp_get_attachment_image(
		$fh_image_id,
		'fh-product',
		false,
		array(
			'class'   => '',
			'loading' => $fh_index < 4 ? 'eager' : 'lazy',
			'alt'     => $fh_product->get_name(),
		)
	)
	: '<img src="' . esc_url( wc_placeholder_img_src() ) . '" alt="' . esc_attr( $fh_product->get_name() ) . '" loading="lazy" />';
?>
<article class="fh-card" data-reveal>
	<div class="fh-card__top">
		<?php if ( $fh_product->is_featured() ) : ?>
			<span class="fh-badge"><?php esc_html_e( 'Bestseller', 'fh-collectibles' ); ?></span>
		<?php else : ?>
			<span></span>
		<?php endif; ?>
		<button type="button" class="fh-wishlist" data-wishlist-id="<?php echo esc_attr( (string) $fh_product->get_id() ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to wishlist', 'fh-collectibles' ), $fh_card_title ) ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
		</button>
	</div>

	<a class="fh-card__media" href="<?php echo esc_url( $fh_product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $fh_product->get_name() ); ?>">
		<?php echo $fh_img_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="fh-card__shine" aria-hidden="true"></span>
	</a>

	<h3 class="fh-card__title">
		<a href="<?php echo esc_url( $fh_product->get_permalink() ); ?>"><?php echo esc_html( $fh_card_title ); ?></a>
	</h3>
	<?php if ( $fh_card_variant ) : ?>
		<p class="fh-card__variant"><?php echo esc_html( $fh_card_variant ); ?></p>
	<?php endif; ?>

	<div class="fh-card__foot">
		<span class="fh-card__price"><?php echo wp_kses_post( $fh_product->get_price_html() ); ?></span>
		<?php fh_add_to_cart_button( $fh_product ); ?>
	</div>
</article>
