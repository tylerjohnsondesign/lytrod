<?php
/**
 * Admin notification: recurring payment failed and the customer was asked to
 * authenticate.
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
        /* translators: 1: order number, 2: customer full name, 3: human readable retry time, e.g. "in 12 hours". */
        __( 'The automatic recurring payment for order %1$s from %2$s failed. The customer has been emailed to authenticate the payment; if they do not, they will be asked again %3$s.', 'lytrod-emails' ),
        $order->get_order_number(),
        $order->get_formatted_billing_full_name(),
        $email->get_retry_time()
    )
);

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-renewal-authentication-requested.php */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-renewal-authentication-requested.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-renewal-authentication-requested.php */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
