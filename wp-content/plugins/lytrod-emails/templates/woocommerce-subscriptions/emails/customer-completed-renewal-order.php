<?php
/**
 * Customer email: completed renewal order.
 *
 * Leads with its own success block, so an empty heading is passed to the header —
 * email-header.php collapses #header_wrapper when the heading is empty.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', '', $email );

echo lytrod_emails_renewal_receipt( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $order,
    __( 'Thank you for your continued business!', 'lytrod-emails' ),
    __( 'Your software license has been successfully renewed. A summary of your renewal and payment details are provided below.', 'lytrod-emails' )
);

// Called directly rather than via woocommerce_subscriptions_email_order_details, so
// replacing the order-items table with the license table does not drop downloads.
if ( is_callable( array( 'WC_Subscriptions_Email', 'order_download_details' ) ) ) {
    WC_Subscriptions_Email::order_download_details( $order, $sent_to_admin, $plain_text, $email );
}

/** This action is documented in woocommerce-subscriptions/templates/emails/customer-completed-renewal-order.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) ( $additional_content ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
