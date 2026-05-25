<?php
/**
 * Hide shipping rates when free shipping is available, but keep "Local pickup" 
 * Updated to support WooCommerce 2.6 Shipping Zones
 */

function hide_shipping_when_free_is_available( $rates, $package ) {
	$new_rates = array();
	foreach ( $rates as $rate_id => $rate ) {
		// Only modify rates if free_shipping is present.
		if ( 'free_shipping' === $rate->method_id ) {
			$new_rates[ $rate_id ] = $rate;
			break;
		}
	}

	if ( ! empty( $new_rates ) ) {
		//Save local pickup if it's present.
		foreach ( $rates as $rate_id => $rate ) {
			if ('local_pickup' === $rate->method_id ) {
				$new_rates[ $rate_id ] = $rate;
				break;
			}
		}
		return $new_rates;
	}

	return $rates;
}

add_filter( 'woocommerce_package_rates', 'hide_shipping_when_free_is_available', 10, 2 );

/**
 * Strip the express-payment area from the Cart and Checkout blocks.
 *
 * Each payment gateway (PayPal, Stripe → Apple Pay / Google Pay / Link, etc.)
 * injects itself into the block's express-payment slot. We don't want any of
 * them surfaced before the standard checkout flow, so we render the whole
 * block as empty regardless of which gateways are active.
 */
add_filter('render_block', function ($block_content, $block) {
	if (!isset($block['blockName'])) {
		return $block_content;
	}
	$stripped = [
		'woocommerce/cart-express-payment-block',
		'woocommerce/checkout-express-payment-block',
	];
	if (in_array($block['blockName'], $stripped, true)) {
		return '';
	}
	return $block_content;
}, 10, 2);

/**
 * CSS fallback for any express-payment markup that slips past the block
 * filter (mini-cart, legacy shortcode cart, gateway-injected wrappers).
 */
add_action('wp_head', function () {
	echo '<style>'
		. '.wp-block-woocommerce-cart-express-payment-block,'
		. '.wp-block-woocommerce-checkout-express-payment-block,'
		. '.wc-block-components-express-payment,'
		. '.wc-block-components-express-payment-continue-rule'
		. '{display:none !important;}'
		. '</style>';
});