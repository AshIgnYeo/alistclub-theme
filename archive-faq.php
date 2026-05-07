<?php
/**
 * FAQ archive — renders all FAQs with a live-filter search bar.
 */

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
				<h1 class="title"><?php post_type_archive_title(); ?></h1>
				<?php get_template_part( 'template-parts/content', 'faq' ); ?>
			</div>
		</div>
	</div>
</main>
<?php
get_template_part( 'template-parts/section', 'footer' );
get_footer();
