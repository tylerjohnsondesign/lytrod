<?php
/**
 * WP-CLI commands for auditing and backfilling subscription payment methods.
 *
 * `wp lytrod-billing audit`    - read-only readiness report
 * `wp lytrod-billing backfill` - attach saved tokens; dry run unless --execute
 *
 * @package lytrod-auto-renewal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LAR_CLI {

	/**
	 * Report which subscriptions can and cannot be billed automatically.
	 *
	 * Read-only. Run before and after every change and diff the output.
	 *
	 * ## OPTIONS
	 *
	 * [--status=<status>]
	 * : Comma-separated subscription statuses. Default: active,on-hold
	 *
	 * [--only=<verdict>]
	 * : Show only rows with this verdict, e.g. BACKFILL
	 *
	 * [--format=<format>]
	 * : table, csv, json or count. Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp lytrod-billing audit
	 *     wp lytrod-billing audit --only=BACKFILL --format=csv
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function audit( $args, $assoc_args ) {
		$statuses = explode( ',', WP_CLI\Utils\get_flag_value( $assoc_args, 'status', 'active,on-hold' ) );
		$only     = WP_CLI\Utils\get_flag_value( $assoc_args, 'only', '' );
		$format   = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

		$rows = array();

		foreach ( self::get_subscriptions( $statuses ) as $subscription ) {
			$row = self::inspect( $subscription );

			if ( '' !== $only && $row['verdict'] !== $only ) {
				continue;
			}

			$rows[] = $row;
		}

		if ( empty( $rows ) ) {
			WP_CLI::success( 'No subscriptions matched.' );
			return;
		}

		WP_CLI\Utils\format_items(
			$format,
			$rows,
			array( 'id', 'status', 'manual', 'gateway', 'source', 'customer', 'token', 'token_type', 'next_payment', 'total', 'verdict', 'detail' )
		);

		// Only for human-readable output: appending this to csv/json would corrupt
		// the document for anything trying to parse it.
		if ( ! in_array( $format, array( 'csv', 'json' ), true ) ) {
			$counts = array_count_values( wp_list_pluck( $rows, 'verdict' ) );

			foreach ( $counts as $verdict => $count ) {
				WP_CLI::log( sprintf( '%-10s %d', $verdict, $count ) );
			}
		}
	}

	/**
	 * Attach each customer's saved Stripe token to their subscriptions.
	 *
	 * Dry run by default: pass --execute to write. Idempotent - a subscription that
	 * already has a payment method is skipped unless --replace is given.
	 *
	 * ## OPTIONS
	 *
	 * [--subscription=<ids>]
	 * : Comma-separated subscription IDs. Default: every eligible subscription.
	 *
	 * [--status=<status>]
	 * : Comma-separated subscription statuses. Default: active,on-hold
	 *
	 * [--execute]
	 * : Actually write the changes. Without this nothing is modified.
	 *
	 * [--replace]
	 * : Also re-point subscriptions that already have a Stripe payment method.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lytrod-billing backfill
	 *     wp lytrod-billing backfill --subscription=29889 --execute
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function backfill( $args, $assoc_args ) {
		$ids      = WP_CLI\Utils\get_flag_value( $assoc_args, 'subscription', '' );
		$statuses = explode( ',', WP_CLI\Utils\get_flag_value( $assoc_args, 'status', 'active,on-hold' ) );
		$execute  = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'execute', false );
		$replace  = (bool) WP_CLI\Utils\get_flag_value( $assoc_args, 'replace', false );

		$only_ids = array_filter( array_map( 'absint', explode( ',', (string) $ids ) ) );

		if ( ! $execute ) {
			WP_CLI::log( 'DRY RUN - nothing will be written. Re-run with --execute to apply.' );
		}

		$changed = 0;
		$skipped = 0;
		$failed  = 0;

		foreach ( self::get_subscriptions( $statuses ) as $subscription ) {
			if ( ! empty( $only_ids ) && ! in_array( $subscription->get_id(), $only_ids, true ) ) {
				continue;
			}

			$token = LAR_Token_Sync::get_default_stripe_token( $subscription->get_customer_id() );

			if ( ! $token ) {
				++$skipped;
				WP_CLI::log( sprintf( '#%d  SKIP  no saved Stripe token', $subscription->get_id() ) );
				continue;
			}

			$reason = LAR_Token_Sync::why_not_eligible( $subscription, $token, $replace );

			if ( '' !== $reason ) {
				++$skipped;
				WP_CLI::log( sprintf( '#%d  SKIP  %s', $subscription->get_id(), $reason ) );
				continue;
			}

			if ( ! $execute ) {
				++$changed;
				WP_CLI::log(
					sprintf(
						'#%d  WOULD ATTACH  token #%d (%s) %s',
						$subscription->get_id(),
						$token->get_id(),
						$token->get_type(),
						$token->get_token()
					)
				);
				continue;
			}

			$result = LAR_Token_Sync::attach( $subscription, $token, $replace );

			if ( is_wp_error( $result ) ) {
				++$failed;
				WP_CLI::warning( sprintf( '#%d  FAILED  %s', $subscription->get_id(), $result->get_error_message() ) );
				continue;
			}

			if ( $result ) {
				++$changed;
				WP_CLI::log( sprintf( '#%d  ATTACHED  token #%d (%s)', $subscription->get_id(), $token->get_id(), $token->get_type() ) );
			} else {
				++$skipped;
				WP_CLI::log( sprintf( '#%d  SKIP  already configured', $subscription->get_id() ) );
			}
		}

		$summary = sprintf( '%s: %d, skipped: %d, failed: %d', $execute ? 'attached' : 'would attach', $changed, $skipped, $failed );

		if ( $failed > 0 ) {
			WP_CLI::warning( $summary );
		} else {
			WP_CLI::success( $summary );
		}
	}

	/* ---------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Build one audit row for a subscription.
	 *
	 * @param WC_Subscription $subscription Subscription to inspect.
	 * @return array
	 */
	private static function inspect( $subscription ) {
		$token       = LAR_Token_Sync::get_default_stripe_token( $subscription->get_customer_id() );
		$source      = (string) $subscription->get_meta( '_stripe_source_id', true );
		$customer_id = LAR_Token_Sync::get_stripe_customer_id( $subscription );
		$gateway     = $subscription->get_payment_method();

		$configured = LAR_Token_Sync::GATEWAY_ID === $gateway && '' !== $source;

		if ( $configured ) {
			// Check this first. A subscription that is correctly set up stays correctly
			// set up even if we cannot resolve a token for its customer right now, and
			// reporting it as NO_TOKEN hid a real regression behind a reassuring row.
			$verdict = 'OK';
			$detail  = 'Already configured for automatic renewal.';
		} elseif ( ! $token ) {
			$verdict = 'NO_TOKEN';
			$detail  = 'Customer has no saved Stripe payment method.';
		} else {
			$reason = LAR_Token_Sync::why_not_eligible( $subscription, $token, false );

			if ( '' === $reason ) {
				$verdict = 'BACKFILL';
				$detail  = 'Can be attached to token #' . $token->get_id() . '.';
			} elseif ( LAR_Token_Sync::GATEWAY_ID === $gateway && '' === $source ) {
				// The dangerous middle state: looks configured in the admin, but the
				// off-session PaymentIntent goes out with no payment method and fails.
				$verdict = 'CONFLICT';
				$detail  = 'Gateway set but no _stripe_source_id - renewal would fail. Use --replace.';
			} else {
				$verdict = 'SKIP';
				$detail  = $reason;
			}
		}

		return array(
			'id'           => $subscription->get_id(),
			'status'       => $subscription->get_status(),
			'manual'       => $subscription->is_manual() ? 'yes' : 'no',
			'gateway'      => '' === $gateway ? '-' : $gateway,
			'source'       => '' === $source ? '-' : $source,
			'customer'     => '' === $customer_id ? '-' : $customer_id,
			'token'        => $token ? $token->get_token() : '-',
			'token_type'   => $token ? $token->get_type() : '-',
			'next_payment' => $subscription->get_date( 'next_payment' ) ? $subscription->get_date( 'next_payment' ) : '-',
			'total'        => $subscription->get_total(),
			'verdict'      => $verdict,
			'detail'       => $detail,
		);
	}

	/**
	 * Fetch subscriptions by status.
	 *
	 * @param array $statuses Statuses to include.
	 * @return WC_Subscription[]
	 */
	private static function get_subscriptions( $statuses ) {
		$statuses = array_filter( array_map( 'trim', $statuses ) );

		return wcs_get_subscriptions(
			array(
				'subscription_status'    => $statuses,
				'subscriptions_per_page' => -1,
			)
		);
	}
}
