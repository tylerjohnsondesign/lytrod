<?php
/**
 * Admin notification: customer switched a subscription plan.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

// WooCommerce Subscriptions' own template calls count() on this unguarded, which
// fatals if the email is rendered without a full trigger() (e.g. a preview).
$subscriptions  = is_array( $subscriptions ) ? $subscriptions : array();
$switched_count = count( $subscriptions );

echo lytrod_emails_intro( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $order,
    $email,
    1 === $switched_count
        ? sprintf(
            /* translators: %s: customer full name. */
            __( '%s has switched their license plan. The new details are below.', 'lytrod-emails' ),
            $order->get_formatted_billing_full_name()
        )
        : sprintf(
            /* translators: 1: customer full name, 2: number of subscriptions switched. */
            __( '%1$s has switched %2$d of their licenses. The new details are below.', 'lytrod-emails' ),
            $order->get_formatted_billing_full_name(),
            $switched_count
        )
);

/** This action is documented in woocommerce-subscriptions/templates/emails/admin-new-switch-order.php */
do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-subscriptions/templates/emails/admin-new-switch-order.php */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_heading( __( 'New license details', 'lytrod-emails' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

foreach ( $subscriptions as $switched_subscription ) {
    /** This action is documented in woocommerce-subscriptions/templates/emails/admin-new-switch-order.php */
    do_action( 'woocommerce_subscriptions_email_order_details', $switched_subscription, $sent_to_admin, $plain_text, $email );
}

/** This action is documented in woocommerce-subscriptions/templates/emails/admin-new-switch-order.php */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
