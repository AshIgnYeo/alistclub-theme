<?php
/**
 * alistclub_wc_single_modifications
 * 
 * Type: Hook
 * Description: 
 * Main wrapper function for modification functions
 */
function alistclub_wc_single_modifications()
{
	/**
	 * Remove SKU from single product
	 * Remove related products from the single product page
	 */
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}
add_action('wp', 'alistclub_wc_single_modifications');