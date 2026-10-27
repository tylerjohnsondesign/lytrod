<?php
/**
 * Restricts the variation dropdown on a subscription switch to the allowed group.
 *
 * The My Account "Add Years" / "Upgrade to Plus" links carry the permitted variation IDs
 * in ?variation_id=a,b,c. subscriptions-upgrader enforced that list in the browser: one
 * wp_footer script called $(option).hide() on everything else, and a second, 100ms later
 * and behind a 200ms setTimeout, selected "the first visible option".
 *
 * That is a race. On a cold first load - first login, nothing cached - WooCommerce's own
 * variations_form script finishes after the timer, re-rendering the options and undoing
 * the hiding, so the customer saw variations that were not on offer and the auto-select
 * landed on nothing valid, leaving checkout unreachable. A second attempt with warm
 * caches worked, which is exactly how it was reported. Hiding <option> elements is also
 * simply ignored in Safari.
 *
 * Filtering server-side means the disallowed variations are never rendered at all, so
 * there is nothing to race against and nothing to hide.
 *
 * @package lytrod-auto-renewal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LAR_Switch_Variations {

	public static function init() {
		add_filter( 'woocommerce_get_children', array( __CLASS__, 'filter_children' ), 20, 3 );
	}

	/**
	 * The variation IDs this switch request is allowed to offer.
	 *
	 * @return int[] Empty when this is not a gated switch request.
	 */
	public static function allowed_ids() {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$cache = array();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display filter; the switch nonce is verified by WooCommerce Subscriptions before the cart is touched.
		if ( empty( $_GET['switch-subscription'] ) || empty( $_GET['variation_id'] ) ) {
			return $cache;
		}

		$subscription_id = absint( wp_unslash( $_GET['switch-subscription'] ) );
		$raw             = sanitize_text_field( wp_unslash( $_GET['variation_id'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Only ever narrow the list for a subscription the shopper actually owns, so a
		// crafted URL cannot be used to probe the catalogue.
		if ( ! $subscription_id || ! function_exists( 'lytrod_user_owns_subscription' ) || ! lytrod_user_owns_subscription( $subscription_id ) ) {
			return $cache;
		}

		$cache = array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );

		return $cache;
	}

	/**
	 * @param int[]      $children Variation IDs.
	 * @param WC_Product $product  Parent product.
	 * @param bool       $visible  Whether only visible children were requested.
	 * @return int[]
	 */
	public static function filter_children( $children, $product = null, $visible = false ) {
		return self::restrict( $children );
	}

	/**
	 * Intersect a child list with the allowed IDs.
	 *
	 * @param mixed $children Variation IDs.
	 * @return mixed
	 */
	private static function restrict( $children ) {
		$allowed = self::allowed_ids();

		if ( empty( $allowed ) || ! is_array( $children ) ) {
			return $children;
		}

		$filtered = array_values( array_intersect( array_map( 'absint', $children ), $allowed ) );

		// Never hand back an empty list: a product with no variations renders as broken.
		// If the allowlist and the product disagree, show the product unfiltered rather
		// than a dead page.
		return empty( $filtered ) ? $children : $filtered;
	}
}
