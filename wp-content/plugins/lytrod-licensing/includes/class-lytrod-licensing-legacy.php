<?php
/**
 * Reading the pre-rebuild catalogue, so the new one can reproduce it exactly.
 *
 * The old model sold six `variable-subscription` products whose attribute value encoded both
 * the tier and the term — "Intellicut Plus 2 Years" — with multi-year totals stored as a
 * `_subscription_sign_up_fee` and the term faked as a matching free trial.
 *
 * Everything here exists to seed the new tier and term-discount tables from that catalogue and
 * to derive (seats, years) for each of the 283 live subscriptions during migration. Once the
 * migration has run and the old products are retired, this class is only needed to re-run or
 * audit it.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Legacy catalogue reader.
 */
class Lytrod_Licensing_Legacy {

    /**
     * Old variable-subscription parent id => the new simple product's meta pointer.
     */
    const META_REPLACES = '_lytrod_replaces_product';

    /**
     * Anchor prices for a legacy parent product.
     *
     * Derived by taking the MODE of each tier's recurring price rather than the first or the
     * minimum, because two variations carried known data errors — Vision Direct's 4-Year row was
     * priced 350 against its siblings' 360, and BizcardCut's Registration carried the Plus rate.
     * The mode is immune to both. (Both are since corrected, but the mode costs nothing.)
     *
     * @param int $parent_id Legacy parent product id.
     * @return array{base: float, plus: float|null}
     */
    public static function anchors( int $parent_id ): array {
        $product  = wc_get_product( $parent_id );
        $standard = array();
        $plus     = array();

        if ( $product && $product->is_type( 'variable-subscription' ) ) {
            foreach ( $product->get_children() as $variation_id ) {
                $variation = wc_get_product( $variation_id );

                if ( ! $variation ) {
                    continue;
                }

                $label = Lytrod_Licensing_Rates::variation_label( $variation );
                $rate  = (float) $variation->get_meta( '_subscription_price', true );

                if ( '' === $label || $rate <= 0 ) {
                    continue;
                }

                // Registration / Sign up are $0 entry points whose recurring rate is the
                // Standard rate, but BizcardCut's was mis-keyed, so exclude them entirely.
                if ( preg_match( '/(sign\s*up|registration)/i', $label ) ) {
                    continue;
                }

                if ( false !== stripos( $label, 'plus' ) ) {
                    $plus[] = $rate;
                } else {
                    $standard[] = $rate;
                }
            }
        }

        return array(
            'base' => self::mode( $standard ),
            'plus' => $plus ? self::mode( $plus ) : null,
        );
    }

    /**
     * Most frequently occurring value in a list of prices.
     *
     * @param float[] $values Prices.
     * @return float
     */
    private static function mode( array $values ): float {
        if ( ! $values ) {
            return 0.0;
        }

        $counts = array();

        foreach ( $values as $value ) {
            $key            = (string) round( (float) $value, 2 );
            $counts[ $key ] = ( $counts[ $key ] ?? 0 ) + 1;
        }

        arsort( $counts );

        return (float) array_key_first( $counts );
    }

    /**
     * Tier table that reproduces a legacy product's Basic and Plus prices exactly.
     *
     * Rows 1 and 2 are derived, not chosen: seat 1 at the base annual rate makes 1 seat equal
     * today's Standard price, and seats 2-3 at half the Plus-minus-base gap makes 3 seats equal
     * today's Plus price. Only the Enterprise rate is a judgement, set at 0.20 × base — the
     * rate implied by the one real four-seat sale on the books (simple SKU 29182, $1,700).
     *
     * @param int $parent_id Legacy parent product id.
     * @return array<int, array{name: string, from: int, to: int|null, rate: float}>
     */
    public static function seed_tiers( int $parent_id ): array {
        $anchors = self::anchors( $parent_id );
        $base    = (float) $anchors['base'];

        // Products with no Plus tier (Vision Direct) use the ratio the other five imply.
        $plus_gap = null !== $anchors['plus']
            ? ( (float) $anchors['plus'] - $base ) / 2
            : $base * 0.25;

        return array(
            array(
                'name' => __( 'Basic', 'lytrod-licensing' ),
                'from' => 1,
                'to'   => 1,
                'rate' => round( $base, 2 ),
            ),
            array(
                'name' => __( 'Plus', 'lytrod-licensing' ),
                'from' => 2,
                'to'   => 3,
                'rate' => round( $plus_gap, 2 ),
            ),
            array(
                'name' => __( 'Enterprise', 'lytrod-licensing' ),
                'from' => 4,
                'to'   => null,
                'rate' => round( $base * 0.20, 2 ),
            ),
        );
    }

    /**
     * Term discounts implied by a legacy product's multi-year sign-up fees.
     *
     * The old catalogue charged `annual × multiplier` up front for an N-year term, so the
     * discount is whatever makes `years × (1 − d) == multiplier`. Reading it back out of the
     * data rather than hardcoding 12.5/25 means the seed stays correct even where a product
     * priced its terms differently.
     *
     * @param int $parent_id Legacy parent product id.
     * @return array<int, float> Years => percent off.
     */
    public static function seed_term_discounts( int $parent_id ): array {
        $anchors   = self::anchors( $parent_id );
        $base      = (float) $anchors['base'];
        $discounts = array( 1 => 0.0 );

        if ( $base <= 0 ) {
            return Lytrod_Licensing_Rates::default_term_discounts();
        }

        $product = wc_get_product( $parent_id );

        if ( $product && $product->is_type( 'variable-subscription' ) ) {
            foreach ( $product->get_children() as $variation_id ) {
                $variation = wc_get_product( $variation_id );

                if ( ! $variation ) {
                    continue;
                }

                $label = Lytrod_Licensing_Rates::variation_label( $variation );

                // Only Standard term rows describe the term curve.
                if ( '' === $label || false !== stripos( $label, 'plus' ) ) {
                    continue;
                }

                $years = Lytrod_Licensing_Rates::years_from_label( $label );
                $fee   = (float) $variation->get_meta( '_subscription_sign_up_fee', true );

                if ( $years < 2 || $fee <= 0 ) {
                    continue;
                }

                $multiplier = $fee / $base;
                $discount   = ( 1 - ( $multiplier / $years ) ) * 100;

                $discounts[ $years ] = round( max( 0.0, $discount ), 4 );
            }
        }

        // Offer a 5-year term on the same footing as the longest discounted term we found.
        if ( ! isset( $discounts[5] ) ) {
            $discounts[5] = $discounts[4] ?? 25.0;
        }

        ksort( $discounts );

        return $discounts;
    }

    /**
     * Seats and term implied by a legacy subscription line item.
     *
     * Tier came from the attribute value — anything containing "Plus" was the 3-seat package,
     * everything else including the Sign up and Registration entry points was 1 seat. Term came
     * from the same string.
     *
     * @param mixed $item Legacy line item.
     * @return array{seats: int, years: int, label: string}
     */
    public static function seats_and_term( $item ): array {
        $label = '';

        if ( $item instanceof WC_Order_Item ) {
            $label = (string) $item->get_meta( 'choose', true );

            if ( '' === $label ) {
                $label = (string) $item->get_meta( 'years', true );
            }

            if ( '' === $label && is_callable( array( $item, 'get_variation_id' ) ) && $item->get_variation_id() ) {
                $label = Lytrod_Licensing_Rates::variation_label( wc_get_product( $item->get_variation_id() ) );
            }
        }

        $seats = ( '' !== $label && false !== stripos( $label, 'plus' ) ) ? 3 : 1;
        $years = Lytrod_Licensing_Rates::years_from_label( $label );

        return array(
            'seats' => $seats,
            'years' => $years > 0 ? $years : 1,
            'label' => $label,
        );
    }

    /**
     * The six legacy parent products, as id => name.
     *
     * @return array<int, string>
     */
    public static function parents(): array {
        $ids = wc_get_products(
            array(
                'type'   => 'variable-subscription',
                'limit'  => -1,
                'status' => array( 'publish', 'private', 'draft' ),
                'return' => 'ids',
            )
        );

        $parents = array();

        foreach ( $ids as $id ) {
            $product = wc_get_product( $id );

            if ( $product ) {
                $parents[ $id ] = $product->get_name();
            }
        }

        return $parents;
    }

    /**
     * The new simple product that replaces a legacy parent, if it has been created.
     *
     * @param int $parent_id Legacy parent id.
     * @return int Zero when no replacement exists yet.
     */
    public static function replacement_for( int $parent_id ): int {
        $found = get_posts(
            array(
                'post_type'      => 'product',
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_key'       => self::META_REPLACES,
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                'meta_value'     => (string) $parent_id,
            )
        );

        return $found ? (int) $found[0] : 0;
    }
}
