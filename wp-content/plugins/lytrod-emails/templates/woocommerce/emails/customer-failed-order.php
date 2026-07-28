<?php
/**
 * Customer failed order.
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
        /* translators: %s: customer first name. */
        __( 'Hi %s — we could not process payment for your order, so it has not been completed. Retrying usually resolves it.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order )
    )
);

/** This action is documented in woocommerce/templates/emails/customer-failed-order.php */
do_action( 'woocommerce_email_order_details', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-failed-order.php */
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-failed-order.php */
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
