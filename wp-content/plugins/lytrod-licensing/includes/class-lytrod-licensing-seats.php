<?php
/**
 * Seats and term on orders and subscriptions.
 *
 * **Seats are the line item's quantity.** A 10-seat licence is quantity 10, with the unit price
 * set to `term_total / 10` so `quantity × unit_price` lands exactly on the term total. The term
 * in years is the subscription's billing interval — a 3-year licence literally bills every
 * 3 years.
 *
 * An earlier version of this plugin pinned quantity to 1 and hid seats in meta, on the stated
 * grounds that `calculate_totals()` would otherwise recompute the line as quantity × product
 * price. That was wrong: `WC_Abstract_Order::calculate_totals()`
 * (woocommerce/includes/abstracts/abstract-wc-order.php:1993-2056) reads each item's *stored*
 * total via `get_cart_total_for_order()` and only sums them — the one `set_total()` in that
 * method is inside the fees loop. Quantity-as-seats is safe.
 *
 * Two hazards that are real:
 *
 * 1. `WC_Abstract_Order::add_product()` DOES derive totals from quantity × price at insert
 *    time, so `apply()` always sets subtotal and total explicitly afterwards.
 * 2. The unit price must never be rounded to 2dp. WooCommerce keeps four extra decimals of
 *    precision internally, so full-precision `term_total / seats` multiplies back exactly;
 *    a pre-rounded unit drifts by up to a couple of cents and worsens with the seat count.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seat and term storage.
 */
class Lytrod_Licensing_Seats {

    /**
     * Line-item meta mirroring the quantity, for querying and for the provisioning guardrail.
     */
    const META_SEATS = '_lytrod_seats';

    /**
     * Line-item meta holding the term in whole years.
     */
    const META_TERM = '_lytrod_term_years';

    /**
     * Subscription meta flagging that seats were provisioned in LimeLM.
     */
    const META_PROVISIONED = '_lytrod_seats_provisioned';

    /**
     * Cart item keys carrying the customer's choices from the product page.
     */
    const CART_SEATS = 'lytrod_seats';
    const CART_TERM  = 'lytrod_term';

    /**
     * Seat count for an order, subscription or line item.
     *
     * Quantity is authoritative. The meta mirror is only consulted for line items that predate
     * the migration, where quantity is still 1.
     *
     * @param mixed $thing WC_Order, WC_Subscription or WC_Order_Item.
     * @return int Zero when nothing is recorded.
     */
    public static function get( $thing ): int {
        if ( $thing instanceof WC_Order_Item_Product ) {
            $quantity = (int) $thing->get_quantity();
            $meta     = (int) $thing->get_meta( self::META_SEATS, true );

            // A migrated item has quantity == seats. An un-migrated one has quantity 1 and the
            // real count in meta, so prefer whichever is larger rather than guessing.
            return max( $quantity, $meta );
        }

        if ( ! $thing instanceof WC_Order ) {
            return 0;
        }

        foreach ( $thing->get_items() as $item ) {
            $seats = self::get( $item );

            if ( $seats > 0 ) {
                return $seats;
            }
        }

        return 0;
    }

    /**
     * Seat count, falling back to 1 when nothing is recorded at all.
     *
     * @param mixed $thing Order, subscription or line item.
     * @return int
     */
    public static function get_or_infer( $thing ): int {
        $seats = self::get( $thing );

        return $seats > 0 ? $seats : 1;
    }

    /**
     * Term in whole years for a line item.
     *
     * Prefers the recorded meta, then the owning subscription's billing interval, then 1.
     *
     * @param mixed $item  Line item.
     * @param mixed $order Owning order or subscription, when known.
     * @return int
     */
    public static function term_years( $item, $order = null ): int {
        if ( $item instanceof WC_Order_Item ) {
            $stored = (int) $item->get_meta( self::META_TERM, true );

            if ( $stored > 0 ) {
                return $stored;
            }
        }

        if ( $order instanceof WC_Order && is_callable( array( $order, 'get_billing_interval' ) ) ) {
            $interval = (int) $order->get_billing_interval();
            $period   = (string) $order->get_billing_period();

            if ( $interval > 0 && 'year' === $period ) {
                return $interval;
            }
        }

        return 1;
    }

    /**
     * Write seats, term and price onto a line item.
     *
     * @param WC_Order_Item_Product $item       Line item.
     * @param int                   $seats      Seat count.
     * @param int                   $years      Term in whole years.
     * @param float|null            $term_total Total for the term. Computed when omitted.
     * @return void
     */
    public static function apply( $item, int $seats, int $years = 1, ?float $term_total = null ): void {
        if ( ! $item instanceof WC_Order_Item_Product ) {
            return;
        }

        $seats = max( 1, $seats );
        $years = max( 1, $years );

        if ( null === $term_total ) {
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            $term_total = (float) Lytrod_Licensing_Rates::price( $product_id, $seats, $years )['term_total'];
        }

        $item->set_quantity( $seats );
        $item->update_meta_data( self::META_SEATS, $seats );
        $item->update_meta_data( self::META_TERM, $years );

        // Explicit, because add_product() would otherwise have derived these from
        // quantity × product price at insert time.
        $item->set_subtotal( $term_total );
        $item->set_total( $term_total );
    }

    /**
     * Put a subscription onto an N-year billing interval without moving its next payment.
     *
     * `_subscription_length` must stay 0. It counts periods and ignores the interval
     * (WC_Subscriptions_Product::get_expiration_date(), class-wc-subscriptions-product.php:669),
     * so a length equal to the interval would give exactly one payment and then expiry.
     *
     * @param mixed $subscription Subscription.
     * @param int   $years        Term in whole years.
     * @return void
     */
    public static function apply_term( $subscription, int $years ): void {
        if ( ! $subscription instanceof WC_Subscription ) {
            return;
        }

        $subscription->set_billing_period( 'year' );
        $subscription->set_billing_interval( max( 1, $years ) );
    }

    /**
     * Whether a subscription's seats have been provisioned in LimeLM.
     *
     * @param mixed $subscription Subscription.
     * @return bool
     */
    public static function is_provisioned( $subscription ): bool {
        return $subscription instanceof WC_Order
            && 'yes' === $subscription->get_meta( self::META_PROVISIONED, true );
    }

    /**
     * Whether a subscription still needs a human to raise its LimeLM activation limit.
     *
     * A single seat needs no action; the default entitlement is already 1.
     *
     * @param mixed $subscription Subscription.
     * @return bool
     */
    public static function needs_provisioning( $subscription ): bool {
        if ( ! $subscription instanceof WC_Order ) {
            return false;
        }

        return self::get_or_infer( $subscription ) > 1 && ! self::is_provisioned( $subscription );
    }
}
