<?php
/**
 * Recipient email: completed renewal order.
 *
 * Passes an empty heading to the header on purpose — this template leads with its own
 * success block instead of the standard <h1>, and email-header.php collapses
 * #header_wrapper when the heading is empty.
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

// The vendor template calls this directly rather than through the order-details hook,
// so software delivery survives replacing that table with the license table.
if ( is_callable( array( 'WC_Subscriptions_Email', 'order_download_details' ) ) ) {
    WC_Subscriptions_Email::order_download_details( $order, $sent_to_admin, $plain_text, $email );
}

/** This action is documented in woocommerce-subscriptions/templates/gifting/emails/recipient-completed-renewal-order.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-subscriptions/templates/gifting/emails/recipient-completed-renewal-order.php */
do_action( 'woocommerce_subscriptions_gifting_recipient_email_details', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) ( $additional_content ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
