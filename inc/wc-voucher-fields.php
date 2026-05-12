<?php
/**
 * Voucher personalisation fields.
 *
 * Adds To / From / Message inputs to products in the "vouchers" category
 * and carries the values through cart, checkout, order, and emails.
 */

const ALISTCLUB_VOUCHER_CATEGORY = 'vouchers';
const ALISTCLUB_VOUCHER_MESSAGE_MAX_WORDS = 250;

/**
 * Whether the given product belongs to the voucher category.
 */
function alistclub_is_voucher_product($product_id): bool
{
	return has_term(ALISTCLUB_VOUCHER_CATEGORY, 'product_cat', $product_id);
}

/**
 * Render the To / From / Message inputs above the add-to-cart button.
 */
add_action('woocommerce_before_add_to_cart_button', function () {
	global $product;
	if (!$product || !alistclub_is_voucher_product($product->get_id())) {
		return;
	}

	$to      = isset($_POST['voucher_to']) ? wc_clean(wp_unslash($_POST['voucher_to'])) : '';
	$from    = isset($_POST['voucher_from']) ? wc_clean(wp_unslash($_POST['voucher_from'])) : '';
	$message = isset($_POST['voucher_message']) ? sanitize_textarea_field(wp_unslash($_POST['voucher_message'])) : '';
	?>
	<div class="alistclub-voucher-fields">
		<p class="form-row form-row-wide">
			<label for="voucher_to"><?php esc_html_e('To', 'alistclub'); ?>&nbsp;<abbr class="required" title="<?php esc_attr_e('required', 'alistclub'); ?>">*</abbr></label>
			<input type="text" id="voucher_to" name="voucher_to" maxlength="100" required value="<?php echo esc_attr($to); ?>">
		</p>
		<p class="form-row form-row-wide">
			<label for="voucher_from"><?php esc_html_e('From', 'alistclub'); ?>&nbsp;<abbr class="required" title="<?php esc_attr_e('required', 'alistclub'); ?>">*</abbr></label>
			<input type="text" id="voucher_from" name="voucher_from" maxlength="100" required value="<?php echo esc_attr($from); ?>">
		</p>
		<p class="form-row form-row-wide">
			<label for="voucher_message">
				<?php esc_html_e('Message', 'alistclub'); ?>
				<small>(<?php printf(esc_html__('max %d words', 'alistclub'), ALISTCLUB_VOUCHER_MESSAGE_MAX_WORDS); ?>)</small>
			</label>
			<textarea id="voucher_message" name="voucher_message" rows="4"><?php echo esc_textarea($message); ?></textarea>
		</p>
	</div>
	<?php
});

/**
 * Validate fields on add-to-cart.
 */
add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id) {
	if (!alistclub_is_voucher_product($product_id)) {
		return $passed;
	}

	$to   = isset($_POST['voucher_to']) ? trim(wc_clean(wp_unslash($_POST['voucher_to']))) : '';
	$from = isset($_POST['voucher_from']) ? trim(wc_clean(wp_unslash($_POST['voucher_from']))) : '';
	$message = isset($_POST['voucher_message']) ? trim(sanitize_textarea_field(wp_unslash($_POST['voucher_message']))) : '';

	if ($to === '' || $from === '') {
		wc_add_notice(__('Please fill in both the “To” and “From” fields for your voucher.', 'alistclub'), 'error');
		return false;
	}

	if ($message !== '' && str_word_count($message) > ALISTCLUB_VOUCHER_MESSAGE_MAX_WORDS) {
		wc_add_notice(sprintf(
			__('Your voucher message exceeds the %d-word limit.', 'alistclub'),
			ALISTCLUB_VOUCHER_MESSAGE_MAX_WORDS
		), 'error');
		return false;
	}

	return $passed;
}, 10, 2);

/**
 * Attach the voucher values to the cart item so identical products
 * with different recipients stay as separate cart lines.
 */
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
	if (!alistclub_is_voucher_product($product_id)) {
		return $cart_item_data;
	}

	$cart_item_data['alistclub_voucher'] = [
		'to'      => isset($_POST['voucher_to']) ? wc_clean(wp_unslash($_POST['voucher_to'])) : '',
		'from'    => isset($_POST['voucher_from']) ? wc_clean(wp_unslash($_POST['voucher_from'])) : '',
		'message' => isset($_POST['voucher_message']) ? sanitize_textarea_field(wp_unslash($_POST['voucher_message'])) : '',
	];
	$cart_item_data['unique_key'] = md5(microtime() . wp_rand());

	return $cart_item_data;
}, 10, 2);

/**
 * Display voucher details in cart and checkout summaries.
 */
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
	if (empty($cart_item['alistclub_voucher'])) {
		return $item_data;
	}

	$voucher = $cart_item['alistclub_voucher'];
	if (!empty($voucher['to'])) {
		$item_data[] = ['key' => __('To', 'alistclub'), 'value' => $voucher['to']];
	}
	if (!empty($voucher['from'])) {
		$item_data[] = ['key' => __('From', 'alistclub'), 'value' => $voucher['from']];
	}
	if (!empty($voucher['message'])) {
		$item_data[] = ['key' => __('Message', 'alistclub'), 'value' => wp_kses_post($voucher['message'])];
	}

	return $item_data;
}, 10, 2);

/**
 * Persist voucher details onto the order line item so they appear in
 * the admin, customer order details, and email notifications.
 */
add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values) {
	if (empty($values['alistclub_voucher'])) {
		return;
	}

	$voucher = $values['alistclub_voucher'];
	if (!empty($voucher['to'])) {
		$item->add_meta_data(__('To', 'alistclub'), $voucher['to']);
	}
	if (!empty($voucher['from'])) {
		$item->add_meta_data(__('From', 'alistclub'), $voucher['from']);
	}
	if (!empty($voucher['message'])) {
		$item->add_meta_data(__('Message', 'alistclub'), $voucher['message']);
	}
}, 10, 3);
