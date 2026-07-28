<?php
/**
 * Recipient email: account created for a gifted license.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

$lytrod_password_url = add_query_arg(
    array(
        'key' => $reset_key,
        'id'  => $user_id,
    ),
    wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) )
);
?>

<div class="email-introduction">
    <p class="lytrod-lede">
        <?php
        printf(
            /* translators: 1: purchaser name, 2: store name. */
            esc_html__( '%1$s just purchased a license for you at %2$s, so we created an account you can use to manage it.', 'lytrod-emails' ),
            wp_kses( $subscription_purchaser, wp_kses_allowed_html( 'user_description' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by wp_kses().
            esc_html( $blogname )
        );
        ?>
    </p>
</div>

<?php
echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    '<p class="lytrod-mono">' . esc_html( $user_login ) . '</p>',
    __( 'Your username', 'lytrod-emails' )
);

echo lytrod_emails_button( __( 'Set your password', 'lytrod-emails' ), $lytrod_password_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

$lytrod_details_url = wc_get_endpoint_url( 'new-recipient-account', '', wc_get_page_permalink( 'myaccount' ) );

if ( $lytrod_details_url ) {
    echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
        '<p class="lytrod-panel-text" style="margin-bottom:0;">' . sprintf(
            /* translators: %s: link to the account details page. */
            esc_html__( 'Once your password is set, finish your profile here: %s.', 'lytrod-emails' ),
            '<a class="link" href="' . esc_url( $lytrod_details_url ) . '">' . esc_html__( 'Account details', 'lytrod-emails' ) . '</a>'
        ) . '</p>',
        __( 'Next step', 'lytrod-emails' )
    );
}

echo lytrod_emails_additional_content( (string) ( $additional_content ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
