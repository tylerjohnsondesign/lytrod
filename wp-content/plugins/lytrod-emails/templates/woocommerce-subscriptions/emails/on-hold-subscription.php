<?php
/**
 * Admin notification: subscription suspended by the customer.
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
            /* translators: %s: customer full name. */
            esc_html__( 'The license belonging to %s has been suspended by the customer.', 'lytrod-emails' ),
            esc_html( $subscription->get_formatted_billing_full_name() )
        );
        ?>
    </p>
</div>

<?php
echo lytrod_emails_subscription_table( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $subscription,
    __( 'Date suspended', 'lytrod-emails' ),
    esc_html( date_i18n( wc_date_format(), time() ) )
);

echo lytrod_emails_product_id_panel( $subscription ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** This action is documented in woocommerce-subscriptions/templates/emails/on-hold-subscription.php */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

/** This action is documented in woocommerce-subscriptions/templates/emails/on-hold-subscription.php */
do_action( 'woocommerce_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
