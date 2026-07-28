<?php
/**
 * Admin notification: a refund could not be processed.
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
        /* translators: %s: order number. */
        __( 'The refund on order %s failed at the payment processor and needs to be resolved manually in Stripe.', 'lytrod-emails' ),
        $order->get_order_number()
    )
);

if ( ! empty( $reason ) ) {
    echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
        '<p class="lytrod-panel-text lytrod-danger" style="margin-bottom:0;">' . esc_html( $reason ) . '</p>',
        __( 'Reason reported by the processor', 'lytrod-emails' )
    );
}

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-refund-admin.php */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-refund-admin.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-gateway-stripe/templates/emails/failed-refund-admin.php */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
