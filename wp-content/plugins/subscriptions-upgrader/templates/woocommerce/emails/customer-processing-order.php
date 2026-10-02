<?php
/**
 * Customer processing order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-processing-order.php.
 *
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
*/
?>

<?php 
$valid = false;
$product_name = '';
foreach ($order->get_items() as $item_id => $item) {
    $variation_id = $item->get_variation_id();
	$checkproduct = $item->get_product();

    if ($variation_id) {
        $product = wc_get_product($item->get_product_id());
        $product_name = $product ? $product->get_name() : '';
        $variation = wc_get_product($variation_id);
        $variation_name = $variation ? $variation->get_name() : '';

        // Remove the parent product name from the variation name
        $variation_name = str_replace($product_name . ' - ', '', $variation_name);

        // Check if variation contains "sign up"
        if ($checkproduct && $checkproduct->is_type('subscription_variation')) {
			$valid = true;
		}
    }
}

if($valid) {
    $email_heading =  'Your Lytrod Software ' . $product_name . ' Subscription Order Received';
    do_action( 'woocommerce_email_header', $email_heading, $email );
} else {
    do_action( 'woocommerce_email_header', $email_heading, $email );
}
 
if($valid) {

    ?>
    <p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
    <?php /* translators: %s: Order number */ ?>
    <p><?php printf( esc_html__( 'Just to let you know &mdash; we\'ve received your order #%s, and it is now being processed:', 'woocommerce' ), esc_html( $order->get_order_number() ) ); ?></p>
    <?php
	 
		/*
        * @hooked WC_Emails::order_details() Shows the order details table.
        * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
        * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
        * @since 2.5.0
        */       
	 	do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
	
} else {

    ?>
    <p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
    <?php /* translators: %s: Order number */ ?>
    <p><?php printf( esc_html__( 'Just to let you know &mdash; we\'ve received your order #%s, and it is now being processed:', 'woocommerce' ), esc_html( $order->get_order_number() ) ); ?></p>
    
    <?php

        /*
        * @hooked WC_Emails::order_details() Shows the order details table.
        * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
        * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
        * @since 2.5.0
        */
        do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

        /*
        * @hooked WC_Emails::order_meta() Shows order meta data.
        */
        do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

        /*
        * @hooked WC_Emails::customer_details() Shows customer details
        * @hooked WC_Emails::email_address() Shows email address
        */
        do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

        /**
         * Show user-defined additional content - this is set in each email's settings.
        */
        if ( $additional_content ) {
        	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
        }
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
