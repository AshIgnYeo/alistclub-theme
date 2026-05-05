<?php
/**
 * Front page template.
 */
get_header();
get_template_part( 'template-parts/section', 'nav' );
get_template_part( 'template-parts/section', 'banner' );
?>

<main id="page-content" tabindex="-1">
	<section id="store-area">
		<div class="container">
			<div class="row">
				<div class="col-left">
					<?php get_sidebar(); ?>
				</div>
				<div class="col-right">
					<div class="input-group store__search-wrapper">
						<label for="main-search" class="screen-reader-text"><?php esc_html_e( 'Search for products, articles, concerns', 'alistclub' ); ?></label>
						<input
							type="search"
							id="main-search"
							placeholder="<?php esc_attr_e( 'Search for products, articles, concerns', 'alistclub' ); ?>"
							class="form-input">
					</div>

					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<div class="store__products-wrapper">
							<h2 class="title"><?php esc_html_e( 'Best Sellers', 'alistclub' ); ?></h2>
							<div id="store__products">
								<?php
								echo do_shortcode( '[products limit="6" columns="3" orderby="popularity" visibility="visible"]' );
								?>
							</div>
						</div>
					<?php endif; ?>

					<?php
					// Render any content set on the static front page (Settings → Reading).
					if ( have_posts() ) :
						while ( have_posts() ) : the_post();
							the_content();
						endwhile;
					endif;
					?>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_template_part( 'template-parts/section', 'footer' );
get_footer();
