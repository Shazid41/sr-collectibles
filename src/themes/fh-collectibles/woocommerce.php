<?php
/**
 * Generic page wrapper for WooCommerce archives & single products.
 *
 * @package fh-collectibles
 */

get_header();
?>
<div class="fh-container fh-woo">
	<?php woocommerce_content(); ?>
</div>
<?php
get_footer();
