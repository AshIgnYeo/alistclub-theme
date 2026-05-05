<?php
get_header();
get_template_part( 'template-parts/section', 'nav' );
get_template_part( 'template-parts/section', 'banner' );
?>

<main id="page-content" tabindex="-1">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class(); ?>>
					<?php the_content(); ?>
				</article>
			<?php endwhile; ?>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'alistclub' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_template_part( 'template-parts/section', 'footer' );
get_footer();
