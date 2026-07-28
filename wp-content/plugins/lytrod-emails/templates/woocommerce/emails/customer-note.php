<?php
/**
 * Customer note added to an order.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/** @hooked WC_Emails::email_header() */
do_action( 'woocommerce_email_header', $email_heading, $email );

echo lytrod_emails_intro( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    $order,
    $email,
    sprintf(
        /* translators: %s: customer first name. */
        __( 'Hi %s — a note has been added to your order by our team.', 'lytrod-emails' ),
        Lytrod_Emails_Content::first_name( $order )
    )
);

echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    '<p class="lytrod-panel-text" style="margin-bottom:0;">' . wp_kses( nl2br( wptexturize( $customer_note ) ), array( 'br' => array() ) ) . '</p>',
    __( 'Note from Lytrod', 'lytrod-emails' )
);

/** This action is documented in woocommerce/templates/emails/customer-note.php */
do_action( 'woocommerce_email_order_details', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-note.php */
do_action( 'woocommerce_email_order_meta', $order, false, false, $email );

/** This action is documented in woocommerce/templates/emails/customer-note.php */
do_action( 'woocommerce_email_customer_details', $order, false, false, $email );

echo lytrod_emails_additional_content( (string) $additional_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
echo lytrod_emails_support_block(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.

/** @hooked WC_Emails::email_footer() */
do_action( 'woocommerce_email_footer', $email );
