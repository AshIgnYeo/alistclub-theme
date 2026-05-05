<?php
get_header();
get_template_part( 'template-parts/section', 'nav' );
get_template_part( 'template-parts/section', 'banner' );
?>

<main id="page-content" tabindex="-1">
	<div class="container">
		<div class="row">
			<div class="col-left">
				<?php get_sidebar(); ?>
			</div>
			<div class="col-right">
				<?php
				$content_type = get_post_field( 'post_name', get_the_ID() );
				$slug_part    = sanitize_file_name( $content_type );
				if ( $slug_part && locate_template( 'template-parts/content-' . $slug_part . '.php' ) ) {
					get_template_part( 'template-parts/content', $slug_part );
				} else {
					get_template_part( 'template-parts/content', 'page' );
				}
				?>
			</div>
		</div>
	</div>
</main>

<?php
get_template_part( 'template-parts/section', 'footer' );
get_footer();
