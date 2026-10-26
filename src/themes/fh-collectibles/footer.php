<?php
/**
 * Theme footer.
 *
 * @package fh-collectibles
 */

?>
<footer class="fh-footer">
	<div class="fh-container">
		<div class="fh-footer__grid">
			<div class="fh-footer__brand">
				<a class="fh-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( fh_brand_monogram() ); ?></a>
				<p><?php esc_html_e( 'SR Collectibles crafts precision scaled miniature automotive keychains for supercar enthusiasts, drift fans, and passionate gearheads worldwide.', 'fh-collectibles' ); ?></p>
				<span class="fh-footer__secure"><?php esc_html_e( 'SECURE CHECKOUT • FAST DISPATCH', 'fh-collectibles' ); ?></span>
			</div>

			<div>
				<h5><?php esc_html_e( 'Catalog', 'fh-collectibles' ); ?></h5>
				<ul>
					<?php if ( fh_has_woocommerce() ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Hot Wheels Series', 'fh-collectibles' ); ?></a></li>
						<li><a href="<?php echo esc_url( fh_category_url( 'racing-editions' ) ); ?>"><?php esc_html_e( 'Die-Cast Muscle Cars', 'fh-collectibles' ); ?></a></li>
						<li><a href="<?php echo esc_url( fh_category_url( 'classic-collectibles' ) ); ?>"><?php esc_html_e( 'JDM Legend Models', 'fh-collectibles' ); ?></a></li>
						<li><a href="<?php echo esc_url( fh_category_url( 'limited-editions' ) ); ?>"><?php esc_html_e( 'Limited Vault Drops', 'fh-collectibles' ); ?></a></li>
					<?php else : ?>
						<li><a href="<?php echo esc_url( fh_section_url( 'products' ) ); ?>"><?php esc_html_e( 'Hot Wheels Series', 'fh-collectibles' ); ?></a></li>
						<li><a href="<?php echo esc_url( fh_section_url( 'products' ) ); ?>"><?php esc_html_e( 'Die-Cast Muscle Cars', 'fh-collectibles' ); ?></a></li>
						<li><a href="<?php echo esc_url( fh_section_url( 'products' ) ); ?>"><?php esc_html_e( 'JDM Legend Models', 'fh-collectibles' ); ?></a></li>
						<li><a href="<?php echo esc_url( fh_section_url( 'products' ) ); ?>"><?php esc_html_e( 'Limited Vault Drops', 'fh-collectibles' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

			<div>
				<h5><?php esc_html_e( 'Support', 'fh-collectibles' ); ?></h5>
				<ul>
					<li><a href="<?php echo esc_url( fh_has_woocommerce() ? wc_get_account_endpoint_url( 'orders' ) : '#' ); ?>"><?php esc_html_e( 'Track Order', 'fh-collectibles' ); ?></a></li>
					<li><a href="<?php echo esc_url( fh_section_url( 'features' ) ); ?>"><?php esc_html_e( 'Shipping & Returns', 'fh-collectibles' ); ?></a></li>
					<li><a href="<?php echo esc_url( fh_section_url( 'features' ) ); ?>"><?php esc_html_e( 'Authenticity Guarantee', 'fh-collectibles' ); ?></a></li>
					<li><a href="<?php echo esc_url( fh_has_woocommerce() ? wc_get_account_endpoint_url( '' ) : '#' ); ?>"><?php esc_html_e( 'Contact Support', 'fh-collectibles' ); ?></a></li>
				</ul>
			</div>

			<div>
				<h5><?php esc_html_e( 'Legal', 'fh-collectibles' ); ?></h5>
				<ul>
					<li><a href="#"><?php esc_html_e( 'Privacy Policy', 'fh-collectibles' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Terms of Service', 'fh-collectibles' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Affiliate Program', 'fh-collectibles' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'srcollectibles.com', 'fh-collectibles' ); ?></a></li>
				</ul>
			</div>
		</div>

		<div class="fh-footer__bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'fh-collectibles' ); ?></p>
			<p class="fh-footer__sig">
				<em><?php esc_html_e( 'Designed & built by', 'fh-collectibles' ); ?></em>
				<a href="https://github.com/Shazid41" target="_blank" rel="noopener noreferrer">
					<strong>Md. Shazidur Rahaman</strong>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.55 0-.27-.01-1.17-.02-2.12-3.2.7-3.88-1.36-3.88-1.36-.52-1.33-1.28-1.68-1.28-1.68-1.04-.71.08-.7.08-.7 1.15.08 1.76 1.19 1.76 1.19 1.03 1.75 2.69 1.25 3.35.95.1-.74.4-1.25.72-1.54-2.55-.29-5.24-1.28-5.24-5.68 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.17 1.18a11.04 11.04 0 015.78 0c2.2-1.49 3.16-1.18 3.16-1.18.63 1.59.24 2.76.12 3.05.74.81 1.18 1.83 1.18 3.09 0 4.42-2.69 5.39-5.26 5.67.41.36.78 1.05.78 2.13 0 1.54-.01 2.78-.01 3.16 0 .31.21.67.8.55A11.51 11.51 0 0023.5 12C23.5 5.65 18.35.5 12 .5z"></path></svg>
				</a>
			</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
