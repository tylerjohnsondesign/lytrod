<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://webfielddesign.com/
 * @since      1.0.0add_filter('woocommerce_add_to_cart_validation', function($passed, $product_id, $quantity) {
 *
 * @package    Subscriptions_Upgrader
 * @subpackage Subscriptions_Upgrader/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Subscriptions_Upgrader
 * @subpackage Subscriptions_Upgrader/public
 * @author     talktaimor <talktaimor@gmail.com>
 */
class Subscriptions_Upgrader_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('woocommerce_account_subscriptions_endpoint', [$this, 'custom_my_subscriptions_template']);
		add_action( 'wp_footer', [$this,'custom_my_account_scripts'] );
		add_action('woocommerce_single_product_summary', [$this,'modify_product_title_based_on_url'], 4);
		add_action('woocommerce_before_add_to_cart_button', [$this,'add_hidden_product_title_field']);
		add_filter( 'woocommerce_add_cart_item_data', [$this,'set_custom_product_title_in_cart'], 10, 3 );
		add_filter( 'woocommerce_cart_item_name', [$this,'display_custom_title_in_cart'], 10, 3 );
		add_action('woocommerce_checkout_before_customer_details', [$this,'add_custom_hidden_field_in_checkout'], 10);
		add_action('woocommerce_checkout_update_order_meta',  [$this,'save_custom_product_title_in_order_meta'], 10, 2);
		add_filter('woocommerce_order_item_name', [$this, 'customize_order_item_title'], 10, 2);
		add_filter('woocommerce_order_item_get_name', [$this,'customize_order_item_title'], 15, 2);
		add_filter('woocommerce_email_order_item_name', [$this,'customize_order_item_title'], 20, 2);
		add_action('woocommerce_thankyou', [$this,'redirect_after_order'], 10, 1);
		add_action('wp_head', [$this, 'custom_css']);
		add_action('woocommerce_checkout_create_order_line_item', [$this,'copy_prorated_fee_to_order_item'], 10, 4);

		//add_action('woocommerce_after_order_notes', [$this, 'add_hidden_field']);
        //add_action('woocommerce_checkout_process', [$this, 'validate_hidden_field']);
       //add_action('woocommerce_checkout_update_order_meta', [$this, 'save_hidden_field']);
        //add_action('woocommerce_admin_order_data_after_order_details', [$this, 'display_hidden_field_admin']);
       add_action('woocommerce_thankyou', [$this, 'display_hidden_field_thankyou']);
		
	   add_filter('query_vars', function ($vars) {
			$vars[] = 'groupcount';
			$vars[] = 'product_title';
			$vars[] = 'switch_variation_group';
			return $vars;
		});
		
		// Handle resubscribe with sign up variation - auto add 1 year subscription
		add_action('template_redirect', [$this, 'handle_resubscribe_signup_auto_add'], 5);
	}
	
	
	public function copy_prorated_fee_to_order_item($item, $cart_item_key, $values, $order) {
		if (isset($values['custom_prorated_fee'])) {
			$item->add_meta_data('custom_prorated_fee', $values['custom_prorated_fee'], false);
		}
	}
	
	/**
	 * Handle resubscribe when subscription variation is "Sign up" (free trial)
	 * Automatically add 1-year subscription to cart
	 */
	public function handle_resubscribe_signup_auto_add() {
		// Only run on my-account page
		if (!is_account_page()) {
			return;
		}
		
		// Check if resubscribe parameter exists
		if (!isset($_GET['resubscribe']) || empty($_GET['resubscribe'])) {
			return;
		}
		
		// Prevent multiple executions using transient
		$subscription_id = intval($_GET['resubscribe']);
	
		$transient_key = 'resubscribe_processed_' . $subscription_id . '_' . get_current_user_id();
		if (get_transient($transient_key)) {
			return; // Already processed
		}
		
		// Check if WooCommerce Subscriptions is active
		if (!function_exists('wcs_get_subscription')) {
			return;
		}
		
		
		
		// Check if cart already has items (don't interfere with existing cart)
// 		if (!WC()->cart->is_empty()) {
// 			return;
// 		}
		
		// Get the subscription
		$subscription = wcs_get_subscription($subscription_id);
		if (!$subscription) {
			return;
		}
		
		
		// Check if user owns this subscription
		if ($subscription->get_user_id() != get_current_user_id()) {
			return;
		}

		// Already renewed, superseded, or has a renewal order to pay: don't build a new checkout.
		$destination = lytrod_resubscribe_destination($subscription);
		if ($destination) {
			if ($destination['notice']) {
				wc_add_notice($destination['notice'], 'notice');
			}
			wp_safe_redirect($destination['url']);
			exit;
		}

		// Get subscription items
		$items = $subscription->get_items();
		if (empty($items)) {
			return;
		}
		
		// Get the first item (assuming single product subscription)
		$item = reset($items);
		$variation_id = $item->get_variation_id();
		
		if (!$variation_id) {
			return;
		}
		
		// Get the variation product
		$variation = wc_get_product($variation_id);
		if (!$variation || !$variation->is_type('subscription_variation')) {
			return;
		}
		
		// Get variation attributes
		$variation_attributes = $variation->get_attributes();
		$attributes_text = array();
		foreach ($variation_attributes as $attribute_name => $attribute_value) {
			$attributes_text[] = $attribute_value;
		}
		
		// Check if variation is "Sign up" (free trial)
		$is_signup = false;
		if (in_array('Sign up', $attributes_text) || 
			in_array('Sign Up', $attributes_text) || 
			in_array('Registration', $attributes_text) ||
			in_array('sign up', $attributes_text)) {
			$is_signup = true;
		}
		
		// If not sign up variation, return (let normal resubscribe flow handle it)
		if (!$is_signup) {
			return;
		}
		
		// Get parent product ID
		$parent_id = $variation->get_parent_id();
		if (!$parent_id) {
			return;
		}
		
		// Get parent product
		$parent_product = wc_get_product($parent_id);
		if (!$parent_product) {
			return;
		}
		
		// Find the 1-year variation
		$one_year_variation_id = null;
		$variations = $parent_product->get_available_variations();
		
		foreach ($variations as $variation_data) {
			$var_id = $variation_data['variation_id'];
			$var_product = wc_get_product($var_id);
			
			if (!$var_product || !$var_product->is_type('subscription_variation')) {
				continue;
			}
			
			$var_attributes = $var_product->get_attributes();
			$var_attributes_text = array();
			foreach ($var_attributes as $attr_name => $attr_value) {
				$var_attributes_text[] = $attr_value;
			}
			
			// Check if this is a 1-year variation
			if (in_array('1 Year', $var_attributes_text) || 
				in_array('Intellicut Plus 1 year', $var_attributes_text) || 
				in_array('Intellicut AeroCut nano Edition 1 Year', $var_attributes_text) || 
				in_array('Intellicut AeroCut nano Edition Plus Year', $var_attributes_text) || 
				in_array('VRCut Plus 1 year', $var_attributes_text) || 
				in_array('Bizcard Plus 1 year', $var_attributes_text)) {
				$one_year_variation_id = $var_id;
				break;
			}
		}
		
		// If 1-year variation not found, try alternative method using get_children
		if (!$one_year_variation_id) {
			$children = $parent_product->get_children();
			foreach ($children as $child_id) {
				$child_product = wc_get_product($child_id);
				if (!$child_product || !$child_product->is_type('subscription_variation')) {
					continue;
				}
				
				$child_attributes = $child_product->get_attributes();
				$child_attributes_text = array();
				foreach ($child_attributes as $attr_name => $attr_value) {
					$child_attributes_text[] = $attr_value;
				}
				
				// Check if this is a 1-year variation
				if (in_array('1 Year', $child_attributes_text) || 
					in_array('Intellicut Plus 1 year', $child_attributes_text) || 
					in_array('Intellicut AeroCut nano Edition 1 Year', $child_attributes_text) || 
					in_array('Intellicut AeroCut nano Edition Plus Year', $child_attributes_text) || 
					in_array('VRCut Plus 1 year', $child_attributes_text) || 
					in_array('Bizcard Plus 1 year', $child_attributes_text)) {
					$one_year_variation_id = $child_id;
					break;
				}
			}
		}
		
		if (!$one_year_variation_id) {
			return; // Could not find 1-year variation
		}
		
		// Set transient to prevent multiple executions (expires in 5 minutes)
		set_transient($transient_key, true, 5 * MINUTE_IN_SECONDS);
		
		// Clear cart first
		WC()->cart->empty_cart();
		
		// Add 1-year subscription to cart with resubscribe data
		$cart_item_data = array(
			'subscription_resubscribe' => array(
				'subscription_id' => $subscription_id
			)
		);
		
		// Add to cart
		$cart_item_key = WC()->cart->add_to_cart($parent_id, 1, $one_year_variation_id, array(), $cart_item_data);
		
		if ($cart_item_key) {
			// Redirect to checkout
			wp_safe_redirect(wc_get_checkout_url());
			exit;
		}
	}
	
	public function custom_css() {
		?>
			<style>
				.woocommerce div.product {
					margin-top: 150px;
				}
			</style>
		<?php
	}
	
	public function redirect_after_order($order_id) {
		// Check if WooCommerce Subscriptions is active
		if (!function_exists('wcs_get_subscriptions_for_order') || !function_exists('wcs_get_subscription')) {
			return;
		}

		// Get order
		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		// Get expired subscription ID (resubscribe case)
		$expired_subscription_id = $order->get_meta('_subscription_resubscribe', true);

		// Also check for standard resubscribe meta key
		if (!$expired_subscription_id) {
			$expired_subscription_id = $order->get_meta('_resubscribe_subscription_id', true);
		}

		if (!$expired_subscription_id) {
			// Not a resubscribe flow - use original logic
			$is_subscription = false;
			foreach ($order->get_items() as $item) {
				$product = $item->get_product();
				if ($product && $product->is_type('subscription_variation')) {
					$is_subscription = true;
					break;
				}
			}

			if ($is_subscription) {
				$subscriptions = wcs_get_subscriptions_for_order($order_id);
				if (!empty($subscriptions)) {
					$subscription_id = key($subscriptions);
					$subscription = reset($subscriptions);
					$next_payment_date = get_post_meta($subscription_id, 'calculated_end_date', true);
					if($next_payment_date) {
						// ============ FIX #1: REGULAR SUBSCRIPTION CREATION ============
						// Set next_payment 1 second after trial_end
						$trial_end_formatted = $next_payment_date;
						$next_payment_formatted = date('Y-m-d H:i:s', strtotime($next_payment_date . ' +5 minutes'));

						// Use update_dates method to handle all date fields
						if (method_exists($subscription, 'update_dates')) {
							$subscription->update_dates(array(
								'trial_end' => '',
								'next_payment' => $next_payment_formatted,
							));
							$subscription->save();
						}

						// ONLY update custom meta (not WooCommerce Subscriptions standard fields)
						update_post_meta($subscription_id, 'expiration_date', $next_payment_date);
						// REMOVED: All _schedule_* and _schedule_*_timestamp_gmt updates
						// These are now handled internally by update_dates()
						// ============ END FIX #1 ============
					}
				}
				wp_redirect(home_url('/my-account/subscriptions/'));
				exit;
			} else {
				wp_redirect(home_url('/my-account/orders/'));
				exit;
			}
			return;
		}

		// Get NEW subscription created by this order
		$subscriptions = wcs_get_subscriptions_for_order($order_id);
		if (empty($subscriptions)) {
			return;
		}

		// NEW subscription (only this will be updated)
		$new_subscription_id = key($subscriptions);
		$new_subscription = wcs_get_subscription($new_subscription_id);

		if (!$new_subscription) {
			return;
		}

		// Get variation ID from order items
		$variation_id = null;
		$years_to_add = 0;

		foreach ($order->get_items() as $item) {
			$item_variation_id = $item->get_variation_id();
			if ($item_variation_id) {
				$variation_id = $item_variation_id;

				// Get variation product
				$variation = wc_get_product($variation_id);
				if ($variation && $variation->is_type('variation')) {
					// Get variation attributes
					$variation_attributes = $variation->get_attributes();
					$attributes_text = [];

					// Collect variation attributes
					foreach ($variation_attributes as $attribute_name => $attribute_value) {
						$attributes_text[] = $attribute_value;
					}

					// Calculate years to add based on attributes
					if (in_array('1 Year', $attributes_text) || in_array('Intellicut Plus 1 year', $attributes_text) || in_array('Intellicut AeroCut nano Edition 1 Year', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus Year', $attributes_text) || in_array('VRCut Plus 1 year', $attributes_text) || in_array('Bizcard Plus 1 year', $attributes_text)) {
						$years_to_add = 1;
					} elseif (in_array('2 Years', $attributes_text) || in_array('Intellicut Plus 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 2 Years', $attributes_text) || in_array('VRCut Plus 2 years', $attributes_text) || in_array('Bizcard Plus 2 Years', $attributes_text)) {
						$years_to_add = 2;
					} elseif (in_array('3 Years', $attributes_text) || in_array('Intellicut Plus 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 3 Years', $attributes_text) || in_array('VRCut Plus 3 years', $attributes_text) || in_array('Bizcard Plus 3 years', $attributes_text)) {
						$years_to_add = 3;
					} elseif (in_array('4 Years', $attributes_text) || in_array('Intellicut Plus 4 years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 4 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 4 years', $attributes_text) || in_array('VRCut Plus 4 years', $attributes_text) || in_array('Bizcard Plus 4 years', $attributes_text)) {
						$years_to_add = 4;
					}

					break; // Found variation, exit loop
				}
			}
		}

		// Calculate next payment date based on expired subscription's calculated_end_date + years
		if ($years_to_add > 0 && $expired_subscription_id) {
			// Get expired subscription's calculated_end_date
			$expired_calculated_end_date = get_post_meta($expired_subscription_id, 'calculated_end_date', true);
			// Fallback to other date fields if calculated_end_date not found
			if (empty($expired_calculated_end_date)) {
				$expired_calculated_end_date = get_post_meta($expired_subscription_id, '_custom_expiration_date', true);
			}

			if (empty($expired_calculated_end_date)) {
				$expired_calculated_end_date = get_post_meta($expired_subscription_id, '_schedule_trial_end', true);
			}

			if (empty($expired_calculated_end_date)) {
				$expired_subscription_obj = wcs_get_subscription($expired_subscription_id);
				if ($expired_subscription_obj) {
					$expired_calculated_end_date = $expired_subscription_obj->get_date('next_payment');
				}
			}

			if ($expired_calculated_end_date) {
				try {
					// Start from expired subscription's calculated_end_date and add years
					$base_date = new DateTime($expired_calculated_end_date);
					// If the old expiration date is in the past, use today as the base
					$now = new DateTime();
					if ($base_date < $now) {
						$base_date = $now;
					}

					$expiration_date = clone $base_date;
					$expiration_date->modify("+{$years_to_add} years");

					// Set end date after expiration
					$end_date = clone $expiration_date;
					$end_date->modify('+1 day');

					$next_payment_date = $expiration_date->format('Y-m-d H:i:s');
					$calculated_end_date = $end_date->format('Y-m-d H:i:s');

					// ============ FIX #2: RESUBSCRIBE WITH YEARS ============
					// Set trial_end and next_payment with 1 second difference
					$trial_end_formatted = $next_payment_date;
					$next_payment_formatted = date('Y-m-d H:i:s', strtotime($next_payment_date . ' +5 minutes'));

					// Update new subscription dates
					if (method_exists($new_subscription, 'update_dates')) {    
						$new_subscription->update_dates(array(
							'trial_end' => '',
							'next_payment' => $next_payment_formatted,
							'end' => '',
						));
						$new_subscription->save();
					}
					// ============ END FIX #2 ============

					// ONLY update custom meta fields
					update_post_meta($new_subscription_id, '_custom_expiration_date', $next_payment_date);
					update_post_meta($new_subscription_id, 'expiration_date', $next_payment_date);
					update_post_meta($new_subscription_id, 'calculated_end_date', $calculated_end_date);
					update_post_meta($new_subscription_id, 'years_added', $years_to_add);
					update_post_meta($expired_subscription_id, 'is_expired_subscription', 'yes');
					

					// REMOVED: All _schedule_* and _schedule_*_timestamp_gmt updates
					// These are now handled internally by update_dates()

				} catch (Exception $e) {
					// Log error but don't break the flow
					error_log('Error calculating subscription dates: ' . $e->getMessage());
				}
			}
		} else {
			// ============ FIX #3: FALLBACK LOGIC ============
			// Fallback: use existing calculated_end_date if available
			$next_payment_date = get_post_meta($new_subscription_id, 'calculated_end_date', true);
			if ($next_payment_date) {
				// Fix: Set next_payment 1 second after trial_end
				$trial_end_formatted = $next_payment_date;
				$next_payment_formatted = date('Y-m-d H:i:s', strtotime($next_payment_date . ' +5 minutes'));

				// Update subscription dates using update_dates method
				if (method_exists($new_subscription, 'update_dates')) {
					$new_subscription->update_dates(array(
						'trial_end' => '',
						'next_payment' => $next_payment_formatted,
					));
					$new_subscription->save();
				}

				// ONLY update custom meta
				update_post_meta($new_subscription_id, '_custom_expiration_date', $next_payment_date);
				update_post_meta($new_subscription_id, 'expiration_date', $next_payment_date);

				// REMOVED: _schedule_* and _schedule_*_timestamp_gmt updates

			} else {
				// Last fallback: try to get from expired subscription
				$expired_custom_expiration = get_post_meta($expired_subscription_id, '_custom_expiration_date', true);
				$expired_trial_end = get_post_meta($expired_subscription_id, '_schedule_trial_end', true);

				if (!empty($expired_custom_expiration)) {
					update_post_meta($new_subscription_id, '_custom_expiration_date', $expired_custom_expiration);

					// Use update_dates instead of direct meta update
					if (method_exists($new_subscription, 'update_dates')) {
						$new_subscription->update_dates(array(
							'trial_end' => $expired_custom_expiration,
						));
						$new_subscription->save();
					}
					// REMOVED: GMT timestamp update

				} elseif (!empty($expired_trial_end)) {
					update_post_meta($new_subscription_id, '_custom_expiration_date', $expired_trial_end);

					// Use update_dates instead of direct meta update
					if (method_exists($new_subscription, 'update_dates')) {
						$new_subscription->update_dates(array(
							'trial_end' => $expired_trial_end,
						));
						$new_subscription->save();
					}
					// REMOVED: GMT timestamp update
				}
			}
			// ============ END FIX #3 ============
		}

		wp_redirect(home_url('/my-account/subscriptions/'));
		exit;
	}	
	
	public function customize_order_item_title($product_name, $item) {
		// Get the order ID from the order item
		$order_id = $item->get_order_id();
		$product_id = $item->get_product_id();
		$product = wc_get_product($product_id); // Get the product object
		$product_url = get_permalink($product_id); // Get the product URL
	
		// Retrieve custom product title from order item meta (if available)
		$custom_product_title = get_post_meta($order_id, '_custom_product_title', true);
		
		// If a custom title exists, use it; otherwise, use the default product name
		if (!empty($custom_product_title)) {
			$product_name = $custom_product_title;
		}
	
		// Initialize a variable to hold the variation details
		$variation_text = '';
	
		// Check if the product has a variation and append the variation details to the name
		if ($item->get_variation_id() && $custom_product_title) {
			$variation_id = $item->get_variation_id();
			$variation = wc_get_product($variation_id); // Get variation details
	
			if ($variation) {
				$variation_attributes = $variation->get_attributes(); // Get variation attributes (like color, size, etc.)
				
				// Format the variation details
				if (!empty($variation_attributes)) {
					$attributes_text = [];
					foreach ($variation_attributes as $attribute_name => $attribute_value) {
						// Add variation details in a human-readable format (e.g., "Years: 4")
						$attributes_text[] = $attribute_value;
					}
					
					// Combine the attributes into a string (e.g., "4 Years")
					$variation_text = implode(', ', $attributes_text);
				}
			}
		}
	
		// Add the quantity (x 1) to the product name
	
		// Combine the product name, variation details, and quantity
		if ($variation_text) {
			$product_name .= ' – ' . $variation_text;
		}
	
		if($custom_product_title) {
			$product_name = '<a href="' . esc_url($product_url) . '">' . esc_html($product_name) . '</a>';
		}
	
		// Return the final product title (custom title + variation + quantity + link)
		return $product_name;
	}
		
	
	public function save_custom_product_title_in_order_meta($order_id, $data) {

		if (isset($_POST['intellicut_phone_number'])) {
			update_post_meta($order_id, '_intellicut_phone_number', sanitize_text_field($_POST['intellicut_phone_number']));
		}

		if (isset($_POST['company_name'])) {
			update_post_meta($order_id, '_company_name', sanitize_text_field($_POST['company_name']));
		}

		if (isset($_POST['product_id'])) {
			update_post_meta($order_id, '_product_id', sanitize_text_field($_POST['product_id']));
			update_post_meta($order_id, 'intellicut_serial_number', sanitize_text_field($_POST['product_id']));
		}

		if (isset($_POST['machine_type'])) {
			update_post_meta($order_id, '_machine_type', sanitize_text_field($_POST['machine_type']));
		}

		// Check if this is a resubscribe
		$is_resubscribe = false;
		$old_subscription_id = null;
		foreach (WC()->cart->get_cart() as $cart_item) {
			if (!empty($cart_item['subscription_resubscribe']['subscription_id'])) {
				$is_resubscribe = true;
				$old_subscription_id = intval($cart_item['subscription_resubscribe']['subscription_id']);
				break;
			}
		}

		// Also check POST data for resubscribe (WooCommerce standard)
		if (!$is_resubscribe && isset($_POST['_resubscribe_subscription_id'])) {
			$is_resubscribe = true;
			$old_subscription_id = intval($_POST['_resubscribe_subscription_id']);
		}

		// Also check if WooCommerce already set the order meta (for resubscribe orders)
		if (!$is_resubscribe) {
			$existing_order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
			if ($existing_order_id) {
				$resubscribe_meta = get_post_meta($existing_order_id, '_resubscribe_subscription_id', true);
				if ($resubscribe_meta) {
					$is_resubscribe = true;
					$old_subscription_id = intval($resubscribe_meta);
				}
			}
		}

		// Every source above is shopper-controlled; only honour the customer's own subscription.
		if ($is_resubscribe && !lytrod_user_owns_subscription($old_subscription_id)) {
			$is_resubscribe = false;
			$old_subscription_id = null;
		}

		// Get variation_id from cart items first (works for both resubscribe and switch)
		$variation_id = 0;
		foreach (WC()->cart->get_cart() as $cart_item) {
			if (isset($cart_item['variation_id']) && $cart_item['variation_id'] > 0) {
				$variation_id = $cart_item['variation_id'];
				break;
			}
		}

		// Fallback to POST data if not found in cart
		if (isset($_POST['hidden_product_ids'])) {
			$variation_id = sanitize_text_field($_POST['hidden_product_ids']);
		}

		if ($variation_id > 0) {
			// Get the variation object using the variation ID
			$variation = wc_get_product($variation_id);

			if ($variation && $variation->is_type('variation')) {
				// Get the variation name (attributes)
				$variation_name = $variation->get_name();

				// Get variation attributes
				$variation_attributes = $variation->get_attributes();
				$attributes_text = [];

				// Collect variation attributes
				foreach ($variation_attributes as $attribute_name => $attribute_value) {
					$attributes_text[] = $attribute_value;
				}

				// Calculate years to add
				$years_to_add = 0;
				if (in_array('1 Year', $attributes_text) || in_array('Intellicut Plus 1 year', $attributes_text) || in_array('Intellicut AeroCut nano Edition 1 Year', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus Year', $attributes_text) || in_array('VRCut Plus 1 year', $attributes_text) || in_array('Bizcard Plus 1 year', $attributes_text)) {
					$years_to_add = 1;
				} elseif (in_array('2 Years', $attributes_text) || in_array('Intellicut Plus 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 2 Years', $attributes_text) || in_array('VRCut Plus 2 years', $attributes_text) || in_array('Bizcard Plus 2 Years', $attributes_text)) {
					$years_to_add = 2;
				} elseif (in_array('3 Years', $attributes_text) || in_array('Intellicut Plus 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 3 Years', $attributes_text) || in_array('VRCut Plus 3 years', $attributes_text) || in_array('Bizcard Plus 3 years', $attributes_text)) {
					$years_to_add = 3;
				} elseif (in_array('4 Years', $attributes_text) || in_array('Intellicut Plus 4 years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 4 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 4 years', $attributes_text) || in_array('VRCut Plus 4 years', $attributes_text) || in_array('Bizcard Plus 4 years', $attributes_text)) {
					$years_to_add = 4;
				} elseif (in_array('Sign up', $attributes_text) || in_array('Sign Up', $attributes_text) || in_array('Registration', $attributes_text)) {
					update_post_meta($order_id, 'product_id', $variation_id);
				}

				if ($years_to_add > 0) {
					// Calculate the expiration date
					if (class_exists('WC_Subscriptions')) {

						// RESUBSCRIBE LOGIC - Calculate from old subscription's expiration
						if ($is_resubscribe && $old_subscription_id) {
							$old_subscription = wcs_get_subscription($old_subscription_id);

							if ($old_subscription) {
								// Get the old subscription's expiration date
								$old_expiration = get_post_meta($old_subscription_id, 'calculated_end_date', true);

								if (empty($old_expiration)) {
									$old_expiration = get_post_meta($old_subscription_id, '_custom_expiration_date', true);
								}

								if (empty($old_expiration)) {
									$old_expiration = get_post_meta($old_subscription_id, '_schedule_trial_end', true);
								}

								if (empty($old_expiration)) {
									$old_expiration = $old_subscription->get_date('next_payment');
								}

								if ($old_expiration) {
									// **FIX** - Strip time component to avoid rounding issues
									$old_expiration_date_only = date('Y-m-d', strtotime($old_expiration));

									// Create DateTime from date only
									$expiration_date = new DateTime($old_expiration_date_only);
									$now = new DateTime();
									if ($expiration_date < $now) {
										$expiration_date = $now;
									}

									// Add the purchased years
									$expiration_date->modify("+{$years_to_add} years");

									// Set end date to 1 day AFTER expiration
									$end_date = clone $expiration_date;
									$end_date->modify('+1 day');

									// Store for use in subscription creation
									update_post_meta($order_id, 'expiration_date', $expiration_date->format('Y-m-d H:i:s'));
									update_post_meta($order_id, 'calculated_end_date', $end_date->format('Y-m-d H:i:s'));
									update_post_meta($order_id, 'years_added', $years_to_add);
									update_post_meta($order_id, '_resubscribe_subscription_id', $old_subscription_id);
									update_post_meta($order_id, 'old_subscription_id', $old_subscription_id);
								}
							}
						} else {
							// SWITCH LOGIC - Calculate from existing subscription's next payment
							foreach (WC()->cart->get_cart() as $cart_item) {

								if (!empty($cart_item['subscription_switch']['subscription_id'])) {
									update_post_meta($order_id, 'variation_id_test', $cart_item['subscription_switch']['subscription_id']);
									update_post_meta($order_id, 'year_to_add', $years_to_add);

									$subscription = wcs_get_subscription($cart_item['subscription_switch']['subscription_id']);
									if ($subscription) {
										$next_payment_date = get_post_meta($subscription->get_id(), '_schedule_next_payment', true);

										update_post_meta($order_id, 'paydate', $next_payment_date);
										update_post_meta($order_id, 'years_to_add', $years_to_add);
										update_post_meta($order_id, 'nextpay', $next_payment_date);
										update_post_meta($order_id, 'subid', $subscription->get_id());

										if ($next_payment_date) {
											$expiration_date = new DateTime($next_payment_date);
											$expiration_date->modify("+{$years_to_add} years");

											update_post_meta($order_id, 'variation_date', $expiration_date);

											// Set end date after next payment
											$end_date = clone $expiration_date;
											$end_date->modify('+1 day');

											$expiration_date_formatted = $expiration_date->format('Y-m-d H:i:s');
											$end_date_formatted = $end_date->format('Y-m-d H:i:s');

											// ============ CORRECTED: SWITCH DATE HANDLING ============
											// Fix: Set next_payment 1 second after trial_end
											$trial_end_formatted = $expiration_date_formatted;
											$next_payment_formatted = date('Y-m-d H:i:s', strtotime($expiration_date_formatted . ' +5 minutes'));

											$subscription->update_dates(array(
												'trial_end' => '',
												'next_payment' => $next_payment_formatted,
												'end' => ''
											));
											$subscription->save();

											// ONLY update custom meta
											update_post_meta($order_id, 'calculated_end_date', $expiration_date_formatted);
											update_post_meta($subscription->get_id(), 'calculated_end_date', $expiration_date_formatted);
											update_post_meta($subscription->get_id(), 'expiration_date', $expiration_date_formatted);
											update_post_meta($subscription->get_id(), '_custom_expiration_date', $expiration_date_formatted);
											

											
										}
									}
								}
							}
						}
					}
				}

				// Save variation name to order meta
				update_post_meta($order_id, 'variation_name', $variation_name);
			}
		}

		// Save the custom product title
		if (isset($_POST['custom_product_title'])) {
			$custom_product_title = sanitize_text_field($_POST['custom_product_title']);
			update_post_meta($order_id, '_custom_product_title', $custom_product_title);
		} else {
			update_post_meta($order_id, '_custom_product_title', get_the_title($variation_id ?? 0));
		}

		if (isset($_POST['groupcount'])) {
			$groupcount = sanitize_text_field($_POST['groupcount']);
		}

		// Collect custom labels and group count from cart items
		$all_filtered_labels = [];
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$product_id = $cart_item['product_id'];
			$product = wc_get_product($product_id);
			$account_group_labels = get_post_meta($product->get_id(), '_custom_variation_groups', true) ?: [];
			if (isset($cart_item['groupcount'])) {
				$groupcount = $cart_item['groupcount'];
			}

			if ($groupcount != '') {
				foreach ($account_group_labels as $key => $val) {
					if ($groupcount == $key + 1) {
						$all_filtered_labels[] = $val['variations_account'];
						update_post_meta($order_id, '_group_count_subscription', $groupcount);
					}
				}
			}
		}

		// Save the custom labels to the order meta
		if (!empty($all_filtered_labels)) {
			update_post_meta($order_id, '_custom_labels_subscription', $all_filtered_labels);
		}
	}
	
	public function add_custom_hidden_field_in_checkout() {
		// Check if the cart contains any items with a custom product title
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			
			if (isset($cart_item['groupcount']) && !empty($cart_item['groupcount'])) {
				echo '<input type="hidden" name="groupcount" value="' . esc_attr($cart_item['groupcount']) . '">';
			} else {
				if(isset($_GET['switch_variation_group']) && $_GET['switch_variation_group']!='') {
					$countvalue = $_REQUEST['switch_variation_group'];
				} else {
					$countvalue = $_REQUEST['groupcount'];
				}
				echo '<input type="hidden" name="groupcount" value="' . esc_attr($countvalue) . '">';
			}
			if (isset($cart_item['custom_product_title']) && !empty($cart_item['custom_product_title'])) {
				// Set the custom product title
				$custom_product_title = sanitize_text_field($cart_item['custom_product_title']);
				// Output the hidden field in the checkout form
				echo '<input type="hidden" name="custom_product_title" value="' . esc_attr($custom_product_title) . '" />';
				break; // Exit loop once the hidden field is added
			} else {
				echo '<input type="hidden" name="custom_product_title" value="' . esc_attr($_REQUEST['product_title']) . '" />';
			}
		}

		$product_ids = [];
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_ids[] = $cart_item['variation_id'];
        }
        echo '<input type="hidden" name="hidden_product_ids" id="hidden_product_ids" value="' . esc_attr(implode(',', $product_ids)) . '" />';	
	}

	public function modify_product_title_based_on_url() {
		// Check if we are on a product page
		if (is_product()) {
			// Check if the 'product_title' query parameter exists in the URL
			if (isset($_GET['product_title']) && !empty($_GET['product_title'])) {
				// Sanitize the product title from the URL query
				$new_product_title = sanitize_text_field($_GET['product_title']);
				
				// Output the new product title in place of the default title
				echo '<h1 class="product_title entry-title">' . esc_html($new_product_title) . '</h1>';
				
				// Prevent the default title from being printed
				remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
			}
		}
	}

	public function add_hidden_product_title_field() {
		// Only add the field on product pages
		if (is_product()) {
			// Check if the URL contains the 'product_title' query parameter
			if (isset($_GET['switch_variation_group']) && !empty($_GET['switch_variation_group'])) {
				
				$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
				$psettings = get_post_meta($product_id ,'_custom_variation_groups', true);	
				
				if(!empty($psettings)){
					$new_product_title = $psettings[$_GET['switch_variation_group']-1]['group_label'];
				}
				
			}
			else if (isset($_GET['product_title']) && !empty($_GET['product_title'])) {
				// Sanitize the title from the URL
				$new_product_title = sanitize_text_field($_GET['product_title']);
			} else {
				// Fallback to the default product title
				$new_product_title = get_the_title();
			}
			
			if (isset($_GET['switch_variation_group']) && !empty($_GET['switch_variation_group'])) {
				$groupcount = sanitize_text_field($_GET['switch_variation_group']);
			} else {
				if (isset($_GET['groupcount']) && !empty($_GET['groupcount'])) {
					$groupcount = sanitize_text_field($_GET['groupcount']);
				}
			}
	
			// Output the hidden input field in the product form
			echo '<input type="hidden" name="custom_product_title" value="' . esc_attr($new_product_title) . '">';
			echo '<input type="hidden" name="groupcount" value="' . esc_attr($groupcount) . '">';
		}
	}

	public function set_custom_product_title_in_cart( $cart_item_data, $product_id, $variation_id ) {

		if (isset($_POST['custom_product_title'])) {
			$cart_item_data['custom_product_title'] = sanitize_text_field($_POST['custom_product_title']);
		} else {
			$customtitle = get_query_var('product_title');
			if ( $customtitle ) {
				$cart_item_data['custom_product_title'] = sanitize_text_field( $customtitle );
			}
		}

		if (isset($_POST['groupcount'])) {
			$cart_item_data['groupcount'] = sanitize_text_field($_POST['groupcount']);
		} else {
			// Fallback to query var from URL
			$groupcount = get_query_var('groupcount');
			if ( $groupcount ) {
				$cart_item_data['groupcount'] = sanitize_text_field( $groupcount );
			}
		}
	
		// Capture the selected variation details
		if ($variation_id) {
			// Get variation attributes
			$variation = wc_get_product($variation_id);
			$cart_item_data['variation_data'] = $variation->get_variation_attributes();
			
			if ($variation && $variation->is_type('variation')) {
				$variation_attributes = $variation->get_attributes();
				$attributes_text = [];
				// Collect variation attributes
				foreach ($variation_attributes as $attribute_name => $attribute_value) {
					$attributes_text[] = $attribute_value;
				}
				// Calculate years to add
				$years_to_add = 0;
				if (in_array('Intellicut Plus 1 year', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus Year', $attributes_text) || in_array('VRCut Plus 1 year', $attributes_text)) {
					$years_to_add = 1;
				} elseif (in_array('Intellicut Plus 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 2 Years', $attributes_text) || in_array('VRCut Plus 2 years', $attributes_text)) {
					$years_to_add = 2;
				} elseif (in_array('Intellicut Plus 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 3 Years', $attributes_text) || in_array('VRCut Plus 3 years', $attributes_text)) {
					$years_to_add = 3;
				} elseif (in_array('Intellicut Plus 4 years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 4 years', $attributes_text) || in_array('VRCut Plus 4 years', $attributes_text)) {
					$years_to_add = 4;
				} else if(in_array('Upgrade to Plus', $attributes_text)) {
					$subscription_signup_fee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
					$cart_item_data['prorated_fee_desc'] = $subscription_signup_fee;
				}
				
				if($years_to_add > 0) {
		
					$switch_subscription_id = isset($_GET['switch-subscription']) ? intval($_GET['switch-subscription']) : 0;
    				if($switch_subscription_id) {
						
						$subscription = wcs_get_subscription($switch_subscription_id);
						
						$subscription_signup_fee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
						$trial_start_date = get_post_meta($subscription->get_id(), '_schedule_start', true);
						$billing_period = get_post_meta($subscription->get_id(), '_billing_period', true);
						$billing_interval = get_post_meta($subscription->get_id(), '_billing_interval', true);
						$next_payment_date = $subscription->get_date('next_payment');
						$now = current_time('timestamp');

						$start_timestamp = strtotime($trial_start_date);
						if ($billing_period == 'year') {
							$trial_end_date = strtotime("+$billing_interval year", $start_timestamp);
						} elseif ($billing_period == 'month') {
							$trial_end_date = strtotime("+$billing_interval month", $start_timestamp);
						} else {
							$trial_end_date = $subscription->get_date('trial_end');
						}

						$trial_end_date = date('Y-m-d', $trial_end_date);
						$varaition_id = isset($_GET['variation_id']) ? $_GET['variation_id'] : $variationid;
						$subscription_price = get_post_meta($varaition_id, '_subscription_price', true);
						if ($trial_start_date && $trial_end_date && strtotime($trial_end_date) > $now){
							
							$custom_prorated_fee = 	floatval($subscription_signup_fee);
							$custom_prorated_fee = number_format($custom_prorated_fee, 2, '.', '');
							$cart_item_data['custom_plus_prorated_fee'] = $custom_prorated_fee;
						}
					}
				}
			}
		}
	
		return $cart_item_data;
	}
	
	
	public function display_custom_title_in_cart( $item_name, $cart_item, $cart_item_key ) {
		
		if (isset($cart_item['custom_product_title'])) {
			$item_name = $cart_item['custom_product_title'];
		}

		// If the item has variation attributes, display them (without the attribute names)
		if (isset($cart_item['variation_data']) && is_array($cart_item['variation_data']) && isset($cart_item['custom_product_title'])) {
			$variation_values = array_map(function($key, $value) {
				// Only show the attribute value without the attribute name
				return $value;
			}, array_keys($cart_item['variation_data']), $cart_item['variation_data']);
			
			// Append the attribute values to the item name, separated by commas
			$item_name .= ' – ' . implode(', ', $variation_values);
		}

		// Get the product ID and variation ID
		$product_id = $cart_item['product_id'];
		$variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;

		// Generate the product URL for the variation
		$product_url = get_permalink($product_id);
		if ($variation_id) {
			// Add the variation ID as a query param to the URL
			$product_url = add_query_arg('variation_id', $variation_id, $product_url);
		}

		// Make the product title linkable
		$item_name = '<a href="' . esc_url($product_url) . '">' . $item_name . '</a>';
	
		return $item_name;
	}
	

    // Step 2: Validate the hidden field (optional, can be skipped)
    public function validate_hidden_field() {
        if (empty($_POST['hidden_product_ids'])) {
            wc_add_notice(__('Error: Hidden product IDs are missing.'), 'error');
        }
    }

    // Step 3: Save the hidden field value and expiration date to order metadata
    public function save_hidden_field($order_id) {
        if (isset($_POST['hidden_product_ids'])) {
            $hidden_value = sanitize_text_field($_POST['hidden_product_ids']);
            update_post_meta($order_id, 'hidden_product_ids', $hidden_value);

            // If using WooCommerce Subscriptions
            if (class_exists('WC_Subscriptions')) {
                foreach (WC()->cart->get_cart() as $cart_item) {
                    $subscription = wcs_get_subscription($cart_item['subscription_id'] ?? null);
					update_post_meta($order_id, 'subs', $subscription);
                    if ($subscription) {
                        $next_payment_date = $subscription->get_next_payment_date();
                        if ($next_payment_date) {
                            $expiration_date = new DateTime($next_payment_date);
                            update_post_meta($order_id, 'expiration_date', $expiration_date->format('Y-m-d H:i:s'));
                        }
                    }
                }
            } else {
				update_post_meta($order_id, 'expiration_date', '');
			}
        }
    }

    // Step 4: Display the hidden field value in the admin order details
    public function display_hidden_field_admin($order) {
        $hidden_value = get_post_meta($order->get_id(), 'hidden_product_ids', true);
        if ($hidden_value) {
            echo '<p><strong>' . __('Hidden Product IDs') . ':</strong> ' . esc_html($hidden_value) . '</p>';
        }

        $expiration_date = get_post_meta($order->get_id(), 'expiration_date', true);
        if ($expiration_date) {
            echo '<p><strong>' . __('Expiration Date') . ':</strong> ' . esc_html($expiration_date) . '</p>';
        }
    }

    // Step 5: Display the hidden field value on the thank-you page
    public function display_hidden_field_thankyou($order_id) {
        $hidden_value = get_post_meta($order_id, 'hidden_product_ids', true);
        if ($hidden_value) {
            echo '<p><strong>' . __('Product ID') . ':</strong> ' . esc_html($hidden_value) . '</p>';
        }

        $expiration_date = get_post_meta($order_id, 'expiration_date', true);
        if ($expiration_date) {
            echo '<p><strong>' . __('Expiration Date') . ':</strong> ' . esc_html($expiration_date) . '</p>';
        }
    }
	
	public function custom_my_account_scripts() {
		if ( is_account_page() ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					// Change the URL of the Subscriptions link
					$('.woocommerce-MyAccount-navigation-link--subscriptions a').attr('href', '/my-account/subscriptions/');
				});
				
				jQuery( document ).ready( function( $ ) {
					$( 'button[name="delete_payment_method"]' ).on( 'click', function() {
						var paymentMethodId = jQuery(this).parent().find('input[name="payment_method_last4"]').val();
						var nonce = jQuery(this).parent().find('#_wpnonce').val();
				
						if ( confirm( 'Are you sure you want to delete this payment method?' ) ) {
							// Send AJAX request
							jQuery.ajax({
								url:"<?php echo admin_url('admin-ajax.php'); ?>",
								method: 'POST',
								data: {
									action: 'delete_payment_method',
									payment_method_last4: paymentMethodId,
									nonce: nonce,
								},
								success: function( response ) {
									console.log(response);
									if ( response.success ) {
										alert( 'Payment method deleted successfully.' );
										location.reload(); // Reload the page to reflect the changes
									} else {
										alert( 'Error: ' + response.data );
									}
								},
								error: function() {
									alert( 'There was an error processing your request.' );
								}
							});
						}
					});
				});
	
			</script>
			<?php
		}

        if(isset($_GET['add-to-cart-variation']) && $_GET['add-to-cart-variation']!='') {
            ?>
                <style>
                    table.shop_table.woocommerce-checkout-review-order-table {
                        display: none !important;
                    }
                    .elementor-widget:not(:last-child) {
                        padding-bottom:20px;
                    }
                    .woocommerce-form-coupon-toggle {
                        display:none;
                    }
                </style>
            <?php
        }
	}

	
	public function custom_my_subscriptions_template() {
		// Path to your custom template
		include plugin_dir_path(__FILE__) . '../templates/myaccount/my-subscriptions.php';
	}
	

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscriptions_Upgrader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscriptions_Upgrader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/subscriptions-upgrader-public.css?v=' . time() , array(), '1.0.4', 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscriptions_Upgrader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscriptions_Upgrader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/subscriptions-upgrader-public.js', array( 'jquery' ), '1.4.3', false );
		// wp_localize_script( 
		// 	$this->plugin_name, 
		// 	'myPluginData', 
		// 	array( 
		// 		'ajax_url' => admin_url( 'admin-ajax.php' ),
		// 		'site_url' => get_site_url(),
		// 	) 
		// );
	}

}

add_action('init', function() {
    if (isset($_GET['add-to-cart-variation'])) {
        if (isset($_GET['_wcsnonce'])) {
            $product_id = intval($_GET['add-to-cart-variation']);
            $variation_id = isset($_GET['variation_id']) ? intval($_GET['variation_id']) : 0;
            $product = wc_get_product($product_id);
            
            if ($product) {
                // Clear the cart before adding the new product
                WC()->cart->empty_cart();
                
                // Add the new item to the cart
                WC()->cart->add_to_cart($product_id, 1, $variation_id);
                
                // Redirect to checkout if specified
                if (isset($_GET['redirect_to_checkout']) && $_GET['redirect_to_checkout'] === 'true') {
                	wp_redirect(wc_get_checkout_url());
					exit;
                }
            } else {
                error_log('Product does not exist: ' . $product_id);
            }
        } else {
            error_log('Invalid nonce for switching subscription: ' . $_GET['_wcsnonce']);
        }
    }
	
	
	
	//Upgrade_plus 
	if (isset($_GET['add-to-cart-upgrade-plus'])) {
		// Check if the user is logged in
		if (is_user_logged_in()) {
			// Get the current user's ID
			$user_id = get_current_user_id();

			// Get the 'alg_wc_ev_is_activated' user meta
			$is_verified = get_user_meta($user_id, 'alg_wc_ev_is_activated', true);

			// If the user is not verified (i.e., the value is 0), redirect to login page with a message
			if ($is_verified == 0) {
				// Redirect to the login page with a custom message
							
				wp_redirect(wp_login_url() . '?message=not_verified');
				exit;
			}
		} 

		// Process the rest of the logic
		if (isset($_GET['_wcsnonce'])) {
			$product_id = intval($_GET['add-to-cart-upgrade-plus']);
			$variation_id = isset($_GET['variation_id']) ? intval($_GET['variation_id']) : 0;
			$product = wc_get_product($product_id);
			$groupcount = isset($_GET['switch_variation_group']) ? intval($_GET['switch_variation_group']) : '';
			if ($groupcount == '') {
				$groupcount = isset($_GET['groupcount']) ? intval($_GET['groupcount']) : '';
			}

			if ($product) {
				$psettings = get_post_meta($product_id, '_custom_variation_groups', true);    
				if (!empty($psettings)) {
					$new_product_title = $psettings[$_GET['switch_variation_group'] - 1]['group_label'];
				}
				WC()->cart->empty_cart();
				WC()->cart->add_to_cart($product_id, 1, $variation_id, array(), array(
					'custom_product_title' => $new_product_title, 
					'groupcount' => $groupcount,                       
				));
				wp_redirect(wc_get_checkout_url());
				exit;
			} else {
				error_log('Product does not exist: ' . $product_id);
			}
		} else {
			error_log('Invalid nonce for switching subscription: ' . $_GET['_wcsnonce']);
		}
	} else if (isset($_GET['switch_variation_group'])) {
		
		if (is_user_logged_in()) {
			// Get the current user's ID
			$user_id = get_current_user_id();

			// Get the 'alg_wc_ev_is_activated' user meta
			$is_verified = get_user_meta($user_id, 'alg_wc_ev_is_activated', true);

			// If the user is not verified (i.e., the value is 0), redirect to login page with a message
			if ($is_verified == 0) {
				// Redirect to the login page with a custom message
							
				wp_redirect(wp_login_url() . '?message=not_verified');
				exit;
			}
		}
	}

});


function get_last_order_product_or_variation_id($subscription_id) {
    $subscription = wcs_get_subscription($subscription_id);
    
    if ($subscription) {
        $args = array(
            'post_type'   => 'shop_order',
            'posts_per_page' => 1,
            'orderby'     => 'date',
            'order'       => 'desc',
            'post_status' => array('wc-completed', 'wc-processing', 'wc-on-hold'),
            'meta_query'  => array(
                array(
                    'key'   => '_subscription_switch',
                    'value' => $subscription_id,
                    'compare' => '='
                )
            )
        );
        $orders = get_posts($args);

        if (!empty($orders)) {
            $last_order = wc_get_order($orders[0]->ID);
			
            if ($last_order) {
                $items = $last_order->get_items();

                foreach ($items as $item) {
                    $product_id = $item->get_product_id();
                    $variation_id = $item->get_variation_id();

                    if ($variation_id > 0) {
                        return $variation_id;
                    }
                    return $product_id;
                }
            }
        }
    }
    return false;
}



/*
 * REMOVED: custom_hide_unavailable_variations().
 *
 * Hid disallowed <option> elements in the browser with $(option).hide(). That raced
 * WooCommerce's own variations_form script, which re-renders the options and undoes the
 * hiding, and it is ignored outright in Safari. The allowlist is now applied server-side
 * in LAR_Switch_Variations (lytrod-auto-renewal) so the disallowed variations are never
 * rendered in the first place.
 */


add_filter('woocommerce_locate_template', 'custom_woocommerce_email_template', 10, 3);

function custom_woocommerce_email_template($template, $template_name, $template_path) {
    // Check if we're trying to override the admin-new-order.php email template
    if ($template_name === 'emails/customer-processing-order.php') {
        // Define the custom path to the email template in your plugin or theme
        $custom_template_path = plugin_dir_path(__FILE__) . '../templates/woocommerce/emails/customer-processing-order.php';
        
        // Check if the template exists in the custom location
        if (file_exists($custom_template_path)) {
            return $custom_template_path;
        }
    }
	
	if ($template_name === 'emails/admin-new-order.php') {
        // Define the custom path to the email template in your plugin or theme
        $custom_template_path = plugin_dir_path(__FILE__) . '../templates/woocommerce/emails/admin-new-order.php';
        
        // Check if the template exists in the custom location
        if (file_exists($custom_template_path)) {
            return $custom_template_path;
        }
    } 

	if ( 'myaccount/downloads.php' === $template_name ) {
        // Define the path to the custom template in your plugin
        $custom_template = plugin_dir_path( __FILE__ ) . '../templates/myaccount/downloads.php';
        
        // Check if the custom template file exists and use it
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    
    // Return the original template if no custom template is found
    return $template;
}


// Step 1: Add Confirm Password field to WooCommerce checkout page
function add_confirm_password_to_checkout($fields) {
    // Check if the user is registering (i.e., the "create an account" checkbox is ticked)
    if (is_checkout() && !is_user_logged_in()) {
        $fields['account']['account_confirm_password'] = array(
            'type'          => 'password',
            'label'         => __('Confirm Password', 'woocommerce'),
            'placeholder'   => __('Confirm your password', 'woocommerce'),
            'required'      => true,
            'class'         => array('input-text'),
            'priority'      => 25,
        );
    }
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'add_confirm_password_to_checkout');

// Step 2: Validate the Confirm Password field
function validate_confirm_password_checkout() {
    if (isset($_POST['account_password']) && isset($_POST['account_confirm_password'])) {
        // Check if password and confirm password match
        if ($_POST['account_password'] !== $_POST['account_confirm_password']) {
            // Add error notice
            wc_add_notice( __('<strong>Password</strong> do not match. Please confirm your password.', 'woocommerce'), 'error' );
        }
    }

	if ( isset( $_POST['product_id'] ) ) {
        $productid = sanitize_text_field( $_POST['product_id'] );
        
        // Check if the Product ID matches the regex (starts with 199 and no longer than 7 characters)
        if ( ! preg_match( '/^199\d{4}$/', $productid ) || strlen($productid) !== 7 ) {
            // Add a custom error message to WooCommerce notices
			wc_add_notice( __('The Intellicut Product ID is wrong', 'woocommerce'), 'error' );
		}
    }
}
add_action('woocommerce_checkout_process', 'validate_confirm_password_checkout');

// Step 3: Ensure the Confirm Password value is not saved (it's just for validation)
function save_account_password_field($customer_id) {
    if (isset($_POST['account_password'])) {
        // Save the password from the "account_password" field
        wp_set_password($_POST['account_password'], $customer_id);
    }
}
add_action('woocommerce_created_customer', 'save_account_password_field');

function custom_order_received_subject($subject, $order) {
    // Flag to check for the "sign up" variation
    $valid = false;
    
    // Loop through the order items
    foreach ($order->get_items() as $item_id => $item) {
        $variation_id = $item->get_variation_id();
    
        if ($variation_id) {
            // Get product and variation details
            $product = wc_get_product($item->get_product_id());
            $product_name = $product ? $product->get_name() : '';
            $variation = wc_get_product($variation_id);
            $variation_name = $variation ? $variation->get_name() : '';
    
            // Remove the parent product name from the variation name
            $variation_name = str_replace($product_name . ' - ', '', $variation_name);
    
            // Check if variation contains "sign up"
            if (strpos(strtolower($variation_name), 'sign up') !== false) {
                $valid = true;
                break;  // No need to check further if one valid "sign up" variation is found
            }
        }
    }

    // If a "sign up" variation is found, change the subject
    if ($valid) {
        $subject = $product_name . ' Registration';
    }

    return $subject;
}


function customer_processing_order_subject($subject, $order) {
    // Flag to check for the "sign up" variation
    $valid = false;
    
    // Loop through the order items
    foreach ($order->get_items() as $item_id => $item) {
        $variation_id = $item->get_variation_id();
    
        if ($variation_id) {
            // Get product and variation details
            $product = wc_get_product($item->get_product_id());
            $product_name = $product ? $product->get_name() : '';
            $variation = wc_get_product($variation_id);
            $variation_name = $variation ? $variation->get_name() : '';
    
            // Remove the parent product name from the variation name
            $variation_name = str_replace($product_name . ' - ', '', $variation_name);
    
            // Check if variation contains "sign up"
            if (strpos(strtolower($variation_name), 'sign up') !== false) {
                $valid = true;
                break;  // No need to check further if one valid "sign up" variation is found
            }
        }
    }

    // If a "sign up" variation is found, change the subject
    if ($valid) {
        $subject = 'Your Lytrod Software ' . $product_name . ' Subscription Order Received';
    }

    return $subject;
}

// Apply to both admin and customer processing order emails
add_filter('woocommerce_email_subject_customer_processing_order', 'customer_processing_order_subject', 10, 2);
add_filter('woocommerce_email_subject_new_order', 'custom_order_received_subject', 10, 2);

// Override the WooCommerce subscription page template
function load_custom_subscription_template( $template, $template_name, $args ) {
    // Check if it's a subscription template page
    
    if ( 'myaccount/view-subscription.php' === $template_name ) {
        // Specify the path to your custom template
        $custom_template = plugin_dir_path(__FILE__) . '../templates/myaccount/view-subscription.php';
        
        // If the custom template exists, load it
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
	
	if ( 'myaccount/payment-methods.php' === $template_name ) {
        // Specify the path to your custom template
        $custom_template = plugin_dir_path(__FILE__) . '../templates/myaccount/payment-methods.php';
        
        // If the custom template exists, load it
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
	

	if ( 'myaccount/related-orders.php' === $template_name ) {
        // Specify the path to your custom template
        $custom_template = plugin_dir_path(__FILE__) . '../templates/myaccount/related-orders.php';
        
        // If the custom template exists, load it
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

	if ( 'myaccount/orders.php' === $template_name ) {

        // Correct path to your custom template inside plugin
        $custom_template = plugin_dir_path(__FILE__) . '../templates/myaccount/orders.php';
        
        // Normalize path
        $custom_template = realpath($custom_template);

        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    return $template;
}
add_filter( 'woocommerce_locate_template', 'load_custom_subscription_template', 10, 3 );

function add_custom_image_before_variation_form() {
	
	if (isset($_GET['product_title']) && !empty($_GET['product_title']) && is_product() && $_GET['product_title']=='Intellicut Plus') {
		
		$image_url = '/wp-content/uploads/2025/05/Intellicut-Plus.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php
	}
    else if ( is_product() && 'Intellicut' === get_the_title() ) {
        $image_url = '/wp-content/uploads/2025/05/Intellicut.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php
    }
	
	if (isset($_GET['product_title']) && !empty($_GET['product_title']) && is_product() && $_GET['product_title']=='VRCut Plus') {
		
		$image_url = '/wp-content/uploads/2025/05/VRCut-Plus.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php	
	} else if ( is_product() && 'VRCut' === get_the_title() ) {
		
		$image_url = '/wp-content/uploads/2025/05/VRCut.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php
	}
	
	if (isset($_GET['product_title']) && !empty($_GET['product_title']) && is_product() && $_GET['product_title']=='Intellicut AeroCut nano Edition Plus') {
		
		$image_url = '/wp-content/uploads/2025/05/Intellicut-Aerocut-nano-edition-Plus.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php	
	} else if ( is_product() && 'Intellicut: AeroCut nano Edition' === get_the_title() ) {
		
		$image_url = '/wp-content/uploads/2025/05/Intellicut-Aerocut-non-Edition.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php
	} 
	
	
	if (isset($_GET['product_title']) && !empty($_GET['product_title']) && is_product() && $_GET['product_title']=='Intellicut Global Plus') {
		
		$image_url = '/wp-content/uploads/2025/06/Intellicut-global-plus.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php	
	} else if ( is_product() && 'Intellicut Global' === get_the_title() ) {
		
		$image_url = '/wp-content/uploads/2025/06/Intellicut-global.png';
        echo '<div class="custom-image-before-variations">';
        echo '<img src="' . esc_url( $image_url ) . '" alt="Custom Image" />';
        echo '</div>';
		?>
		<style>
			.custom-image-before-variations {
				margin-bottom:10px;
			}
			@media (max-width: 768px) {
				.custom-image-before-variations {
					margin-bottom:10px;
					width: 100%;
					text-align:start;
				}
			}
		</style>
		<?php
	} 
}
add_action( 'woocommerce_before_variations_form', 'add_custom_image_before_variation_form', 5 );

function custom_change_price_from_text($price, $product) {
    if (is_product()) {

        // Case: product is "Intellicut Plus" and has variation IDs in URL
        if (
            isset($_GET['product_title']) && $_GET['product_title'] !== '' &&
            isset($_GET['variation_id']) && !empty($_GET['variation_id']) &&
            $product->is_type('variable')
        ) {
            $variation_ids = explode(',', $_GET['variation_id']);
            $signup_fees = [];

            foreach ($variation_ids as $variation_id) {
                $signup_fee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
                $signup_fee = $signup_fee !== '' ? floatval($signup_fee) : 0;
                $signup_fees[$variation_id] = $signup_fee;
            }

            $min_signup_fee = min($signup_fees);
            $min_signup_fee_variation_id = array_search($min_signup_fee, $signup_fees);

            if ($min_signup_fee > 0 && $min_signup_fee_variation_id !== null) {
                $subscription_period = get_post_meta($min_signup_fee_variation_id, '_subscription_period', true);
                $formatted_fee = wc_price($min_signup_fee, array('decimals' => 0));
                $price = '<span class="from">Starting at:</span> ' . $formatted_fee .
                         '<span class="subscription-details"> / ' . $subscription_period . '</span>';
            } else {
                // Fallback to price
                $min_price = null;
                $min_price_variation_id = null;

                foreach ($variation_ids as $variation_id) {
                    $variation = wc_get_product($variation_id);
                    if ($variation && $variation->is_type('variation')) {
                        $variation_price = $variation->get_price();
                        if ($min_price === null || $variation_price < $min_price) {
                            $min_price = $variation_price;
                            $min_price_variation_id = $variation_id;
                        }
                    }
                }

                if ($min_price !== null && $min_price_variation_id !== null) {
                    $subscription_period = get_post_meta($min_price_variation_id, '_subscription_period', true);
                    $formatted_min_price = wc_price($min_price, array('decimals' => 0));
                    $price = '<span class="from">Starting at:</span> ' . $formatted_min_price .
                             '<span class="subscription-details"> / ' . $subscription_period . '</span>';
                }
            }
        }

        // Case: fallback for other variable products
        else if ($product->is_type('variable')) {
            $variation_ids = $product->get_children();
            $signup_fees = [];

            foreach ($variation_ids as $variation_id) {
                $signup_fee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
                $signup_fee = $signup_fee !== '' ? floatval($signup_fee) : 0;
				if($signup_fee > 0) {
                	$signup_fees[$variation_id] = $signup_fee;
				}
            }

            $min_signup_fee = min($signup_fees);
            $min_signup_fee_variation_id = array_search($min_signup_fee, $signup_fees);

            if ($min_signup_fee > 0 && $min_signup_fee_variation_id !== null) {
                $subscription_period = get_post_meta($min_signup_fee_variation_id, '_subscription_period', true);
                $formatted_fee = wc_price($min_signup_fee, array('decimals' => 0));
                $price = '<span class="from">Starting at:</span> ' . $formatted_fee .
                         '<span class="subscription-details"> / ' . $subscription_period . '</span>';
            } else {
                // Fallback to minimum variation price
                $variation_prices = $product->get_variation_prices(true);
                $min_price = min($variation_prices['price']);
                $min_price_variation_id = array_search($min_price, $variation_prices['price']);
                $subscription_period = get_post_meta($min_price_variation_id, '_subscription_period', true);
                $formatted_min_price = wc_price($min_price, array('decimals' => 0));
                $price = '<span class="from">Starting at:</span> ' . $formatted_min_price .
                         '<span class="subscription-details"> / ' . $subscription_period . '</span>';
            }
        }
    }

    return $price;
}
add_filter('woocommerce_get_price_html', 'custom_change_price_from_text', 10, 2);

add_action('woocommerce_remove_cart_item', function($cart_item_key, $cart) {
    if (!empty($cart->applied_coupons)) {
        foreach ($cart->applied_coupons as $coupon) {
            $cart->remove_coupon($coupon);
        }
    }
}, 10, 2);

function apply_prorated_upgrade_fee_on_plus_variation($cart_item_data, $product_id, $variation_id) {
    if (!$variation_id) return $cart_item_data;

    // Prorate only a genuine Base -> Plus tier change. This used to test the variation's
    // `choose` attribute for the exact string 'Upgrade to Plus', which missed the Plus
    // multi-year variations ("Intellicut Plus 3 Years" and friends) and so prorated them
    // for customers who were already on Plus - i.e. adding years at their current tier,
    // which is full price. Shares LAR_Switch_Pricing's predicate so this function and the
    // switch-engine filters can never disagree about what counts as an upgrade.
    $switch_subscription_id_for_tier = isset($_GET['switch-subscription']) ? intval($_GET['switch-subscription']) : 0;

    if (!class_exists('LAR_Switch_Pricing')
        || !$switch_subscription_id_for_tier
        || !LAR_Switch_Pricing::is_tier_upgrade(wcs_get_subscription($switch_subscription_id_for_tier), $variation_id)) {
        return $cart_item_data;
    }

    $variation_product = wc_get_product($variation_id);
    if (!$variation_product) {
        return $cart_item_data;
    }
	
    $full_upgrade_fee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
	$sub_upgrade_fee = get_post_meta($variation_id, '_subscription_price', true);
    $user_id = get_current_user_id();
    if (!$user_id) {
        $cart_item_data['custom_prorated_fee'] = $full_upgrade_fee;
        return $cart_item_data;
    }
	
    $switch_subscription_id = isset($_GET['switch-subscription']) ? intval($_GET['switch-subscription']) : 0;
    if (!$switch_subscription_id) return $cart_item_data;
	
    $subscription = wcs_get_subscription($switch_subscription_id);
    if (!$subscription || $subscription->get_status() !== 'active') {
        return $cart_item_data;
    }
	
	$items = $subscription->get_items();
	$variationid = '';
	if (!empty($items)) {
		foreach ($items as $item_id => $item) {
			$productid = $item->get_product_id();
			$variationid = $item->get_variation_id();
		}
	}
	
	$signup = false;
	$years_to_add = 0;
	
	if ($variation_id > 0) {
		// Get the variation object using the variation ID
		$variation = wc_get_product($variationid);

		if ($variation && $variation->is_type('variation')) {
			// Get the variation name (attributes)
			$variation_name = $variation->get_name();

			// Get variation attributes
			$variation_attributes = $variation->get_attributes();
			$attributes_text = [];

			// Collect variation attributes
			foreach ($variation_attributes as $attribute_name => $attribute_value) {
				$attributes_text[] = $attribute_value;
			}
			
			$signup = false;
			if (in_array('Sign up', $attributes_text) || in_array('Sign Up', $attributes_text) || in_array('Registration', $attributes_text)) {
				$signup = true;
			}
			// Calculate years to add
			$years_to_add = 0;
			if (in_array('1 Year', $attributes_text) || in_array('Intellicut Plus 1 year', $attributes_text) || in_array('Intellicut AeroCut nano Edition 1 Year', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus Year', $attributes_text) || in_array('VRCut Plus 1 year', $attributes_text) || in_array('Bizcard Plus 1 year', $attributes_text)) {
				$years_to_add = 1;
			} elseif (in_array('2 Years', $attributes_text) || in_array('Intellicut Plus 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 2 Years', $attributes_text) || in_array('VRCut Plus 2 years', $attributes_text) || in_array('Bizcard Plus 2 years', $attributes_text)) {
				$years_to_add = 2;
			} elseif (in_array('3 Years', $attributes_text) || in_array('Intellicut Plus 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 3 Years', $attributes_text) || in_array('VRCut Plus 3 years', $attributes_text) || in_array('Bizcard Plus 3 years', $attributes_text)) {
				$years_to_add = 3;
			} elseif (in_array('4 Years', $attributes_text) || in_array('Intellicut Plus 4 years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 4 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 4 years', $attributes_text) || in_array('VRCut Plus 4 years', $attributes_text) || in_array('Bizcard Plus 4 years', $attributes_text)) {
				$years_to_add = 4;
			}
		}
	}
	
	$now = current_time('timestamp');
    $subscription_signup_fee = get_post_meta($variationid, '_subscription_sign_up_fee', true);
    $trial_start_date = get_post_meta($subscription->get_id(), '_schedule_start', true);
	$billing_period = get_post_meta($subscription->get_id(), '_billing_period', true);
    $billing_interval = get_post_meta($subscription->get_id(), '_billing_interval', true);
	$next_payment_date = $subscription->get_date('next_payment');
	$previous_payment_timestamp = strtotime($subscription->get_date('last_payment'));
	if (!$previous_payment_timestamp) {
    	$previous_payment_timestamp = strtotime($subscription->get_date('start'));
	}

	// Calculate total days in billing period
	$total_billing_days = max(1, round( ( strtotime($next_payment_date) - $previous_payment_timestamp) / (60 * 60 * 24) ));
	$remaining_days = max(0, round( (strtotime($next_payment_date) - $now) / (60 * 60 * 24) ));

	$start_timestamp = strtotime($trial_start_date);
    if ($billing_period == 'year') {
        $trial_end_date = strtotime("+$billing_interval year", $start_timestamp);
    } elseif ($billing_period == 'month') {
        $trial_end_date = strtotime("+$billing_interval month", $start_timestamp);
    } else {
        $trial_end_date = $subscription->get_date('trial_end');
    }

    $trial_end_date = date('Y-m-d', $trial_end_date);
	$varaition_id = isset($_GET['variation_id']) ? $_GET['variation_id'] : $variationid;
	$subscription_price = get_post_meta($varaition_id, '_subscription_price', true);
	
	// Single rule for every product: the customer pays the per-year Plus uplift for
	// the time they will actually have Plus. Shared with LAR_Switch_Pricing so the
	// figure shown in the cart and the figure charged cannot diverge.
	//
	// The three branches this replaces were wrong whenever more than a year remained:
	// the denominator was the whole (possibly multi-year) billing period rather than a
	// year, and the result was capped at a single year's uplift. Which branch ran
	// depended on the CURRENT variation's sign-up fee, so the same operation priced
	// differently per product - BizcardCut quoted $39.45 against a correct $79.20.
	if ( class_exists( 'LAR_Switch_Pricing' ) ) {
		$prorated_fee = LAR_Switch_Pricing::plus_prorated_fee( $subscription, $variation_id );

		if ( null !== $prorated_fee ) {
			$cart_item_data['custom_prorated_fee'] = (float) $prorated_fee;
		}
	}

	// Always include next year's subscription cost
	$cart_item_data['next_year_subscription_cost'] = floatval($subscription_price);

    return $cart_item_data;
}

add_filter('woocommerce_add_cart_item_data', 'apply_prorated_upgrade_fee_on_plus_variation', 10, 3);

add_filter('woocommerce_add_cart_item_data', 'handle_year_variation_resubscribe_pricing', 20, 3);
function handle_year_variation_resubscribe_pricing($cart_item_data, $product_id, $variation_id) {
    if (!$variation_id) return $cart_item_data;
    
    // Skip if already processed by Plus upgrade function
    if (isset($cart_item_data['custom_prorated_fee'])) {
        return $cart_item_data;
    }
    
    // Check if this is a resubscribe (WooCommerce standard meta key)
    $resubscribe_subscription_id = 0;
    
    // Check if resubscribe data exists in cart item data (WooCommerce Subscriptions sets this)
    if (isset($cart_item_data['subscription_resubscribe']['subscription_id'])) {
        $resubscribe_subscription_id = intval($cart_item_data['subscription_resubscribe']['subscription_id']);
    }
    
    // Check POST data as fallback
    if (!$resubscribe_subscription_id && isset($_POST['_resubscribe_subscription_id'])) {
        $resubscribe_subscription_id = intval($_POST['_resubscribe_subscription_id']);
    }
    
    // Check GET data (legacy support)
    if (!$resubscribe_subscription_id && isset($_GET['resubscribe_subscription'])) {
        $resubscribe_subscription_id = intval($_GET['resubscribe_subscription']);
    }
    
    if (!$resubscribe_subscription_id) {
        return $cart_item_data; // Not a resubscribe
    }

    // Price only from the shopper's own subscription.
    if (!lytrod_user_owns_subscription($resubscribe_subscription_id)) {
        return $cart_item_data;
    }
    
    // Get the variation product
    $variation_product = wc_get_product($variation_id);
    if (!$variation_product || !$variation_product->is_type('variation')) {
        return $cart_item_data;
    }
    
    // Get variation attributes
    $variation_attributes = $variation_product->get_attributes();
    $attributes_text = [];
    foreach ($variation_attributes as $attr_name => $attr_value) {
        $attributes_text[] = $attr_value;
    }
    
    // Check if this is a year variation (not signup/registration)
    $is_signup = in_array('Sign up', $attributes_text) || 
                 in_array('Sign Up', $attributes_text) || 
                 in_array('Registration', $attributes_text);
    
    if ($is_signup) {
        return $cart_item_data; // Don't process signup variations here
    }
    
    // Determine years being purchased
    $purchased_years = 0;
    if (in_array('1 Year', $attributes_text) || 
        in_array('Intellicut Plus 1 year', $attributes_text) || 
        in_array('VRCut Plus 1 year', $attributes_text) ||
        in_array('Intellicut AeroCut nano Edition 1 Year', $attributes_text)) {
        $purchased_years = 1;
    } elseif (in_array('2 Years', $attributes_text) || 
              in_array('Intellicut Plus 2 Years', $attributes_text) ||
              in_array('VRCut Plus 2 years', $attributes_text)) {
        $purchased_years = 2;
    } elseif (in_array('3 Years', $attributes_text) || 
              in_array('Intellicut Plus 3 Years', $attributes_text) ||
              in_array('VRCut Plus 3 years', $attributes_text)) {
        $purchased_years = 3;
    } elseif (in_array('4 Years', $attributes_text) || 
              in_array('Intellicut Plus 4 years', $attributes_text) ||
              in_array('VRCut Plus 4 years', $attributes_text)) {
        $purchased_years = 4;
    }
    
    if ($purchased_years == 0) {
        return $cart_item_data; // Not a year variation
    }
    
    // Get pricing from variation
    $recurring_price = get_post_meta($variation_id, '_subscription_price', true);
    $signup_fee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
    
    // For resubscribes: charge the recurring price (not signup fee)
    $price_per_year = floatval($recurring_price);
    $total_charge = $price_per_year;
    
    // Set the pricing
    $cart_item_data['custom_prorated_fee'] = $total_charge;
    $cart_item_data['next_year_subscription_cost'] = $price_per_year;
    $cart_item_data['is_year_resubscribe'] = true;
    $cart_item_data['resubscribe_years'] = $purchased_years;
    $cart_item_data['_resubscribe_subscription_id'] = $resubscribe_subscription_id;
    
    return $cart_item_data;
}

function add_prorated_fee_after_order_creation($order_id) {
    if (isset($_GET['add-to-cart-variation'])) return;
    
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    $updated = false;
    
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        
        // Check for prorated fee
        $prorated_fee = $item->get_meta('custom_prorated_fee', true);
        if ($prorated_fee) {
            $item->set_subtotal(floatval($prorated_fee));
            $item->set_total(floatval($prorated_fee));
            $item->save();
            $updated = true;
        }
        // Check for variation subscription price
        else if ($product && $product->is_type('variation')) {
            $recurring_price = get_post_meta($product->get_id(), '_subscription_price', true);
            $signupfee = get_post_meta($product->get_id(), '_subscription_sign_up_fee', true);
            
            $price = ($signupfee > 0) ? floatval($signupfee) : floatval($recurring_price);
            
            if ($price > 0) {
                // This hook runs on woocommerce_checkout_order_processed: after the order
                // exists, but BEFORE the gateway is handed an amount. Rewriting the total
                // here therefore charges a different figure from the one the customer
                // agreed to in the cart, silently. That is how Add Years came to show a
                // prorated price and charge full - see LAR_Switch_Pricing.
                //
                // The cart is now priced correctly, so this should be a no-op. Keep it as
                // an assertion: if the two ever disagree again, say so loudly instead of
                // papering over it.
                if (wc_format_decimal($item->get_total(), 2) !== wc_format_decimal($price, 2)) {
                    $message = sprintf(
                        'Order #%d item "%s": cart total %s does not match the expected product price %s. Charging the cart total; NOT overwriting.',
                        $order_id,
                        $item->get_name(),
                        wc_format_decimal($item->get_total(), 2),
                        wc_format_decimal($price, 2)
                    );

                    if (function_exists('wc_get_logger')) {
                        wc_get_logger()->error($message, array('source' => 'lytrod-switch-pricing'));
                    }

                    $order->add_order_note($message);
                }
            }
        }
    }
    
    if ($updated) {
        $order->calculate_totals();
        $order->save();
    }
}
add_action('woocommerce_checkout_order_processed', 'add_prorated_fee_after_order_creation', 10, 1);

// Hook into WooCommerce after the order is created
add_action('woocommerce_checkout_order_processed', 'add_prorated_plus_fee_after_order_creation', 10, 1);

function add_prorated_plus_fee_after_order_creation($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $updated = false;

    foreach ($order->get_items() as $item_id => $item) {
        // Check for matching cart item (based on product ID and cart data)
        $cart = WC()->cart;
        if (!$cart) continue;

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] === $item->get_product_id() || $cart_item['variation_id'] === $item->get_product_id()) {

                // Check for custom fee
                if (!empty($cart_item['custom_plus_prorated_fee'])) {
                    $price = floatval($cart_item['custom_plus_prorated_fee']);

                    if ($price > 0) {
                        $item->set_subtotal($price);
                        $item->set_total($price);
                        $item->save();
                        $updated = true;
                    }
                }

                break; // Stop after matching this item
            }
        }
    }

    if ($updated) {
        $order->calculate_totals(); // Ensure totals are updated in case anything changed
		$order->save();
    }
}


/*
 * REMOVED: ensure_subscription_switch_emails().
 *
 * It hand-triggered WC_Email_New_Order and WC_Email_Customer_Processing_Order by
 * calling ->trigger() directly, deliberately bypassing
 * WC_Subscriptions_Email::maybe_remove_woocommerce_email(), which suppresses the core
 * transactional emails on switch orders. Its own comment said to delete it once the
 * customer-facing switch email was enabled - which it now is.
 *
 * The replacement is WooCommerce Subscriptions' own customer_completed_switch_order,
 * rendered through the branded template lytrod-emails already ships at
 * templates/woocommerce-subscriptions/emails/customer-completed-switch-order.php. That
 * template shares lytrod_emails_renewal_receipt() with the renewal invoice, so an
 * "Add Years" purchase and an automatic renewal now produce the same licence receipt.
 *
 * What customers received before: the generic processing-order template's subscription
 * branch fires only woocommerce_email_after_order_table and never
 * woocommerce_email_order_details, so the email was a bare "we've received your order
 * #NNNN" with no order table, no licence details and no expiry date.
 */

add_action('woocommerce_before_checkout_form', 'keep_only_current_item_in_cart', 10);
function keep_only_current_item_in_cart() {
    $current_item_key = WC()->session->get('last_added_item_key');
    if (!$current_item_key) {
        return;
    }
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item_key !== $current_item_key) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }
}

add_action('woocommerce_add_to_cart', 'store_last_added_item_key', 10, 6);
function store_last_added_item_key($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    WC()->session->set('last_added_item_key', $cart_item_key);
}


add_action('woocommerce_before_checkout_form', 'display_prorated_fee_table_before_checkout', 10);
function display_prorated_fee_table_before_checkout() {
    
    if (WC()->cart->is_empty()) {
        return; // If the cart is empty, don't display the table
    }

    // Flag to check if we need to display the table
    $display_table = false;

    // Loop through all cart items and check if either custom_prorated_fee or next_year_subscription_cost exists
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_prorated_fee']) || isset($cart_item['next_year_subscription_cost'])) {
            $display_table = true;
            break; // No need to check further, table is required
        } else {
			if(isset($_GET['add-to-cart-variation'])) {
				return '';
			}
			echo do_shortcode('[woocommerce_cart]');	
			?>
				<style>
					.woocommerce-cart-form__contents .actions {
						display:none;
					}
					.woocommerce-cart-form__contents .product-remove ,  .woocommerce-cart-form__contents .product-thumbnail, .woocommerce-cart-form__contents .product-quantity, .woocommerce-cart-form__contents .product-price {
						display: none;
					}
				</style>
			<?php
		}
    }

    // If no prorated fee or next year subscription cost exists, don't display the table
    if (!$display_table) {
        return;
    }
	
	
    // Initialize output with the table structure
    $output = '<table class="shop_table shop_table_responsive">
                <thead>
                    <tr>
					    <th class="product-name"><strong>' . __('Product Name', 'woocommerce') . '</strong></th>
                        <th class="product-name"><strong>' . __('Description', 'woocommerce') . '</strong></th>
                        <th class="product-total"><strong>' . __('Amount', 'woocommerce') . '</strong></th>
                    </tr>
                </thead>
                <tbody>';

    // Loop through all cart items and display the custom data
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        
		if ( ! empty( $cart_item['subscription_switch'] ) && ! empty( $cart_item['subscription_switch']['subscription_id'] ) ) {
			$subscription_id = intval( $cart_item['subscription_switch']['subscription_id'] );

			// Get subscription object
			$subscription = wcs_get_subscription( $subscription_id );

			if ( $subscription ) {
				// Get next payment date as timestamp
				$next_payment_timestamp = $subscription->get_time( 'next_payment' );

				if ( $next_payment_timestamp ) {
					// Format date
					$next_payment_date = date( 'n/j/Y', $next_payment_timestamp );
					// Now you can use $next_payment_date
					// For example, add it to cart item data or output
				}
			}
		}
		
		$product_name = $cart_item['data']->get_name();
		$prorated_fee_desc = '';
		if (isset($cart_item['prorated_fee_desc'])) {
			$prorated_fee_desc = $cart_item['prorated_fee_desc'];
		}
        if (isset($cart_item['custom_prorated_fee'])) {
            $prorated_fee = $cart_item['custom_prorated_fee'];
            $output .= '<tr class="prorated-fee">	
				<td>' . $product_name . '</td>	
				<td style="font-weight: 600;">' . __('Prorated Fee', 'woocommerce');
				if (!empty($next_payment_date) && strtotime($next_payment_date) > current_time('timestamp')) {
					$output .= '<br><span style="font-weight: 300;">' . sprintf(
						__('%1$s until current expiration date of %2$s', 'woocommerce'),
						wc_price($prorated_fee_desc).'/year',
						date('n/j/Y', strtotime($next_payment_date))
					) . '</span>';
				}

				$output .= '</td>
				<td>' . wc_price($prorated_fee) . '</td>
			</tr>';
        }

        // Check if next year subscription cost exists and display it
        if (isset($cart_item['next_year_subscription_cost'])) {
            $next_year_subscription_cost = $cart_item['next_year_subscription_cost'];
			
			if (!empty($next_payment_date) && strtotime($next_payment_date) > current_time('timestamp')) {
				$output .= '<tr class="next-year-subscription-cost">
								<td></td>
								<td style="font-weight: 600;">' . __('Next Subscription Renewal: ', 'woocommerce') . $next_payment_date . '</td>
								<td>' . wc_price($next_year_subscription_cost) . '</td>
							</tr>';
			} else {
				$output .= '<tr class="next-year-subscription-cost">
							<td></td>
                            <td style="font-weight: 600;">' . __('Next Subscription Renewal', 'woocommerce') . '</td>
                            <td>' . wc_price($next_year_subscription_cost) . '</td>
                        </tr>';	
			}        
        }
    }

    $output .= '</tbody></table>';
    echo $output;
}

add_action('template_redirect', function() {
    $request_uri = $_SERVER['REQUEST_URI'];

    if (strpos($request_uri, 'view-subscription') !== false) {
        if (isset($_GET['user_switched']) && $_GET['user_switched'] === 'true') {
            wp_redirect(home_url('/my-account/subscriptions/'));
            exit;
        }
    }
});


add_action('woocommerce_after_add_to_cart_button', 'custom_message_after_add_to_cart_button');
function custom_message_after_add_to_cart_button() {
	if (!is_product()) {
        return;
    }
	$product_id = get_the_ID();
	$product = wc_get_product($product_id);
	$product_type = $product->get_type();
	
	if ($product_type == 'variable-subscription') {
		echo '<br><div class="customtext" style="width: 75%;margin-top:20px;"><p style="margin:0px;">Pricing shown reflects the number of years currently selected. If auto-renew is enabled, your account will automatically renew at the 1-year rate. You can manually renew for multiple years at any time.</p></div>';
	}
}

/*
 * REMOVED: force_select_first_visible_option().
 *
 * Waited 200ms and then selected "the first option whose display is not none". It was
 * only needed because the hiding above happened client-side; with the list filtered
 * server-side WooCommerce selects correctly on its own. The fixed timer was the second
 * half of the first-login race: on a cold load WooCommerce had not finished rendering
 * the options when it fired, so it selected nothing valid and checkout was unreachable.
 *
 * The price-fade on found_variation went with it; the sign-up fee display is handled by
 * show_sign_up_fee_and_price() on woocommerce_subscriptions_product_price_string.
 */

add_filter( 'woocommerce_subscriptions_product_price_string', 'show_sign_up_fee_and_price', 10, 2 );
add_filter( 'woocommerce_subscriptions_variation_price_html', 'show_sign_up_fee_and_price', 10, 2 );

function show_sign_up_fee_and_price( $price_string, $product ) {
    // Check if we're on a single product page
    if ( is_product() ) {
        $sign_up_fee = $product->get_sign_up_fee();

        if ( $sign_up_fee > 0 ) {
            $price_string .= ' <span class="custom-sign-up-fee">' . wc_price( $sign_up_fee ) . '</span>';
        } else {
            $price_string = '<span class="custom-subscription-price">' . wc_price( $product->get_price()) . '</span>';
        }
    }

    return $price_string;
}



add_filter('woocommerce_subscriptions_product_price_string', 'custom_subscription_price_string_append', 20, 2);

function custom_subscription_price_string_append($price_string, $product) {

    if (!class_exists('WC_Subscriptions_Product')) {
        return $price_string;
    }
    if (!WC_Subscriptions_Product::is_subscription($product)) {
        return $price_string;
    }
	
	$custom_price = '';
	if ( WC()->cart && ! WC()->cart->is_empty() ) {
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            // Check if cart item matches current product (variation or simple)
            if ( $cart_item['product_id'] === $product->get_id() || ( isset($cart_item['variation_id']) && $cart_item['variation_id'] === $product->get_id() ) ) {
                if ( ! empty( $cart_item['custom_plus_prorated_fee'] ) ) {
                    $custom_fee = floatval($cart_item['custom_plus_prorated_fee']);
                    $custom_price = $custom_fee;
                    break; // Stop after first match
                }
            }
        }
    }

    if ($product->is_type('variation') && !is_product()) {
        $variation_id = $product->get_id();
        $choosen_attr = get_post_meta($variation_id, 'attribute_choose', true); // Duration (e.g., 3)
        $sign_up_fee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
        $recurring_price = get_post_meta($variation_id, '_subscription_price', true); // Subscription price per year
        $trial_duration = get_post_meta($variation_id, '_trial_duration', true); // Free trial duration (optional)

        if (!$choosen_attr || !$sign_up_fee || !$recurring_price) {
            return $price_string;
        }
		
		if($custom_price) {
			$sign_up_fee = $custom_price;
		}

        $formatted_signup_fee = wc_price($sign_up_fee);
        $formatted_recurring = wc_price($recurring_price);
	
        // Prepare message based on the available data
        if ($trial_duration) {
            // If there's a trial duration, include it in the message
            $custom_line = sprintf(
                '%s <br>Will revert to %s/year auto-renewal upon end of %s-year free trial period and %s license period',
                $formatted_signup_fee,
                $formatted_recurring,
                $trial_duration,
                $choosen_attr
            );
        } else {
            // If no trial duration, just show the regular message
            $custom_line = sprintf(
                '%s <br>Will revert to %s/year auto-renewal upon end of %s license period',
                $formatted_signup_fee,
                $formatted_recurring,
                $choosen_attr
            );
        }

        $price_string = $custom_line;
    }

    return $price_string;
}


/*
 * REMOVED: add_filter('wcs_calculate_prorated_totals', '__return_false');
 *
 * No such filter exists in WooCommerce Subscriptions. The real entry point is
 * WC_Subscriptions_Switcher::calculate_prorated_totals(), hooked unconditionally to
 * woocommerce_before_calculate_totals at priority 99, so this line never had any effect
 * and switch proration ran unopposed the whole time it was here.
 *
 * Switch proration is now controlled properly in LAR_Switch_Pricing
 * (wp-content/plugins/lytrod-auto-renewal), which pins the wcs_switch_should_prorate_*
 * filters that WCS actually provides.
 */

add_action('wp_footer', 'custom_dynamic_checkout_button_text');
function custom_dynamic_checkout_button_text() {
       if (is_checkout() && !isset($_GET['add-to-cart-variation'])) {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            function updatePlaceOrderButtonText() {
                setTimeout(function () {
                    let ordertotal = document.querySelector('.order-total .woocommerce-Price-amount');
                    let cartDiscount = document.querySelector('.cart-discount');
                    let subscriptionPriceElement = document.querySelector('.subscription-price .woocommerce-Price-amount');
                    let proratedFeeElement = document.querySelector('.prorated-fee td:last-child .woocommerce-Price-amount');
                    let orderButton = document.querySelector('#place_order');

                    if (orderButton) {
                        // Use prorated fee if available, else fallback to subscription price
                        if (proratedFeeElement) {
                            orderButton.textContent = 'Complete Purchase: ' + proratedFeeElement.textContent.trim();
                        } 
                        else if (ordertotal && parseFloat(ordertotal.textContent.replace(/[^0-9.]/g, '')) > 0 && cartDiscount) {
                             orderButton.textContent = 'Complete Purchase: ' + ordertotal.textContent.trim();
                        } 
                        else if (subscriptionPriceElement) {
                            orderButton.textContent = 'Complete Purchase: ' + subscriptionPriceElement.textContent.trim();
                        }
                    }
                }, 100); // 0.1 second delay
            }

            // Initial update
            updatePlaceOrderButtonText();

            // Re-run after checkout updates via AJAX
            jQuery(document.body).on('updated_checkout', function () {
                updatePlaceOrderButtonText();
            });
        });
    </script>
    <?php
    }
}

/**
 * to add a confirm password field on the register form under My Accounts.
 */ 
add_action('woocommerce_register_post', 'validate_password_confirmation', 10, 3);
function validate_password_confirmation($username, $email, $validation_errors) {
    if ($_POST['password'] !== $_POST['password2']) {
        $validation_errors->add('password_mismatch', __('Passwords do not match.', 'woocommerce'));
    }
    return $validation_errors;
}

function woocommerce_register_form_password_repeat() {
	?>
	<p class="form-row form-row-wide">
		<label for="reg_password2"><?php _e( 'Confirm password', 'woocommerce' ); ?> <span class="required">*</span></label>
		<input type="password" class="input-text" name="password2" id="reg_password2" value="<?php if ( ! empty( $_POST['password2'] ) ) echo esc_attr( $_POST['password2'] ); ?>" />
	</p>
	<?php
}
add_action( 'woocommerce_register_form', 'woocommerce_register_form_password_repeat' );

// Disable password strength requirement
add_filter('woocommerce_min_password_strength', '__return_false');
add_filter('woocommerce_min_password_length', function() { return 4; });

/*
 * REMOVED: check_payment_method_before_renewal() and auto_expire_trial_subscriptions().
 *
 * Both were hooked to 'woocommerce_scheduled_subscription_payment' at priority 5
 * with 2 accepted args, but WooCommerce Subscriptions passes only 1. Every renewal
 * therefore died with:
 *
 *   Too few arguments to function check_payment_method_before_renewal(),
 *   1 passed ... and exactly 2 expected
 *
 * WCS registers prepare_renewal at priority 1 and gateway_scheduled_subscription_payment
 * at priority 10. The TypeError fired between them, so the renewal order was created and
 * the subscription went on hold, but the gateway was never called and no card was ever
 * charged. 91 scheduled payment actions failed this way before the hooks were commented out.
 *
 * auto_expire_trial_subscriptions() additionally called
 * remove_all_actions('woocommerce_scheduled_subscription_payment'), which would disable
 * renewals for every other subscription processed later in the same queue batch.
 *
 * Deleted rather than left commented out so they cannot be re-enabled by accident.
 */

// Send email notification when no payment method is available
// function send_no_payment_method_email($subscription) {
//     $customer_email = $subscription->get_billing_email();
//     $customer_name = $subscription->get_billing_first_name() . ' ' . $subscription->get_billing_last_name();
//     $subscription_id = $subscription->get_id();
//     $next_payment_date = $subscription->get_date('next_payment');
    
//     $subject = sprintf(__('Action Required: Update Payment Method for Subscription #%s', 'woocommerce'), $subscription_id);
    
//     $message = sprintf(
//         __('Dear %s,

//             Your subscription #%s is currently on hold because we don\'t have a valid payment method on file.

//             To reactivate your subscription, please:
//             1. Log in to your account: %s
//             2. Go to "My Account" > "Subscriptions"
//             3. Update your payment method

//             Your subscription was scheduled for renewal on: %s

//             If you have any questions, please don\'t hesitate to contact us.

//             Best regards,
//             %s', 'woocommerce'),
//         $customer_name,
//         $subscription_id,
//         wc_get_page_permalink('myaccount'),
//         date('F j, Y', strtotime($next_payment_date)),
//         get_bloginfo('name')
//     );
    
//     // Send email
//     $headers = array('Content-Type: text/html; charset=UTF-8');
//     wp_mail($customer_email, $subject, nl2br($message), $headers);
    
//     // Log the email send
//     error_log("No payment method email sent to {$customer_email} for subscription #{$subscription_id}");
// }

function send_no_payment_method_email($subscription) {

    // GUARD: Do NOT send if customer already has a valid payment method
    $payment_method = $subscription->get_payment_method();
    if (!empty($payment_method)) {
        $customer_id = $subscription->get_customer_id();
        $payment_tokens = WC_Payment_Tokens::get_customer_tokens($customer_id, $payment_method);
        if (!empty($payment_tokens)) {
            return; // Customer has payment info — do not send "no payment method" email
        }
    }

    $customer_email = $subscription->get_billing_email();
    $customer_name = $subscription->get_billing_first_name() . ' ' . $subscription->get_billing_last_name();
    $subscription_id = $subscription->get_id();
    $next_payment_date = $subscription->get_date('next_payment');

    $subject = sprintf(__('Action Required: Update Payment Method for Subscription #%s', 'woocommerce'), $subscription_id);

    $message = sprintf(
        __('Dear %s,

Your subscription #%s is currently on hold because we don\'t have a valid payment method on file.

To reactivate your subscription, please:
1. Log in to your account: %s
2. Go to "My Account" > "Subscriptions"
3. Update your payment method

Your subscription was scheduled for renewal on: %s

If you have any questions, please don\'t hesitate to contact us.

Best regards,
%s', 'woocommerce'),
        $customer_name,
        $subscription_id,
        wc_get_page_permalink('myaccount'),
        date('F j, Y', strtotime($next_payment_date)),
        get_bloginfo('name')
    );

    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($customer_email, $subject, nl2br($message), $headers);

    error_log("No payment method email sent to {$customer_email} for subscription #{$subscription_id}");
}

// Auto-reactivate subscription when payment method is updated
// add_action('woocommerce_subscription_payment_method_updated', 'reactivate_subscription_after_payment_update', 10, 2);
// function reactivate_subscription_after_payment_update($subscription, $payment_method) {
//     // If subscription was on hold due to no payment method, reactivate it
//     if ($subscription->get_status() === 'on-hold') {
//         // Check if it was put on hold due to payment method issue
//         $recent_notes = $subscription->get_order_notes(1);
//         if (!empty($recent_notes)) {
//             $last_note = reset($recent_notes);
//             if (strpos($last_note->comment_content, 'no payment method') !== false) {
//                 $subscription->update_status('active', __('Subscription reactivated - payment method updated.', 'woocommerce'));
//             }
//         }
//     }
// }


add_action('woocommerce_subscription_payment_method_updated', 'reactivate_subscription_after_payment_update', 10, 2);
function reactivate_subscription_after_payment_update($subscription, $payment_method) {

    // If subscription is on-hold, reactivate it when payment method is added
    if ($subscription->get_status() === 'on-hold') {

        // Verify the new payment method is actually valid
        $customer_id = $subscription->get_customer_id();
        $payment_tokens = WC_Payment_Tokens::get_customer_tokens($customer_id, $payment_method);

        if (!empty($payment_tokens)) {
            // Reactivate subscription
            $subscription->update_status(
                'active',
                __('Subscription reactivated - valid payment method added.', 'woocommerce')
            );
            $subscription->save();

            // Send correct reactivation email to customer
            $mailer = WC()->mailer();
            $emails = $mailer->get_emails();

            // Send the "subscription activated" email, NOT the no-payment-method email
            if (!empty($emails['WCS_Email_Customer_Notification'])) {
                $emails['WCS_Email_Customer_Notification']->trigger($subscription->get_id(), $subscription);
            }
        }
    }
}


/*
 * REMOVED: schedule_subscription_expiration_check() and check_and_expire_subscriptions_period().
 *
 * The 'check_and_expire_subscriptions' daily event was still being registered on every
 * request even though its callback had been commented out, leaving an orphaned cron entry
 * scheduled indefinitely. The callback mass-expired every active subscription whose
 * next_payment had passed - i.e. every subscription waiting on a renewal - which is
 * precisely the state a healthy subscription occupies while the queue drains.
 *
 * Expiry is WooCommerce Subscriptions' job (woocommerce_scheduled_subscription_end_of_prepaid_term
 * and the subscription's own end date). Nothing here should be second-guessing it.
 *
 * The already-scheduled event is cleared on upgrade by lytrod-auto-renewal.
 */

/*
 * REMOVED: check_subscription_period_on_payment() (both the commented-out original and the
 * live redeclaration) and prevent_renewal_without_payment_method().
 *
 * check_subscription_period_on_payment() was the second function that broke renewals via a
 * TypeError: hooked at priority 1 with 2 args while WCS passes 1. It also expired any active
 * subscription whose next_payment had passed - which is every subscription that is due - so
 * even with a correct signature it would have cancelled renewals instead of processing them.
 * The commented-out original additionally called
 * remove_all_actions('woocommerce_scheduled_subscription_payment').
 *
 * prevent_renewal_without_payment_method() blocked renewal orders for any subscription whose
 * customer had no token for that gateway. On this store every free-year sign-up is created
 * without a payment method, so it would have suppressed renewal orders for the majority of
 * subscriptions and hidden the underlying problem.
 *
 * Deleted rather than left commented out so they cannot be re-enabled by accident.
 */

add_filter('woocommerce_hidden_order_itemmeta', function($hidden_meta) {
    $hidden_meta[] = 'custom_prorated_fee';
    return $hidden_meta;
});

add_filter( 'wcs_can_user_reactivate_subscription', function( $can_reactivate, $subscription ) {
    if ( $subscription->has_status( 'on-hold' ) ) {
        $can_reactivate = true;
    }
    return $can_reactivate;
}, 10, 2 );


add_filter( 'wcs_view_subscription_actions', 'add_custom_reactivate_button', 10, 2 );
function add_custom_reactivate_button($actions, $subscription) {
    
    // Show reactivate for: on-hold, expired, pending-cancel (NOT cancelled)
    if ($subscription->has_status(array('on-hold', 'expired'))) {
        
        // Remove WooCommerce's default reactivate/resubscribe buttons; this site
        // routes reactivation through its own custom resubscribe flow below, so we
        // present a single button and avoid duplicate "Resubscribe" actions
        // (core adds a native `resubscribe` action for expired subscriptions).
        if (isset($actions['reactivate'])) {
            unset($actions['reactivate']);
        }
        if (isset($actions['resubscribe'])) {
            unset($actions['resubscribe']);
        }

        // Show the custom Resubscribe button for ALL on-hold/expired subscriptions,
        // regardless of tier. (Previously this was gated on the variation having a
        // zero sign-up fee, which silently suppressed the button for Plus variations
        // because their prorated upgrade amount is stored as a non-zero sign-up fee.)
        // Still hide it for subscriptions already superseded by a resubscribe.
        $old_sub_expired = get_post_meta($subscription->get_id(), 'old-subscription-expired', true);

        if (!$old_sub_expired) {
            // PRIMARY PATH: if the subscription has an unpaid renewal order, send the
            // customer to pay THAT order. The renewal order already carries the
            // subscription's own line item (hence the correct tier and price), and
            // paying it reactivates the SAME subscription rather than creating a new
            // one. This sidesteps the resubscribe flow's base-tier / duplicate-subscription
            // bugs entirely (the tier no longer depends on the buggy _group_count_subscription).
            $renewal_order = lytrod_get_payable_renewal_order($subscription);

            if ($renewal_order) {
                $actions['custom_reactivate'] = array(
                    'url'  => $renewal_order->get_checkout_payment_url(),
                    'name' => __('Reactivate', 'woocommerce-subscriptions'),
                );
            } else {
                // FALLBACK (e.g. expired subs with no renewal order): custom resubscribe
                // redirect. The handler selects the correct tier from the subscription's
                // own current variation (see handle_custom_reactivate_subscription_request()).
                $actions['custom_reactivate'] = array(
                    'url' => wp_nonce_url(
                        add_query_arg('custom_reactivate_subscription', $subscription->get_id()),
                        'custom_reactivate_nonce'
                    ),
                    'name' => __('Resubscribe', 'woocommerce-subscriptions'),
                );
            }
        }
    }

    return $actions;
}

/**
 * Return the most recent unpaid renewal order for a subscription, or null.
 *
 * Used to route the reactivate button to WooCommerce Subscriptions' native
 * "pay for renewal" flow: the renewal order mirrors the subscription's current
 * line item (correct tier/price) and, once paid, reactivates the SAME
 * subscription instead of creating a new one.
 */
function lytrod_get_payable_renewal_order($subscription) {
    if (!is_a($subscription, 'WC_Subscription') || !method_exists($subscription, 'get_related_orders')) {
        return null;
    }

    $renewal_ids = $subscription->get_related_orders('ids', 'renewal');
    if (empty($renewal_ids)) {
        return null;
    }

    $candidates = array();
    foreach ($renewal_ids as $oid) {
        $order = wc_get_order($oid);
        if (!$order) {
            continue;
        }
        // Unpaid and has something to charge.
        if ($order->get_total() > 0 && $order->has_status(array('pending', 'failed', 'on-hold'))) {
            $candidates[] = $order;
        }
    }

    if (empty($candidates)) {
        return null;
    }

    // Most recent first.
    usort($candidates, function ($a, $b) {
        $ta = $a->get_date_created() ? $a->get_date_created()->getTimestamp() : 0;
        $tb = $b->get_date_created() ? $b->get_date_created()->getTimestamp() : 0;
        return $tb - $ta;
    });

    return $candidates[0];
}

/**
 * Whether a subscription belongs to the given user (the current user by default).
 *
 * Resubscribe IDs arrive in posted form data, so anything that copies from or writes to "the
 * old subscription" must check this first.
 */
function lytrod_user_owns_subscription($subscription_id, $user_id = null) {
    if (!function_exists('wcs_get_subscription') || !absint($subscription_id)) {
        return false;
    }

    $subscription = wcs_get_subscription(absint($subscription_id));
    $user_id      = null === $user_id ? get_current_user_id() : (int) $user_id;

    return $subscription && $user_id > 0 && (int) $subscription->get_user_id() === $user_id;
}

/**
 * Decide where a "resubscribe" link should really take the customer, based on the
 * subscription's state at the moment they click it.
 *
 * The licence-ended email links to ?resubscribe=ID. Before this, that link always built a
 * fresh checkout — so a customer whose saved card had already renewed them paid twice, and
 * one with an unpaid renewal order got a second subscription instead of paying the first.
 *
 * Returns array('url' => string, 'notice' => string) when the customer should be sent
 * somewhere else, or null when a genuine resubscribe is still the right thing to do.
 */
function lytrod_resubscribe_destination($subscription) {
    if (!is_a($subscription, 'WC_Subscription')) {
        return null;
    }

    $subscriptions_url = wc_get_account_endpoint_url('subscriptions');

    // Already renewed (e.g. by the saved card) — nothing to pay.
    if ($subscription->has_status(array('active', 'pending-cancel'))) {
        $date_type = $subscription->get_time('next_payment') > 0 ? 'next_payment' : 'end';
        $timestamp = $subscription->get_time($date_type, 'site');

        $product = '';
        foreach ($subscription->get_items() as $item) {
            $parent  = wc_get_product($item->get_product_id());
            $product = wp_strip_all_tags($parent ? $parent->get_name() : $item->get_name());
            break;
        }

        $license = $product ? sprintf(__('Your %s license', 'subscriptions-upgrader'), $product) : __('Your license', 'subscriptions-upgrader');

        $notice = $timestamp > 0 && 'next_payment' === $date_type
            ? sprintf(__('%1$s is active and renews on %2$s. No payment is needed.', 'subscriptions-upgrader'), $license, date_i18n(wc_date_format(), $timestamp))
            : sprintf(__('%s is active. No payment is needed.', 'subscriptions-upgrader'), $license);

        return array('url' => $subscriptions_url, 'notice' => $notice);
    }

    // Superseded by an earlier resubscribe.
    if ($subscription->get_meta('replaced_by_subscription_id', true)) {
        return array(
            'url'    => $subscriptions_url,
            'notice' => __('This license has already been renewed.', 'subscriptions-upgrader'),
        );
    }

    // Pay the renewal order WooCommerce Subscriptions already raised: it reactivates the SAME
    // subscription, so the order carries exactly one subscription. WCS only lets a renewal
    // order be paid while its subscription is on-hold or pending (class-wcs-cart-renewal.php),
    // so an expired one still needs a real resubscribe.
    if ($subscription->has_status(array('on-hold', 'pending'))) {
        $renewal_order = lytrod_get_payable_renewal_order($subscription);
        if ($renewal_order) {
            return array('url' => $renewal_order->get_checkout_payment_url(), 'notice' => '');
        }
    }

    return null;
}

/**
 * Show only the subscription an order actually pays for.
 *
 * WooCommerce Subscriptions lists related subscriptions on order emails and on My Account >
 * View order using order_type 'any', which for a resubscribe order includes the old,
 * superseded subscription as well as the new one — so customers saw two licences on one
 * order. These mirror WCS's own callbacks exactly, minus the 'resubscribe' relation.
 */
add_action('init', function () {
    if (!class_exists('WC_Subscriptions_Order') || !class_exists('WC_Subscriptions_Plugin')) {
        return;
    }

    if (remove_action('woocommerce_email_after_order_table', 'WC_Subscriptions_Order::add_sub_info_email', 15)) {
        add_action('woocommerce_email_after_order_table', 'lytrod_add_sub_info_email', 15, 3);
    }

    if (remove_action('woocommerce_order_details_after_order_table', 'WC_Subscriptions_Order::add_subscriptions_to_view_order_templates', 10)) {
        add_action('woocommerce_order_details_after_order_table', 'lytrod_add_subscriptions_to_view_order_templates', 10, 1);
    }
});

/**
 * Subscriptions an order pays for: parent, renewal and switch, but not the one it resubscribed from.
 */
function lytrod_get_order_display_subscriptions($order) {
    return wcs_get_subscriptions_for_order($order, array('order_type' => array('parent', 'renewal', 'switch')));
}

/**
 * Mirrors WC_Subscriptions_Order::add_sub_info_email().
 */
function lytrod_add_sub_info_email($order, $is_admin_email, $plaintext = false) {
    $subscriptions = lytrod_get_order_display_subscriptions($order);

    if (empty($subscriptions)) {
        return;
    }

    wc_get_template(
        $plaintext ? 'emails/plain/subscription-info.php' : 'emails/subscription-info.php',
        array(
            'order'                => $order,
            'subscriptions'        => $subscriptions,
            'is_admin_email'       => $is_admin_email,
            'plain_text'           => $plaintext,
            'skip_my_account_link' => false,
        ),
        '',
        WC_Subscriptions_Plugin::instance()->get_plugin_directory('templates/')
    );
}

/**
 * Mirrors WC_Subscriptions_Order::add_subscriptions_to_view_order_templates().
 */
function lytrod_add_subscriptions_to_view_order_templates($order_id) {
    $subscriptions = lytrod_get_order_display_subscriptions($order_id);

    if (empty($subscriptions)) {
        return;
    }

    wc_get_template(
        'myaccount/related-subscriptions.php',
        array(
            'order_id'      => $order_id,
            'subscriptions' => $subscriptions,
        ),
        '',
        WC_Subscriptions_Plugin::instance()->get_plugin_directory('templates/')
    );
}

/**
 * Resolve the 1-based tier group (matching _group_count_subscription semantics) for a
 * given variation, based on the variation's own tier rather than any stored groupcount.
 *
 * A variation is treated as Plus when its "choose" attribute contains "Plus" (this
 * covers both the "... Plus N year" renewal variations and the one-time "Upgrade to
 * Plus" variation). The matching group is the one whose group_label tier matches.
 *
 * Returns the 1-based group index, or null if it cannot be determined.
 */
function lytrod_resolve_groupcount_for_variation($product_settings, $variation_id) {
    if (empty($product_settings) || !is_array($product_settings) || !$variation_id) {
        return null;
    }

    $variation = wc_get_product($variation_id);
    if (!$variation) {
        return null;
    }

    $choose        = (string) $variation->get_attribute('choose');
    $variation_plus = (stripos($choose, 'Plus') !== false);

    foreach ($product_settings as $idx => $group) {
        $label      = isset($group['group_label']) ? (string) $group['group_label'] : '';
        $group_plus = (stripos($label, 'Plus') !== false);
        if ($group_plus === $variation_plus) {
            return $idx + 1; // 1-based, matches _group_count_subscription
        }
    }

    return null;
}

add_action('woocommerce_checkout_process', 'require_payment_method_for_resubscribe');
function require_payment_method_for_resubscribe() {
    if (!is_checkout()) {
        return;
    }
    
    $cart = WC()->cart;
    if (!$cart || $cart->is_empty()) {
        return;
    }
    
    // Check if this is a resubscribe order
    $is_resubscribe = false;
    foreach ($cart->get_cart() as $item) {
        if (!empty($item['subscription_resubscribe']['subscription_id'])) {
            $is_resubscribe = true;
            break;
        }
    }
    
    if (!$is_resubscribe) {
        return;
    }
    
    // Verify payment method is selected
    $chosen_payment_method = WC()->session->get('chosen_payment_method');
    if (empty($chosen_payment_method)) {
        wc_add_notice(__('Please select a payment method to complete the resubscription.', 'woocommerce'), 'error');
    }
}

add_action('woocommerce_before_checkout_form', 'show_payment_methods_on_resubscribe', 5);
function show_payment_methods_on_resubscribe() {
    if (!is_checkout()) {
        return;
    }
    
    $cart = WC()->cart;
    if (!$cart || $cart->is_empty()) {
        return;
    }
    
    // Check if resubscribe
    $is_resubscribe = false;
    foreach ($cart->get_cart() as $item) {
        if (!empty($item['subscription_resubscribe']['subscription_id'])) {
            $is_resubscribe = true;
            break;
        }
    }
    
    if ($is_resubscribe) {
        echo '<style>
            .woocommerce-billing-fields { display: block !important; }
            .woocommerce-shipping-fields { display: none !important; }
            .woocommerce-additional-fields { display: block !important; }
            .payment { display: block !important; }
            .woocommerce-payment-methods { display: block !important; }
            #payment { display: block !important; }
            .payment_methods { display: block !important; }
        </style>';
    }
}

// add_filter( 'wcs_view_subscription_actions', 'add_custom_reactivate_button1', 10, 2 );
// function add_custom_reactivate_button1( $actions, $subscription ) {
//     // Only show for subscriptions that are on hold
//     if ( $subscription->has_status( array( 'on-hold' ) ) ) {

//         // Remove default Reactivate button to avoid duplication
//         if ( isset( $actions['reactivate'] ) ) {
//             unset( $actions['reactivate'] );
//         }

//         // Your existing code...
//         $payment_tokens = WC_Payment_Tokens::get_customer_tokens( $subscription->get_user_id() );
//         $has_payment = ! empty( $payment_tokens );

//         $next_payment = $subscription->get_time( 'next_payment' );
//         $is_expired = ! $next_payment || $next_payment < time();

//         if ( $has_payment && $is_expired ) {

//             $last_order = $subscription->get_last_order( 'any' );

//             if ( $last_order && $last_order->get_id() && $last_order->get_order_key() ) {
//                 $pay_url = wc_get_checkout_url() . 'order-pay/' . $last_order->get_id() . '/?pay_for_order=true&key=' . $last_order->get_order_key() . '&subscription_renewal=true';
//             } else {
//                 $pay_url = wp_nonce_url( add_query_arg( array(
//                     'custom_reactivate_subscription' => $subscription->get_id(),
//                 ) ), 'custom_reactivate_nonce' );
//             }

//             $actions['custom_reactivate'] = array(
//                 'url'  => esc_url( $pay_url ),
//                 'name' => __( 'Reactivate', 'woocommerce-subscriptions' ),
//             );
//         }
//     }

//     return $actions;
// }



add_action( 'template_redirect', 'handle_custom_reactivate_subscription_request' );
function handle_custom_reactivate_subscription_request() {
    // Support both old custom_reactivate and new resubscribe parameters
    $subscription_id = 0;
    $nonce_verified = false;
    
    if ( isset( $_GET['custom_reactivate_subscription'], $_GET['_wpnonce'] ) ) {
        if ( wp_verify_nonce( $_GET['_wpnonce'], 'custom_reactivate_nonce' ) ) {
            $subscription_id = absint( $_GET['custom_reactivate_subscription'] );
            $nonce_verified = true;
        }
    }
    
    if ( !$subscription_id || !$nonce_verified ) {
        return;
    }
    $subscription = wcs_get_subscription( $subscription_id );

    if (!$subscription || get_current_user_id() !== $subscription->get_user_id()) {
        wc_add_notice( 'Invalid subscription.', 'error' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'subscriptions' ) );
        exit;
    }

    // Get product info from subscription items
    $items = $subscription->get_items();
    $prod_id = '';
    $variation_id = '';
    $first_item_id = '';
    
    foreach ($items as $item_id => $item) {
        $prod_id = $item->get_product_id();
        $variation_id = $item->get_variation_id();
        $first_item_id = $item_id;
        break;
    }
    
    if (!$prod_id) {
        wc_add_notice( 'Unable to find product information.', 'error' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'subscriptions' ) );
        exit;
    }
    
    // Get related orders to find last order
    $related_orders = $subscription->get_related_orders();
    $order_id = $subscription->get_parent_id();
    
    if (!empty($related_orders)) {
        usort($related_orders, function($a, $b) {
            $order_a = wc_get_order($a);
            $order_b = wc_get_order($b);
            return $order_b->get_date_created()->getTimestamp() - $order_a->get_date_created()->getTimestamp();
        });
        $order_id = $related_orders[0];
    }
    
    $custom_product_title = get_post_meta($order_id, '_custom_product_title', true);
    $groupcount = get_post_meta($order_id, '_group_count_subscription', true);
    
    // Get product settings to find available variations
    $product_settings = get_post_meta($prod_id, '_custom_variation_groups', true);
    $variation_ids = [];
    $group_label = '';

    // Resolve the tier group from the SUBSCRIPTION'S OWN current variation instead of
    // trusting _group_count_subscription off the most-recent (often renewal) order.
    // Renewal orders can carry a stale base groupcount ("1") even for a Plus sub, which
    // previously downgraded the resubscribe to the base tier and price. The customer's
    // real tier is whatever their current line item is.
    if ($variation_id && !empty($product_settings) && is_array($product_settings)) {
        $resolved_groupcount = lytrod_resolve_groupcount_for_variation($product_settings, $variation_id);
        if ($resolved_groupcount !== null) {
            $groupcount = $resolved_groupcount;
        }
    }

    if ($groupcount !== '' && !empty($product_settings)) {
        if (isset($product_settings[$groupcount - 1])) {
            $selected_group = $product_settings[$groupcount - 1];
            $group_label = $product_settings[$groupcount - 1]['group_label'];
            
            // Get variations from variations_account
            if (!empty($selected_group['variations_account'])) {
                foreach ($selected_group['variations_account'] as $account_variation) {
                    // Look for "Add Years" option specifically
                    if (!empty($account_variation['account_variation_ids']) && 
                        (strpos(strtolower($account_variation['account_label']), 'add years') !== false || 
                         strpos(strtolower($account_variation['account_label']), 'add license years') !== false)) {
                        $variation_ids = $account_variation['account_variation_ids'];
                        break;
                    }
                }
                
                // If no "Add Years" found, get all variation IDs from first option
                if (empty($variation_ids) && !empty($selected_group['variations_account'][0]['account_variation_ids'])) {
                    $variation_ids = $selected_group['variations_account'][0]['account_variation_ids'];
                }
            }
        }
    }
    
    // FALLBACK: If still no variation IDs, use the product's children
    if (empty($variation_ids)) {
        $product = wc_get_product($prod_id);
        if ($product && $product->is_type('variable')) {
            $variation_ids = $product->get_children();
        }
    }
    
    // Build the product page URL with resubscribe parameters
    $product = wc_get_product($prod_id);
    if (!$product) {
        wc_add_notice( 'Product not found.', 'error' );
        wp_safe_redirect( wc_get_account_endpoint_url( 'subscriptions' ) );
        exit;
    }
    
    $permalink = $product->get_permalink();
    
    // Generate custom nonce for resubscribe
    $resubscribe_nonce = wp_create_nonce('resubscribe_sub_' . $subscription_id);
    
    // Lead with the subscription's OWN current variation so the correct tier is the
    // first/selected one downstream, then the rest of the resolved (tier-correct) group.
    if ($variation_id && !in_array($variation_id, $variation_ids)) {
        array_unshift($variation_ids, $variation_id);
    } elseif ($variation_id) {
        $variation_ids = array_merge(array($variation_id), array_values(array_diff($variation_ids, array($variation_id))));
    }

    // Build variation_id string
    $variation_id_string = !empty($variation_ids) ? implode(',', $variation_ids) : '';

    // Remove any existing query parameters from permalink
    $base_url = strtok($permalink, '?');
    
    // Build query parameters - custom for resubscribe flow
    $query_params = array(
        'resubscribe_subscription' => $subscription_id,
        'subscription_item' => $first_item_id,
        '_resubscribe_nonce' => $resubscribe_nonce,
        'product_id' => $prod_id,
        'variation_id' => $variation_id_string,
        'product_title' => $custom_product_title ? $custom_product_title : $group_label,
        'groupcount' => $groupcount,
        'switch_variation_group' => $groupcount
    );
    
    // Build URL
    $redirect_url = add_query_arg($query_params, $base_url);
    
    // Fix the encoded commas
    $redirect_url = str_replace('%2C', ',', $redirect_url);
    
    // Redirect to product page
    wp_safe_redirect($redirect_url);
    exit;
}

add_action('woocommerce_before_add_to_cart_button', 'add_resubscribe_hidden_fields');
function add_resubscribe_hidden_fields() {
    if (!is_product()) {
        return;
    }
    
    // Check if this is a resubscribe request (WooCommerce standard or custom)
    $subscription_id = 0;
    $nonce_verified = false;
    
    // Check for WooCommerce standard resubscribe
    if (isset($_GET['resubscribe_subscription']) && isset($_GET['_resubscribe_nonce'])) {
        $subscription_id = intval($_GET['resubscribe_subscription']);
        if (wp_verify_nonce($_GET['_resubscribe_nonce'], 'resubscribe_sub_' . $subscription_id)) {
            $nonce_verified = true;
        }
    }
    
    // Legacy support for old reactivate parameter
    if (!$subscription_id && isset($_GET['reactivate_subscription']) && isset($_GET['_reactivate_nonce'])) {
        $subscription_id = intval($_GET['reactivate_subscription']);
        if (wp_verify_nonce($_GET['_reactivate_nonce'], 'reactivate_sub_' . $subscription_id)) {
            $nonce_verified = true;
        }
    }
    
    if (!$subscription_id || !$nonce_verified) {
        return;
    }
    
    // Get subscription
    $subscription = wcs_get_subscription($subscription_id);
    if (!$subscription || get_current_user_id() !== $subscription->get_user_id()) {
        return;
    }
    
    // Define all possible product ID meta keys
    $product_meta_keys = array(
        'intellicut_serial_number',
        'intellicut_global_product_id',
        'vrcutimposeinstallation_product_id',
        'intellicutaneinstallation_product_id',
        'installation_product_id',
		'bizcard_product_id'
    );
    
    $product_id_value = '';
    $product_id_key = '';
    
    // Get from subscription first
    foreach ($product_meta_keys as $meta_key) {
        $value = get_post_meta($subscription_id, $meta_key, true);
        if (!empty($value)) {
            $product_id_value = $value;
            $product_id_key = $meta_key;
            break;
        }
    }
    
    // If not found in subscription, check parent order
    if (empty($product_id_value)) {
        $parent_order_id = $subscription->get_parent_id();
        foreach ($product_meta_keys as $meta_key) {
            $value = get_post_meta($parent_order_id, $meta_key, true);
            if (!empty($value)) {
                $product_id_value = $value;
                $product_id_key = $meta_key;
                break;
            }
        }
    }
    
    // If still not found, check related orders
    if (empty($product_id_value)) {
        $related_orders = $subscription->get_related_orders();
        foreach ($related_orders as $order_id) {
            foreach ($product_meta_keys as $meta_key) {
                $value = get_post_meta($order_id, $meta_key, true);
                if (!empty($value)) {
                    $product_id_value = $value;
                    $product_id_key = $meta_key;
                    break 2;
                }
            }
        }
    }
    
    // Use WooCommerce standard meta key for resubscribe
    echo '<input type="hidden" name="_resubscribe_subscription_id" value="' . esc_attr($subscription_id) . '">';
    
    if (!empty($product_id_value) && !empty($product_id_key)) {
        echo '<input type="hidden" name="product_id_from_old" value="' . esc_attr($product_id_value) . '">';
        echo '<input type="hidden" name="product_id_meta_key" value="' . esc_attr($product_id_key) . '">';
    }
}

/**
 * Handles subscription resubscribe during checkout.
 * Copies meta from old subscription, applies custom expiration dates,
 * and marks the old subscription as replaced after the new one is created.
 */
add_action('woocommerce_checkout_subscription_created', 'process_subscription_resubscribe', 10, 2);
function process_subscription_resubscribe($subscription, $order) {

    if (!function_exists('wcs_get_subscription')) {
        return;
    }

    $order_id = $order->get_id();
    $new_subscription_id = $subscription->get_id();

    // Check order meta first (WooCommerce standard)
    $old_subscription_id = $order->get_meta('_resubscribe_subscription_id');
    $is_switch = false;
    
    // Fallback to cart item data using correct structure
    if (!$old_subscription_id) {
        $cart = WC()->cart;
        if ($cart && !$cart->is_empty()) {
            foreach ($cart->get_cart() as $item) {
                if (!empty($item['subscription_resubscribe']['subscription_id'])) {
                    $old_subscription_id = intval($item['subscription_resubscribe']['subscription_id']);
                    break;
                }
                // Also check for switch subscriptions
                if (!empty($item['subscription_switch']['subscription_id'])) {
                    $old_subscription_id = intval($item['subscription_switch']['subscription_id']);
                    $is_switch = true;
                    break;
                }
            }
        }
    }
    
    // Check order meta for switch
    if (!$old_subscription_id) {
        $switch_subscription_id = $order->get_meta('_subscription_switch');
        if ($switch_subscription_id) {
            $old_subscription_id = intval($switch_subscription_id);
            $is_switch = true;
        }
    }
    
    // Legacy support for old reactivate key
    if (!$old_subscription_id) {
        $old_subscription_id = $order->get_meta('reactivate_old_subscription_id');
    }

    if (!$old_subscription_id) {
        return;
    }
    
    $old_subscription_id = intval($old_subscription_id);

    $old_subscription = wcs_get_subscription($old_subscription_id);
    if (!$old_subscription) {
        return;
    }

    // The old ID can come from posted form data, so never copy from or mark a subscription
    // that belongs to a different customer.
    if ((int) $old_subscription->get_customer_id() !== (int) $order->get_customer_id()) {
        return;
    }

    // Step 1: Get new subscription's product ID from order items
    $new_product_id = null;
    $new_variation_id = null;
    foreach ($order->get_items() as $item) {
        $new_product_id = $item->get_product_id();
        $new_variation_id = $item->get_variation_id();
        if ($new_variation_id) {
            break; // Prefer variation ID if available
        }
    }

    // Step 2: Copy product meta from old subscription to new subscription
    $meta_keys = [
        'intellicut_serial_number',
        'intellicut_global_product_id',
        'vrcutimposeinstallation_product_id',
        'intellicutaneinstallation_product_id',
        'installation_product_id',
		'bizcard_product_id'
    ];

    foreach ($meta_keys as $key) {
        $value = get_post_meta($old_subscription_id, $key, true);
        if (!empty($value)) {
            update_post_meta($new_subscription_id, $key, $value);
            update_post_meta($order_id, $key, $value);
        }
    }

    // Step 3: Apply custom expiration date with correct calculation
    $expiration_date = get_post_meta($order_id, 'expiration_date', true);
    $calculated_end_date = get_post_meta($order_id, 'calculated_end_date', true);
    $years_added = get_post_meta($order_id, 'years_added', true);
    
    // For switches, also check variation_date meta
    if (empty($expiration_date) && $is_switch) {
        $variation_date = get_post_meta($order_id, 'variation_date', true);
        if ($variation_date && is_object($variation_date) && method_exists($variation_date, 'format')) {
            $expiration_date = $variation_date->format('Y-m-d H:i:s');
        } elseif (!empty($variation_date)) {
            $expiration_date = $variation_date;
        }
    }

    if (!empty($expiration_date)) {
        $next_payment = date('Y-m-d H:i:s', strtotime($expiration_date));
    
        if (empty($calculated_end_date)) {
            $calculated_end_date = date('Y-m-d H:i:s', strtotime($next_payment . ' +1 day'));
        }

        // ============ CORRECTED: RESUBSCRIBE DATE HANDLING ============
        // Fix: Set next_payment 1 second after trial_end
        $trial_end_formatted = $next_payment;
        $next_payment_formatted = date('Y-m-d H:i:s', strtotime($next_payment . ' +5 minutes'));

        $subscription->update_dates(array(
            'trial_end' => '',
            'next_payment' => $next_payment_formatted,
            'end' => '',
        ));

        // ONLY update custom meta fields
        update_post_meta($new_subscription_id, 'expiration_date', $next_payment);
        update_post_meta($new_subscription_id, 'calculated_end_date', $calculated_end_date);
        update_post_meta($new_subscription_id, '_custom_expiration_date', $next_payment);
        
        // REMOVED: _schedule_trial_end direct update
        // REMOVED: GMT timestamp calculation
        // These are handled by update_dates()
        // ============ END CORRECTION ============
        
        if (!empty($years_added)) {
            update_post_meta($new_subscription_id, 'years_added', $years_added);
        }
    } else {
        // Fallback: Try to get expiration date from old subscription
        $old_custom_expiration = get_post_meta($old_subscription_id, '_custom_expiration_date', true);
        $old_trial_end = get_post_meta($old_subscription_id, '_schedule_trial_end', true);
        
        if (!empty($old_custom_expiration)) {
            update_post_meta($new_subscription_id, '_custom_expiration_date', $old_custom_expiration);
            
            if (method_exists($subscription, 'update_dates')) {
                $subscription->update_dates(array(
                    'trial_end' => $old_custom_expiration,
                ));
                $subscription->save();
            }
            // REMOVED: GMT timestamp update
            
        } elseif (!empty($old_trial_end)) {
            update_post_meta($new_subscription_id, '_custom_expiration_date', $old_trial_end);
            
            if (method_exists($subscription, 'update_dates')) {
                $subscription->update_dates(array(
                    'trial_end' => $old_trial_end,
                ));
                $subscription->save();
            }
            // REMOVED: GMT timestamp update
        }
    }

    // Step 4: Set new subscription to ACTIVE status immediately
    $subscription->update_status('active', 'Resubscribed subscription - payment captured at checkout.');
    
    // Step 5: Mark old subscription as replaced
    update_post_meta($old_subscription_id, 'old-subscription-expired', 'yes');
    update_post_meta($order_id, '_subscription_resubscribe', $old_subscription_id);
    
    // Add link between old and new subscriptions
    update_post_meta($old_subscription_id, 'replaced_by_subscription_id', $new_subscription_id);
    update_post_meta($new_subscription_id, 'replaces_subscription_id', $old_subscription_id);
}


// Store resubscribe data in cart
add_filter('woocommerce_add_cart_item_data', 'store_resubscribe_data_in_cart', 10, 3);
function store_resubscribe_data_in_cart($cart_item_data, $product_id, $variation_id) {
    // Use WooCommerce standard meta key
    if (isset($_POST['_resubscribe_subscription_id']) && lytrod_user_owns_subscription($_POST['_resubscribe_subscription_id'])) {
        $subscription_id = intval($_POST['_resubscribe_subscription_id']);

        // Set in WooCommerce Subscriptions format
        $cart_item_data['subscription_resubscribe'] = array(
            'subscription_id' => $subscription_id
        );
    }
    
    if (isset($_POST['product_id_from_old'])) {
        $cart_item_data['product_id_from_old'] = sanitize_text_field($_POST['product_id_from_old']);
    }
    
    if (isset($_POST['product_id_meta_key'])) {
        $cart_item_data['product_id_meta_key'] = sanitize_text_field($_POST['product_id_meta_key']);
    }
    
    return $cart_item_data;
}

add_action( 'woocommerce_subscription_status_updated', 'capture_expiration_date_on_hold', 10, 3 );
function capture_expiration_date_on_hold( $subscription, $new_status, $old_status ) {
    if ( $new_status === 'on-hold' ) {
        $subscription_id = $subscription->get_id();

        // Get scheduled trial end date
        $trial_end_timestamp = $subscription->get_time( 'trial_end' );

        if ( $trial_end_timestamp ) {
            // Convert to MySQL date format
            $trial_end_date = date( 'Y-m-d H:i:s', $trial_end_timestamp );

            // Save as custom expiration date
            update_post_meta( $subscription_id, '_custom_expiration_date', $trial_end_date );
        }
    }
}


// Add custom meta box to subscription edit page
add_action( 'add_meta_boxes', 'add_custom_expiration_date_meta_box' );
function add_custom_expiration_date_meta_box() {
    add_meta_box(
        'custom_expiration_date_meta_box',          // Meta box ID
        __('Expiration Date', 'your-textdomain'), // Title
        'render_custom_expiration_date_meta_box',  // Callback function
        'shop_subscription',                        // Post type
        'side',                                    // Context (side, normal, etc)
        'default'                                  // Priority
    );
}

// Render the meta box content
function render_custom_expiration_date_meta_box( $post ) {
    
	// Get expiration date from meta
    $expiration_date = get_post_meta( $post->ID, '_custom_expiration_date', true );

    // Get trial end date from meta (already in datetime format)
    $trial_end_raw = get_post_meta( $post->ID, '_schedule_trial_end', true );

    // Use trial end if expiration date is not set
    $display_expiration_date = $expiration_date ?: $trial_end_raw;

    // Format for input[type="datetime-local"]
    $formatted_expiration = $display_expiration_date ? date( 'Y-m-d', strtotime( $display_expiration_date ) ) : '';
    $formatted_trial_end  = $trial_end_raw ? date( 'Y-m-d', strtotime( $trial_end_raw ) ) : '';

    // Use nonce for verification
    wp_nonce_field( 'save_custom_expiration_date_nonce', 'custom_expiration_date_nonce' );

    ?>
    <p>
        <label for="custom_expiration_date_field"><?php _e('Set Expiration date:', 'woocommerce'); ?></label><br>
        <input 
            type="date" 
            id="custom_expiration_date_field" 
            name="custom_expiration_date_field" 
            value="<?php echo esc_attr( $formatted_expiration ); ?>" 
            style="width:100%" 
        />
    </p>
    <?php
}

// Save the meta box data when subscription is saved
add_action( 'save_post_shop_subscription', 'save_custom_expiration_date_meta_box_data' );
function save_custom_expiration_date_meta_box_data( $post_id ) { 

    // Check if our nonce is set and verify it
    if ( ! isset( $_POST['custom_expiration_date_nonce'] ) || ! wp_verify_nonce( $_POST['custom_expiration_date_nonce'], 'save_custom_expiration_date_nonce' ) ) { 
        return;
    }

    // Check user permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['custom_expiration_date_field'] ) ) {
        $new_date = sanitize_text_field( $_POST['custom_expiration_date_field'] );
        if ( ! empty( $new_date ) ) {
            // Save custom expiration date
            update_post_meta( $post_id, '_custom_expiration_date', $new_date );
            
            // Get the subscription object
            $subscription = wcs_get_subscription( $post_id );
            
            if ( $subscription ) {
                // Convert date to datetime (end of day)
                $expiration_timestamp = strtotime( $new_date . ' 23:59:59' );
                $expiration_datetime = date( 'Y-m-d H:i:s', $expiration_timestamp );
                $now = current_time('timestamp');
                
                // Update subscription schedule dates
                try {
                    // ============ CORRECTED: ADMIN DATE UPDATE ============
                    // Set trial_end and next_payment with 1 second difference
                    $trial_end_formatted = $expiration_datetime;
                    $next_payment_formatted = date('Y-m-d H:i:s', strtotime($expiration_datetime . ' +5 minutes'));

                    // Use update_dates to handle all date fields
                    $subscription->update_dates( array(
                        'trial_end' => '',
                        'next_payment' => $next_payment_formatted,
                        'end' => '' // Clear end date
                    ) );
                    
                    // Handle status based on date
                    if ( $expiration_timestamp > $now ) {
                        // Future date - activate if currently expired/on-hold
                        if ( in_array( $subscription->get_status(), array( 'on-hold', 'expired', 'cancelled' ) ) ) {
                            $subscription->update_status( 'active', 'Subscription reactivated with new expiration date from admin.' );
                        }
                    } else {
                        // Past date - expire if currently active
                        if ( $subscription->get_status() !== 'expired' ) {
                            $subscription->update_status( 'expired', 'Subscription expired based on custom expiration date from admin.' );
                        }
                    }
                    
                    $subscription->save();
                    
                    // REMOVED: All direct meta updates for _schedule_* fields
                    // REMOVED: GMT timestamp calculations
                    // update_dates() handles these internally
                    // ============ END CORRECTION ============
                    
                } catch ( Exception $e ) {
                    error_log( 'Error updating subscription dates: ' . $e->getMessage() );
                }
            }
            
        } else {
            delete_post_meta( $post_id, '_custom_expiration_date' );
        }
    }
}

add_action( 'woocommerce_scheduled_subscription_end_of_prepaid_term', 'set_pending_cancellation_to_expired', 10, 1 );

function set_pending_cancellation_to_expired( $subscription ) {
    // Only act if subscription is Pending Cancellation
    if ( $subscription->has_status( 'pending-cancel' ) ) {
        $subscription->update_status( 'expired', 'Subscription period ended, marking as Expired instead of Cancelled.' );
    }
}

add_action('woocommerce_subscription_status_pending-cancelled', function($subscription) {
    // Check if end date reached
    if (time() >= $subscription->get_time('end')) {
        $subscription->update_status('on-hold', 'Set to on-hold instead of cancelled');
    }
});