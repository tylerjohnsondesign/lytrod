<?php
/**
 * Keeps a customer's saved card and their subscriptions pointing at each other.
 *
 * The gap this closes: on this store a machine purchase includes a free first year,
 * so the cart total is £0/$0 at checkout and WooCommerce Subscriptions creates the
 * subscription with no payment method and _requires_manual_renewal = true
 * (class-wc-subscriptions-checkout.php, "if ( ! $cart->needs_payment() )"). If the
 * customer later saves a card on their account, nothing ever connected that token to
 * the subscription - WCS only reads the subscription's own payment meta, never the
 * account's token list. So "customer has a card on file" and "subscription will
 * auto-bill" were unrelated facts, and every renewal fell through to the manual path.
 *
 * @package lytrod-auto-renewal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LAR_Token_Sync {

	const GATEWAY_ID = 'stripe';

	public static function init() {
		add_action( 'woocommerce_new_payment_token', array( __CLASS__, 'on_token_added' ), 10, 2 );
		add_action( 'woocommerce_payment_token_set_default', array( __CLASS__, 'on_token_set_default' ), 10, 2 );
		add_action( 'woocommerce_payment_token_deleted', array( __CLASS__, 'on_token_deleted' ), 10, 2 );
	}

	/* ---------------------------------------------------------------------
	 * Hooks
	 * ------------------------------------------------------------------ */

	/**
	 * A customer saved a new card: adopt it for any subscription that has none.
	 *
	 * @param int              $token_id Token ID.
	 * @param WC_Payment_Token $token    Token object.
	 */
	public static function on_token_added( $token_id, $token ) {
		if ( ! $token instanceof WC_Payment_Token ) {
			return;
		}

		foreach ( self::get_customer_subscriptions( $token->get_user_id() ) as $subscription ) {
			// Only fill a gap here. An existing, working payment method is left alone;
			// changing it is the customer's explicit choice, handled by set_default.
			if ( '' !== $subscription->get_payment_method() ) {
				continue;
			}

			self::attach( $subscription, $token );
		}
	}

	/**
	 * A customer changed their default card: move subscriptions onto it.
	 *
	 * @param int              $token_id Token ID.
	 * @param WC_Payment_Token $token    Token object.
	 */
	public static function on_token_set_default( $token_id, $token ) {
		if ( ! $token instanceof WC_Payment_Token ) {
			return;
		}

		foreach ( self::get_customer_subscriptions( $token->get_user_id() ) as $subscription ) {
			$current = $subscription->get_payment_method();

			// Don't hijack a subscription paying through some other gateway.
			if ( '' !== $current && self::GATEWAY_ID !== $current ) {
				continue;
			}

			self::attach( $subscription, $token, true );
		}
	}

	/**
	 * A token was deleted: re-point or cleanly revert any subscription using it.
	 *
	 * Without this a deleted card leaves the subscription pointing at a payment
	 * method Stripe no longer has, and the renewal fails months later with
	 * no_such_payment_method and no indication why.
	 *
	 * @param int              $token_id Token ID.
	 * @param WC_Payment_Token $token    Token object.
	 */
	public static function on_token_deleted( $token_id, $token ) {
		if ( ! $token instanceof WC_Payment_Token ) {
			return;
		}

		$deleted_value = $token->get_token();
		$user_id       = $token->get_user_id();
		$replacement   = self::get_default_stripe_token( $user_id );

		foreach ( self::get_customer_subscriptions( $user_id ) as $stale ) {
			// Re-read rather than trusting the object the query handed us. Replacing a
			// card fires token-added and token-deleted in the same request, so by the
			// time we get here the subscription may already have been re-pointed at the
			// new token and the cached copy would still show the old source.
			$subscription = wcs_get_subscription( $stale->get_id() );

			if ( ! $subscription ) {
				continue;
			}

			if ( (string) $subscription->get_meta( '_stripe_source_id', true ) !== (string) $deleted_value ) {
				continue;
			}

			if ( $replacement && $replacement->get_token() !== $deleted_value ) {
				self::attach( $subscription, $replacement, true );
				continue;
			}

			// Nothing left to charge with. Say so on the subscription rather than
			// leaving it pointing at a dead payment method.
			$subscription->set_payment_method( '' );
			$subscription->delete_meta_data( '_stripe_source_id' );
			$subscription->save();
			$subscription->add_order_note(
				'Saved payment method was deleted and no replacement is on file. This subscription has been set back to manual renewal.'
			);
		}
	}

	/* ---------------------------------------------------------------------
	 * Core
	 * ------------------------------------------------------------------ */

	/**
	 * Point a subscription at a saved token so WooCommerce Subscriptions can charge it.
	 *
	 * @param WC_Subscription  $subscription Subscription to update.
	 * @param WC_Payment_Token $token        Token to attach.
	 * @param bool             $replace      Allow replacing an existing Stripe method.
	 * @return bool|WP_Error True on change, false when skipped, WP_Error on failure.
	 */
	public static function attach( $subscription, $token, $replace = false ) {
		$reason = self::why_not_eligible( $subscription, $token, $replace );

		if ( $reason ) {
			return false;
		}

		$gateway = self::get_gateway();

		if ( ! $gateway ) {
			return new WP_Error( 'lar_no_gateway', 'The Stripe gateway is not available.' );
		}

		$customer_id = self::get_stripe_customer_id( $subscription );
		$source_id   = $token->get_token();

		// Already set up exactly this way - nothing to do, and re-saving would add a
		// pointless order note every time a customer touches their payment methods.
		if ( self::GATEWAY_ID === $subscription->get_payment_method()
			&& (string) $subscription->get_meta( '_stripe_source_id', true ) === (string) $source_id
			&& (string) $subscription->get_meta( '_stripe_customer_id', true ) === (string) $customer_id
		) {
			return false;
		}

		try {
			// set_payment_method() also clears _requires_manual_renewal, because
			// wcs_is_manual_renewal_required() is false on this store and the Stripe
			// gateway supports 'subscriptions'. Setting that meta by hand would work
			// today and silently rot if either of those ever changes.
			$subscription->set_payment_method(
				$gateway,
				array(
					'post_meta' => array(
						'_stripe_customer_id' => array( 'value' => $customer_id ),
						'_stripe_source_id'   => array( 'value' => $source_id ),
					),
				)
			);
			$subscription->save();
		} catch ( Exception $e ) {
			// Stripe validates the meta shape and throws on a bad customer/source id.
			return new WP_Error( 'lar_attach_failed', $e->getMessage() );
		}

		$subscription->add_order_note(
			sprintf(
				'Automatic renewal enabled: payment method set to saved token #%d (%s) for Stripe customer %s.',
				$token->get_id(),
				$token->get_type(),
				$customer_id
			)
		);

		return true;
	}

	/**
	 * Explain why a subscription/token pair cannot be linked.
	 *
	 * Returns a human-readable reason, or an empty string when the pair is eligible.
	 * Shared by attach() and the CLI audit so the dry run cannot disagree with what
	 * the real thing would do.
	 *
	 * @param WC_Subscription  $subscription Subscription to check.
	 * @param WC_Payment_Token $token        Token to check.
	 * @param bool             $replace      Allow replacing an existing Stripe method.
	 * @return string
	 */
	public static function why_not_eligible( $subscription, $token, $replace = false ) {
		if ( ! $subscription instanceof WC_Subscription ) {
			return 'Not a subscription.';
		}

		if ( ! $token instanceof WC_Payment_Token ) {
			return 'No saved payment token.';
		}

		if ( ! $subscription->has_status( array( 'active', 'on-hold' ) ) ) {
			return 'Subscription is ' . $subscription->get_status() . '.';
		}

		// A resubscribe leaves the old subscription behind, often still on-hold with an unpaid
		// renewal order. Billing it would charge the customer for a licence they already replaced.
		$replaced_by = (int) $subscription->get_meta( 'replaced_by_subscription_id', true );

		if ( $replaced_by ) {
			return 'Subscription was replaced by #' . $replaced_by . '.';
		}

		if ( (float) $subscription->get_total( 'edit' ) <= 0 ) {
			return 'Subscription total is zero.';
		}

		if ( self::GATEWAY_ID !== $token->get_gateway_id() ) {
			return 'Token belongs to gateway "' . $token->get_gateway_id() . '".';
		}

		if ( (int) $token->get_user_id() !== (int) $subscription->get_customer_id() ) {
			return 'Token belongs to a different customer.';
		}

		$current = $subscription->get_payment_method();

		if ( '' !== $current && ! $replace ) {
			return 'Subscription already uses "' . $current . '".';
		}

		if ( '' !== $current && self::GATEWAY_ID !== $current ) {
			return 'Subscription uses another gateway ("' . $current . '").';
		}

		$source_id = (string) $token->get_token();

		// Matches WC_Stripe_Subscriptions_Trait::validate_subscription_payment_meta(),
		// which will throw on anything else.
		if ( 0 !== strpos( $source_id, 'pm_' ) && 0 !== strpos( $source_id, 'src_' ) && 0 !== strpos( $source_id, 'card_' ) ) {
			return 'Token value "' . $source_id . '" is not a usable Stripe payment method id.';
		}

		$customer_id = self::get_stripe_customer_id( $subscription );

		if ( '' === $customer_id || 0 !== strpos( $customer_id, 'cus_' ) ) {
			return 'No valid Stripe customer id for this customer.';
		}

		return '';
	}

	/* ---------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Resolve the Stripe customer id for a subscription.
	 *
	 * Same order of preference the gateway itself uses in
	 * WC_Stripe_Payment_Gateway::get_stripe_customer_id(): the order's own meta
	 * first, then the user option.
	 *
	 * @param WC_Subscription $subscription Subscription to inspect.
	 * @return string
	 */
	public static function get_stripe_customer_id( $subscription ) {
		$customer_id = (string) $subscription->get_meta( '_stripe_customer_id', true );

		if ( '' === $customer_id ) {
			$customer_id = (string) get_user_option( '_stripe_customer_id', $subscription->get_customer_id() );
		}

		return $customer_id;
	}

	/**
	 * The customer's default Stripe token, preferring a real card over a wallet.
	 *
	 * A plain card is the safest thing to bill off-session; wallet-backed methods
	 * such as Link are only chargeable when a reusable mandate was captured, which
	 * cannot be determined from WordPress.
	 *
	 * @param int $user_id Customer ID.
	 * @return WC_Payment_Token|null
	 */
	public static function get_default_stripe_token( $user_id ) {
		$tokens = WC_Payment_Tokens::get_customer_tokens( $user_id, self::GATEWAY_ID );

		if ( empty( $tokens ) ) {
			return null;
		}

		$default = null;
		$card    = null;
		$first   = null;

		foreach ( $tokens as $token ) {
			if ( ! $first ) {
				$first = $token;
			}

			if ( 'CC' === $token->get_type() && ! $card ) {
				$card = $token;
			}

			if ( $token->is_default() && ! $default ) {
				$default = $token;
			}
		}

		// Preference order, most to least desirable. The final fallback matters:
		// a customer whose only saved method is a non-default wallet token (a Stripe
		// Link token with is_default = 0, say) still has a perfectly chargeable
		// payment method. Returning null for them previously read as "no card on
		// file", which reverted live subscriptions to manual renewal and made the
		// audit report NO_TOKEN for customers who plainly had one.
		if ( $default && 'CC' === $default->get_type() ) {
			return $default;
		}

		if ( $card ) {
			return $card;
		}

		if ( $default ) {
			return $default;
		}

		return $first;
	}

	/**
	 * Active and on-hold subscriptions belonging to a customer.
	 *
	 * @param int $user_id Customer ID.
	 * @return WC_Subscription[]
	 */
	public static function get_customer_subscriptions( $user_id ) {
		if ( ! $user_id || ! function_exists( 'wcs_get_users_subscriptions' ) ) {
			return array();
		}

		$subscriptions = array();

		foreach ( wcs_get_users_subscriptions( $user_id ) as $subscription ) {
			if ( $subscription->has_status( array( 'active', 'on-hold' ) ) ) {
				$subscriptions[] = $subscription;
			}
		}

		return $subscriptions;
	}

	/**
	 * The Stripe gateway instance, or null when unavailable.
	 *
	 * @return WC_Payment_Gateway|null
	 */
	public static function get_gateway() {
		if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
			return null;
		}

		$gateways = WC()->payment_gateways()->payment_gateways();

		return isset( $gateways[ self::GATEWAY_ID ] ) ? $gateways[ self::GATEWAY_ID ] : null;
	}
}
