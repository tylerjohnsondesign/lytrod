<?php
/**
 * Admin notification: order cancelled.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

echo lytrod_emails_intro( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $order,
    $email,
    sprintf(
        /* translators: 1: order number, 2: customer full name. */
        __( 'Order %1$s from %2$s has been cancelled.', 'lytrod-emails' ),
        $order->get_order_number(),
        trim( $order->get_formatted_billing_full_name() )
    )
);

/** This action is documented in woocommerce/templates/emails/admin-cancelled-order.php */
do_action( 'woocommerce_email_order_details', $order, true, false, $email );

/** This action is documented in woocommerce/templates/emails/admin-cancelled-order.php */
do_action( 'woocommerce_email_order_meta', $order, true, false, $email );

/** This action is documented in woocommerce/templates/emails/admin-cancelled-order.php */
do_action( 'woocommerce_email_customer_details', $order, true, false, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
