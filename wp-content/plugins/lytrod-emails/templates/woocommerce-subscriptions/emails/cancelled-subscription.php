<?php
/**
 * Admin notification: subscription cancelled.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

$pending_cancel = 'pending-cancel' === $subscription->get_status();
$end_time       = $subscription->get_time( 'end', 'site' );
$end_date       = $end_time ? date_i18n( wc_date_format(), $end_time ) : '';
?>

<div class="email-introduction">
    <p class="lytrod-lede">
        <?php
        if ( $pending_cancel ) {
            printf(
                /* translators: 1: customer full name, 2: date the subscription ends. */
                esc_html__( 'The license belonging to %1$s is pending cancellation and will end on %2$s.', 'lytrod-emails' ),
                esc_html( $subscription->get_formatted_billing_full_name() ),
                esc_html( $end_date )
            );
        } else {
            printf(
                /* translators: %s: customer full name. */
                esc_html__( 'The license belonging to %s has been cancelled.', 'lytrod-emails' ),
                esc_html( $subscription->get_formatted_billing_full_name() )
            );
        }
        ?>
    </p>
</div>

<?php
echo lytrod_emails_subscription_table( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $subscription,
    $pending_cancel ? __( 'End of prepaid term', 'lytrod-emails' ) : __( 'End date', 'lytrod-emails' ),
    $end_date ? esc_html( $end_date ) : '&mdash;'
);

echo lytrod_emails_product_id_panel( $subscription ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** This action is documented in woocommerce-subscriptions/templates/emails/cancelled-subscription.php */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-subscriptions/templates/emails/cancelled-subscription.php */
do_action( 'woocommerce_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
