<?php
/**
 * Custom New Order Email Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$blog_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
$order_id = $order->get_id();
$order_number = $order->get_order_number();
$customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
$order_items = $order->get_items();
?>

<p>Dear Admin,</p>

<p>A new order has been placed with the variation name "Sign up". Here are the details:</p>

<table>
    <tr>
        <th>Order Number test:</th>
        <td><?php echo $order_number; ?></td>
    </tr>
    <tr>
        <th>Customer Name:</th>
        <td><?php echo $customer_name; ?></td>
    </tr>
    <tr>
        <th>Order Items:</th>
        <td>
            <?php foreach ( $order_items as $item_id => $item ) : ?>
                <p><?php echo $item->get_name(); ?> (Quantity: <?php echo $item->get_quantity(); ?>)</p>
            <?php endforeach; ?>
        </td>
    </tr>
</table>

<p>Thank you for your business!</p>

<?php
do_action( 'woocommerce_email_after_order_table', $order, false );
?>
