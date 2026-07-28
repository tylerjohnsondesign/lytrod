<?php
/**
 * Plugin loader.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wires up the pieces once WooCommerce is known to be present.
 */
class Lytrod_Emails {

    /**
     * Boot on `plugins_loaded`.
     *
     * @return void
     */
    public static function init(): void {
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( __CLASS__, 'missing_woocommerce_notice' ) );

            return;
        }

        load_plugin_textdomain( 'lytrod-emails', false, dirname( plugin_basename( LYTROD_EMAILS_FILE ) ) . '/languages' );

        Lytrod_Emails_Context::init();
        Lytrod_Emails_Lexicon::init();
        Lytrod_Emails_Payment::init();

        /*
         * Suppress WooCommerce's hardcoded `<hr style="border-top:1px solid #1E1E1E">`
         * section dividers. Inline style attributes beat Emogrifier-inlined CSS, so
         * these cannot be recoloured — only turned off. Our own partials draw their
         * own hairlines using the brand palette.
         */
        add_filter( 'woocommerce_email_body_display_section_divider', '__return_false' );

        /*
         * Link the header logo at the site root. WooCommerce's own header template
         * uses this filter; ours honours it too.
         */
        add_filter( 'woocommerce_email_header_image_url', array( __CLASS__, 'header_image_url' ), 5 );

        /*
         * Subscriptions Gifting renders two of its own tables on its own hook
         * (includes/gifting/class-wcsg-email.php:60-61) rather than through
         * `woocommerce_email_customer_details`, so the gate inside email-addresses.php
         * cannot see them. On a renewal receipt both are wrong:
         *
         * - `get_address_table` prints a shipping address for a software license.
         * - `get_related_subscriptions_table` prints an End Date read from `_schedule_end`,
         *   which is the string '0' on 256 of 283 subscriptions and therefore renders as
         *   "When Cancelled" — directly contradicting the Expiration Date in our License
         *   details table a few rows above it.
         *
         * Drop both for the duration of the action, then restore them so nothing leaks
         * into the next email in the same request.
         */
        add_action( 'woocommerce_subscriptions_gifting_recipient_email_details', array( __CLASS__, 'suppress_gifting_tables' ), 1 );
        add_action( 'woocommerce_subscriptions_gifting_recipient_email_details', array( __CLASS__, 'restore_gifting_tables' ), 999 );
    }

    /**
     * Gifting callbacks removed for the current render, as method => priority.
     *
     * @var array<string, int>
     */
    private static $gifting_removed = array();

    /**
     * Remove the Gifting recipient tables that a license renewal receipt supersedes.
     *
     * @return void
     */
    public static function suppress_gifting_tables(): void {
        if ( ! class_exists( 'WCSG_Email' ) || ! Lytrod_Emails_Content::hides_addresses() ) {
            return;
        }

        foreach ( array( 'get_related_subscriptions_table' => 10, 'get_address_table' => 11 ) as $method => $priority ) {
            $removed = remove_action(
                'woocommerce_subscriptions_gifting_recipient_email_details',
                array( 'WCSG_Email', $method ),
                $priority
            );

            if ( $removed ) {
                self::$gifting_removed[ $method ] = $priority;
            }
        }
    }

    /**
     * Restore whatever we removed.
     *
     * @return void
     */
    public static function restore_gifting_tables(): void {
        foreach ( self::$gifting_removed as $method => $priority ) {
            add_action(
                'woocommerce_subscriptions_gifting_recipient_email_details',
                array( 'WCSG_Email', $method ),
                $priority,
                3
            );
        }

        self::$gifting_removed = array();
    }

    /**
     * Default the header logo link to the store home page.
     *
     * @param string $url Incoming URL.
     * @return string
     */
    public static function header_image_url( $url ): string {
        return $url ? (string) $url : home_url( '/' );
    }

    /**
     * Admin notice shown when WooCommerce is not active.
     *
     * @return void
     */
    public static function missing_woocommerce_notice(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Lytrod Emails', 'lytrod-emails' ) . '</strong> ';
        echo esc_html__( 'requires WooCommerce to be installed and active.', 'lytrod-emails' );
        echo '</p></div>';
    }
}
