<?php
/**
 * Customer notification: free trial ending, manual renewal required.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<div class="email-introduction">
    <p class="lytrod-lede">
        <?php
        printf(
            /* translators: 1: customer first name, 2: human readable time difference, 3: date in site format. */
            esc_html__( 'Hi %1$s — your Lytrod trial ends in %2$s, on %3$s. To keep working without interruption, renew before then. This license will not renew on its own.', 'lytrod-emails' ),
            esc_html( $subscription->get_billing_first_name() ),
            esc_html( $subscription_time_til_event ),
            esc_html( $subscription_event_date )
        );
        ?>
    </p>
</div>

<?php
echo lytrod_emails_button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $can_renew_early ? __( 'Choose a plan', 'lytrod-emails' ) : __( 'Manage license', 'lytrod-emails' ),
    (string) $url_for_renewal
);

echo lytrod_emails_product_id_panel( $subscription ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

\WC_Subscriptions_Email::subscription_details( $subscription, $order, $sent_to_admin, $plain_text );

/** This action is documented in woocommerce-subscriptions/templates/emails/customer-notification-auto-renewal.php */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
