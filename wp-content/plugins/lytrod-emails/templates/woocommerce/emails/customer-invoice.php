<?php
/**
 * Customer invoice / order payment request.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

$needs_payment = $order->needs_payment();

if ( $needs_payment ) {
    $lede = sprintf(
        /* translators: 1: customer first name, 2: order total. */
        __( 'Hi %1$s — your order is ready for payment. The balance due is %2$s.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order ),
        wp_strip_all_tags( $order->get_formatted_order_total() )
    );
} else {
    $lede = sprintf(
        /* translators: %s: customer first name. */
        __( 'Hi %s — here are the details of your order for your records.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order )
    );
}

echo lytrod_emails_intro( $order, $email, $lede ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** This action is documented in woocommerce/templates/emails/customer-invoice.php */
do_action( 'woocommerce_email_order_details', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-invoice.php */
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-invoice.php */
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
