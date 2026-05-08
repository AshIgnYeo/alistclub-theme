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
 * Left column opening tag (archives only — hidden on single product).
 */
function alistclub_wc_left_column_open() {
	if ( is_product() ) {
		return;
	}
	echo '<div class="col-left">';
}
add_action( 'woocommerce_before_main_content', 'alistclub_wc_left_column_open', 5 );

/**
 * Default sidebar inside the left column (archives only).
 */
function alistclub_wc_maybe_get_sidebar() {
	if ( is_product() ) {
		return;
	}
	woocommerce_get_sidebar();
}
add_action( 'woocommerce_before_main_content', 'alistclub_wc_maybe_get_sidebar', 6 );

/**
 * Left column closing tag (archives only).
 */
function alistclub_wc_left_column_close() {
	if ( is_product() ) {
		return;
	}
	echo '</div>';
}
add_action( 'woocommerce_before_main_content', 'alistclub_wc_left_column_close', 7 );

/**
 * Right column opening tag — full width on single product pages.
 */
function alistclub_wc_right_column_open() {
	echo is_product() ? '<div class="col-full">' : '<div class="col-right">';
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

/**
 * Replace the default <ul class="products"> wrapper on archives so the
 * archive grid uses the same flex layout as the homepage Store grid.
 * Single product pages are unaffected.
 */
function alistclub_wc_loop_start() {
	return '';
}
add_filter( 'woocommerce_product_loop_start', 'alistclub_wc_loop_start' );

function alistclub_wc_loop_end() {
	return '';
}
add_filter( 'woocommerce_product_loop_end', 'alistclub_wc_loop_end' );

/**
 * Remove the default WC result count + ordering dropdown — the sidebar
 * already provides sorting, and these elements break our flex layout.
 */
function alistclub_wc_strip_archive_controls() {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
}
add_action( 'wp', 'alistclub_wc_strip_archive_controls' );
