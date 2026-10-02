<?php
/**
 * Related Orders table on the View Subscription page
 *
 * @author   Prospress
 * @category WooCommerce Subscriptions/Templates
 * @version  2.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function get_subscription_id_from_url() {
    // Check if we're on a subscription page
    if (!is_wc_endpoint_url('view-subscription')) {
        return false;
    }
    
    // Get the current URL
    $current_url = $_SERVER['REQUEST_URI'];
    
    // Extract subscription ID using regex
    preg_match('/view-subscription\/(\d+)\//', $current_url, $matches);
    
    if (isset($matches[1])) {
        return absint($matches[1]);
    }
    
    // Alternative method: get directly from query var
    global $wp;
    if (isset($wp->query_vars['view-subscription'])) {
        return absint($wp->query_vars['view-subscription']);
    }
    
    return false;
}


?>
<header>
	<h2><?php esc_html_e( 'Order History', 'woocommerce-subscriptions' ); ?></h2>
</header>

<table class="shop_table shop_table_responsive my_account_orders woocommerce-orders-table woocommerce-MyAccount-orders woocommerce-orders-table--orders">

	<thead>
		<tr>
			<th class="order-number woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php esc_html_e( 'Order', 'woocommerce-subscriptions' ); ?></span></th>
			<th class="order-date woocommerce-orders-table__header woocommerce-orders-table__header-order-date woocommerce-orders-table__header-order-date"><span class="nobr"><?php esc_html_e( 'Date', 'woocommerce-subscriptions' ); ?></span></th>
			<th class="order-status woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e( 'Status', 'woocommerce-subscriptions' ); ?></span></th>
			<th class="order-total woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php echo esc_html_x( 'Total', 'table heading', 'woocommerce-subscriptions' ); ?></span></th>
			<th class="order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions">&nbsp;</th>
		</tr>
	</thead>

	<tbody>
		<?php 
        $subscription_id = get_subscription_id_from_url();
        if ($subscription_id) {
            $new_related_order = get_post_meta($subscription_id, '_custom_related_orders', true);
            if (!empty($new_related_order) && is_array($new_related_order)) {
                $valid_orders = [];
                foreach ($new_related_order as $order_id) {
                    if (wc_get_order($order_id)) {
                        $valid_orders[] = $order_id;
                    }
                }
                if (!empty($valid_orders)) {
                    $subscription_orders = array_merge($subscription_orders, $valid_orders);
                }
            }  
            $subscription_orders = array_unique($subscription_orders);        
        }

		foreach ( $subscription_orders as $subscription_order ) :
			$order = wc_get_order( $subscription_order );

			if ( ! $order ) {
				continue;
			}

			$item_count = $order->get_item_count();
			$order_date = $order->get_date_created();

			?><tr class="order woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?>">
				<td class="order-number woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="<?php esc_attr_e( 'Order Number', 'woocommerce-subscriptions' ); ?>">
					<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
						<?php echo sprintf( esc_html_x( '#%s', 'hash before order number', 'woocommerce-subscriptions' ), esc_html( $order->get_order_number() ) ); ?>
					</a>
				</td>
				<td class="order-date woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="<?php esc_attr_e( 'Date', 'woocommerce-subscriptions' ); ?>">
					<time datetime="<?php echo esc_attr( $order_date->date( 'Y-m-d' ) ); ?>" title="<?php echo esc_attr( $order_date->getTimestamp() ); ?>"><?php echo wp_kses_post( $order_date->date_i18n( wc_date_format() ) ); ?></time>
				</td>
				<td class="order-status woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="<?php esc_attr_e( 'Status', 'woocommerce-subscriptions' ); ?>" style="white-space:nowrap;">
					<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
				</td>
				<td class="order-total woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total" data-title="<?php echo esc_attr_x( 'Total', 'Used in data attribute. Escaped', 'woocommerce-subscriptions' ); ?>">
					<?php
					// translators: $1: formatted order total for the order, $2: number of items bought
					echo wp_kses_post( sprintf( _n( '%1$s for %2$d item', '%1$s for %2$d items', $item_count, 'woocommerce-subscriptions' ), $order->get_formatted_order_total(), $item_count ) );
					?>
				</td>
				<td class="order-actions woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">
					<?php $actions = array();

					if ( $order->needs_payment() && wcs_get_objects_property( $order, 'id' ) == $subscription->get_last_order( 'ids', 'any' ) ) {
						$actions['pay'] = array(
							'url'  => $order->get_checkout_payment_url(),
							'name' => esc_html_x( 'Pay', 'pay for a subscription', 'woocommerce-subscriptions' ),
						);
					}

					if ( in_array( $order->get_status(), apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ) ) ) {
						$redirect = wc_get_page_permalink( 'myaccount' );

						if ( wcs_is_view_subscription_page() ) {
							$redirect = $subscription->get_view_order_url();
						}

						$actions['cancel'] = array(
							'url'  => $order->get_cancel_order_url( $redirect ),
							'name' => esc_html_x( 'Cancel', 'an action on a subscription', 'woocommerce-subscriptions' ),
						);
					}

					$actions['view'] = array(
						'url'  => $order->get_view_order_url(),
						'name' => esc_html_x( 'View', 'view a subscription', 'woocommerce-subscriptions' ),
					);

					$actions = apply_filters( 'woocommerce_my_account_my_orders_actions', $actions, $order );

					if ( $actions ) {
						foreach ( $actions as $key => $action ) {
							echo wp_kses_post( '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>' );
						}
					}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php //do_action( 'woocommerce_subscription_details_after_subscription_related_orders_table', $subscription ); ?>
