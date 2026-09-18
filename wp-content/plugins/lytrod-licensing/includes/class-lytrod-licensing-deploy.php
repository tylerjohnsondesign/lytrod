<?php
/**
 * Everything a fresh site needs, in order, idempotently.
 *
 * The rebuild was applied to staging as a sequence of commands typed by hand. That is not a
 * deployment. This class is the repeatable form: settings, catalogue repair, price alignment,
 * product build, migration — each step reporting what it would do, each safe to re-run, and the
 * whole thing gated on the migration dry run proving no price and no date moves.
 *
 * Nothing here is keyed on a post ID. Every defect is matched on what it *is* rather than which
 * row it happened to be on staging, so the same command finds production's own instances.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Deployment steps.
 */
class Lytrod_Licensing_Deploy {

    /**
     * Store settings the rebuild depends on.
     *
     * These live in the database, not in git, so they have to be applied per environment.
     *
     * @return array<string, array{value: string, why: string}>
     */
    public static function settings(): array {
        return array(
            'woocommerce_subscriptions_accept_manual_renewals' => array(
                'value' => 'yes',
                'why'   => 'A "Free for" licence starts with no card, so its first renewal must be payable by invoice.',
            ),
            'woocommerce_subscriptions_zero_initial_payment_requires_payment' => array(
                // Misleadingly named. The checkbox reads "Allow $0 initial checkout without a
                // payment method", so 'yes' means allow-without-card.
                'value' => 'yes',
                'why'   => 'Lets a $0 initial checkout complete with no payment method.',
            ),
            'woocommerce_subscriptions_allow_switching' => array(
                'value' => 'no',
                'why'   => 'Native switching mis-prices this catalogue: it charges an entire multi-year term on top of a proration.',
            ),
        );
    }

    /**
     * Apply the store settings.
     *
     * @param bool $write Whether to write.
     * @return array<int, array<string, string>> Report rows.
     */
    public static function apply_settings( bool $write ): array {
        $rows = array();

        foreach ( self::settings() as $option => $spec ) {
            $current = get_option( $option, '(unset)' );
            $matches = (string) $current === $spec['value'];

            if ( $write && ! $matches ) {
                update_option( $option, $spec['value'] );
            }

            $rows[] = array(
                'setting' => $option,
                'current' => (string) $current,
                'target'  => $spec['value'],
                'action'  => $matches ? 'already ok' : ( $write ? 'SET' : 'would set' ),
                'why'     => $spec['why'],
            );
        }

        return $rows;
    }

    /**
     * Catalogue defects, found by shape rather than by id.
     *
     * Each rule returns corrections for one variation. Only unambiguous errors are reported —
     * anything that merely looks unusual is left alone and surfaces in the migration gate
     * instead, where a human sees it.
     *
     * @param bool $write Whether to write.
     * @return array<int, array<string, string>> Report rows.
     */
    public static function repair_catalogue( bool $write ): array {
        $rows = array();

        foreach ( Lytrod_Licensing_Legacy::parents() as $parent_id => $parent_name ) {
            $anchors = Lytrod_Licensing_Legacy::anchors( $parent_id );
            $product = wc_get_product( $parent_id );

            if ( ! $product || $anchors['base'] <= 0 ) {
                continue;
            }

            foreach ( $product->get_children() as $variation_id ) {
                $variation = wc_get_product( $variation_id );

                if ( ! $variation ) {
                    continue;
                }

                $label = Lytrod_Licensing_Rates::variation_label( $variation );
                $years = Lytrod_Licensing_Rates::years_from_label( $label );
                $fee   = (float) $variation->get_meta( '_subscription_sign_up_fee', true );
                $fixes = array();

                if ( '' === $label ) {
                    // An attribute-less variation matches "Any" and can be bought for nothing.
                    if ( (float) $variation->get_price() <= 0 && 'yes' !== $variation->get_meta( Lytrod_Licensing::META_RETIRED, true ) ) {
                        $fixes[ Lytrod_Licensing::META_RETIRED ] = array( 'yes', 'orphan variation, no attribute and no price' );
                    }
                } else {
                    $is_entry = (bool) preg_match( '/(sign\s*up|registration)/i', $label );

                    // A prepaid term must be expressed in years, or the customer is re-billed
                    // after that many months or days instead.
                    if ( $fee > 0 && $years > 0 ) {
                        if ( 'year' !== $variation->get_meta( '_subscription_trial_period', true ) ) {
                            $fixes['_subscription_trial_period'] = array( 'year', 'prepaid term must run in years' );
                        }

                        if ( (int) $variation->get_meta( '_subscription_trial_length', true ) !== $years ) {
                            $fixes['_subscription_trial_length'] = array( (string) $years, sprintf( 'label says %d years', $years ) );
                        }
                    }

                    // An "Upgrade to ..." row is a mid-term delta, always a one-year bridge.
                    if ( $fee > 0 && 0 === $years && false !== stripos( $label, 'upgrade' ) ) {
                        if ( 'year' !== $variation->get_meta( '_subscription_trial_period', true ) ) {
                            $fixes['_subscription_trial_period'] = array( 'year', 'upgrade bridge must run in years' );
                        }

                        if ( 1 !== (int) $variation->get_meta( '_subscription_trial_length', true ) ) {
                            $fixes['_subscription_trial_length'] = array( '1', 'upgrade bridge is a single year' );
                        }
                    }

                    // A term row that stops after N payments instead of renewing.
                    if ( 0 !== (int) $variation->get_meta( '_subscription_length', true ) ) {
                        $fixes['_subscription_length'] = array( '0', 'would terminate instead of renewing' );
                    }

                    /*
                     * An entry point renews at the product's Standard rate on every product but
                     * one, where it was keyed to the Plus rate. Only corrected when the value
                     * matches the Plus anchor exactly, so a deliberately different price is left
                     * alone.
                     */
                    if ( $is_entry && null !== $anchors['plus'] ) {
                        $recurring = (float) $variation->get_meta( '_subscription_price', true );

                        if ( abs( $recurring - (float) $anchors['plus'] ) < 0.005 && abs( $recurring - (float) $anchors['base'] ) >= 0.005 ) {
                            foreach ( array( '_subscription_price', '_regular_price', '_price' ) as $key ) {
                                $fixes[ $key ] = array( (string) $anchors['base'], 'entry point priced at the Plus rate' );
                            }
                        }
                    }
                }

                foreach ( $fixes as $key => $fix ) {
                    $rows[] = array(
                        'variation' => $variation_id,
                        'product'   => $parent_name,
                        'label'     => $label ? $label : '(none)',
                        'meta'      => $key,
                        'current'   => (string) ( $variation->get_meta( $key, true ) ?: '(empty)' ),
                        'target'    => $fix[0],
                        'reason'    => $fix[1],
                    );

                    if ( $write ) {
                        $variation->update_meta_data( $key, $fix[0] );
                    }
                }

                if ( $write && $fixes ) {
                    $variation->save();
                }
            }
        }

        if ( $write && $rows ) {
            wc_delete_product_transients();
        }

        return $rows;
    }

    /**
     * Bring subscriptions billed off-catalogue onto the rate table.
     *
     * The migration gate refuses to run while any subscription would be repriced, so anything
     * historically mis-billed has to be reconciled first. This is the only step that moves
     * money, which is why it reports every affected customer by name before it will write.
     *
     * @param bool $write Whether to write.
     * @return array<int, array<string, string>> Report rows.
     */
    public static function align_prices( bool $write ): array {
        $rows = array();

        if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
            return $rows;
        }

        $subscriptions = wcs_get_subscriptions(
            array(
                'subscriptions_per_page' => -1,
                'subscription_status'    => 'any',
            )
        );

        foreach ( $subscriptions as $subscription ) {
            $dirty = false;

            foreach ( $subscription->get_items() as $item ) {
                if ( ! $item instanceof WC_Order_Item_Product ) {
                    continue;
                }

                $parent_id = $item->get_product_id();
                $target    = Lytrod_Licensing_Legacy::replacement_for( $parent_id );
                $current   = round( (float) $item->get_total(), 2 );

                // Only pre-migration lines need aligning; a migrated line is already on-table.
                if ( ! $target || $current <= 0 ) {
                    continue;
                }

                $derived  = Lytrod_Licensing_Legacy::seats_and_term( $item );
                $computed = round( (float) Lytrod_Licensing_Rates::annual( $target, $derived['seats'] ), 2 );

                if ( abs( $computed - $current ) < 0.01 ) {
                    continue;
                }

                $rows[] = array(
                    'subscription' => $subscription->get_id(),
                    'customer'     => $subscription->get_billing_email(),
                    'label'        => $derived['label'],
                    'seats'        => (string) $derived['seats'],
                    'was'          => number_format( $current, 2 ),
                    'now'          => number_format( $computed, 2 ),
                    'change'       => number_format( $computed - $current, 2 ),
                );

                if ( $write ) {
                    $item->set_subtotal( $computed );
                    $item->set_total( $computed );
                    $dirty = true;
                }
            }

            if ( $dirty ) {
                $subscription->calculate_totals( false );
                $subscription->save();
            }
        }

        return $rows;
    }
}
