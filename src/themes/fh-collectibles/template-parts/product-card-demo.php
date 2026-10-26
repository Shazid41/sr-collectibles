<?php
/**
 * Fallback demo product card (WooCommerce not active).
 *
 * @package fh-collectibles
 *
 * @var array $fh_demo_card [ image, title, variant ].
 * @var int   $fh_index     Zero-based position.
 */

$fh_demo_card = get_query_var( 'fh_demo_card' );
$fh_index     = (int) get_query_var( 'fh_index' );

if ( empty( $fh_demo_card ) ) {
	return;
}
?>
<article class="fh-card" data-reveal>
	<div class="fh-card__top">
		<span class="fh-badge"><?php esc_html_e( 'Bestseller', 'fh-collectibles' ); ?></span>
		<button type="button" class="fh-wishlist" data-wishlist-id="demo-<?php echo esc_attr( (string) $fh_index ); ?>" aria-pressed="false" aria-label="<?php esc_attr_e( 'Add to wishlist', 'fh-collectibles' ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
		</button>
	</div>

	<div class="fh-card__media">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $fh_demo_card[0] ) ); ?>" alt="<?php echo esc_attr( $fh_demo_card[1] . ' — ' . $fh_demo_card[2] ); ?>" loading="<?php echo $fh_index < 4 ? 'eager' : 'lazy'; ?>" width="720" height="540" />
		<span class="fh-card__shine" aria-hidden="true"></span>
	</div>

	<h3 class="fh-card__title"><?php echo esc_html( $fh_demo_card[1] ); ?></h3>
	<p class="fh-card__variant"><?php echo esc_html( $fh_demo_card[2] ); ?></p>

	<div class="fh-card__foot">
		<span class="fh-card__price">$24.00</span>
		<a class="fh-btn fh-btn--dark is-disabled" aria-disabled="true" href="#"><?php esc_html_e( 'Add to Cart', 'fh-collectibles' ); ?></a>
	</div>
</article>
