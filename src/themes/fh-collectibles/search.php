<?php
/**
 * Search results.
 *
 * @package fh-collectibles
 */

get_header();
?>
<main id="fh-main">
	<div class="fh-container fh-page">
		<h1 class="fh-page__title">
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__( 'Search results: %s', 'fh-collectibles' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
		<?php if ( have_posts() ) : ?>
			<div class="fh-page__content">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class(); ?> style="margin-bottom:28px;">
						<h2 style="font-size:20px;margin:0 0 4px;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p style="margin:0;"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
					</article>
					<?php
				endwhile;
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __( '&larr;', 'fh-collectibles' ),
						'next_text' => __( '&rarr;', 'fh-collectibles' ),
					)
				);
				?>
			</div>
		<?php else : ?>
			<p class="fh-page__content"><?php esc_html_e( 'No collectibles matched your search. Try a different keyword.', 'fh-collectibles' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
