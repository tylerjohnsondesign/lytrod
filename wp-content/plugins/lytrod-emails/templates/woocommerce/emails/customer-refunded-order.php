<?php
/**
 * Customer refunded order — handles both full and partial refunds.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

$amount = $refund ? $refund->get_amount() : $order->get_total_refunded();

if ( $partial_refund ) {
    $lede = sprintf(
        /* translators: 1: customer first name, 2: refunded amount. */
        __( 'Hi %1$s — we have refunded %2$s against your order. It can take a few business days to appear on your statement.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order ),
        wp_strip_all_tags( wc_price( $amount, array( 'currency' => $order->get_currency() ) ) )
    );
} else {
    $lede = sprintf(
        /* translators: %s: customer first name. */
        __( 'Hi %s — your order has been fully refunded. It can take a few business days to appear on your statement.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order )
    );
}

echo lytrod_emails_intro( $order, $email, $lede ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** This action is documented in woocommerce/templates/emails/customer-refunded-order.php */
do_action( 'woocommerce_email_order_details', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-refunded-order.php */
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-refunded-order.php */
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
