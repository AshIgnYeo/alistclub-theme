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

/**
 * Hide stock quantity ("X in stock") on the single product page.
 * Keeps out-of-stock and backorder messages intact.
 */
add_filter('woocommerce_get_availability_text', function ($availability, $product) {
	if ($product->is_in_stock() && !$product->is_on_backorder()) {
		return __('In stock', 'alistclub');
	}
	return $availability;
}, 10, 2);

/**
 * Restrict single-product tabs to the standard WooCommerce set.
 * Prevents third-party plugins (e.g. payment gateways) from injecting
 * extra tabs onto the product page.
 */
add_filter('woocommerce_product_tabs', function ($tabs) {
	$allowed = ['description', 'additional_information', 'reviews'];
	foreach (array_keys($tabs) as $key) {
		if (!in_array($key, $allowed, true)) {
			unset($tabs[$key]);
		}
	}
	return $tabs;
}, 98);

/**
 * Hide the PayPal Smart Button (and Pay Later messaging) from the
 * single product page — checkout-only PayPal flow is preferred.
 *
 * Redirects the plugin's render hook to a no-op action so the wrapper
 * markup is never emitted; the `_disabled` filters alone only suppress
 * the button JS and leave the empty placeholder visible.
 */
add_filter('woocommerce_paypal_payments_single_product_renderer_hook', function () {
	return 'alistclub_ppcp_noop';
});
add_filter('woocommerce_paypal_payments_product_buttons_disabled', '__return_true');
add_filter('woocommerce_paypal_payments_product_buttons_paylater_disabled', '__return_true');