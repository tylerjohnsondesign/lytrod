<?php
/**
 * Product page -> cart -> order, for a licence bought by seats and years.
 *
 * Two things travel with the cart item: the seat count, which becomes the quantity, and the
 * term in years, which becomes the subscription's billing interval. Nothing else.
 *
 * The term is applied by filtering `woocommerce_subscriptions_product_period_interval`
 * (WC_Subscriptions_Product::get_interval(), class-wc-subscriptions-product.php:517) for the
 * duration of the cart item's pricing, so a single product can be sold on any term without a
 * variation per term.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seat and term selection through the cart.
 */
class Lytrod_Licensing_Cart {

    /**
     * Term currently being applied, keyed by product id, while totals are calculated.
     *
     * @var array<int, int>
     */
    private static $term_override = array();

    /**
     * Register the pipeline.
     *
     * @return void
     */
    public static function init(): void {
        add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'render_picker' ), 20 );
        add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate' ), 10, 4 );
        add_filter( 'woocommerce_add_to_cart_quantity', array( __CLASS__, 'force_quantity' ), 10, 2 );
        add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 30, 3 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'restore_from_session' ), 10, 2 );
        add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_pricing' ), 20 );
        add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'display_in_cart' ), 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'persist_to_order_item' ), 20, 4 );
        add_action( 'woocommerce_checkout_subscription_created', array( __CLASS__, 'persist_to_subscription' ), 20, 3 );

        // Term override, consulted only while a cart item is being priced.
        add_filter( 'woocommerce_subscriptions_product_period_interval', array( __CLASS__, 'filter_interval' ), 20, 2 );
    }

    /**
     * Seats and term posted with an add-to-cart request.
     *
     * @return array{seats: int, years: int}
     */
    private static function posted(): array {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce owns add-to-cart nonce handling.
        $seats = isset( $_REQUEST[ Lytrod_Licensing_Seats::CART_SEATS ] ) ? absint( wp_unslash( $_REQUEST[ Lytrod_Licensing_Seats::CART_SEATS ] ) ) : 0;
        $years = isset( $_REQUEST[ Lytrod_Licensing_Seats::CART_TERM ] ) ? absint( wp_unslash( $_REQUEST[ Lytrod_Licensing_Seats::CART_TERM ] ) ) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        return array(
            'seats' => $seats,
            'years' => $years,
        );
    }

    /**
     * The seat and term picker on the product page.
     *
     * @return void
     */
    public static function render_picker(): void {
        global $product;

        if ( ! Lytrod_Licensing::is_licensed_product( $product ) ) {
            return;
        }

        wp_enqueue_style(
            'lytrod-licence-picker',
            LYTROD_LICENSING_URL . 'assets/css/picker.css',
            array(),
            LYTROD_LICENSING_VERSION
        );

        wp_enqueue_script(
            'lytrod-licence-picker',
            LYTROD_LICENSING_URL . 'assets/js/picker.js',
            array( 'jquery' ),
            LYTROD_LICENSING_VERSION,
            true
        );

        wp_localize_script(
            'lytrod-licence-picker',
            'lytrodLicence',
            array(
                'tiers'     => Lytrod_Licensing_Rates::tiers( $product->get_id() ),
                'discounts' => Lytrod_Licensing_Rates::term_discounts( $product->get_id() ),
                'maxSeats'  => Lytrod_Licensing_Rates::max_seats(),
                'currency'  => get_woocommerce_currency_symbol(),
                'decimals'  => wc_get_price_decimals(),
                'freeFor'   => Lytrod_Licensing::free_for_label( $product ),
            )
        );

        $terms    = Lytrod_Licensing_Rates::terms( $product->get_id() );
        $free_for = Lytrod_Licensing::free_for_label( $product );

        include LYTROD_LICENSING_DIR . 'templates/picker.php';
    }

    /**
     * Reject an out-of-range choice server-side.
     *
     * The picker constrains the inputs, but nothing from the browser may be trusted — a crafted
     * request would otherwise price a licence at whatever it liked.
     *
     * @param bool $passed       Current validation state.
     * @param int  $product_id   Product id.
     * @param int  $quantity     Quantity.
     * @param int  $variation_id Variation id.
     * @return bool
     */
    public static function validate( $passed, $product_id, $quantity, $variation_id = 0 ) {
        $target = $variation_id ? $variation_id : $product_id;

        if ( ! Lytrod_Licensing::is_licensed_product( wc_get_product( $target ) ) ) {
            return $passed;
        }

        $choice = self::posted();
        $max    = Lytrod_Licensing_Rates::max_seats();

        if ( $choice['seats'] > $max ) {
            wc_add_notice(
                sprintf(
                    /* translators: %d: maximum licences. */
                    esc_html__( 'Please choose %d licences or fewer, or contact us for a larger volume quote.', 'lytrod-licensing' ),
                    $max
                ),
                'error'
            );

            return false;
        }

        if ( $choice['years'] > 0 && ! in_array( $choice['years'], Lytrod_Licensing_Rates::terms( $target ), true ) ) {
            wc_add_notice( esc_html__( 'Please choose one of the available licence terms.', 'lytrod-licensing' ), 'error' );

            return false;
        }

        return $passed;
    }

    /**
     * Make the cart quantity the seat count.
     *
     * @param int $quantity   Requested quantity.
     * @param int $product_id Product id.
     * @return int
     */
    public static function force_quantity( $quantity, $product_id ) {
        if ( ! Lytrod_Licensing::is_licensed_product( wc_get_product( $product_id ) ) ) {
            return $quantity;
        }

        $seats = self::posted()['seats'];

        return $seats > 0 ? min( $seats, Lytrod_Licensing_Rates::max_seats() ) : $quantity;
    }

    /**
     * Carry the choice into the cart item.
     *
     * @param array $cart_item_data Cart item data.
     * @param int   $product_id     Product id.
     * @param int   $variation_id   Variation id.
     * @return array
     */
    public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
        $target = $variation_id ? $variation_id : $product_id;

        if ( ! Lytrod_Licensing::is_licensed_product( wc_get_product( $target ) ) ) {
            return $cart_item_data;
        }

        $choice = self::posted();
        $terms  = Lytrod_Licensing_Rates::terms( $target );

        $cart_item_data[ Lytrod_Licensing_Seats::CART_SEATS ] = $choice['seats'] > 0
            ? min( $choice['seats'], Lytrod_Licensing_Rates::max_seats() )
            : 1;

        $cart_item_data[ Lytrod_Licensing_Seats::CART_TERM ] = in_array( $choice['years'], $terms, true )
            ? $choice['years']
            : ( $terms ? (int) min( $terms ) : 1 );

        return $cart_item_data;
    }

    /**
     * Restore the choice when the cart is rebuilt from the session.
     *
     * Without this the choice survives only the request that made it, and the price silently
     * reverts on the next page load.
     *
     * @param array $cart_item     Cart item.
     * @param array $session_value Stored session values.
     * @return array
     */
    public static function restore_from_session( $cart_item, $session_value ) {
        foreach ( array( Lytrod_Licensing_Seats::CART_SEATS, Lytrod_Licensing_Seats::CART_TERM ) as $key ) {
            if ( isset( $session_value[ $key ] ) ) {
                $cart_item[ $key ] = (int) $session_value[ $key ];
            }
        }

        return $cart_item;
    }

    /**
     * Price every licensed cart item.
     *
     * @param WC_Cart $cart Cart.
     * @return void
     */
    public static function apply_pricing( $cart ): void {
        if ( ! $cart instanceof WC_Cart ) {
            return;
        }

        if ( is_admin() && ! wp_doing_ajax() ) {
            return;
        }

        self::$term_override = array();

        foreach ( $cart->get_cart() as $cart_item ) {
            if ( empty( $cart_item[ Lytrod_Licensing_Seats::CART_SEATS ] ) || empty( $cart_item['data'] ) ) {
                continue;
            }

            $product = $cart_item['data'];

            if ( ! $product instanceof WC_Product ) {
                continue;
            }

            $seats = max( 1, (int) $cart_item[ Lytrod_Licensing_Seats::CART_SEATS ] );
            $years = max( 1, (int) ( $cart_item[ Lytrod_Licensing_Seats::CART_TERM ] ?? 1 ) );
            $price = Lytrod_Licensing_Rates::price( $product->get_id(), $seats, $years );

            /*
             * Unit price, NOT the term total: WooCommerce multiplies by quantity, and quantity is
             * the seat count. Passed at full float precision on purpose — rounding it to 2dp
             * here is what makes quantity × price drift off the term total.
             */
            $product->set_price( $price['unit_price'] );
            $product->update_meta_data( '_subscription_price', $price['unit_price'] );

            // The term is the billing interval. Nothing is prepaid as a sign-up fee any more.
            $product->update_meta_data( '_subscription_sign_up_fee', 0 );
            $product->update_meta_data( '_subscription_period', 'year' );
            $product->update_meta_data( '_subscription_period_interval', $years );
            $product->update_meta_data( '_subscription_length', 0 );

            self::$term_override[ $product->get_id() ] = $years;
        }
    }

    /**
     * Serve the chosen term to WooCommerce Subscriptions' interval getter.
     *
     * @param mixed $interval Interval from product meta.
     * @param mixed $product  Product.
     * @return mixed
     */
    public static function filter_interval( $interval, $product ) {
        if ( $product instanceof WC_Product && isset( self::$term_override[ $product->get_id() ] ) ) {
            return self::$term_override[ $product->get_id() ];
        }

        return $interval;
    }

    /**
     * Show the licence, term and tier in the cart and checkout tables.
     *
     * @param array $item_data Existing rows.
     * @param array $cart_item Cart item.
     * @return array
     */
    public static function display_in_cart( $item_data, $cart_item ) {
        if ( empty( $cart_item[ Lytrod_Licensing_Seats::CART_SEATS ] ) ) {
            return $item_data;
        }

        $seats   = (int) $cart_item[ Lytrod_Licensing_Seats::CART_SEATS ];
        $years   = (int) ( $cart_item[ Lytrod_Licensing_Seats::CART_TERM ] ?? 1 );
        $product = $cart_item['data'] ?? null;
        $tier    = $product instanceof WC_Product ? Lytrod_Licensing_Rates::tier_name( $product->get_id(), $seats ) : '';

        $item_data[] = array(
            'key'   => __( 'Licences', 'lytrod-licensing' ),
            'value' => $tier
                ? sprintf(
                    /* translators: 1: seat count, 2: tier name. */
                    _n( '%1$d seat (%2$s)', '%1$d seats (%2$s)', $seats, 'lytrod-licensing' ),
                    $seats,
                    $tier
                )
                : (string) $seats,
        );

        $item_data[] = array(
            'key'   => __( 'Term', 'lytrod-licensing' ),
            'value' => sprintf(
                /* translators: %d: number of years. */
                _n( '%d year', '%d years', $years, 'lytrod-licensing' ),
                $years
            ),
        );

        return $item_data;
    }

    /**
     * Persist the choice onto the order line item.
     *
     * @param WC_Order_Item_Product $item          Order line item.
     * @param string                $cart_item_key Cart key.
     * @param array                 $values        Cart item.
     * @param WC_Order              $order         Order.
     * @return void
     */
    public static function persist_to_order_item( $item, $cart_item_key, $values, $order ): void {
        if ( empty( $values[ Lytrod_Licensing_Seats::CART_SEATS ] ) ) {
            return;
        }

        $item->update_meta_data( Lytrod_Licensing_Seats::META_SEATS, max( 1, (int) $values[ Lytrod_Licensing_Seats::CART_SEATS ] ) );
        $item->update_meta_data( Lytrod_Licensing_Seats::META_TERM, max( 1, (int) ( $values[ Lytrod_Licensing_Seats::CART_TERM ] ?? 1 ) ) );
    }

    /**
     * Put the term and the priced line onto the subscription itself.
     *
     * The subscription — not the parent order — is what every future renewal is copied from
     * (wcs_copy_order_item(), wcs-order-functions.php:811), so this is what makes renewals bill
     * the right amount for years afterwards.
     *
     * @param WC_Subscription $subscription Subscription.
     * @param WC_Order        $order        Parent order.
     * @param WC_Cart         $cart         Recurring cart.
     * @return void
     */
    public static function persist_to_subscription( $subscription, $order, $cart ): void {
        if ( ! $subscription instanceof WC_Subscription ) {
            return;
        }

        $years   = 0;
        $changed = false;

        foreach ( $subscription->get_items() as $item ) {
            if ( ! $item instanceof WC_Order_Item_Product ) {
                continue;
            }

            $seats = (int) $item->get_meta( Lytrod_Licensing_Seats::META_SEATS, true );

            if ( $seats < 1 ) {
                continue;
            }

            $years      = max( 1, (int) $item->get_meta( Lytrod_Licensing_Seats::META_TERM, true ) );
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            $term_total = (float) Lytrod_Licensing_Rates::price( $product_id, $seats, $years )['term_total'];

            Lytrod_Licensing_Seats::apply( $item, $seats, $years, $term_total );
            $changed = true;
        }

        if ( $years > 0 ) {
            Lytrod_Licensing_Seats::apply_term( $subscription, $years );
            $changed = true;
        }

        if ( $changed ) {
            /*
             * Roll the priced line items up into the subscription's own total. Safe to call:
             * calculate_totals() sums the STORED line totals (abstract-wc-order.php:1993-2056)
             * and never re-derives them from quantity x product price. Without it the
             * subscription's _order_total stays at 0 and My Account shows $0.00.
             */
            $subscription->calculate_totals( false );
            $subscription->save();
        }
    }
}
