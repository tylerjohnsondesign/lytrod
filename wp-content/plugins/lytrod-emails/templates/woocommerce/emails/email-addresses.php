<?php
/**
 * Billing and shipping addresses.
 *
 * Rendered as brand panels rather than bordered boxes. The stock `<hr>` divider is
 * omitted — it is hardcoded inline as `#1E1E1E` in the WooCommerce original and so
 * cannot be recoloured, only replaced.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/*
 * Renewals ship nothing, so an address block is noise. Gating here rather than
 * removing `do_action( 'woocommerce_email_customer_details', … )` from each renewal
 * template keeps it in one place and covers templates this plugin does not own.
 *
 * Safe to do: nothing on this site hooks `woocommerce_email_customer_details` except
 * WooCommerce itself, and its other two callbacks are already no-ops here —
 * `customer_details` (priority 10) finds no `woocommerce_email_customer_details_fields`
 * callbacks, and `additional_checkout_fields` (priority 30) finds no block-checkout
 * fields. Note `woocommerce_email_addresses` is a method name, not a hook.
 *
 * Subscriptions Gifting renders its OWN address table on a different hook
 * (`woocommerce_subscriptions_gifting_recipient_email_details`), so the suppression
 * list lives in Lytrod_Emails_Content and is applied in both places.
 */
if ( Lytrod_Emails_Content::hides_addresses() ) {
    return;
}

$address       = $order->get_formatted_billing_address();
$shipping      = $order->get_formatted_shipping_address();
$sent_to_admin = $sent_to_admin ?? false;
$text_align    = is_rtl() ? 'right' : 'left';

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping;
$column_width  = $show_shipping ? '50%' : '100%';
?>

<h2 class="lytrod-section-title"><?php esc_html_e( 'Billing details', 'lytrod-emails' ); ?></h2>

<table id="addresses" cellspacing="0" cellpadding="0" width="100%" border="0" role="presentation" style="width:100%;vertical-align:top;margin-bottom:8px;padding:0;">
    <tr>
        <td class="font-family text-align-left" style="border:0;padding:0;text-align:<?php echo esc_attr( $text_align ); ?>;" valign="top" width="<?php echo esc_attr( $column_width ); ?>">
            <b class="address-title"><?php esc_html_e( 'Billing address', 'lytrod-emails' ); ?></b>

            <address class="address">
                <?php echo wp_kses_post( $address ? $address : esc_html__( 'N/A', 'lytrod-emails' ) ); ?>
                <?php if ( $order->get_billing_phone() ) : ?>
                    <br /><?php echo wc_make_phone_clickable( $order->get_billing_phone() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WooCommerce. ?>
                <?php endif; ?>
                <?php if ( $order->get_billing_email() ) : ?>
                    <br /><?php echo esc_html( $order->get_billing_email() ); ?>
                <?php endif; ?>
                <?php
                /** This action is documented in woocommerce/templates/emails/email-addresses.php */
                do_action( 'woocommerce_email_customer_address_section', 'billing', $order, $sent_to_admin, false );
                ?>
            </address>
        </td>
        <?php if ( $show_shipping ) : ?>
            <td class="font-family text-align-left" style="border:0;padding:0;text-align:<?php echo esc_attr( $text_align ); ?>;" valign="top" width="50%">
                <b class="address-title"><?php esc_html_e( 'Shipping address', 'lytrod-emails' ); ?></b>

                <address class="address">
                    <?php echo wp_kses_post( $shipping ); ?>
                    <?php if ( $order->get_shipping_phone() ) : ?>
                        <br /><?php echo wc_make_phone_clickable( $order->get_shipping_phone() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WooCommerce. ?>
                    <?php endif; ?>
                    <?php
                    /** This action is documented in woocommerce/templates/emails/email-addresses.php */
                    do_action( 'woocommerce_email_customer_address_section', 'shipping', $order, $sent_to_admin, false );
                    ?>
                </address>
            </td>
        <?php endif; ?>
    </tr>
</table>
