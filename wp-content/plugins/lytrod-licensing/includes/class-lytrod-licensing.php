<?php
/**
 * Plugin loader and the shared predicates every component keys off.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loader.
 */
class Lytrod_Licensing {

    /**
     * Product meta marking a product as sold by the seat.
     */
    const META_LICENSED = '_lytrod_licensed';

    /**
     * Variation meta withdrawing a legacy variation from sale.
     *
     * Used on the old variable products during the transition — most importantly on an orphan
     * variation with no attribute and no price, which matches "Any" and can otherwise be added
     * to the cart for nothing. The variation stays published so existing subscriptions that
     * reference it still resolve.
     */
    const META_RETIRED = '_lytrod_retired';

    /**
     * Boot once WooCommerce is known to be present.
     *
     * @return void
     */
    public static function init(): void {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( __CLASS__, 'missing_woocommerce_notice' ) );

            return;
        }

        load_plugin_textdomain( 'lytrod-licensing', false, dirname( plugin_basename( LYTROD_LICENSING_FILE ) ) . '/languages' );

        Lytrod_Licensing_Cart::init();
        Lytrod_Licensing_Admin::init();
        Lytrod_Licensing_Provisioning::init();

        // Report the real seat count in transactional email. The filter already exists in
        // Lytrod Emails precisely as this extension point.
        add_filter( 'lytrod_emails_licensed_seats', array( __CLASS__, 'email_seats' ), 10, 4 );
    }

    /**
     * Whether a product is sold by the seat.
     *
     * Any subscription product qualifies unless explicitly opted out, so a new licence product
     * needs no extra switch thrown. The legacy variable products are excluded — they are being
     * retired and must not gain a seat picker on the way out.
     *
     * @param mixed $product Product.
     * @return bool
     */
    public static function is_licensed_product( $product ): bool {
        if ( ! $product instanceof WC_Product ) {
            return false;
        }

        $flag = $product->get_meta( self::META_LICENSED, true );

        if ( '' !== $flag ) {
            $is_licensed = 'yes' === $flag;
        } else {
            $is_licensed = $product->is_type( 'subscription' );
        }

        /**
         * Filter whether a product is sold by the seat.
         *
         * @since 1.0.0
         * @param bool       $is_licensed Whether seats apply.
         * @param WC_Product $product     Product.
         */
        return (bool) apply_filters( 'lytrod_licensing_is_licensed_product', $is_licensed, $product );
    }

    /**
     * The free period a licence starts with, in raw form.
     *
     * "Free for" is stored in WooCommerce Subscriptions' own trial fields, so its saver, its
     * clamping and its cart handling all apply unchanged. What makes it different from a trial
     * is store configuration, not storage: with a $0 initial total and
     * `woocommerce_subscriptions_zero_initial_payment_requires_payment` off, checkout completes
     * with no payment method at all.
     *
     * @param mixed $product Product.
     * @return array{length: int, period: string}
     */
    public static function free_for( $product ): array {
        if ( ! $product instanceof WC_Product ) {
            return array(
                'length' => 0,
                'period' => 'year',
            );
        }

        return array(
            'length' => (int) $product->get_meta( '_subscription_trial_length', true ),
            'period' => (string) ( $product->get_meta( '_subscription_trial_period', true ) ?: 'year' ),
        );
    }

    /**
     * Human-readable free period, e.g. "1 year".
     *
     * @param mixed $product Product.
     * @return string Empty string when the licence is not free to start.
     */
    public static function free_for_label( $product ): string {
        $free = self::free_for( $product );

        if ( $free['length'] < 1 ) {
            return '';
        }

        $periods = function_exists( 'wcs_get_available_time_periods' )
            ? wcs_get_available_time_periods( 1 === $free['length'] ? 'singular' : 'plural' )
            : array();

        $unit = $periods[ $free['period'] ] ?? $free['period'];

        return trim( $free['length'] . ' ' . $unit );
    }

    /**
     * Feed the real seat count into transactional email.
     *
     * @param int    $seats Current value.
     * @param string $tier  Tier label.
     * @param mixed  $item  Line item.
     * @param mixed  $order Order.
     * @return int
     */
    public static function email_seats( $seats, $tier, $item, $order ) {
        $stored = Lytrod_Licensing_Seats::get( $item );

        return $stored > 0 ? $stored : (int) $seats;
    }

    /**
     * Admin notice shown when WooCommerce is not active.
     *
     * @return void
     */
    public static function missing_woocommerce_notice(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Lytrod Licensing', 'lytrod-licensing' ) . '</strong> ';
        echo esc_html__( 'requires WooCommerce to be installed and active.', 'lytrod-licensing' );
        echo '</p></div>';
    }
}
