<?php
/**
 * New customer account.
 *
 * Receives $user_login, $blogname, $set_password_url, $password_generated and
 * $additional_content — but no order, so it builds its own CTA rather than calling
 * Lytrod_Emails_Content::cta_for().
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
            /* translators: %s: site name. */
            esc_html__( 'Welcome to %s. Your account is ready — sign in any time to manage your licenses, download installers and review your renewals.', 'lytrod-emails' ),
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

if ( $password_generated && $set_password_url ) {
    echo lytrod_emails_button( __( 'Set your password', 'lytrod-emails' ), $set_password_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
} else {
    $account_url = wc_get_page_permalink( 'myaccount' );

    if ( $account_url ) {
        echo lytrod_emails_button( __( 'Go to your account', 'lytrod-emails' ), $account_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    }
}

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
