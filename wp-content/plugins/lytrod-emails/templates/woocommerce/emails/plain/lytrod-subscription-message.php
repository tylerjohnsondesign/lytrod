<?php
/**
 * Licence lifecycle email body, plain text.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$lytrod_body    = isset( $body ) ? (string) $body : '';
$lytrod_cta     = isset( $cta ) && is_array( $cta ) ? $cta : array();
$lytrod_extra   = isset( $additional_content ) ? (string) $additional_content : '';
$lytrod_heading = isset( $email_heading ) ? (string) $email_heading : '';

if ( '' !== $lytrod_heading ) {
    echo esc_html( wp_strip_all_tags( $lytrod_heading ) ) . "\n";
    echo esc_html( str_repeat( '=', 60 ) ) . "\n\n";
}

// The HTML version promotes a standalone URL to a button; plain text keeps it inline, so the
// body is emitted as written.
echo esc_html( wp_strip_all_tags( trim( $lytrod_body ) ) ) . "\n\n";

if ( ! empty( $lytrod_cta['label'] ) && ! empty( $lytrod_cta['url'] ) ) {
    echo esc_html( wp_strip_all_tags( $lytrod_cta['label'] ) ) . ': ' . esc_url_raw( $lytrod_cta['url'] ) . "\n\n";
}

if ( '' !== trim( $lytrod_extra ) ) {
    echo esc_html( str_repeat( '-', 60 ) ) . "\n";
    echo esc_html( wp_strip_all_tags( $lytrod_extra ) ) . "\n\n";
}

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
