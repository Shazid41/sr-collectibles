<?php
/**
 * 404 template.
 *
 * @package fh-collectibles
 */

get_header();
?>
<main id="fh-main">
	<div class="fh-container fh-page">
		<h1 class="fh-page__title"><?php esc_html_e( '404 — Wrong Turn', 'fh-collectibles' ); ?></h1>
		<p class="fh-page__content"><?php esc_html_e( 'This model has left the showroom. Head back to the garage and keep browsing the collection.', 'fh-collectibles' ); ?></p>
		<p style="margin-top:24px;"><a class="fh-btn fh-btn--gold" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'fh-collectibles' ); ?></a></p>
	</div>
</main>
<?php
get_footer();
