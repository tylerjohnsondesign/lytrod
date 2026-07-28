<?php
/**
 * Customer renewal invoice.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

if ( $order->has_status( 'failed' ) ) {
    $lede = sprintf(
        /* translators: 1: customer first name, 2: store name. */
        __( 'Hi %1$s — the automatic payment to renew your %2$s license did not go through. Renewing below reactivates it right away.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order ),
        get_bloginfo( 'name' )
    );
} else {
    $lede = sprintf(
        /* translators: 1: customer first name, 2: order total. */
        __( 'Hi %1$s — your renewal invoice is ready. The amount due is %2$s.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order ),
        wp_strip_all_tags( $order->get_formatted_order_total() )
    );
}

echo lytrod_emails_intro( $order, $email, $lede ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** This action is documented in woocommerce-subscriptions/templates/emails/customer-renewal-invoice.php */
do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
