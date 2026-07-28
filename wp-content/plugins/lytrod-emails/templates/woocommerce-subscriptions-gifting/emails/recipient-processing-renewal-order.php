<?php
/**
 * Recipient email: processing renewal order.
 *
 * Wording is deliberately weaker than the completed email's: payment received is not
 * the same as a renewal fulfilled, so this does not claim the license is already
 * renewed.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', '', $email );

echo lytrod_emails_renewal_receipt( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $order,
    __( 'Thank you for your continued business!', 'lytrod-emails' ),
    __( 'Your software license renewal is being processed. A summary of your renewal and payment details are provided below, and we will confirm as soon as it completes.', 'lytrod-emails' )
);

if ( is_callable( array( 'WC_Subscriptions_Email', 'order_download_details' ) ) ) {
    WC_Subscriptions_Email::order_download_details( $order, $sent_to_admin, $plain_text, $email );
}

/** This action is documented in woocommerce-subscriptions/templates/gifting/emails/recipient-processing-renewal-order.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-subscriptions/templates/gifting/emails/recipient-processing-renewal-order.php */
do_action( 'woocommerce_subscriptions_gifting_recipient_email_details', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) ( $additional_content ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
