<?php
/**
 * Licence lifecycle email body.
 *
 * Shared by all five licence emails. Their copy is written by an administrator rather than in a
 * template, so this file's job is only to place that copy inside the branded shell.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$lytrod_body     = isset( $body ) ? (string) $body : '';
$lytrod_cta      = isset( $cta ) && is_array( $cta ) ? $cta : array();
$lytrod_extra    = isset( $additional_content ) ? (string) $additional_content : '';
$lytrod_heading  = isset( $email_heading ) ? $email_heading : '';

/*
 * A line holding nothing but a URL becomes the call-to-action button instead of a bare link.
 *
 * The copy being migrated puts the account URL on its own line, which `wpautop()` renders as
 * unclickable text — a live defect in the emails this replaces. Promoting that line to the button
 * honours what the author meant ("send them here") and reads as a real call to action. URLs that
 * appear inside a sentence are left alone and simply become links.
 */
$lytrod_body = implode(
    "\n",
    array_filter(
        preg_split( '/\R/', $lytrod_body ),
        static function ( $line ) {
            return ! preg_match( '#^\s*https?://\S+\s*$#i', (string) $line );
        }
    )
);

$lytrod_html = make_clickable( wpautop( wp_kses_post( trim( $lytrod_body ) ) ) );

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $lytrod_heading, $email );

?>
<div class="email-introduction">
    <?php echo $lytrod_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above via wp_kses_post(). ?>
</div>
<?php

if ( ! empty( $lytrod_cta['label'] ) && ! empty( $lytrod_cta['url'] ) ) {
    echo lytrod_emails_button( (string) $lytrod_cta['label'], (string) $lytrod_cta['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
}

echo lytrod_emails_additional_content( $lytrod_extra ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
