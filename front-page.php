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
					<div class="store__search-sticky">
						<div class="input-group store__search-wrapper">
							<label for="main-search" class="screen-reader-text"><?php esc_html_e( 'Filter products by name or brand', 'alistclub' ); ?></label>
							<input
								type="search"
								id="main-search"
								placeholder="<?php esc_attr_e( 'Filter products by name or brand', 'alistclub' ); ?>"
								class="form-input">
						</div>
					</div>

					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<div class="store__products-wrapper">
							<h2 class="title" data-store-title><?php esc_html_e( 'Store', 'alistclub' ); ?></h2>
							<?php
							// Page 1 of the same query the REST endpoint pages through,
							// so the grid's infinite scroll continues from page 2.
							$initial = new WP_Query( alistclub_store_query_args() );
							?>
							<div id="store__products" data-sort="all" data-total-pages="<?php echo esc_attr( (int) $initial->max_num_pages ); ?>" aria-live="polite">
								<?php
								while ( $initial->have_posts() ) :
									$initial->the_post();
									wc_get_template_part( 'content', 'product' );
								endwhile;
								wp_reset_postdata();
								?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_template_part( 'template-parts/section', 'footer' );
get_footer();
