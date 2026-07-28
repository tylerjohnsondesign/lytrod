<?php
/**
 * Carries the current WC_Email into the header and footer templates.
 *
 * WooCommerce's own `WC_Emails::email_footer()` declares zero parameters and calls
 * wc_get_template() with no args (woocommerce/includes/class-wc-emails.php:385),
 * so the `$email` object that every template passes to
 * `do_action( 'woocommerce_email_footer', $email )` never actually reaches
 * email-footer.php. `email_header()` passes only `email_heading` and `store_name`.
 *
 * We listen on both actions at priority 1 and stash the object ourselves.
 *
 * The instanceof guard is load-bearing, not defensive boilerplate:
 * subscriptions-upgrader/templates/woocommerce/emails/admin-new-order.php:66 does
 * `$email = $order->get_billing_email();`, overwriting the WC_Email object with a
 * string before firing the footer action.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Per-request email context.
 */
class Lytrod_Emails_Context {

    /**
     * The email currently being rendered.
     *
     * @var WC_Email|null
     */
    private static $email = null;

    /**
     * Register the capture listeners.
     *
     * @return void
     */
    public static function init(): void {
        add_action( 'woocommerce_email_header', array( __CLASS__, 'capture_header' ), 1, 2 );
        add_action( 'woocommerce_email_footer', array( __CLASS__, 'capture_footer' ), 1, 1 );
    }

    /**
     * Capture the email object as the header renders.
     *
     * @param string $email_heading Heading text (unused).
     * @param mixed  $email         Expected to be a WC_Email; may be anything.
     * @return void
     */
    public static function capture_header( $email_heading = '', $email = null ): void {
        self::set( $email );
    }

    /**
     * Capture the email object as the footer renders.
     *
     * @param mixed $email Expected to be a WC_Email; may be anything.
     * @return void
     */
    public static function capture_footer( $email = null ): void {
        self::set( $email );
    }

    /**
     * Store the email object when it really is one.
     *
     * @param mixed $email Candidate value.
     * @return void
     */
    public static function set( $email ): void {
        if ( $email instanceof WC_Email ) {
            self::$email = $email;
        }
    }

    /**
     * The email currently being rendered, if known.
     *
     * @return WC_Email|null
     */
    public static function get() {
        return self::$email instanceof WC_Email ? self::$email : null;
    }

    /**
     * Identifier of the email currently being rendered.
     *
     * @return string Empty string when unknown.
     */
    public static function id(): string {
        $email = self::get();

        return $email && ! empty( $email->id ) ? (string) $email->id : '';
    }

    /**
     * Forget the stashed email.
     *
     * @return void
     */
    public static function reset(): void {
        self::$email = null;
    }
}
