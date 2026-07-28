<?php
/**
 * Customer notification: a refund could not be processed.
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
        /* translators: 1: customer first name, 2: order number. */
        __( 'Hi %1$s — we were not able to complete the refund on order %2$s. Our team has been notified and will follow up; no action is needed from you.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order ),
        $order->get_order_number()
    )
);

if ( ! empty( $reason ) ) {
    echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
        '<p class="lytrod-panel-text lytrod-danger" style="margin-bottom:0;">' . esc_html( $reason ) . '</p>',
        __( 'Reason reported by the processor', 'lytrod-emails' )
    );
}

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-refund-customer.php */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-refund-customer.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-refund-customer.php */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
