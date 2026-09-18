<?php
/**
 * Admin: two Product data tabs, and the seat field on a subscription.
 *
 * Everything that used to be a metabox below the Product data panel now lives inside it, where
 * WooCommerce keeps pricing. Two tabs:
 *
 *   Licenses       - "Free for", plus the tier table (label, seat range, price per seat)
 *   Term Discounts - years => percent off
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin screens.
 */
class Lytrod_Licensing_Admin {

    /**
     * The product types these tabs apply to.
     *
     * Both are first-class in WooCommerce's own show/hide loop, because `wc_get_product_types()`
     * includes them once WooCommerce Subscriptions is active
     * (woocommerce/assets/js/admin/meta-boxes-product.js:221-270).
     */
    const TYPE_CLASSES = 'show_if_subscription show_if_variable-subscription';

    /**
     * Register admin hooks.
     *
     * @return void
     */
    public static function init(): void {
        add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_tabs' ) );
        add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'render_panels' ) );

        /*
         * Saved on `woocommerce_admin_process_product_object`, which fires immediately BEFORE
         * $product->save() (class-wc-meta-box-product-data.php:437-444), so the meta is flushed
         * in the same write with no second save and no extra save_post round trip. Nonce,
         * autosave, revision and capability are all already checked before it fires.
         */
        add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );

        // "Free for" lives on the Licenses tab now, so remove the duplicate control
        // WooCommerce Subscriptions renders on the General tab.
        add_action( 'admin_init', array( __CLASS__, 'dedupe_trial_field' ), 20 );

        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'woocommerce_process_shop_subscription_meta', array( __CLASS__, 'save_subscription_seats' ), 20 );

        /*
         * Retire subscriptions-upgrader's "Subscription Settings" metabox from the outside.
         *
         * It has to be done from here rather than by editing that plugin: its 35 files are
         * versioned nowhere. The parent repo holds only a gitlink (mode 160000) for the path,
         * there is no .gitmodules entry and no repo on disk, so any edit made there is local to
         * one server forever and would never reach production.
         */
        add_action( 'add_meta_boxes', array( __CLASS__, 'retire_legacy_metabox' ), 99 );
        add_action( 'admin_init', array( __CLASS__, 'retire_legacy_saver' ), 99 );
    }

    /**
     * Remove the legacy licence-tier metabox from the product screen.
     *
     * Licence pricing lives in the Licenses and Term Discounts tabs now, and a licence is priced
     * from quantity and term rather than from a ladder of variation groups.
     *
     * @return void
     */
    public static function retire_legacy_metabox(): void {
        remove_meta_box( 'subscription_settings', 'product', 'normal' );
    }

    /**
     * Unhook the legacy metabox's saver.
     *
     * This one matters. `Subscriptions_Upgrader_Admin::save_subscription_meta()` hangs off bare
     * `save_post` and unconditionally deletes `_custom_variation_groups` whenever
     * `$_POST['variation_select']` is absent. Its nonce check happens to make it harmless once
     * the metabox stops rendering, but that is an accident of ordering rather than a guarantee —
     * and the group data is still read by the resubscribe modal and the My Account template.
     *
     * @return void
     */
    public static function retire_legacy_saver(): void {
        self::remove_hook_by_method( 'save_post', 'Subscriptions_Upgrader_Admin', 'save_subscription_meta' );
        self::remove_hook_by_method( 'wp_ajax_add_more_group', 'Subscriptions_Upgrader_Admin', 'handle_add_more_group' );
        self::remove_hook_by_method( 'wp_ajax_add_more_group_subscription', 'Subscriptions_Upgrader_Admin', 'handle_add_more_group_subscription' );
    }

    /**
     * Remove a hook registered against an object instance we do not hold a reference to.
     *
     * `remove_action()` needs the exact callable, and these were registered as
     * `[ $this, 'method' ]` inside a constructor, so the instance is unreachable. Matching on
     * class name and method is the only way to detach them from outside the plugin.
     *
     * @param string $hook   Hook name.
     * @param string $class  Class name of the callback's object.
     * @param string $method Method name.
     * @return bool Whether anything was removed.
     */
    private static function remove_hook_by_method( string $hook, string $class, string $method ): bool {
        global $wp_filter;

        if ( empty( $wp_filter[ $hook ] ) ) {
            return false;
        }

        $removed = false;

        foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
            foreach ( $callbacks as $key => $callback ) {
                $function = $callback['function'] ?? null;

                if ( ! is_array( $function ) || ! is_object( $function[0] ?? null ) ) {
                    continue;
                }

                if ( $class === get_class( $function[0] ) && $method === ( $function[1] ?? '' ) ) {
                    remove_action( $hook, $function, $priority );
                    $removed = true;
                }
            }
        }

        return $removed;
    }

    /**
     * Register the two tabs.
     *
     * @param array $tabs Existing tabs.
     * @return array
     */
    public static function add_tabs( $tabs ) {
        $tabs['lytrod_licenses'] = array(
            'label'    => __( 'Licenses', 'lytrod-licensing' ),
            'target'   => 'lytrod_licenses_data',
            'class'    => array( 'show_if_subscription', 'show_if_variable-subscription' ),
            'priority' => 15,
        );

        $tabs['lytrod_terms'] = array(
            'label'    => __( 'Term Discounts', 'lytrod-licensing' ),
            'target'   => 'lytrod_terms_data',
            'class'    => array( 'show_if_subscription', 'show_if_variable-subscription' ),
            'priority' => 16,
        );

        return $tabs;
    }

    /**
     * Render both panels.
     *
     * @return void
     */
    public static function render_panels(): void {
        global $product_object;

        $product_id = $product_object instanceof WC_Product ? $product_object->get_id() : 0;

        Lytrod_Licensing_Rates::flush();

        $tiers     = Lytrod_Licensing_Rates::tiers( $product_id );
        $discounts = Lytrod_Licensing_Rates::term_discounts( $product_id );
        $free      = Lytrod_Licensing::free_for( $product_object );
        $classes   = self::TYPE_CLASSES;

        wp_nonce_field( 'lytrod_licensing_product', 'lytrod_licensing_product_nonce' );

        include LYTROD_LICENSING_DIR . 'templates/admin-licenses-tab.php';
        include LYTROD_LICENSING_DIR . 'templates/admin-terms-tab.php';
    }

    /**
     * A single tier row, used both for existing rows and as the add-row template.
     *
     * @param array $tier Tier row.
     * @return void
     */
    public static function tier_row( array $tier = array() ): void {
        $tier = wp_parse_args(
            $tier,
            array(
                'name' => '',
                'from' => '',
                'to'   => '',
                'rate' => '',
            )
        );

        include LYTROD_LICENSING_DIR . 'templates/admin-tier-row.php';
    }

    /**
     * A single term-discount row.
     *
     * @param int|string   $years    Years.
     * @param float|string $discount Percent off.
     * @return void
     */
    public static function term_row( $years = '', $discount = '' ): void {
        include LYTROD_LICENSING_DIR . 'templates/admin-term-row.php';
    }

    /**
     * Persist both tables and the free period.
     *
     * @param WC_Product $product Product being saved.
     * @return void
     */
    public static function save( $product ): void {
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        if ( ! isset( $_POST['lytrod_licensing_product_nonce'] )
            || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['lytrod_licensing_product_nonce'] ) ), 'lytrod_licensing_product' ) ) {
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified immediately above.
        $names = isset( $_POST['lytrod_tier_name'] ) ? (array) wp_unslash( $_POST['lytrod_tier_name'] ) : array();
        $froms = isset( $_POST['lytrod_tier_from'] ) ? (array) wp_unslash( $_POST['lytrod_tier_from'] ) : array();
        $tos   = isset( $_POST['lytrod_tier_to'] ) ? (array) wp_unslash( $_POST['lytrod_tier_to'] ) : array();
        $rates = isset( $_POST['lytrod_tier_rate'] ) ? (array) wp_unslash( $_POST['lytrod_tier_rate'] ) : array();

        $years     = isset( $_POST['lytrod_term_years'] ) ? (array) wp_unslash( $_POST['lytrod_term_years'] ) : array();
        $discounts = isset( $_POST['lytrod_term_discount'] ) ? (array) wp_unslash( $_POST['lytrod_term_discount'] ) : array();
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        // Parallel arrays, zipped by position — the downloadable-files pattern. DOM order is the
        // index, so add, remove and reorder need no reindexing.
        $tiers = array();

        foreach ( $froms as $i => $from ) {
            $from = absint( $from );

            if ( $from < 1 ) {
                continue;
            }

            $to = isset( $tos[ $i ] ) && '' !== trim( (string) $tos[ $i ] ) ? absint( $tos[ $i ] ) : null;

            $tiers[] = array(
                'name' => sanitize_text_field( $names[ $i ] ?? '' ),
                'from' => $from,
                'to'   => $to,
                'rate' => (float) wc_format_decimal( $rates[ $i ] ?? 0 ),
            );
        }

        $terms = array();

        foreach ( $years as $i => $year ) {
            $year = absint( $year );

            if ( $year < 1 ) {
                continue;
            }

            $terms[ $year ] = max( 0.0, min( 100.0, (float) wc_format_decimal( $discounts[ $i ] ?? 0 ) ) );
        }

        if ( $tiers ) {
            $product->update_meta_data( Lytrod_Licensing_Rates::META_TIERS, $tiers );
        } else {
            $product->delete_meta_data( Lytrod_Licensing_Rates::META_TIERS );
        }

        if ( $terms ) {
            $product->update_meta_data( Lytrod_Licensing_Rates::META_TERMS, $terms );
        } else {
            $product->delete_meta_data( Lytrod_Licensing_Rates::META_TERMS );
        }

        Lytrod_Licensing_Rates::flush();
    }

    /**
     * Remove WooCommerce Subscriptions' own trial control from the General tab.
     *
     * Ours posts the same field names, so its saver still persists and clamps the value — we are
     * only removing a second, identical-looking control from a different tab.
     *
     * @return void
     */
    public static function dedupe_trial_field(): void {
        if ( ! class_exists( 'WC_Subscriptions_Admin' ) ) {
            return;
        }

        remove_action( 'woocommerce_product_options_general_product_data', 'WC_Subscriptions_Admin::subscription_pricing_fields' );
        add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'pricing_fields_without_trial' ) );
    }

    /**
     * Subscriptions' pricing fields with the trial control suppressed.
     *
     * Rendering theirs inside an output buffer and stripping the one field is deliberately less
     * fragile than reimplementing the whole block, which carries price, interval, period, length
     * and expiry and changes between releases.
     *
     * @return void
     */
    public static function pricing_fields_without_trial(): void {
        ob_start();
        WC_Subscriptions_Admin::subscription_pricing_fields();
        $html = (string) ob_get_clean();

        $html = preg_replace(
            '#<p class="form-field _subscription_trial_length_field".*?</p>#s',
            '',
            $html,
            1
        );

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core markup, one field removed.
    }

    /**
     * Enqueue the add/remove-row script and the panel styles.
     *
     * @return void
     */
    public static function enqueue(): void {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        if ( ! $screen || 'product' !== $screen->id ) {
            return;
        }

        wp_enqueue_style(
            'lytrod-licensing-admin',
            LYTROD_LICENSING_URL . 'assets/css/admin.css',
            array( 'woocommerce_admin_styles' ),
            LYTROD_LICENSING_VERSION
        );

        wp_enqueue_script(
            'lytrod-licensing-admin',
            LYTROD_LICENSING_URL . 'assets/js/admin.js',
            array( 'jquery', 'wc-admin-meta-boxes', 'wc-admin-product-meta-boxes' ),
            LYTROD_LICENSING_VERSION,
            true
        );
    }

    /**
     * The seats box on a subscription.
     *
     * @return void
     */
    public static function add_meta_boxes(): void {
        add_meta_box(
            'lytrod-licensing-seats',
            __( 'Licensed seats', 'lytrod-licensing' ),
            array( __CLASS__, 'render_seats_box' ),
            'shop_subscription',
            'side',
            'default'
        );
    }

    /**
     * Seat count, term and provisioning state on a subscription.
     *
     * @param WP_Post $post Subscription post.
     * @return void
     */
    public static function render_seats_box( $post ): void {
        $subscription = function_exists( 'wcs_get_subscription' ) ? wcs_get_subscription( $post->ID ) : null;

        if ( ! $subscription ) {
            return;
        }

        $seats       = Lytrod_Licensing_Seats::get_or_infer( $subscription );
        $items       = $subscription->get_items();
        $years       = $items ? Lytrod_Licensing_Seats::term_years( current( $items ), $subscription ) : 1;
        $provisioned = Lytrod_Licensing_Seats::is_provisioned( $subscription );

        wp_nonce_field( 'lytrod_licensing_seats', 'lytrod_licensing_seats_nonce' );

        include LYTROD_LICENSING_DIR . 'templates/admin-seats.php';
    }

    /**
     * Save an admin edit to the seat count.
     *
     * Repricing is offered because the subscription's own line total is what every future renewal
     * is copied from — changing seats without it would leave the customer billed at the old rate
     * indefinitely.
     *
     * @param int $post_id Subscription id.
     * @return void
     */
    public static function save_subscription_seats( $post_id ): void {
        if ( ! isset( $_POST['lytrod_licensing_seats_nonce'] )
            || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['lytrod_licensing_seats_nonce'] ) ), 'lytrod_licensing_seats' )
            || ! current_user_can( 'edit_shop_subscription', $post_id ) ) {
            return;
        }

        $subscription = function_exists( 'wcs_get_subscription' ) ? wcs_get_subscription( $post_id ) : null;

        if ( ! $subscription ) {
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified above.
        $seats       = isset( $_POST['lytrod_seats'] ) ? absint( wp_unslash( $_POST['lytrod_seats'] ) ) : 0;
        $provisioned = ! empty( $_POST['lytrod_seats_provisioned'] );
        $reprice     = ! empty( $_POST['lytrod_seats_reprice'] );
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( $seats > 0 ) {
            foreach ( $subscription->get_items() as $item ) {
                if ( ! $item instanceof WC_Order_Item_Product ) {
                    continue;
                }

                $years = Lytrod_Licensing_Seats::term_years( $item, $subscription );

                if ( $reprice ) {
                    Lytrod_Licensing_Seats::apply( $item, $seats, $years );
                } else {
                    $item->set_quantity( $seats );
                    $item->update_meta_data( Lytrod_Licensing_Seats::META_SEATS, $seats );
                }
            }
        }

        $subscription->update_meta_data( Lytrod_Licensing_Seats::META_PROVISIONED, $provisioned ? 'yes' : 'no' );

        if ( $seats > 0 && $reprice ) {
            $subscription->calculate_totals( false );
        }

        $subscription->save();
    }
}
