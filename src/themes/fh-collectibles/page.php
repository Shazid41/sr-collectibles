<?php
/**
 * Page template.
 *
 * @package fh-collectibles
 */

get_header();
?>
<main id="fh-main">
	<div class="fh-container fh-page">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1 class="fh-page__title"><?php the_title(); ?></h1>
				<div class="fh-page__content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
