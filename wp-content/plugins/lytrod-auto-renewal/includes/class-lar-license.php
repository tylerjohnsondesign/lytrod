<?php
/**
 * Extends the custom licence expiry meta when a subscription renewal is paid.
 *
 * Background: the licence dates (expiration_date, calculated_end_date,
 * _custom_expiration_date, switch_base_date) were only ever advanced by
 * Subscriptions_Upgrader_Public::redirect_after_order(), which hangs off
 * 'woocommerce_thankyou'. That fires when a customer completes checkout in a
 * browser - it does NOT fire for a renewal, automatic or manual. So every renewal
 * left the licence dates untouched while WooCommerce Subscriptions advanced
 * next_payment, and the two drifted a year apart. Verified on live data: sub 29949
 * renewed 2026-09-09, next_payment 2027-09-10, expiration_date still 2026-09-09.
 *
 * 'woocommerce_subscription_renewal_payment_complete' fires for both automatic
 * charges and customer-paid renewal orders, so this fixes the existing manual
 * renewals as well as enabling automatic ones.
 *
 * @package lytrod-auto-renewal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LAR_License {

	/** Meta key recording the renewal order this subscription was last extended for. */
	const GUARD_META = '_lar_license_extended_order';

	public static function init() {
		add_action( 'woocommerce_subscription_renewal_payment_complete', array( __CLASS__, 'extend_on_renewal' ), 10, 2 );
	}

	/**
	 * Advance the licence dates by one billing cycle.
	 *
	 * @param WC_Subscription $subscription The subscription that was renewed.
	 * @param WC_Order        $last_order   The renewal order that was paid.
	 */
	public static function extend_on_renewal( $subscription, $last_order ) {
		if ( ! $subscription instanceof WC_Subscription || ! $last_order instanceof WC_Order ) {
			return;
		}

		$order_id = $last_order->get_id();

		// payment_complete_for_order() can be reached more than once for the same
		// order (the gateway completing the payment, then a Stripe webhook for the
		// same intent). Without this guard a single renewal would add two years.
		if ( (int) $subscription->get_meta( self::GUARD_META, true ) === (int) $order_id ) {
			return;
		}

		$base = self::get_base_date( $subscription );

		if ( ! $base ) {
			$subscription->add_order_note( 'Licence expiry not extended: no existing expiry date could be determined.' );
			return;
		}

		$old_expiry = $base->format( 'Y-m-d H:i:s' );

		// Never hand back a licence that is already partly spent: a renewal paid
		// late starts from today, not from the lapsed date. Same clamp the
		// resubscribe flow uses in class-subscriptions-upgrader-public.php.
		$now = new DateTime( current_time( 'Y-m-d' ) );

		if ( $base < $now ) {
			$base = $now;
		}

		$modifier = self::get_cycle_modifier( $subscription );

		if ( ! $modifier ) {
			$subscription->add_order_note( 'Licence expiry not extended: unrecognised billing period.' );
			return;
		}

		$expiration = clone $base;
		$expiration->modify( $modifier );

		// calculated_end_date is expiry + 1 day. This is the dominant convention in
		// the existing data (148 of 189 subscriptions) and matches the resubscribe flow.
		$end_date = clone $expiration;
		$end_date->modify( '+1 day' );

		$expiration_formatted = $expiration->format( 'Y-m-d H:i:s' );

		$subscription->update_meta_data( 'expiration_date', $expiration_formatted );
		$subscription->update_meta_data( 'calculated_end_date', $end_date->format( 'Y-m-d H:i:s' ) );
		$subscription->update_meta_data( 'switch_base_date', $expiration_formatted );

		// _custom_expiration_date is stored date-only on some subscriptions and as a
		// datetime on others. Preserve whichever shape this subscription already uses
		// so nothing downstream has to cope with a format change.
		$existing_custom = (string) $subscription->get_meta( '_custom_expiration_date', true );
		$custom_format   = ( '' !== $existing_custom && false === strpos( $existing_custom, ':' ) ) ? 'Y-m-d' : 'Y-m-d H:i:s';
		$subscription->update_meta_data( '_custom_expiration_date', $expiration->format( $custom_format ) );

		$subscription->update_meta_data( self::GUARD_META, $order_id );
		$subscription->save();

		$subscription->add_order_note(
			sprintf(
				'Licence expiry extended %s to %s following renewal order #%d.',
				$old_expiry,
				$expiration_formatted,
				$order_id
			)
		);
	}

	/**
	 * Resolve the date the licence currently runs to.
	 *
	 * Mirrors the fallback chain already used by the resubscribe flow so both paths
	 * agree on what "the current expiry" means.
	 *
	 * @param WC_Subscription $subscription Subscription to inspect.
	 * @return DateTime|null
	 */
	private static function get_base_date( $subscription ) {
		$candidates = array(
			(string) $subscription->get_meta( 'expiration_date', true ),
			(string) $subscription->get_meta( '_custom_expiration_date', true ),
			(string) $subscription->get_meta( '_schedule_trial_end', true ),
		);

		foreach ( $candidates as $candidate ) {
			if ( '' !== $candidate && '0' !== $candidate ) {
				$timestamp = strtotime( $candidate );

				if ( $timestamp ) {
					// Time component stripped deliberately - the existing flow does the
					// same to avoid hour-level drift accumulating across renewals.
					return new DateTime( gmdate( 'Y-m-d', $timestamp ) );
				}
			}
		}

		$next_payment = $subscription->get_date( 'next_payment' );

		if ( $next_payment ) {
			$timestamp = strtotime( $next_payment );

			if ( $timestamp ) {
				return new DateTime( gmdate( 'Y-m-d', $timestamp ) );
			}
		}

		return null;
	}

	/**
	 * Build a DateTime::modify() string for one billing cycle.
	 *
	 * Taken from the subscription's own schedule rather than parsing product
	 * attribute names. A renewal always buys exactly one billing period; the
	 * "2 Years"/"3 Years" attribute ladders elsewhere in this codebase describe
	 * multi-year *purchases*, which is a different thing.
	 *
	 * @param WC_Subscription $subscription Subscription to inspect.
	 * @return string|null
	 */
	private static function get_cycle_modifier( $subscription ) {
		$period   = $subscription->get_billing_period();
		$interval = (int) $subscription->get_billing_interval();

		if ( $interval < 1 ) {
			$interval = 1;
		}

		$allowed = array( 'day', 'week', 'month', 'year' );

		if ( ! in_array( $period, $allowed, true ) ) {
			return null;
		}

		return sprintf( '+%d %s', $interval, $period );
	}
}
