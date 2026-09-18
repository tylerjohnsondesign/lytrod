<?php
/**
 * WP-CLI: build the new catalogue, then migrate onto it.
 *
 * Nothing here writes anything a `--dry-run` has not first reported, and the migration refuses
 * to move a price or a renewal date.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * `wp lytrod-licensing`
 */
class Lytrod_Licensing_CLI {

    /**
     * Line-item meta holding everything the migration overwrote, for `--revert`.
     */
    const META_SNAPSHOT = '_lytrod_pre_migration';

    /**
     * Register the command.
     *
     * @return void
     */
    public static function init(): void {
        WP_CLI::add_command( 'lytrod-licensing', __CLASS__ );
    }

    /**
     * Put migrated subscriptions back exactly as they were.
     *
     * Restores from the snapshot the migration recorded on each line item: product, variation,
     * quantity, subtotal, total, billing schedule, dates and the order total.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Report only. Default.
     *
     * [--write]
     * : Perform the rollback.
     *
     * @param array $args       Positional args.
     * @param array $assoc_args Flags.
     * @return void
     */
    public function revert( $args, $assoc_args ): void {
        $write = isset( $assoc_args['write'] );
        $rows  = array();

        $subscriptions = wcs_get_subscriptions(
            array(
                'subscriptions_per_page' => -1,
                'subscription_status'    => 'any',
            )
        );

        foreach ( $subscriptions as $subscription ) {
            $restored = false;

            foreach ( $subscription->get_items() as $item ) {
                if ( ! $item instanceof WC_Order_Item_Product ) {
                    continue;
                }

                $snapshot = $item->get_meta( self::META_SNAPSHOT, true );

                if ( ! is_array( $snapshot ) || empty( $snapshot['product_id'] ) ) {
                    continue;
                }

                $rows[] = array(
                    'subscription' => $subscription->get_id(),
                    'from_product' => $item->get_product_id(),
                    'to_product'   => $snapshot['product_id'],
                    'to_variation' => $snapshot['variation_id'],
                    'quantity'     => $snapshot['quantity'],
                    'total'        => number_format( (float) $snapshot['total'], 2 ),
                    'schedule'     => $snapshot['billing_interval'] . ' ' . $snapshot['billing_period'],
                );

                if ( ! $write ) {
                    continue;
                }

                $item->set_product_id( (int) $snapshot['product_id'] );
                $item->set_variation_id( (int) $snapshot['variation_id'] );
                $item->set_quantity( (int) $snapshot['quantity'] );
                $item->set_subtotal( $snapshot['subtotal'] );
                $item->set_total( $snapshot['total'] );
                $item->delete_meta_data( Lytrod_Licensing_Seats::META_SEATS );
                $item->delete_meta_data( Lytrod_Licensing_Seats::META_TERM );
                $item->delete_meta_data( self::META_SNAPSHOT );

                $subscription->set_billing_period( $snapshot['billing_period'] );
                $subscription->set_billing_interval( $snapshot['billing_interval'] );

                $dates = array();

                foreach ( array( 'next_payment', 'end', 'trial_end' ) as $date_type ) {
                    $dates[ $date_type ] = $snapshot[ $date_type ] ? $snapshot[ $date_type ] : 0;
                }

                $subscription->update_dates( $dates );
                $subscription->set_total( $snapshot['order_total'] );

                $restored = true;
            }

            if ( $restored ) {
                $subscription->save();
            }
        }

        if ( ! $rows ) {
            WP_CLI::success( 'Nothing to revert — no migration snapshots found.' );

            return;
        }

        WP_CLI\Utils\format_items( 'table', $rows, array( 'subscription', 'from_product', 'to_product', 'to_variation', 'quantity', 'total', 'schedule' ) );

        if ( $write ) {
            WP_CLI::success( sprintf( 'Reverted %d subscription(s).', count( $rows ) ) );
        } else {
            WP_CLI::log( sprintf( '%d subscription(s) would be reverted. Re-run with --write.', count( $rows ) ) );
        }
    }

    /**
     * Create a simple subscription product for each legacy variable product, and seed its tables.
     *
     * Re-running is safe: an existing replacement is updated rather than duplicated.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Report only. Default.
     *
     * [--write]
     * : Create and seed the products.
     *
     * @param array $args       Positional args.
     * @param array $assoc_args Flags.
     * @return void
     */
    public function build( $args, $assoc_args ): void {
        $write = isset( $assoc_args['write'] );
        $rows  = array();

        foreach ( Lytrod_Licensing_Legacy::parents() as $parent_id => $name ) {
            $anchors  = Lytrod_Licensing_Legacy::anchors( $parent_id );
            $tiers    = Lytrod_Licensing_Legacy::seed_tiers( $parent_id );
            $terms    = Lytrod_Licensing_Legacy::seed_term_discounts( $parent_id );
            $existing = Lytrod_Licensing_Legacy::replacement_for( $parent_id );

            if ( $anchors['base'] <= 0 ) {
                WP_CLI::warning( sprintf( '%s (%d): no base price found, skipped.', $name, $parent_id ) );
                continue;
            }

            $new_id = $existing;

            if ( $write ) {
                $product = $existing ? wc_get_product( $existing ) : new WC_Product_Subscription();

                if ( ! $product ) {
                    $product = new WC_Product_Subscription();
                }

                $legacy = wc_get_product( $parent_id );

                $product->set_name( $name );
                $product->set_status( 'draft' );
                $product->set_catalog_visibility( 'hidden' );
                $product->set_virtual( true );
                $product->set_regular_price( (string) $anchors['base'] );

                if ( $legacy ) {
                    $product->set_description( $legacy->get_description() );
                    $product->set_short_description( $legacy->get_short_description() );
                    $product->set_image_id( $legacy->get_image_id() );
                    $product->set_category_ids( $legacy->get_category_ids() );
                }

                /*
                 * Quantity is the seat count, so `_sold_individually` must never be set. It is
                 * `yes` on five of the six legacy products — put there by a WooCommerce
                 * Subscriptions 1.5 upgrade routine years ago — and it hard-clamps quantity to 1
                 * in WC_Cart::add_to_cart() (class-wc-cart.php:1276).
                 */
                $product->set_sold_individually( false );

                $product->update_meta_data( '_subscription_price', (string) $anchors['base'] );
                $product->update_meta_data( '_subscription_period', 'year' );
                $product->update_meta_data( '_subscription_period_interval', 1 );
                // Must stay 0: length counts periods and ignores the interval, so a length equal
                // to the term would give one payment and then expiry.
                $product->update_meta_data( '_subscription_length', 0 );
                $product->update_meta_data( '_subscription_sign_up_fee', 0 );
                $product->update_meta_data( '_subscription_limit', 'no' );

                $product->update_meta_data( Lytrod_Licensing_Rates::META_TIERS, $tiers );
                $product->update_meta_data( Lytrod_Licensing_Rates::META_TERMS, $terms );
                $product->update_meta_data( Lytrod_Licensing::META_LICENSED, 'yes' );
                $product->update_meta_data( Lytrod_Licensing_Legacy::META_REPLACES, (string) $parent_id );

                $new_id = $product->save();
            }

            $rows[] = array(
                'legacy'     => $parent_id,
                'product'    => $name,
                'new'        => $new_id ? $new_id : '(would create)',
                'base'       => number_format( $anchors['base'], 2 ),
                'tiers'      => implode(
                    ' / ',
                    array_map(
                        static function ( array $t ): string {
                            return sprintf( '%s %d-%s @%s', $t['name'], $t['from'], null === $t['to'] ? '∞' : $t['to'], number_format( $t['rate'], 2 ) );
                        },
                        $tiers
                    )
                ),
                'discounts'  => implode( ' ', array_map( static function ( $y, $d ) { return $y . 'y:' . rtrim( rtrim( number_format( $d, 2 ), '0' ), '.' ) . '%'; }, array_keys( $terms ), $terms ) ),
            );
        }

        WP_CLI\Utils\format_items( 'table', $rows, array( 'legacy', 'product', 'new', 'base', 'tiers', 'discounts' ) );

        if ( $write ) {
            Lytrod_Licensing_Rates::flush();
            wc_delete_product_transients();
            WP_CLI::success( sprintf( 'Built %d licence products as drafts. Review, then publish.', count( $rows ) ) );
        } else {
            WP_CLI::log( 'Dry run. Re-run with --write to create them.' );
        }
    }

    /**
     * Apply the whole rebuild to a site, in order.
     *
     * Idempotent: every step reports "already ok" for work that is done, so re-running is safe
     * and a partial run can simply be repeated. Steps 1-3 prepare, step 4 builds, step 5 is
     * gated on proving that no price and no renewal date moves.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Report every step without writing. Default.
     *
     * [--write]
     * : Apply steps 1 to 4. The migration is NOT run automatically — see --migrate.
     *
     * [--migrate]
     * : With --write, also run the migration. Refuses if the gate is not clean.
     *
     * ## EXAMPLES
     *
     *     wp lytrod-licensing deploy --dry-run
     *     wp lytrod-licensing deploy --write
     *     wp lytrod-licensing deploy --write --migrate
     *
     * @param array $args       Positional args.
     * @param array $assoc_args Flags.
     * @return void
     */
    public function deploy( $args, $assoc_args ): void {
        $write   = isset( $assoc_args['write'] );
        $migrate = isset( $assoc_args['migrate'] );
        $mode    = $write ? 'APPLYING' : 'DRY RUN';

        WP_CLI::log( sprintf( "Lytrod licensing deploy — %s\n%s", $mode, str_repeat( '=', 60 ) ) );

        // --- 1. store settings ------------------------------------------------------
        WP_CLI::log( "\n[1/5] Store settings" );
        $rows = Lytrod_Licensing_Deploy::apply_settings( $write );
        WP_CLI\Utils\format_items( 'table', $rows, array( 'setting', 'current', 'target', 'action' ) );

        foreach ( $rows as $row ) {
            if ( 'already ok' !== $row['action'] ) {
                WP_CLI::log( sprintf( '      %s — %s', $row['setting'], $row['why'] ) );
            }
        }

        // --- 2. catalogue repair ----------------------------------------------------
        WP_CLI::log( "\n[2/5] Catalogue defects" );
        $rows = Lytrod_Licensing_Deploy::repair_catalogue( $write );

        if ( $rows ) {
            WP_CLI\Utils\format_items( 'table', $rows, array( 'variation', 'product', 'label', 'meta', 'current', 'target', 'reason' ) );
        } else {
            WP_CLI::log( '      none found' );
        }

        // --- 3. price alignment -----------------------------------------------------
        WP_CLI::log( "\n[3/5] Subscriptions billed off-catalogue" );
        $rows = Lytrod_Licensing_Deploy::align_prices( $write );

        if ( $rows ) {
            WP_CLI\Utils\format_items( 'table', $rows, array( 'subscription', 'customer', 'label', 'seats', 'was', 'now', 'change' ) );
            WP_CLI::warning( sprintf( '%d subscription(s) change price. This is the only step that moves money — review the list above.', count( $rows ) ) );
        } else {
            WP_CLI::log( '      none — every subscription already matches the rate table' );
        }

        // --- 4. build the products --------------------------------------------------
        WP_CLI::log( "\n[4/5] Licence products" );
        $this->build( array(), $write ? array( 'write' => true ) : array() );

        // --- 5. migration -----------------------------------------------------------
        WP_CLI::log( "\n[5/5] Migration" );

        if ( ! $write ) {
            // Evaluate the gate when replacements already exist — on a site that has been
            // deployed before, a dry run should be able to prove the whole thing end to end.
            $built = 0;

            foreach ( array_keys( Lytrod_Licensing_Legacy::parents() ) as $parent_id ) {
                if ( Lytrod_Licensing_Legacy::replacement_for( $parent_id ) ) {
                    ++$built;
                }
            }

            if ( $built ) {
                Lytrod_Licensing_Rates::flush();
                $this->migrate( array(), array() );
            } else {
                WP_CLI::log( '      not evaluated — the licence products do not exist yet, so there is nothing to migrate onto' );
            }

            WP_CLI::log( "\nRe-run with --write to apply steps 1-4, then --write --migrate to migrate." );

            return;
        }

        Lytrod_Licensing_Rates::flush();

        if ( ! $migrate ) {
            $this->migrate( array(), array() );
            WP_CLI::log( "\nSteps 1-4 applied. Review the gate above, then re-run with --write --migrate." );

            return;
        }

        $this->migrate( array(), array( 'write' => true ) );
        WP_CLI::success( 'Deploy complete. Products are drafts — publish them when ready.' );
    }

    /**
     * Move live subscriptions onto the new products.
     *
     * The dry run is the gate on the whole rebuild: every subscription must price identically
     * under the new model and keep its exact renewal date. Any movement blocks the write.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Report only. Default.
     *
     * [--write]
     * : Perform the migration. Refuses while any price or date would move.
     *
     * [--force]
     * : Migrate anyway. Only with the deltas understood.
     *
     * [--limit=<n>]
     * : Only process the first N subscriptions.
     *
     * @param array $args       Positional args.
     * @param array $assoc_args Flags.
     * @return void
     */
    public function migrate( $args, $assoc_args ): void {
        $write = isset( $assoc_args['write'] );
        $force = isset( $assoc_args['force'] );
        $limit = isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : -1;

        if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
            WP_CLI::error( 'WooCommerce Subscriptions is not active.' );
        }

        $subscriptions = wcs_get_subscriptions(
            array(
                'subscriptions_per_page' => $limit > 0 ? $limit : -1,
                'subscription_status'    => 'any',
            )
        );

        $deltas    = array();
        $planned   = array();
        $skipped   = 0;
        $by_seats  = array();
        $by_term   = array();
        $no_target = array();
        $done      = 0;
        $drifted   = array();
        $repairs   = array();

        foreach ( $subscriptions as $subscription ) {
            $items = $subscription->get_items();

            if ( ! $items ) {
                ++$skipped;
                continue;
            }

            $item      = current( $items );
            $parent_id = $item->get_product_id();
            $target    = Lytrod_Licensing_Legacy::replacement_for( $parent_id );
            $derived   = Lytrod_Licensing_Legacy::seats_and_term( $item );
            $current   = round( (float) $item->get_total(), 2 );

            if ( $current <= 0 ) {
                // $0 registrations carry no price to compare.
                ++$skipped;
                continue;
            }

            /*
             * A line already sitting on a replacement product has been migrated by an earlier
             * run. It has no legacy parent, so replacement_for() finds nothing — without this
             * branch a re-run reports every migrated customer as "no replacement product" and
             * then refuses to proceed. Re-verify instead of re-migrating: the term total still
             * has to agree with the rate table.
             */
            if ( '' !== (string) get_post_meta( $parent_id, Lytrod_Licensing_Legacy::META_REPLACES, true ) ) {
                ++$done;

                $seats    = max( 1, (int) $item->get_quantity() );
                $years    = max( 1, (int) $subscription->get_billing_interval() );
                $expected = round( (float) Lytrod_Licensing_Rates::price( $parent_id, $seats, $years )['term_total'], 2 );

                if ( abs( $expected - $current ) >= 0.01 ) {
                    $drifted[] = array(
                        'subscription' => $subscription->get_id(),
                        'status'       => $subscription->get_status(),
                        'seats'        => $seats,
                        'years'        => $years,
                        'billed_now'   => number_format( $current, 2 ),
                        'rate_table'   => number_format( $expected, 2 ),
                        'per_year_now' => number_format( $current / $years, 2 ),
                        'per_year_new' => number_format( $expected / $years, 2 ),
                    );

                    $repairs[] = array(
                        'subscription' => $subscription,
                        'item_id'      => $item->get_id(),
                        'seats'        => $seats,
                        'years'        => $years,
                        'term_total'   => $expected,
                    );
                }

                continue;
            }

            if ( ! $target ) {
                $no_target[ $parent_id ] = ( $no_target[ $parent_id ] ?? 0 ) + 1;
                continue;
            }

            $seats = $derived['seats'];
            $years = $derived['years'];

            $by_seats[ $seats ] = ( $by_seats[ $seats ] ?? 0 ) + 1;
            $by_term[ $years ]  = ( $by_term[ $years ] ?? 0 ) + 1;

            /*
             * The old line total is the ANNUAL recurring rate — multi-year money lived on the
             * parent order as a sign-up fee, never on the subscription. The new model bills the
             * whole term at once, so the comparable figure is the new annual rate.
             */
            $new_annual = round( (float) Lytrod_Licensing_Rates::annual( $target, $seats ), 2 );
            $delta      = round( $new_annual - $current, 2 );

            $row = array(
                'subscription' => $subscription->get_id(),
                'status'       => $subscription->get_status(),
                'tier_label'   => $derived['label'],
                'seats'        => $seats,
                'years'        => $years,
                'billed_now'   => number_format( $current, 2 ),
                'new_annual'   => number_format( $new_annual, 2 ),
                'delta'        => number_format( $delta, 2 ),
            );

            if ( abs( $delta ) >= 0.01 ) {
                $deltas[] = $row;
            }

            $planned[] = array(
                'subscription' => $subscription,
                'item_id'      => $item->get_id(),
                'target'       => $target,
                'seats'        => $seats,
                'years'        => $years,
                'annual'       => $new_annual,
                /*
                 * What the line must actually carry. The subscription's billing interval becomes
                 * the term, so a line billed every N years has to hold the whole N-year total.
                 * Writing the annual rate here would bill an N-year licence for one year's money
                 * every N years. For a 1-year term the two are equal, which is why this was
                 * invisible on the 272 single-year subscriptions.
                 */
                'term_total'   => round( (float) Lytrod_Licensing_Rates::price( $target, $seats, $years )['term_total'], 2 ),
            );
        }

        WP_CLI::log( '' );
        WP_CLI::log( sprintf( 'Subscriptions examined : %d', count( $subscriptions ) ) );
        WP_CLI::log( sprintf( 'Skipped (no price)     : %d', $skipped ) );
        WP_CLI::log( sprintf( 'Already migrated       : %d', $done ) );
        WP_CLI::log( sprintf( 'Ready to migrate       : %d', count( $planned ) ) );

        ksort( $by_seats );
        foreach ( $by_seats as $seats => $count ) {
            WP_CLI::log( sprintf( '  %2d seat(s)           : %d', $seats, $count ) );
        }

        ksort( $by_term );
        foreach ( $by_term as $years => $count ) {
            WP_CLI::log( sprintf( '  %d-year term          : %d', $years, $count ) );
        }

        if ( $no_target ) {
            WP_CLI::log( '' );
            foreach ( $no_target as $parent_id => $count ) {
                $product = wc_get_product( $parent_id );
                WP_CLI::warning( sprintf( '%d subscription(s) on "%s" (%d) have no replacement product. Run `build --write` first.', $count, $product ? $product->get_name() : '?', $parent_id ) );
            }
        }

        WP_CLI::log( sprintf( "\nPrice deltas           : %d", count( $deltas ) ) );

        if ( $deltas ) {
            WP_CLI::log( '' );
            WP_CLI\Utils\format_items( 'table', $deltas, array( 'subscription', 'status', 'tier_label', 'seats', 'years', 'billed_now', 'new_annual', 'delta' ) );
        }

        if ( $drifted ) {
            WP_CLI::log( '' );
            WP_CLI::warning( sprintf( '%d already-migrated subscription(s) no longer match the rate table:', count( $drifted ) ) );
            WP_CLI\Utils\format_items( 'table', $drifted, array( 'subscription', 'status', 'seats', 'years', 'billed_now', 'rate_table', 'per_year_now', 'per_year_new' ) );
            WP_CLI::log( '`--write` reprices these onto the rate table.' );
        }

        // Drift is repairable from the rate table, so it reports but does not block.
        $blocked = $deltas || $no_target;

        if ( ! $write ) {
            WP_CLI::log( '' );

            if ( $blocked ) {
                WP_CLI::warning( 'Resolve the above before migrating.' );
            } elseif ( ! $planned && $drifted ) {
                WP_CLI::log( sprintf( 'Nothing to migrate — all %d licensed subscription(s) are already on the new model, but %d need repricing above.', $done, count( $drifted ) ) );
            } elseif ( ! $planned ) {
                WP_CLI::success( sprintf( 'Nothing to migrate — all %d licensed subscription(s) are already on the new model and price correctly.', $done ) );
            } else {
                WP_CLI::success( 'Every subscription prices identically under the new model. Safe to run with --write.' );
            }

            return;
        }

        if ( $blocked && ! $force ) {
            WP_CLI::error( 'Refusing to migrate while prices would move or products are missing. Re-run with --force once understood.' );
        }

        // Repair already-migrated lines that no longer agree with the rate table. The rate table
        // is the authority once a subscription is on the new model, so this needs no snapshot —
        // the original pre-migration snapshot is already on the item and stays untouched.
        $repaired = 0;

        foreach ( $repairs as $repair ) {
            $subscription = $repair['subscription'];

            foreach ( $subscription->get_items() as $item ) {
                if ( ! $item instanceof WC_Order_Item_Product || (int) $item->get_id() !== (int) $repair['item_id'] ) {
                    continue;
                }

                Lytrod_Licensing_Seats::apply( $item, $repair['seats'], $repair['years'], $repair['term_total'] );
                ++$repaired;
            }

            $subscription->calculate_totals( false );
            $subscription->save();
        }

        if ( $repaired ) {
            WP_CLI::success( sprintf( 'Repriced %d already-migrated subscription(s) onto the rate table.', $repaired ) );
        }

        if ( ! $planned ) {
            WP_CLI::success( sprintf( 'Nothing to migrate — all %d licensed subscription(s) are already on the new model.', $done ) );

            return;
        }

        $migrated = 0;

        foreach ( $planned as $plan ) {
            $subscription = $plan['subscription'];

            // Pin the renewal date before touching the schedule, and restore it afterwards, so
            // changing the billing interval cannot move anybody's next payment.
            $dates_before = array();

            foreach ( array( 'next_payment', 'end', 'trial_end' ) as $date_type ) {
                $dates_before[ $date_type ] = $subscription->get_date( $date_type );
            }

            foreach ( $subscription->get_items() as $item ) {
                if ( ! $item instanceof WC_Order_Item_Product || (int) $item->get_id() !== (int) $plan['item_id'] ) {
                    continue;
                }

                /*
                 * Record everything about to be overwritten, so `migrate --revert` can put a
                 * subscription back exactly as it was. Written only once — re-running the
                 * migration must not overwrite the original snapshot with post-migration values.
                 */
                if ( ! $item->get_meta( self::META_SNAPSHOT, true ) ) {
                    $item->update_meta_data(
                        self::META_SNAPSHOT,
                        array(
                            'product_id'       => $item->get_product_id(),
                            'variation_id'     => $item->get_variation_id(),
                            'quantity'         => $item->get_quantity(),
                            'subtotal'         => $item->get_subtotal(),
                            'total'            => $item->get_total(),
                            'billing_period'   => $subscription->get_billing_period(),
                            'billing_interval' => $subscription->get_billing_interval(),
                            'next_payment'     => $dates_before['next_payment'],
                            'end'              => $dates_before['end'],
                            'trial_end'        => $dates_before['trial_end'],
                            'order_total'      => $subscription->get_total(),
                        )
                    );
                }

                $item->set_product_id( $plan['target'] );
                $item->set_variation_id( 0 );

                // The old term encoding is gone: the line carries the whole term total and the
                // interval carries the term length.
                Lytrod_Licensing_Seats::apply( $item, $plan['seats'], $plan['years'], $plan['term_total'] );
            }

            Lytrod_Licensing_Seats::apply_term( $subscription, $plan['years'] );

            // Clear the fake trial that used to encode the term.
            $restore = array( 'trial_end' => 0 );

            if ( $dates_before['next_payment'] ) {
                $restore['next_payment'] = $dates_before['next_payment'];
            }

            $subscription->update_dates( $restore );

            // Sum the repriced lines into the subscription total. Only sums stored line
            // totals, so it cannot move a price.
            $subscription->calculate_totals( false );
            $subscription->save();

            ++$migrated;
        }

        WP_CLI::success( sprintf( 'Migrated %d subscription(s). No price and no renewal date was moved.', $migrated ) );
    }

    /**
     * Print each product's tier table and the resulting prices.
     *
     * ## OPTIONS
     *
     * [--seats=<list>]
     * : Comma-separated seat counts. Default 1,2,3,4,5,10,25,50.
     *
     * [--years=<n>]
     * : Term to price at. Default 1.
     *
     * @param array $args       Positional args.
     * @param array $assoc_args Flags.
     * @return void
     */
    public function table( $args, $assoc_args ): void {
        $seats = isset( $assoc_args['seats'] )
            ? array_map( 'intval', array_filter( explode( ',', (string) $assoc_args['seats'] ) ) )
            : array( 1, 2, 3, 4, 5, 10, 25, 50 );

        $years = isset( $assoc_args['years'] ) ? max( 1, (int) $assoc_args['years'] ) : 1;

        $ids = wc_get_products(
            array(
                'type'   => array( 'subscription', 'variable-subscription' ),
                'limit'  => -1,
                'status' => array( 'publish', 'draft', 'private' ),
                'return' => 'ids',
            )
        );

        $rows = array();

        foreach ( $ids as $id ) {
            $product = wc_get_product( $id );

            if ( ! $product || ! $product->get_meta( Lytrod_Licensing_Rates::META_TIERS, true ) ) {
                continue;
            }

            $row = array(
                'id'      => $id,
                'product' => $product->get_name(),
                'type'    => $product->get_type(),
            );

            foreach ( $seats as $count ) {
                $row[ $count . ' seats' ] = number_format( Lytrod_Licensing_Rates::price( $id, $count, $years )['term_total'], 2 );
            }

            $rows[] = $row;
        }

        if ( ! $rows ) {
            WP_CLI::error( 'No products have a licence tier table yet. Run `build --write` first.' );
        }

        WP_CLI::log( sprintf( 'Term: %d year(s)', $years ) );
        WP_CLI\Utils\format_items( 'table', $rows, array_keys( $rows[0] ) );
    }
}

Lytrod_Licensing_CLI::init();
