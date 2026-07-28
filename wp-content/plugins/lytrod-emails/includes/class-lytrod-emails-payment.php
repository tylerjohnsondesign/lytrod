<?php
/**
 * Resolves card brand and last four digits for an order.
 *
 * WooCommerce exposes `WC_Order::get_payment_card_info()`, which asks the
 * `wc_order_payment_card_info` filter for the answer
 * (woocommerce/src/Internal/Orders/PaymentInfo.php:45). Nothing on this site hooks
 * it, so the method returns empty brand/last4 for every order — which is why the
 * "Payment method" row in stock emails reads a bare "Credit / Debit Card", and why
 * WooCommerce's own last4 append at class-wc-order.php:2491 never fires.
 *
 * Hooking it here fixes brand and last4 in one place for the WooCommerce admin order
 * screen, My Account, every stock email and our own templates simultaneously.
 *
 * Neither branch makes an HTTP request: this runs inside wp_mail().
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Card detail resolution.
 */
class Lytrod_Emails_Payment {

    /**
     * Register the filter.
     *
     * @return void
     */
    public static function init(): void {
        add_filter( 'wc_order_payment_card_info', array( __CLASS__, 'card_info' ), 10, 2 );
    }

    /**
     * Supply brand and last four for an order.
     *
     * @param array    $info  Existing card info.
     * @param WC_Order $order Order.
     * @return array
     */
    public static function card_info( $info, $order ): array {
        $info = is_array( $info ) ? $info : array();

        // Never overwrite an answer another integration already provided.
        if ( ! empty( $info['brand'] ) || ! empty( $info['last4'] ) ) {
            return $info;
        }

        if ( ! $order instanceof WC_Order ) {
            return $info;
        }

        $gateway = $order->get_payment_method();

        if ( 'stripe' === $gateway || 0 === strpos( (string) $gateway, 'stripe_' ) ) {
            return array_merge( $info, self::stripe_card( $order ) );
        }

        if ( 'ebizcharge' === $gateway ) {
            return array_merge( $info, self::ebizcharge_card( $order ) );
        }

        return $info;
    }

    /**
     * Resolve a Stripe-paid order's card from the local payment-token tables.
     *
     * `_stripe_card_brand` and `_stripe_card_last4` do not exist on this install — the
     * only local source is `wp_woocommerce_payment_tokens`, joined on the order's
     * `_stripe_source_id`. Historically that resolves for a minority of orders, because
     * many source ids are legacy `src_…` Sources or belong to deleted tokens.
     *
     * We deliberately do NOT fall back to the customer's default token. It adds no
     * coverage on this data and would print a card the customer was never charged on,
     * which is worse than printing nothing.
     *
     * @param WC_Order $order Order.
     * @return array{brand?: string, last4?: string}
     */
    private static function stripe_card( WC_Order $order ): array {
        // Stripe Link is email-based and has no card. Do not invent one.
        if ( 'Link' === trim( wp_strip_all_tags( (string) $order->get_payment_method_title() ) ) ) {
            return array( 'brand' => __( 'Stripe Link', 'lytrod-emails' ) );
        }

        $source = (string) $order->get_meta( '_stripe_source_id' );

        // Renewal orders sometimes carry the source only on the parent subscription.
        if ( '' === $source && function_exists( 'wcs_get_subscriptions_for_renewal_order' ) ) {
            foreach ( wcs_get_subscriptions_for_renewal_order( $order ) as $subscription ) {
                $source = (string) $subscription->get_meta( '_stripe_source_id' );

                if ( '' !== $source ) {
                    break;
                }
            }
        }

        $customer_id = $order->get_customer_id();

        if ( '' === $source || ! $customer_id || ! class_exists( 'WC_Payment_Tokens' ) ) {
            return array();
        }

        foreach ( WC_Payment_Tokens::get_customer_tokens( $customer_id, 'stripe' ) as $token ) {
            if ( $token->get_token() !== $source || ! is_callable( array( $token, 'get_last4' ) ) ) {
                continue;
            }

            return array(
                'brand' => wc_get_credit_card_type_label( $token->get_card_type() ),
                'last4' => $token->get_last4(),
            );
        }

        return array();
    }

    /**
     * Resolve an EBizCharge-paid order's card from order meta.
     *
     * The gateway stores these itself
     * (woocommerce-ebizcharge-gateway/woocommerce-gateway-ebizcharge.php:2351-2358),
     * but `_card_number` is built as `'XXXXXXXXXXXX' . substr( $card, 12, 16 )`, which
     * assumes a 16-digit PAN. A 15-digit American Express yields only THREE digits —
     * 28 of 201 stored values do not end in four. So match four trailing digits
     * explicitly and fall back to brand-only rather than printing a wrong last four.
     *
     * @param WC_Order $order Order.
     * @return array{brand?: string, last4?: string}
     */
    private static function ebizcharge_card( WC_Order $order ): array {
        $card = array();
        $type = trim( (string) $order->get_meta( '_card_type' ) );

        if ( '' !== $type ) {
            $card['brand'] = $type;
        }

        $masked = (string) $order->get_meta( '_card_number' );

        if ( preg_match( '/(\d{4})$/', $masked, $matches ) ) {
            $card['last4'] = $matches[1];
        }

        return $card;
    }

    /**
     * Human-readable payment method for display, e.g. "Visa •••• 4242".
     *
     * Falls back to the stored gateway label, which MUST be tag-stripped: the theme's
     * `woocommerce_gateway_title` filter (themes/lytrod/functions.php:646) injects an
     * `<img>` into the title, and WooCommerce persists that into `_payment_method_title`
     * — 110 rows on this site contain markup, 30 of them pointing at the staging
     * hostname.
     *
     * @param WC_Order $order Order.
     * @return string Empty string when no gateway is recorded at all.
     */
    public static function describe( WC_Order $order ): string {
        $info  = $order->get_payment_card_info();
        $brand = ! empty( $info['brand'] ) ? $info['brand'] : '';
        $last4 = ! empty( $info['last4'] ) ? $info['last4'] : '';

        if ( '' !== $brand && '' !== $last4 ) {
            /* translators: 1: card brand, 2: last four digits. */
            return sprintf( __( '%1$s •••• %2$s', 'lytrod-emails' ), $brand, $last4 );
        }

        if ( '' !== $brand ) {
            return $brand;
        }

        return trim( wp_strip_all_tags( (string) $order->get_payment_method_title() ) );
    }
}
