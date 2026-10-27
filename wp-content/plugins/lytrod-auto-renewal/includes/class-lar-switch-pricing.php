<?php
/**
 * Forces full price on "Add Years" subscription switches.
 *
 * Background: both My Account actions ("Add Years" and "Upgrade to Plus") are ordinary
 * WooCommerce Subscriptions switches, so the switch engine prorates both of them. That is
 * wrong for Add Years, where the purchased years are simply appended to the customer's
 * existing expiry date and nothing is being given up.
 *
 * Two WCS mechanisms were discounting it:
 *
 *   1. WCS_Switch_Totals_Calculator::apportion_sign_up_fees() charges
 *      max($sign_up_fee_due - $sign_up_fee_paid, 0). Lytrod's multi-year price lives
 *      entirely in the sign-up fee (2 Years = 1750 while the recurring price stays 1000),
 *      so this credited the fee already paid. Order items still carry the evidence in
 *      _switched_subscription_sign_up_fee_prorated: 750 charged against a 1750 fee on
 *      order 29551, 2000 against 3000 on 28998. It stopped only because the site option
 *      woocommerce_subscriptions_apportion_sign_up_fee happens to read 'full'; nothing in
 *      code asserted it, so one save of the Subscriptions settings screen would bring it back.
 *
 *   2. WCS classifies a switch by price per day alone, and deliberately excludes the
 *      sign-up fee from that comparison - which is exactly where the Lytrod price lives.
 *      So Add Years reads as a downgrade or crossgrade, triggers extend_prepaid_term(),
 *      and maybe_set_free_trial() then leaves nothing due now.
 *
 * Either way the customer was shown a reduced figure and then charged the full amount,
 * because add_prorated_fee_after_order_creation() in subscriptions-upgrader rewrites the
 * order on woocommerce_checkout_order_processed - after the customer agrees a price but
 * before the gateway is handed an amount.
 *
 * Note there is a long-standing `add_filter('wcs_calculate_prorated_totals','__return_false')`
 * in subscriptions-upgrader which was intended to do this job. No such filter exists in
 * WooCommerce Subscriptions; it has never had any effect. It is removed alongside this class.
 *
 * @package lytrod-auto-renewal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LAR_Switch_Pricing {

	public static function init() {
		// All three gate the proration paths in WCS_Switch_Totals_Calculator. Pinning only
		// the recurring-price one would leave the sign-up fee exposed the moment the site
		// option changes, so assert all of them in code.
		add_filter( 'wcs_switch_should_prorate_recurring_price', array( __CLASS__, 'filter_should_prorate' ), 10, 2 );
		add_filter( 'wcs_switch_should_prorate_sign_up_fee', array( __CLASS__, 'filter_should_prorate' ), 10, 2 );
		add_filter( 'wcs_switch_should_extend_prepaid_term', array( __CLASS__, 'filter_should_prorate' ), 10, 2 );

		// apportion_length() runs OUTSIDE the proration gate above, so it needs pinning
		// separately. It is inert while a variation's _subscription_length is 0, which is
		// true of every Add Years variation except 26807 ("VRCut - 2 Years", length 2 -
		// a data-entry slip). Left unpinned, that one variation would be given a real
		// length and therefore an end date, stopping the licence instead of extending it.
		add_filter( 'wcs_switch_should_prorate_length', array( __CLASS__, 'filter_should_prorate' ), 10, 2 );

		// Pin the amount due today. Turning proration off is NOT sufficient on its own:
		// WCS_Switch_Totals_Calculator::calculate_prorated_totals() sets
		// first_payment_timestamp on line 91, OUTSIDE the proration gate, so
		// maybe_set_free_trial() still mocks a 1-period trial on the cart product. With a
		// trial present, set_subscription_prices_for_calculation() returns the SIGN-UP FEE
		// alone - which is 0 on the one-year variations (26481, 26806, 26819, 27704),
		// giving a $0 cart. Pinning the price closes that, and also forecloses the
		// opposite case (fee + recurring) on a subscription with no next_payment date.
		add_filter( 'woocommerce_subscriptions_cart_get_price', array( __CLASS__, 'filter_due_today_price' ), 20, 2 );

		// Drop WooCommerce Subscriptions' "(Upgrade)/(Downgrade)/(Crossgrade)" tag from
		// the cart line. It is derived purely from a price-per-day comparison that
		// excludes sign-up fees - which is where this store's entire price lives - so it
		// routinely labels a Plus upgrade a "Crossgrade". It is also jargon to a customer
		// buying a licence. Removed on wp_loaded so WCS has finished registering it.
		add_action( 'wp_loaded', array( __CLASS__, 'remove_switch_direction_label' ), 20 );

		// Apply the purchased years server-side, before the switch email is sent.
		// Priority 5: after WC_Subscriptions_Switcher::complete_subscription_switches()
		// has written its own dates, and before
		// WC_Subscriptions_Email::send_switch_order_email() at priority 10.
		add_action( 'woocommerce_subscriptions_switch_completed', array( __CLASS__, 'apply_switch_dates' ), 5, 1 );
	}

	/**
	 * Apply an "Add Years" purchase to the subscription's dates.
	 *
	 * subscriptions-upgrader works the target date out at checkout and stores it on the
	 * order as `calculated_end_date`, but only ever applied it to the subscription from
	 * redirect_after_order() on `woocommerce_thankyou`. WooCommerce Subscriptions
	 * overwrites the dates during the status transition, so the thank-you page was the
	 * only thing putting them back - and it does not run if the customer closes the tab,
	 * or if the gateway returns them anywhere else.
	 *
	 * Measured before this fix: 9 of 38 switch orders, $8,800 of purchases, never
	 * received the extension at all. Order #30203 is the clearest case - the order says
	 * 2028-10-19, the subscription stayed on 2027-10-19 after a $1,000 payment.
	 *
	 * Running here also means the licence dates are correct before the switch email
	 * renders, which is what made that email quote the old expiry.
	 *
	 * @param WC_Order|int $order Switch order.
	 * @return void
	 */
	public static function apply_switch_dates( $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order || ! function_exists( 'wcs_get_subscriptions_for_switch_order' ) ) {
			return;
		}

		$target = (string) $order->get_meta( 'calculated_end_date', true );

		if ( '' === $target ) {
			return;
		}

		$target_time = strtotime( $target );

		if ( ! $target_time ) {
			return;
		}

		foreach ( wcs_get_subscriptions_for_switch_order( $order ) as $subscription ) {
			// Only ever move a licence forward. A tier upgrade keeps the existing expiry
			// and must not be pushed out, and a replayed hook must not stack years.
			if ( (int) $subscription->get_time( 'next_payment' ) >= $target_time ) {
				continue;
			}

			$expiration = gmdate( 'Y-m-d H:i:s', $target_time );

			try {
				// Same shape as redirect_after_order(): next_payment sits five minutes
				// past the expiry so the two never collide.
				$subscription->update_dates(
					array(
						'trial_end'    => '',
						'next_payment' => gmdate( 'Y-m-d H:i:s', $target_time + ( 5 * MINUTE_IN_SECONDS ) ),
						'end'          => '',
					)
				);
			} catch ( Exception $e ) {
				$subscription->add_order_note( 'Could not apply purchased years: ' . $e->getMessage() );
				continue;
			}

			$subscription->update_meta_data( 'expiration_date', $expiration );
			$subscription->update_meta_data( 'calculated_end_date', $expiration );
			$subscription->update_meta_data( '_custom_expiration_date', $expiration );
			$subscription->save();

			$subscription->add_order_note(
				sprintf( 'Licence extended to %s following switch order #%d.', $expiration, $order->get_id() )
			);
		}
	}

	/**
	 * Unhook the switch-direction label from the cart line subtotal.
	 *
	 * @return void
	 */
	public static function remove_switch_direction_label() {
		remove_filter(
			'woocommerce_cart_item_subtotal',
			array( 'WC_Subscriptions_Switcher', 'add_cart_item_switch_direction' ),
			10
		);
	}

	/**
	 * Force the amount due today on an Add Years switch to the variation's full price.
	 *
	 * Only reached when WC_Subscriptions_Cart::$calculation_type is 'none' - the up-front
	 * cart total. The recurring carts are calculated as 'recurring_total' and never reach
	 * this filter, so the renewal price stays at the variation's own rate.
	 *
	 * @param float      $price   Price WCS calculated.
	 * @param WC_Product $product The in-cart product instance.
	 * @return float
	 */
	public static function filter_due_today_price( $price, $product ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || ! is_a( $product, 'WC_Product' ) ) {
			return $price;
		}

		foreach ( WC()->cart->cart_contents as $cart_item ) {
			if ( empty( $cart_item['subscription_switch']['subscription_id'] ) || empty( $cart_item['data'] ) ) {
				continue;
			}

			// Identity, not ID: this filter also fires for price HTML elsewhere in the
			// same request, and we must only touch the object attached to the cart item.
			if ( $cart_item['data'] !== $product ) {
				continue;
			}

			$subscription = wcs_get_subscription( $cart_item['subscription_switch']['subscription_id'] );
			$variation_id = ! empty( $cart_item['variation_id'] ) ? (int) $cart_item['variation_id'] : (int) $cart_item['product_id'];

			// Plus upgrades are prorated. Show the figure that will actually be
			// charged: previously the cart printed the undiscounted amount while the
			// card was charged custom_prorated_fee, so the order review, the bespoke
			// prorated table and the Place Order button all disagreed.
			if ( self::is_tier_upgrade( $subscription, $variation_id ) ) {
				$prorated = self::plus_prorated_fee( $subscription, $variation_id );

				return null === $prorated ? $price : $prorated;
			}

			$full = self::get_full_switch_price( $variation_id );

			return null === $full ? $price : $full;
		}

		return $price;
	}

	/**
	 * The prorated uplift for a Base -> Plus upgrade.
	 *
	 * One rule for every product: the customer pays the per-year difference for the
	 * time they will actually have Plus, and nothing for time already elapsed.
	 *
	 *     fee = (days_remaining / 365) x upgrade_fee_per_year
	 *
	 * This replaces two branches in subscriptions-upgrader's
	 * apply_prorated_upgrade_fee_on_plus_variation(), which were wrong in two ways
	 * whenever more than a year remained:
	 *
	 *   - the denominator was the whole billing period rather than a year, and that
	 *     period is multi-year once a customer has bought Add Years, so the ratio
	 *     under-counted by roughly the number of years in the term. BizcardCut
	 *     #29375 quoted 480/730 x $60 = $39.45 against a correct $79.20.
	 *   - min($fee, $full_upgrade_fee) capped the charge at a single year's uplift,
	 *     so three years remaining still cost one year.
	 *
	 * Which branch ran depended on the CURRENT variation's sign-up fee, which has
	 * nothing to do with proration - hence Intellicut being right and BizcardCut
	 * wrong for the same operation.
	 *
	 * @param WC_Subscription $subscription        Subscription being upgraded.
	 * @param int             $target_variation_id Plus variation being switched to.
	 * @return float|null Null when it cannot be computed.
	 */
	public static function plus_prorated_fee( $subscription, $target_variation_id ) {
		if ( ! $subscription instanceof WC_Subscription || ! wc_get_product( $target_variation_id ) ) {
			return null;
		}

		$fee_per_year = (float) WC_Subscriptions_Product::get_sign_up_fee( $target_variation_id );

		if ( $fee_per_year <= 0 ) {
			return null;
		}

		$expiry = (int) $subscription->get_time( 'next_payment' );

		if ( $expiry <= 0 ) {
			$expiry = (int) $subscription->get_time( 'end' );
		}

		$remaining = $expiry - (int) current_time( 'timestamp', true );

		// Hand the decision back to WooCommerce Subscriptions, which quotes the full fee.
		// Returning 0 here would price a Plus upgrade at nothing for any subscription whose
		// expiry has already passed - 63 of them today. WCS only allows an active
		// subscription to be switched (is_action_allowed(), line 1112), so this is not
		// reachable now; it is pinned so no future change can turn it into free Plus.
		if ( $remaining <= 0 ) {
			return null;
		}

		$years_remaining = ( $remaining / DAY_IN_SECONDS ) / 365;

		return round( $years_remaining * $fee_per_year, 2 );
	}

	/**
	 * The full, un-prorated amount for one unit of a variation.
	 *
	 * Multi-year variations carry the whole amount in the sign-up fee and keep the
	 * one-year rate in _subscription_price; the one-year variations carry it in the
	 * price with no fee. Same rule add_prorated_fee_after_order_creation() applied when
	 * it rewrote the order, i.e. the amount every completed Add Years order was charged.
	 *
	 * @param int $variation_id Variation ID.
	 * @return float|null
	 */
	public static function get_full_switch_price( $variation_id ) {
		if ( ! $variation_id || ! wc_get_product( $variation_id ) ) {
			return null;
		}

		$fee = (float) WC_Subscriptions_Product::get_sign_up_fee( $variation_id );

		return $fee > 0 ? $fee : (float) WC_Subscriptions_Product::get_price( $variation_id );
	}

	/**
	 * Allow proration only for a genuine Base -> Plus tier change.
	 *
	 * @param bool                 $should_prorate WCS's own answer, from the store settings.
	 * @param WCS_Switch_Cart_Item $switch_item    The switch being priced.
	 * @return bool
	 */
	public static function filter_should_prorate( $should_prorate, $switch_item ) {
		// Never turn proration ON where WCS had it off; only ever narrow it.
		if ( ! $should_prorate ) {
			return false;
		}

		if ( ! is_object( $switch_item ) || empty( $switch_item->subscription ) || empty( $switch_item->product ) ) {
			return $should_prorate;
		}

		return self::is_tier_upgrade( $switch_item->subscription, $switch_item->product->get_id() );
	}

	/**
	 * Whether a variation belongs to the Plus tier.
	 *
	 * Read from the variation's `choose` attribute, which is an unambiguous discriminator
	 * across every variation on this site: the Plus ones are "Upgrade to Plus" and the
	 * "<product> Plus N year(s)" set, and nothing in the base set contains the word. It
	 * correctly separates "Intellicut AeroCut nano Edition 1 Year" from
	 * "Intellicut AeroCut nano Edition Plus 1 Year".
	 *
	 * @param int $variation_id Variation ID.
	 * @return bool
	 */
	public static function is_plus_tier( $variation_id ) {
		if ( ! $variation_id ) {
			return false;
		}

		$variation = wc_get_product( $variation_id );

		if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
			return false;
		}

		return false !== stripos( (string) $variation->get_attribute( 'choose' ), 'plus' );
	}

	/**
	 * Whether a switch moves the customer from Base up to Plus.
	 *
	 * An existing Plus customer buying "Plus 3 Years" is adding years at the tier they are
	 * already on, not upgrading, so that is full price like any other Add Years.
	 *
	 * @param WC_Subscription $subscription        The subscription being switched.
	 * @param int             $target_variation_id The variation being switched to.
	 * @return bool
	 */
	public static function is_tier_upgrade( $subscription, $target_variation_id ) {
		if ( ! $subscription instanceof WC_Subscription ) {
			return false;
		}

		if ( ! self::is_plus_tier( $target_variation_id ) ) {
			return false;
		}

		foreach ( $subscription->get_items() as $item ) {
			if ( self::is_plus_tier( $item->get_variation_id() ) ) {
				return false;
			}
		}

		return true;
	}
}
