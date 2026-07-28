<?php
/**
 * Recipient email: new licenses purchased for you.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

$lytrod_count = is_array( $subscriptions ) ? count( $subscriptions ) : 0;
$lytrod_noun  = _n( 'a license', 'licenses', max( 1, $lytrod_count ), 'lytrod-emails' );
?>

<div class="email-introduction">
    <p class="lytrod-lede">
        <?php
        printf(
            /* translators: 1: purchaser name, 2: "a license" or "licenses", 3: store name. */
            esc_html__( '%1$s just purchased %2$s for you at %3$s. The details are below.', 'lytrod-emails' ),
            wp_kses( $subscription_purchaser, wp_kses_allowed_html( 'user_description' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by wp_kses().
            esc_html( $lytrod_noun ),
            esc_html( $blogname )
        );
        ?>
    </p>
</div>

<?php
// Mirrors the vendor template: an empty $recipient_user means this is a preview.
$lytrod_new_account = empty( $recipient_user ) ? 'true' : get_user_meta( $recipient_user->ID, 'wcsg_update_account', true );

if ( 'true' === (string) $lytrod_new_account ) {
    echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
        '<p class="lytrod-panel-text" style="margin-bottom:0;">' . esc_html__( 'You did not have an account yet, so we created one for you. Your login details are on their way in a separate email.', 'lytrod-emails' ) . '</p>',
        __( 'Your new account', 'lytrod-emails' )
    );
} else {
    $lytrod_account_url = wc_get_page_permalink( 'myaccount' );

    if ( $lytrod_account_url ) {
        echo lytrod_emails_button( __( 'View your licenses', 'lytrod-emails' ), $lytrod_account_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    }
}

foreach ( (array) $subscriptions as $lytrod_subscription ) {
    if ( ! is_object( $lytrod_subscription ) ) {
        $lytrod_subscription = wcs_get_subscription( $lytrod_subscription );
    }

    if ( ! $lytrod_subscription ) {
        continue;
    }

    /** This action is documented in woocommerce-subscriptions/templates/gifting/emails/recipient-new-initial-order.php */
    do_action( 'wcs_gifting_email_order_details', $lytrod_subscription, $sent_to_admin, $plain_text, $email );

    if ( is_callable( array( 'WC_Subscriptions_Email', 'order_download_details' ) ) ) {
        WC_Subscriptions_Email::order_download_details( $lytrod_subscription, $sent_to_admin, $plain_text, $email );
    }
}

echo lytrod_emails_additional_content( (string) ( $additional_content ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
