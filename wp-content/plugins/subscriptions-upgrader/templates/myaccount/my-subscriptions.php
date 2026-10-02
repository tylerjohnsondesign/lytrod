<?php
/**
 * My Subscriptions section on the My Account page
 *
 * @author   Prospress
 * @category WooCommerce Subscriptions/Templates
 * @version  2.6.4
 */
/**
 * 
 * EXAMPLE
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$user_id = get_current_user_id();
$subscriptions = wcs_get_users_subscriptions( $user_id );

// Load saved payment methods using WooCommerce's function
$saved_methods = WC()->payment_gateways->get_available_payment_gateways();

function get_last_order_id_by_subscription($subscription_id) {
    // Get the subscription object using the subscription ID
    $subscription = wcs_get_subscription($subscription_id);
    
    if ( ! $subscription ) {
        return null; // If no subscription found
    }

    // Get all related orders (initial + renewals)
    $related_orders = $subscription->get_related_orders();

    if ( ! empty( $related_orders ) ) {
        // Sort the orders by the date created (newest first)
        usort($related_orders, function($a, $b) {
            $order_a = wc_get_order($a);
            $order_b = wc_get_order($b);
            return $order_b->get_date_created()->getTimestamp() - $order_a->get_date_created()->getTimestamp();
        });

        // The first item in the sorted array is the most recent order
        $last_order_id = $related_orders[0];
        
        // Return the ID of the last order
        return $last_order_id;
    }

    return null; // No related orders found
}


?>

<!-- SweetAlert2 CDN -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.1/dist/sweetalert2.min.css" rel="stylesheet">

<div class="woocommerce_account_subscriptions">

	<?php if ( ! empty( $subscriptions ) ) : ?>
	<table class="table">

	<thead>
		<tr>
			<th class="subscription-id order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number woocommerce-orders-table__header-subscription-id">
				<span class="nobr"><?php esc_html_e( 'Product', 'woocommerce-subscriptions' ); ?>
			
			</span></th>
<!-- 			<th class="subscription-status order-status woocommerce-orders-table__header woocommerce-orders-table__header-order-status woocommerce-orders-table__header-subscription-status"><span class="nobr"><?php //esc_html_e( 'Status', 'woocommerce-subscriptions' ); ?></span></th> -->
			<th class="subscription-status order-status woocommerce-orders-table__header woocommerce-orders-table__header-order-status woocommerce-orders-table__header-subscription-status"><span class="nobr"><?php esc_html_e( 'Product ID', 'woocommerce-subscriptions' ); ?></span></th>
			<th class="subscription-next-payment order-date woocommerce-orders-table__header woocommerce-orders-table__header-order-date woocommerce-orders-table__header-subscription-next-payment"><span class="nobr"><?php echo esc_html_x( 'Subscription Status', 'table heading', 'woocommerce-subscriptions' ); ?></span></th>
			<th class="subscription-total order-total woocommerce-orders-table__header woocommerce-orders-table__header-order-total woocommerce-orders-table__header-subscription-total"><span class="nobr"><?php echo esc_html_x( 'Payment Details', 'table heading', 'woocommerce-subscriptions' ); ?></span></th>
			<th class="subscription-actions order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__header-subscription-actions" style="display:none;">&nbsp;</th>
			<th class="subscription-actions order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__header-subscription-actions">Action			
			</th>
		</tr>
	</thead>
	<tbody>
	<?php /** @var WC_Subscription $subscription */ ?>
	<?php foreach ( $subscriptions as $subscription_id => $subscription ) : 
		
		$is_expired_subscription = get_post_meta($subscription_id, 'is_expired_subscription', true);
		$old_expired_subscription = get_post_meta($subscription_id, 'old-subscription-expired', true);
		if ( $subscription->get_status() === 'cancelled' || $is_expired_subscription == 'yes' || $old_expired_subscription == 'yes' ) {
        	continue;
    	}
		?>
		<tr id="<?php echo $subscription_id; ?>">
			<td  data-title="<?php esc_attr_e( 'ID', 'woocommerce-subscriptions' ); ?>">
				<?php 
				
				$product_name = '';
				foreach ($subscription->get_items() as $item_id => $item) {
					$variation_id = $item->get_variation_id();
					$prod_id = $item->get_product_id();
					$last_order_id = get_last_order_id_by_subscription($subscription_id);
					if($last_order_id) {
						$order_id = $last_order_id;
					} else {
						$order_id = $item->get_order_id();
					}
					
					$custom_product_title = get_post_meta($order_id, '_custom_product_title', true);
					$product = wc_get_product($prod_id);
					$product_name = $product->get_name(); 

					if($custom_product_title!='') {
						$product_name = $custom_product_title;
					}

					$variation = wc_get_product($variation_id);
					if ($variation && $variation->is_type('variation')) {
						// Get the variation's name
						$variation_name = $variation->get_name();
						$variation_name = str_replace($product_name.' - ', '', $variation_name);
					}
				}
				if($product_name =='') {
					$product_name = esc_html( sprintf( _x( '#%s', 'hash before order number', 'woocommerce-subscriptions' ), $subscription->get_order_number() ) );
				}
				?>
				<?php echo $product_name; ; ?>
				<!-- <a href="<?php //echo esc_url( $subscription->get_view_order_url() ); ?>">
					<?php //echo $product_name; ; ?>
				</a> -->
				
				<?php 
				
				$valid = true;
					foreach ($subscription->get_items() as $item_id => $item) {
						if ($item instanceof WC_Order_Item_Product) {
							$prod_id = $item->get_product_id();
							$product = wc_get_product($prod_id);
							$product_name = $product->get_name(); 
							$item_name_clean = strip_tags($item->get_name());
							$variation_id = $item->get_variation_id();
							if (strpos($item_name_clean, 'Upgrade to Plus') !== false) {
										
								$subscription_price = get_post_meta($variation_id, '_subscription_price', true);		
								if ($subscription_price == 0) {
									
									$signupfee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
									if ($signupfee != '') {
										$currency_symbol = get_woocommerce_currency_symbol();
										
										//echo '<p style="margin-left:-1px;">'.$currency_symbol . number_format($signupfee, 2) . '</p>';
										$valid = false;
									} 	
								}
							}
						}
					}
				?>
				
				
				<?php do_action( 'woocommerce_my_subscriptions_after_subscription_id', $subscription ); ?>
			</td>
<!-- 			<td class="subscription-status order-status woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-status woocommerce-orders-table__cell-order-status" data-title="<?php //esc_attr_e( 'Status', 'woocommerce-subscriptions' ); ?>">
				<?php //echo esc_attr( wcs_get_subscription_status_name( $subscription->get_status() ) ); ?>
			</td> -->
			<td data-title="<?php esc_attr_e( 'Product ID', 'woocommerce-subscriptions' ); ?>">
				<?php 
				
				$order = wc_get_order($subscription_id);
				if ($order->customer_id == get_current_user_id()) {
                    foreach ($order->meta_data as $meta) {
						
						if ($meta->key == 'intellicut_serial_number') {
							echo get_post_meta( $subscription_id, 'intellicut_serial_number', true);
						} else if ($meta->key == 'intellicut_global_product_id') {
							echo get_post_meta( $subscription_id, 'intellicut_global_product_id', true);	
						} else if ($meta->key == 'vrcutimposeinstallation_product_id') {
							echo get_post_meta( $subscription_id, 'vrcutimposeinstallation_product_id', true);		
						} else if ($meta->key == 'intellicutaneinstallation_product_id') {
							echo get_post_meta( $subscription_id, 'intellicutaneinstallation_product_id', true);	
						} else if ($meta->key == 'installation_product_id') {
							echo get_post_meta( $subscription_id, 'installation_product_id', true);
						} else if ($meta->key == 'bizcard_product_id') {
							echo get_post_meta( $subscription_id, 'bizcard_product_id', true);
						}
					}
				}
				?>
			</td>
			<td class="subscription-next-payment order-date woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-next-payment woocommerce-orders-table__cell-order-date" data-title="<?php echo esc_attr_x( 'Next Payment', 'table heading', 'woocommerce-subscriptions' ); ?>">
				<?php
				
				$last_four = '';
				if ( $saved_methods ) {
					foreach ( $saved_methods as $gateway_id => $gateway ) {
						// Check if the user has saved tokens for this gateway
						$tokens = WC_Payment_Tokens::get_customer_tokens( $user_id, $gateway_id );

						if ( ! empty( $tokens ) ) {
							foreach ( $tokens as $token ) {
								if ( is_object( $token ) && method_exists( $token, 'get_last4' ) ) {
									$last_four = $token->get_last4();
								} else {
									$last_four = 'yes';
								}
							}
						}
					}
				}

				$linkreactivate = '';
				// Get both the status name and raw status for accurate comparison
				$subscription_status_raw = $subscription->get_status(); // e.g., 'cancelled', 'active', 'expired'
				$substatus = esc_attr( wcs_get_subscription_status_name( $subscription_status_raw ) ); // e.g., 'Cancelled', 'Active', 'Expired'
				
				// Handle Cancelled status FIRST (before Active check)
				if ($subscription_status_raw == 'cancelled' || $substatus == 'Cancelled') {
					$display_status = '<span style="color: grey;">Cancelled</span>';
					$payment_info = '';
				}
				// Handle Active status
				else if ($subscription_status_raw == 'active' && $substatus == 'Active' && $last_four) {
					 $display_status = '<span style="color: green;">'.$substatus.'</span>';
            		 $payment_info = "***".$last_four;
				} else if ($subscription_status_raw == 'active' && $substatus == 'Active') {
					$display_status = '<span style="color:black;">Active</span>';					
					$payment_info = '';
				} 
				// Handle On Hold status separately
				else if($subscription_status_raw == 'on-hold' || $substatus == 'On hold') {
					if ($last_four) {
						$payment_info = "***".$last_four;
					} else {
						$payment_info = '';
					}
					// Show On Hold status with orange/yellow color to differentiate from Expired
					$display_status = '<span style="color: orange;">On Hold</span>';
				} 
				// Handle Expired status
				else if($subscription_status_raw == 'expired' || $substatus == 'Expired') {
					if ($last_four) {
						$payment_info = "***".$last_four;
					} else {
						$payment_info = '';
					}
					$custom_expiration_date = get_post_meta( $subscription->get_id(), '_custom_expiration_date', true );
					if ($custom_expiration_date) {
						$date = new DateTime($custom_expiration_date);
						$custom_expiration_date = $date->format('F j, Y');
						$custom_expiration_date = '<br>Expiration Date:<br>' . $custom_expiration_date;
					} else {
						$custom_expiration_date = ''; // fallback empty string if no custom date
					}
					$display_status = '<span style="color: red;">Expired</span>'.$custom_expiration_date;
				} 
				// Handle Pending Payment status
				else if($subscription_status_raw == 'pending' || $subscription_status_raw == 'pending-payment' || $substatus == 'Pending' || $substatus == 'Pending Payment') {
					if($last_four) {
						$payment_info = "***".$last_four;
					} else {
						$payment_info = '';
					}
					// Show Pending status with yellow/orange color
					$display_status = '<span style="color: #ff9800;">Pending Payment</span>';
				}
				// Handle Pending Cancellation status
				else if($subscription_status_raw == 'pending-cancel' || $substatus == 'Pending Cancellation') {
					if($last_four) {
						$display_status = '<span style="color: green;">Active</span>';
						$payment_info = "***".$last_four;
					} else {
						$display_status = '<span style="color: green;">Active</span>';
						$payment_info = '';
					}
				} 
				// Default fallback - only show Active if truly active
				else {
					// Only show Active if status is actually active
					if ($subscription_status_raw == 'active') {
						if($last_four) {
							$display_status = '<span style="color: green;">Active</span>';
							$payment_info = "***".$last_four;
						} else {
							$display_status = '<span style="color: black;">Active</span>';
							$payment_info = '';
						}
						$actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );	
						if(!empty($actions)) {
							 foreach ( $actions as $key => $action ) {
								$linkreactivate = '<a href="'.esc_url( $action['url'] ).'" class="button <?php '.sanitize_html_class( $key ). '">'. esc_html( $action['name'] ).'</a>';
							}
						}
					} else {
						// For any other status, show the actual status name
						$display_status = '<span>'.$substatus.'</span>';
						$payment_info = '';
					}
        		}
				
				if($subscription->get_date_to_display( 'next_payment' ) != '-') {
					$next_payment_date = esc_attr( $subscription->get_date_to_display( 'next_payment' ) );	
					
				} else {
					$trial_end_datetime = get_post_meta($subscription_id, '_schedule_trial_end', true);
					if ($trial_end_datetime) {
						$date = new DateTime($trial_end_datetime);
						$next_payment_date = $date->format('F j, Y');
					}
				}
				
					// Display only. This template previously called update_status('expired')
					// here, which expired a live subscription merely because someone
					// rendered the page. A subscription sits in exactly this state
					// (active, next_payment in the past) between its due time and the
					// Action Scheduler queue draining, so that write raced the renewal
					// and permanently cancelled it. Never mutate state from a template.
					$next_payment_timestamp = $subscription->get_time('next_payment');
					if ($substatus == 'Active' && $next_payment_timestamp && $next_payment_timestamp < time()) {
						$next_payment_date = __('Renewal in progress', 'woocommerce');
					}
				
				    echo "<div class='subscription-status'>";
					echo "<p style='margin-bottom:0px;'> $display_status</p>";
		
					// Show next payment date for Active subscriptions
					if($substatus=='Active') {
						echo "<p style='margin-bottom:0px;'>Next Payment Date: <br> $next_payment_date</p>";
					}
					// Show next payment date for On Hold subscriptions
					else if($substatus == 'On hold') {
						if($subscription->get_date_to_display( 'next_payment' ) != '-') {
							$next_payment_date_onhold = esc_attr( $subscription->get_date_to_display( 'next_payment' ) );
							echo "<p class='test' style='margin-bottom:0px;'>Expiration Date: <br> $next_payment_date_onhold</p>";
						} else {
							$trial_end_datetime = get_post_meta($subscription_id, '_schedule_trial_end', true);
							if ($trial_end_datetime) {
								$date = new DateTime($trial_end_datetime);
								$next_payment_date_onhold = $date->format('F j, Y');
								echo "<p style='margin-bottom:0px;'>Expiration Date: <br> $next_payment_date_onhold</p>";
							}
						}
					}
					// Show next payment date for Pending Payment subscriptions
					else if($subscription_status_raw == 'pending' || $subscription_status_raw == 'pending-payment' || $substatus == 'Pending' || $substatus == 'Pending Payment') {
						if($subscription->get_date_to_display( 'next_payment' ) != '-') {
							$next_payment_date_pending = esc_attr( $subscription->get_date_to_display( 'next_payment' ) );
							echo "<p style='margin-bottom:0px;'>Next Payment Date: <br> $next_payment_date_pending</p>";
						} else {
							$trial_end_datetime = get_post_meta($subscription_id, '_schedule_trial_end', true);
							if ($trial_end_datetime) {
								$date = new DateTime($trial_end_datetime);
								$next_payment_date_pending = $date->format('F j, Y');
								echo "<p style='margin-bottom:0px;'>Next Payment Date: <br> $next_payment_date_pending</p>";
							}
						}
					}
					// Show next payment date for Pending Cancellation subscriptions
					else if ($substatus == 'Pending Cancellation') {
						$trial_end_datetime = get_post_meta($subscription_id, '_schedule_end', true);
						if ($trial_end_datetime) {
							$date = new DateTime($trial_end_datetime);
							$next_payment_date = $date->format('F j, Y');
						}
						echo "<p style='margin-bottom:0px;'>Next Payment Date: <br> $next_payment_date</p>";
					}

					echo "</div>";
				?>
		
			</td>
			<td data-title="<?php echo esc_attr_x( 'Next Payment Date', 'Used in data attribute. Escaped', 'woocommerce-subscriptions' ); ?>">
				<?php

				$valid = true;
				foreach ($subscription->get_items() as $item_id => $item) {
					if ($item instanceof WC_Order_Item_Product) {
						$prod_id = $item->get_product_id();
						$product_name = $product->get_name(); 
						$item_name_clean = strip_tags($item->get_name());
						$variation_id = $item->get_variation_id();
						if (strpos($item_name_clean, 'Upgrade to Plus') !== false) {
									
							$subscription_price = get_post_meta($variation_id, '_subscription_price', true);		
							if ($subscription_price == 0) {
								
								$signupfee = get_post_meta($variation_id, '_subscription_sign_up_fee', true);
								if ($signupfee != '') {
									$currency_symbol = get_woocommerce_currency_symbol();
									
									echo '<p style="margin-left:-1px;">'.$currency_symbol . number_format($signupfee, 2) . '</p>';
									$valid = false;
								} 	
							}
						}
					}
				}
				if($valid) {
					echo '<p style="margin:0px;">';
					echo 'Cost : '. wp_kses_post( $subscription->get_formatted_order_total() ); 
					echo '</p>';
				}
				// Handle payment info display based on subscription status
				if($substatus =='Active' && $payment_info) {
					echo '<p style="margin:0px;"><span>Auto Renewal: Active</span></p>';
					if($last_four !== 'yes') {
						echo "<p style='margin:0px;'>Credit Card: $payment_info</p>";
					}
				} 
				// On Hold subscriptions - show payment info if available
				else if($substatus == 'On hold') {
					if($payment_info) {
						echo '<p style="margin:0px;"><span>Auto Renewal: On Hold</span></p>';
						if($last_four !== 'yes') {
							echo "<p style='margin:0px;'>Credit Card: $payment_info</p>";
						}
					} else {
						echo '<p style="margin:0px;"><span>Auto Renewal: On Hold</span></p>';
						echo '<p style="margin:0px;"><span style="color: orange;">Payment method required to reactivate</span></p>';
					}
				} 
				// Expired subscriptions
				else if($substatus == 'Expired' && $payment_info) {
					echo '<p style="margin:0px;"><span>Auto Renewal: Expired</span></p>';
					if($last_four !== 'yes') {
						echo "<p style='margin:0px;'>Credit Card: $payment_info</p>";
					}
				} 
				// Pending Payment
				else if($subscription_status_raw == 'pending' || $subscription_status_raw == 'pending-payment' || $substatus == 'Pending' || $substatus == 'Pending Payment') {
					echo '<p style="margin:0px;"><span style="color: #ff9800;">Auto Renewal: Pending Payment</span></p>';
					if($payment_info && $last_four !== 'yes') {
						echo "<p style='margin:0px;'>Credit Card: $payment_info</p>";
					} else if(!$payment_info) {
						echo '<p style="margin:0px;"><span style="color: #ff9800;">Payment required to activate subscription</span></p>';
					}
				}
				// Pending Cancellation or Cancelled
				else if($substatus=='Pending Cancellation' || $substatus=='Cancelled') {
					echo '<p style="margin:0px;"><span>Auto Renewal: Cancelled</span></p>';
				} 
				// No payment info - show add payment method link
				else {
					echo '<p style="margin:0px;"><span>Auto Renewal: Missing payment info</span></p>';
					echo '<p style="margin:0px;"><a href="/my-account/payment-methods/" style="color: blue;">Add payment method</a></p>';
				}
				?>
			</td>
			<td style="display:none;">
<!-- 			<a href="<?php //echo esc_url( $subscription->get_view_order_url() ) ?>" class="woocommerce-button button view"><?php //echo esc_html_x( 'View', 'view a subscription', 'woocommerce-subscriptions' ); ?></a> -->
			<?php do_action( 'woocommerce_my_subscriptions_actions', $subscription ); ?>
			</td>
			<td class="hh">
				<?php 
				$prod_id = '';
				$subscription_status = $subscription->get_status();
				$switch_subscription_upgrade = wcs_get_subscription($subscription->get_id());
					
				$items_old = $switch_subscription_upgrade->get_items();
				$upvariationid = '';
				if (!empty($items_old)) {
					foreach ($items_old as $item_id => $item) {
						$pproductid = $item->get_product_id();
						$upvariationid = $item->get_variation_id();
					}
				}
    			$subscription_signup_fee = get_post_meta($upvariationid, '_subscription_sign_up_fee', true);
				
				$trial_start_date = get_post_meta($subscription->get_id(), '_schedule_start', true);
				$billing_period = get_post_meta($subscription->get_id(), '_billing_period', true);
				$billing_interval = get_post_meta($subscription->get_id(), '_billing_interval', true);
				$next_payment_date = $subscription->get_date('next_payment');
				$now = current_time('timestamp');

				$start_timestamp = strtotime($trial_start_date);
				if ($billing_period == 'year' || $billing_period == 'day' || $billing_period == 'weak') {
					$trial_end_date = strtotime("+$billing_interval year", $start_timestamp);
				} elseif ($billing_period == 'month') {
					$trial_end_date = strtotime("+$billing_interval month", $start_timestamp);
				} else {
					$trial_end_date = $subscription->get_date('trial_end');
				}

				$trial_end_date = date('Y-m-d', $trial_end_date);
				$subscription_price = get_post_meta($variation_id, '_subscription_price', true);
				$freetrail = false;
				if ($subscription_signup_fee == 0 && $trial_start_date && $trial_end_date && strtotime($trial_end_date) > $now) {
					$freetrail = true;
				}
				
				if ($subscription_status === 'active') {
					$nonce = wp_create_nonce('wcs-switch-' . $subscription->get_id()); 
					foreach ($subscription->get_items() as $item_id => $item) {
						//do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $subscription, false );
							
						$prod_id = $item->get_product_id();
						$product = wc_get_product($prod_id);
						$last_order_id = get_last_order_id_by_subscription($subscription_id);
						if($last_order_id) {
							$order_id = $last_order_id;
						} else {
							$order_id = $item->get_order_id();
						}
						$permalink = $product->get_permalink();
						
						$nonce = wp_create_nonce( 'wcs_switch_request' );
						$subscriptionupgradeurl = '?switch-subscription=' . urlencode($subscription_id) . '&item=' . urlencode($item_id) . '&_wcsnonce=' . urlencode($nonce);
						//$custom_variation_ids = get_post_meta($prod_id, '_custom_variation_ids', true) ?: [];
						$custom_labels = get_post_meta($order_id, '_custom_labels_subscription', true) ?: [];
						$product_settings = get_post_meta($prod_id, '_custom_variation_groups', true);
						$groupcount = get_post_meta($order_id, '_group_count_subscription', true);
						
						$variation_ids = [];
						if ($groupcount !== '') {
							if (isset($product_settings[$groupcount - 1])) {
								$selected_group = $product_settings[$groupcount - 1];
								$grouplabel = $product_settings[$groupcount - 1]['group_label'];
								
								foreach ($selected_group['variations'] as $variation) {
									$variation_ids[] = $variation['variation_id'];
								}
							} 
						} 

						$variationids = implode(',', $variation_ids);
						
						if(!empty($product_settings)) {
							
							foreach($product_settings as $key => $value) {
								
								if($key+ intval(1) == $groupcount){
							
									if(!empty($value['variations_account'])) {
										echo '<ul class="subscription-buttons" style="list-style:none;padding:0px;margin:0px;">';
										$variation_options = '';
										foreach($value['variations_account'] as $key => $val) {
											
											if($val['variation-group-switch']) {
												$switch_group_param = '&switch_variation_group='.$val['variation-group-switch'];
											}
											
											$final_url = $permalink.$subscriptionupgradeurl.'&product_id='.$prod_id.'&variation_id='.implode(',', $val['account_variation_ids']).'&product_title='.$grouplabel.'&groupcount='.$groupcount.$switch_group_param;
											
											if($val['account_label'] == 'Upgrade to Plus') {
    											$final_url .= '&add-to-cart-upgrade-plus='.$prod_id;  // Corrected here
											}
											
											//if ($val['account_label'] == 'Upgrade to Plus' && $freetrail || $val['account_label'] != 'Upgrade to Plus') {
												$variation_options .= '<li>';
												$variation_options .= '<a href="' . $final_url . '">' . $val['account_label'] . '</a><br>';
												$variation_options .= '</li>';
											//}
										}
										echo $variation_options;
										echo '</ul>';
									}
								}
							}
						} 			
					
						$cancel_link = wcs_get_users_change_status_link( $subscription_id, 'cancelled' );
						?>
						<a href="#" style="background:white;" id="cancel-link" data-attr-link="<?php echo esc_url( $cancel_link )  ?>" class="button cancel">Cancel</a>
						<?php	
					}
				}	else {
					if ( ! is_user_logged_in() ) return;
					// Get status again for action section (in case variable scope issue)
					$subscription_status_raw_action = $subscription->get_status();
					$substatus_action = esc_attr( wcs_get_subscription_status_name( $subscription_status_raw_action ) );
					if($substatus_action=='Pending Cancellation' || $substatus_action == 'On hold' || $substatus_action == 'Expired' || $subscription_status_raw_action == 'pending' || $subscription_status_raw_action == 'pending-payment' || $substatus_action == 'Pending' || $substatus_action == 'Pending Payment') {
						$linkreactivate = '';
						$actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );	
						if(!empty($actions)) {
							 foreach ( $actions as $key => $action ) {
								if($key =='cancel') {
									$addclass = 'cancel-link';
									$dataurl = esc_url( $action['url'] );
								} else {
									$addclass = '';
									$dataurl = '';
								}
								$linkreactivate = '<a href="'.esc_url( $action['url'] ).'" id="'.$addclass.'" data-attr-link="'.esc_url($dataurl).'" class="button '.sanitize_html_class( $key ). '">'. esc_html( $action['name'] ).'</a>';
							}
						}
						
						$user_id = get_current_user_id();
    					$payment_tokens = WC_Payment_Tokens::get_customer_tokens( $user_id );
						if ( $substatus_action == 'On hold' ) {
							//if ( ! empty( $payment_tokens ) ) {
								// Payment method exists - show normal actions like Reactivate, Cancel, etc.
								$actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );
								if ( ! empty( $actions ) ) {
									foreach ( $actions as $key => $action ) {
										if ( $key == 'cancel' ) {
											continue;
											$addclass = 'cancel-link';
											$dataurl = esc_url( $action['url'] );
										} else {
											$addclass = '';
											$dataurl = '';
										}
										echo $linkreactivate = '<a href="'.esc_url( $action['url'] ).'" id="'.$addclass.'" data-attr-link="'.esc_url($dataurl).'" class="button ' .sanitize_html_class( $key ). '">'. esc_html( $action['name'] ).'</a>';
									}
								}
							//} else {
								// No payment method on file, show custom message or action
								//echo '<p class="no-payment-method">You have no payment method on file. Please <a href="' . esc_url( wc_get_account_endpoint_url( 'payment-methods' ) ) . '">add a payment method</a> to reactivate your subscription.</p>';
								//echo '<a href="' . esc_url( wc_get_account_endpoint_url( 'payment-methods' ) ) . '" class="button">Add Payment Method</a>';
							//}
						} else if ( $substatus_action == 'Expired' ) {
							// Show Resubscribe button for expired subscriptions
							$actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );
							
							foreach ($subscription->get_items() as $item_id => $item) {
					
								$prod_id = $item->get_product_id();
								$product = wc_get_product($prod_id);
								$last_order_id = get_last_order_id_by_subscription($subscription_id);
								if($last_order_id) {
									$order_id = $last_order_id;
								} else {
									$order_id = $item->get_order_id();
								}
								//$custom_variation_ids = get_post_meta($prod_id, '_custom_variation_ids', true) ?: [];
								$custom_labels = get_post_meta($order_id, '_custom_labels_subscription', true) ?: [];
								$product_settings = get_post_meta($prod_id, '_custom_variation_groups', true);
								$groupcount = get_post_meta($order_id, '_group_count_subscription', true);

								$variation_ids = [];
								if ($groupcount !== '') {
									if (isset($product_settings[$groupcount - 1])) {
										$selected_group = $product_settings[$groupcount - 1];
										$grouplabel = $product_settings[$groupcount - 1]['group_label'];
										foreach ($selected_group['variations'] as $variation) {
											$variation_ids[] = $variation['variation_id'];
										}
									} 
								} 
							}
							
							$variationids = implode(',', $variation_ids);
						
							if(!empty($product_settings)) {

								foreach($product_settings as $key => $value) {

									if($key+ intval(1) == $groupcount){

										if(!empty($value['variations_account'])) {
											
											$variation_options = '';
											foreach($value['variations_account'] as $key => $val) {

												if($val['variation-group-switch']) {
													$switch_group_param = $val['variation-group-switch'];
												}
											}
										}
									}
								}
							}
							
							if ( ! empty( $actions ) ) {
								foreach ( $actions as $key => $action ) {

									// Show Resubscribe / Reactivate button
									if ( $key === 'resubscribe' || $key === 'reactivate' ) {

										$custom_url = add_query_arg(
											array(
												'groupcount'    => absint( $groupcount ),
												'product_title' => rawurlencode( $grouplabel ),
												'switch_variation_group' => $switch_group_param,
											),
											$action['url']
										);

										echo '<a href="' . esc_url( $custom_url ) . '" class="button ' . sanitize_html_class( $key ) . '">'
											. esc_html( $action['name'] ) .
										'</a>';
									}
								}
							}
						} else if ( $subscription_status_raw_action == 'pending' || $subscription_status_raw_action == 'pending-payment' || $substatus_action == 'Pending' || $substatus_action == 'Pending Payment' ) {
							// Show payment/action buttons for pending subscriptions
							$actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );
							if ( ! empty( $actions ) ) {
								foreach ( $actions as $key => $action ) {
									// Show Pay or other relevant actions for pending subscriptions
									if ( $key == 'pay' || $key == 'view' || $key == 'reactivate' ) {
										echo '<a href="'.esc_url( $action['url'] ).'" class="button ' .sanitize_html_class( $key ). '">'. esc_html( $action['name'] ).'</a>';
									}
								}
							}
						} else {
							echo $linkreactivate;	
						}		
					}
 				}
				?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>

	</table>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.1/dist/sweetalert2.min.js"></script>

	<script>
		
		jQuery('body').on('click', '#cancel-link', function(e) {
			e.preventDefault(); // ⛔ Stop the link from auto-following

			const cancelLink = jQuery(this).data('attr-link'); // use .data() for safety
			const fullCancelUrl = window.location.origin + cancelLink;

			Swal.fire({
				title: 'Are you sure you want to cancel the subscription?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes, Cancel',
				cancelButtonText: 'No, Keep Subscription',
				reverseButtons: true
			}).then((result) => {
				if (result.isConfirmed) {
					// ✅ Only redirect if user confirms
					window.location.href = fullCancelUrl;
				}
				// ❌ Do nothing if user clicks "No"
			});
		});

		
	</script>
		<?php if ( 1 < $max_num_pages ) : ?>
			<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'subscriptions', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce-subscriptions' ); ?></a>
			<?php endif; ?>

			<?php if ( intval( $max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'subscriptions', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce-subscriptions' ); ?></a>
			<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<p style="display:none;" class="no_subscriptions woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
			<?php if ( 1 < $current_page ) :
				printf( esc_html__( 'You have reached the end of subscriptions. Go to the %sfirst page%s.', 'woocommerce-subscriptions' ), '<a href="' . esc_url( wc_get_endpoint_url( 'subscriptions', 1 ) ) . '">', '</a>' );
			else :
				esc_html_e( 'You have no active subscriptions.', 'woocommerce-subscriptions' );
				?>
				<!-- <a class="woocommerce-Button button" href="<?php //echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"> -->
					<?php //esc_html_e( 'Browse products', 'woocommerce-subscriptions' ); ?>
				<!-- </a> -->
			<?php
		endif; ?>
		</p>

	<?php endif; ?>

</div>
