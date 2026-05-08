<?php
/**
 * Theme override: WooCommerce loop product tile.
 *
 * Renders the same tile markup as the homepage Store grid so archive pages,
 * category pages, and the JS-driven homepage swap all share one layout.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$brand_tax  = function_exists( 'alistclub_brand_taxonomy' ) ? alistclub_brand_taxonomy() : '';
$brand_name = '';
if ( $brand_tax ) {
	$terms = get_the_terms( $product->get_id(), $brand_tax );
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
