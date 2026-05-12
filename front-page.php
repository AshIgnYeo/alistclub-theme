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
							<div id="store__products" data-sort="all" aria-live="polite">
								<?php
								$brand_tax = function_exists( 'alistclub_brand_taxonomy' ) ? alistclub_brand_taxonomy() : '';
								$initial   = new WP_Query( array(
									'post_type'           => 'product',
									'posts_per_page'      => 12,
									'post_status'         => 'publish',
									'ignore_sticky_posts' => true,
									'no_found_rows'       => true,
									'orderby'             => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
								) );
								while ( $initial->have_posts() ) :
									$initial->the_post();
									global $product;
									$product = wc_get_product( get_the_ID() );
									if ( ! $product || ! $product->is_visible() ) {
										continue;
									}
									$brand_name = '';
									if ( $brand_tax ) {
										$terms = get_the_terms( get_the_ID(), $brand_tax );
										if ( $terms && ! is_wp_error( $terms ) ) {
											$brand_name = $terms[0]->name;
										}
									}
									$img = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
									?>
									<div class="product-item"
										data-name="<?php echo esc_attr( strtolower( $product->get_name() ) ); ?>"
										data-brand="<?php echo esc_attr( strtolower( $brand_name ) ); ?>">
										<a class="product-link" href="<?php the_permalink(); ?>">
											<div class="product-image">
												<?php if ( $img ) : ?>
													<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy" decoding="async">
												<?php endif; ?>
											</div>
											<div class="product-name"><?php echo esc_html( $product->get_name() ); ?></div>
											<?php if ( $brand_name ) : ?>
												<div class="product-brand"><?php echo esc_html( $brand_name ); ?></div>
											<?php endif; ?>
											<div class="product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
										</a>
										<div class="product-cart">
											<?php woocommerce_template_loop_add_to_cart(); ?>
										</div>
									</div>
									<?php
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
