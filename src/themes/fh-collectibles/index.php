<?php
/**
 * Fallback template.
 *
 * @package fh-collectibles
 */

get_header();
?>
<main id="fh-main">
	<div class="fh-container fh-page">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?>>
					<?php if ( ! is_front_page() ) : ?>
						<h1 class="fh-page__title"><?php the_title(); ?></h1>
					<?php endif; ?>
					<div class="fh-page__content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
			endwhile;
		else :
			?>
			<h1 class="fh-page__title"><?php esc_html_e( 'Nothing found', 'fh-collectibles' ); ?></h1>
			<p class="fh-page__content"><?php esc_html_e( 'It looks like nothing was found at this location. Try a search instead.', 'fh-collectibles' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
