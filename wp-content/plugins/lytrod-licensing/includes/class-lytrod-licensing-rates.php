<?php
/**
 * Licence pricing: two tables, two controls.
 *
 * A licence is priced from exactly two things the customer chooses — how many seats and how
 * many years. Everything else is a lookup.
 *
 *     annual     = Σ over tiers ( seats falling in that tier × that tier's price per seat )
 *     term_total = annual × years × ( 1 − discount( years ) )
 *
 * Tiers are GRADUATED: each tier prices only the seats inside it, the way tax brackets work.
 * The alternative — every seat at the matched tier's rate — cannot reproduce both a 1-seat and
 * a 3-seat anchor without making 2 seats cost the same as 1, and it lets a lower rate at a
 * higher threshold make more seats cost less. Graduated is monotonic by construction.
 *
 * A tier is a label, a seat range and a price per seat. Nothing else. "Basic", "Plus" and
 * "Enterprise" are names for seat bands, not products.
 *
 * This formula reproduces the pre-rebuild catalogue exactly, which is what makes the migration
 * provably price-neutral: Intellicut at 1 seat gives 1,000 / 1,750 / 2,625 / 3,000 across
 * 1–4 years, matching every one of the old sign-up fees to the cent.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pricing engine.
 */
class Lytrod_Licensing_Rates {

    /**
     * Product meta holding the tier table.
     */
    const META_TIERS = '_lytrod_tiers';

    /**
     * Product meta holding the term-discount table.
     */
    const META_TERMS = '_lytrod_term_discounts';

    /**
     * Site-wide defaults, used when a product defines none.
     */
    const OPTION_TIERS = 'lytrod_licensing_tiers';
    const OPTION_TERMS = 'lytrod_licensing_term_discounts';

    /**
     * Runtime caches keyed by product id.
     *
     * @var array<int, array>
     */
    private static $tier_cache = array();
    private static $term_cache = array();

    /**
     * Fallback tier table for a product that has none configured.
     *
     * Deliberately a single open-ended row rather than an invented ladder — a product with no
     * tier table should price predictably, not guess at bands.
     *
     * @return array<int, array{name: string, from: int, to: int|null, rate: float}>
     */
    public static function default_tiers(): array {
        $tiers = get_option( self::OPTION_TIERS, array() );

        if ( is_array( $tiers ) && $tiers ) {
            return self::normalise_tiers( $tiers );
        }

        return array(
            array(
                'name' => __( 'Basic', 'lytrod-licensing' ),
                'from' => 1,
                'to'   => null,
                'rate' => 0.0,
            ),
        );
    }

    /**
     * Fallback term-discount table.
     *
     * Percentages off the straight per-year price. These are the discounts the old catalogue
     * already applied — its 2/3/4-year totals were 1.75 / 2.625 / 3.0 times the annual rate,
     * which is exactly 2×0.875, 3×0.875 and 4×0.75.
     *
     * @return array<int, float> Years => percent off.
     */
    public static function default_term_discounts(): array {
        $terms = get_option( self::OPTION_TERMS, array() );

        if ( is_array( $terms ) && $terms ) {
            return self::normalise_terms( $terms );
        }

        return array(
            1 => 0.0,
            2 => 12.5,
            3 => 12.5,
            4 => 25.0,
            5 => 25.0,
        );
    }

    /**
     * The tier table in force for a product.
     *
     * @param int $product_id Product id.
     * @return array<int, array{name: string, from: int, to: int|null, rate: float}>
     */
    public static function tiers( int $product_id = 0 ): array {
        if ( isset( self::$tier_cache[ $product_id ] ) ) {
            return self::$tier_cache[ $product_id ];
        }

        $tiers   = self::default_tiers();
        $product = $product_id ? wc_get_product( $product_id ) : null;

        if ( $product ) {
            $stored = $product->get_meta( self::META_TIERS, true );

            if ( is_array( $stored ) && $stored ) {
                $tiers = self::normalise_tiers( $stored );
            }
        }

        /**
         * Filter a product's tier table.
         *
         * @since 1.0.0
         * @param array $tiers      Ordered tiers.
         * @param int   $product_id Product id.
         */
        $tiers = self::normalise_tiers( (array) apply_filters( 'lytrod_licensing_tiers', $tiers, $product_id ) );

        return self::$tier_cache[ $product_id ] = $tiers;
    }

    /**
     * The term-discount table in force for a product.
     *
     * @param int $product_id Product id.
     * @return array<int, float> Years => percent off.
     */
    public static function term_discounts( int $product_id = 0 ): array {
        if ( isset( self::$term_cache[ $product_id ] ) ) {
            return self::$term_cache[ $product_id ];
        }

        $terms   = self::default_term_discounts();
        $product = $product_id ? wc_get_product( $product_id ) : null;

        if ( $product ) {
            $stored = $product->get_meta( self::META_TERMS, true );

            if ( is_array( $stored ) && $stored ) {
                $terms = self::normalise_terms( $stored );
            }
        }

        /**
         * Filter a product's term-discount table.
         *
         * @since 1.0.0
         * @param array $terms      Years => percent off.
         * @param int   $product_id Product id.
         */
        $terms = self::normalise_terms( (array) apply_filters( 'lytrod_licensing_term_discounts', $terms, $product_id ) );

        return self::$term_cache[ $product_id ] = $terms;
    }

    /**
     * Coerce a stored or filtered tier table into a clean, ordered list.
     *
     * @param array $tiers Raw rows.
     * @return array<int, array{name: string, from: int, to: int|null, rate: float}>
     */
    private static function normalise_tiers( array $tiers ): array {
        $clean = array();

        foreach ( $tiers as $tier ) {
            if ( ! is_array( $tier ) ) {
                continue;
            }

            $from = isset( $tier['from'] ) ? max( 1, (int) $tier['from'] ) : 0;

            if ( ! $from ) {
                continue;
            }

            $to = ( isset( $tier['to'] ) && '' !== $tier['to'] && null !== $tier['to'] ) ? (int) $tier['to'] : null;

            if ( null !== $to && $to < $from ) {
                $to = $from;
            }

            $clean[] = array(
                'name' => isset( $tier['name'] ) ? (string) $tier['name'] : '',
                'from' => $from,
                'to'   => $to,
                'rate' => isset( $tier['rate'] ) ? (float) $tier['rate'] : 0.0,
            );
        }

        usort(
            $clean,
            static function ( array $a, array $b ): int {
                return $a['from'] <=> $b['from'];
            }
        );

        return $clean;
    }

    /**
     * Coerce a term-discount table into `years => percent`, ordered by years.
     *
     * @param array $terms Raw rows.
     * @return array<int, float>
     */
    private static function normalise_terms( array $terms ): array {
        $clean = array();

        foreach ( $terms as $key => $value ) {
            // Accept both `years => percent` and a list of `['years'=>n,'discount'=>p]` rows.
            if ( is_array( $value ) ) {
                $years    = isset( $value['years'] ) ? (int) $value['years'] : 0;
                $discount = isset( $value['discount'] ) ? (float) $value['discount'] : 0.0;
            } else {
                $years    = (int) $key;
                $discount = (float) $value;
            }

            if ( $years < 1 ) {
                continue;
            }

            $clean[ $years ] = max( 0.0, min( 100.0, $discount ) );
        }

        if ( ! isset( $clean[1] ) ) {
            $clean[1] = 0.0;
        }

        ksort( $clean );

        return $clean;
    }

    /**
     * Terms a customer may choose, in years.
     *
     * @param int $product_id Product id.
     * @return int[]
     */
    public static function terms( int $product_id = 0 ): array {
        return array_keys( self::term_discounts( $product_id ) );
    }

    /**
     * Annual price for a seat count, before any term discount.
     *
     * @param int $product_id Product id.
     * @param int $seats      Seat count.
     * @return float
     */
    public static function annual( int $product_id, int $seats ): float {
        $seats = max( 1, $seats );
        $total = 0.0;

        foreach ( self::tiers( $product_id ) as $tier ) {
            $from = $tier['from'];
            $to   = null === $tier['to'] ? PHP_INT_MAX : $tier['to'];

            if ( $seats < $from ) {
                continue;
            }

            $in_tier = min( $seats, $to ) - $from + 1;

            if ( $in_tier > 0 ) {
                $total += $in_tier * $tier['rate'];
            }
        }

        return round( $total, 2 );
    }

    /**
     * The tier a seat count lands in.
     *
     * @param int $product_id Product id.
     * @param int $seats      Seat count.
     * @return string
     */
    public static function tier_name( int $product_id, int $seats ): string {
        $seats = max( 1, $seats );
        $name  = '';

        foreach ( self::tiers( $product_id ) as $tier ) {
            if ( $seats >= $tier['from'] ) {
                $name = $tier['name'];
            }
        }

        return $name;
    }

    /**
     * Price a licence.
     *
     * `unit_price` is deliberately NOT rounded. WooCommerce keeps four extra decimals of
     * precision internally (wc_add_number_precision(), woocommerce/includes/wc-core-functions.php:1907),
     * so `quantity × unit_price` lands exactly on `term_total` to the cent — but only if the
     * unit price arrives at full float precision. Rounding it to 2dp first drifts by up to a
     * couple of cents and the error grows with the seat count.
     *
     * @param int $product_id Product id.
     * @param int $seats      Seat count.
     * @param int $years      Term in whole years.
     * @return array{annual: float, term_total: float, unit_price: float, tier: string, seats: int, years: int, discount: float, breakdown: array}
     */
    public static function price( int $product_id, int $seats, int $years = 1 ): array {
        $seats = max( 1, $seats );
        $years = max( 1, $years );

        $annual    = self::annual( $product_id, $seats );
        $discounts = self::term_discounts( $product_id );
        $discount  = $discounts[ $years ] ?? 0.0;

        $term_total = round( $annual * $years * ( 1 - ( $discount / 100 ) ), 2 );

        $breakdown = array();

        foreach ( self::tiers( $product_id ) as $tier ) {
            $from = $tier['from'];
            $to   = null === $tier['to'] ? PHP_INT_MAX : $tier['to'];

            if ( $seats < $from ) {
                continue;
            }

            $in_tier = min( $seats, $to ) - $from + 1;

            if ( $in_tier > 0 ) {
                $breakdown[] = array(
                    'name'     => $tier['name'],
                    'seats'    => $in_tier,
                    'rate'     => $tier['rate'],
                    'subtotal' => round( $in_tier * $tier['rate'], 2 ),
                );
            }
        }

        $price = array(
            'annual'     => $annual,
            'term_total' => $term_total,
            // Unrounded on purpose — see the docblock.
            'unit_price' => $term_total / $seats,
            'tier'       => self::tier_name( $product_id, $seats ),
            'seats'      => $seats,
            'years'      => $years,
            'discount'   => $discount,
            'breakdown'  => $breakdown,
        );

        /**
         * Filter a computed licence price.
         *
         * @since 1.0.0
         * @param array $price      Computed price.
         * @param int   $product_id Product id.
         * @param int   $seats      Seat count.
         * @param int   $years      Term in years.
         */
        return (array) apply_filters( 'lytrod_licensing_price', $price, $product_id, $seats, $years );
    }

    /**
     * Highest seat count a customer may buy.
     *
     * @return int
     */
    public static function max_seats(): int {
        /**
         * Filter the purchasable seat ceiling.
         *
         * @since 1.0.0
         * @param int $max Maximum seats.
         */
        return (int) apply_filters( 'lytrod_licensing_max_seats', 999 );
    }

    /**
     * Longest term a customer may buy, in years.
     *
     * Capped at 6 because that is the largest billing interval WooCommerce Subscriptions
     * offers (wcs_get_subscription_period_interval_strings(), wcs-time-functions.php:157).
     *
     * @param int $product_id Product id.
     * @return int
     */
    public static function max_years( int $product_id = 0 ): int {
        $terms = self::terms( $product_id );

        return $terms ? min( 6, max( $terms ) ) : 1;
    }

    /**
     * Whole years expressed in a legacy variation label, e.g. "3 Years" => 3.
     *
     * Retained for the migration, which reads the old tier/term hybrid attribute values.
     *
     * @param string $label Attribute value.
     * @return int Zero when the label carries no term.
     */
    public static function years_from_label( string $label ): int {
        if ( preg_match_all( '/(\d+)\s*Years?\b/i', $label, $matches ) ) {
            return max( array_map( 'intval', $matches[1] ) );
        }

        return 0;
    }

    /**
     * A legacy variation's attribute value.
     *
     * The old attribute slug is `choose` on five products and `years` on Vision Direct, so read
     * whichever the variation actually carries. Migration-only.
     *
     * @param mixed $variation Variation.
     * @return string
     */
    public static function variation_label( $variation ): string {
        if ( ! $variation instanceof WC_Product ) {
            return '';
        }

        $attributes = $variation->get_attributes();

        foreach ( array( 'choose', 'years' ) as $slug ) {
            if ( ! empty( $attributes[ $slug ] ) ) {
                return trim( (string) $attributes[ $slug ] );
            }
        }

        return $attributes ? trim( (string) reset( $attributes ) ) : '';
    }

    /**
     * Clear the runtime caches. Used by the admin editor, the migration and tests.
     *
     * @return void
     */
    public static function flush(): void {
        self::$tier_cache = array();
        self::$term_cache = array();
    }
}
