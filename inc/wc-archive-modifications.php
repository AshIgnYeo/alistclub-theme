<?php
/**
 * WooCommerce archive page modifications.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds nav, banner and opening shop wrapper.
 */
function alistclub_wc_add_opening() {
	get_template_part( 'template-parts/section', 'nav' );
	get_template_part( 'template-parts/section', 'banner' );
	get_template_part( 'template-parts/wc/content', 'opening' );
}
add_action( 'woocommerce_before_main_content', 'alistclub_wc_add_opening', 0 );

/**
 * Left column opening tag.
 */
function alistclub_wc_left_column_open() {
	echo '<div class="col-left">';
}
add_action( 'woocommerce_before_main_content', 'alistclub_wc_left_column_open', 5 );

/**
 * Default sidebar inside the left column.
 */
add_action( 'woocommerce_before_main_content', 'woocommerce_get_sidebar', 6 );

/**
 * Left column closing tag.
 */
function alistclub_wc_left_column_close() {
	echo '</div>';
}
add_action( 'woocommerce_before_main_content', 'alistclub_wc_left_column_close', 7 );

/**
 * Right column opening tag.
 */
function alistclub_wc_right_column_open() {
	echo '<div class="col-right">';
}
add_action( 'woocommerce_before_main_content', 'alistclub_wc_right_column_open', 8 );

/**
 * Section heading + product wrapper opening.
 */
function alistclub_wc_right_column_sections() {
	$option = get_query_var( 'orderby', '' );
	switch ( $option ) {
		case 'popularity':
			$store_header = __( 'Best Sellers', 'alistclub' );
			break;
		case 'date':
		case 'date ID':
			$store_header = __( 'New Arrivals', 'alistclub' );
			break;
		default:
			$store_header = __( 'All Products', 'alistclub' );
	}

	printf(
		'<div class="store__products-wrapper"><h2 class="title">%s</h2><div id="store__products">',
		esc_html( $store_header )
	);
}
add_action( 'woocommerce_before_shop_loop', 'alistclub_wc_right_column_sections' );

/**
 * Right column closing tags.
 */
function alistclub_wc_right_column_close() {
	echo '</div></div></div>';
}
add_action( 'woocommerce_after_shop_loop', 'alistclub_wc_right_column_close' );

/**
 * Main content closing tags.
 */
function alistclub_wc_add_closing() {
	echo '</div></div></section>';
}
add_action( 'woocommerce_after_main_content', 'alistclub_wc_add_closing', 5 );

/**
 * Footer.
 */
function alistclub_wc_add_footer() {
	get_template_part( 'template-parts/section', 'footer' );
}
add_action( 'woocommerce_after_main_content', 'alistclub_wc_add_footer', 6 );

/**
 * Remove the default WC sidebar hook (we render our own).
 */
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );

/**
 * Hide the shop page title.
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );
