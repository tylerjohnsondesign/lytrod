<?php
/**
 * Admin notification: new subscription renewal order.
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
        /* translators: 1: customer full name, 2: order total. */
        __( 'A license renewal order has come in from %1$s for %2$s.', 'lytrod-emails' ),
        $order->get_formatted_billing_full_name(),
        wp_strip_all_tags( $order->get_formatted_order_total() )
    )
);

/** This action is documented in woocommerce-subscriptions/templates/emails/admin-new-renewal-order.php */
do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-subscriptions/templates/emails/admin-new-renewal-order.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-subscriptions/templates/emails/admin-new-renewal-order.php */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
