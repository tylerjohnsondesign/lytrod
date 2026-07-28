<?php
/**
 * Customer password reset.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

// WooCommerce's own template passes $user_login straight to rawurlencode(), which
// emits a PHP 8.1+ deprecation whenever the email is rendered without a real user —
// as happens in the WooCommerce > Settings > Emails preview.
$user_login = (string) ( $user_login ?? '' );

$reset_url = esc_url(
    add_query_arg(
        array(
            'key'   => $reset_key,
            'id'    => $user_id,
            'login' => rawurlencode( $user_login ),
        ),
        wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) )
    )
);
?>

<div class="email-introduction">
    <p class="lytrod-lede">
        <?php
        printf(
            /* translators: %s: username. */
            esc_html__( 'Someone requested a password reset for the account %s. If that was you, use the button below. The link is single-use.', 'lytrod-emails' ),
            '<strong>' . esc_html( $user_login ) . '</strong>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inline.
        );
        ?>
    </p>
</div>

<?php
echo lytrod_emails_button( __( 'Reset your password', 'lytrod-emails' ), $reset_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    '<p class="lytrod-panel-text" style="margin-bottom:0;">' . esc_html__( 'If you did not request this, you can safely ignore this email — your password will not change until you use the link above.', 'lytrod-emails' ) . '</p>',
    __( 'Did not request this?', 'lytrod-emails' )
);

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
