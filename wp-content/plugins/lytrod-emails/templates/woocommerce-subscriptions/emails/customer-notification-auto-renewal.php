<?php
/**
 * Customer notification: subscription renews automatically soon.
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
            esc_html__( 'Hi %1$s — your Lytrod license renews automatically in %2$s, on %3$s. No action is needed; we will charge your saved payment method and email you a receipt.', 'lytrod-emails' ),
            esc_html( $subscription->get_billing_first_name() ),
            esc_html( $subscription_time_til_event ),
            esc_html( $subscription_event_date )
        );
        ?>
    </p>
</div>

<?php
echo lytrod_emails_button( __( 'Manage license', 'lytrod-emails' ), (string) $url_for_renewal ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

echo lytrod_emails_product_id_panel( $subscription ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

\WC_Subscriptions_Email::subscription_details( $subscription, $order, $sent_to_admin, $plain_text );

/**
 * Action hook fired after the subscription details in renewal notification emails.
 *
 * @since 1.0.0
 */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
