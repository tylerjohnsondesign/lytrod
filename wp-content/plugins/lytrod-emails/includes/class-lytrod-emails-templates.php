<?php
/**
 * Routes WooCommerce email templates to this plugin.
 *
 * `apply_filters( 'woocommerce_locate_template', ... )` is the return value of
 * wc_locate_template() (woocommerce/includes/wc-core-functions.php:436), so a
 * plugin filter beats any theme override unconditionally. We also mirror on
 * `wc_get_template` (:318) which — unlike wc_locate_template() — is never
 * memoised and validates file_exists() itself.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template resolver.
 */
class Lytrod_Emails_Templates {

    /**
     * Maps a `$default_path` fragment to a directory under templates/.
     *
     * Ordered most-specific-first: the gifting path is a subdirectory of the
     * Subscriptions plugin, so it has to be tested before it.
     *
     * @var array<string, string>
     */
    private static $sources = array(
        'woocommerce-subscriptions/templates/gifting/' => 'woocommerce-subscriptions-gifting',
        'woocommerce-subscriptions/'                   => 'woocommerce-subscriptions',
        'woocommerce-gateway-stripe/'                  => 'woocommerce-gateway-stripe',
        'woocommerce/'                                 => 'woocommerce',
    );

    /**
     * Templates owned by another plugin that we must never take over.
     *
     * subscriptions-upgrader hijacks these two at priority 10 to inject Lytrod
     * business content (23 custom order-meta fields on admin-new-order.php, and a
     * deliberately minimal subscription-variation body on customer-processing-order.php).
     * They still pick up our header, footer and stylesheet because every WooCommerce
     * email template fires `woocommerce_email_header` / `woocommerce_email_footer`.
     *
     * @var array<string, string[]>
     */
    private static $ceded = array(
        'woocommerce' => array(
            'emails/customer-processing-order.php',
            'emails/admin-new-order.php',
        ),
    );

    /**
     * Register the routing filters.
     *
     * Priority 999 beats subscriptions-upgrader, which hooks
     * `woocommerce_locate_template` at priority 10.
     *
     * @return void
     */
    public static function init(): void {
        add_filter( 'woocommerce_locate_template', array( __CLASS__, 'locate_template' ), 999, 4 );
        add_filter( 'wc_get_template', array( __CLASS__, 'get_template' ), 999, 5 );
    }

    /**
     * Filter callback for `woocommerce_locate_template`.
     *
     * @param string $template      Resolved absolute path.
     * @param string $template_name Template name relative to the template root.
     * @param string $template_path Theme-relative override directory.
     * @param string $default_path  Owning plugin's templates/ directory.
     * @return string
     */
    public static function locate_template( $template, $template_name, $template_path = '', $default_path = '' ): string {
        $override = self::resolve( (string) $template_name, (string) $default_path, (string) $template );

        return $override ? $override : (string) $template;
    }

    /**
     * Filter callback for `wc_get_template`.
     *
     * @param string $template      Resolved absolute path.
     * @param string $template_name Template name relative to the template root.
     * @param array  $args          Template args.
     * @param string $template_path Theme-relative override directory.
     * @param string $default_path  Owning plugin's templates/ directory.
     * @return string
     */
    public static function get_template( $template, $template_name, $args = array(), $template_path = '', $default_path = '' ): string {
        $override = self::resolve( (string) $template_name, (string) $default_path, (string) $template );

        return $override ? $override : (string) $template;
    }

    /**
     * Resolve a template name to a file inside this plugin, if we ship one.
     *
     * @param string $template_name Template name, e.g. `emails/email-header.php`.
     * @param string $default_path  Owning plugin's templates/ directory. May be empty.
     * @param string $current       Path resolved so far, used to disambiguate an empty $default_path.
     * @return string Absolute path, or an empty string to leave the template alone.
     */
    private static function resolve( string $template_name, string $default_path, string $current = '' ): string {
        if ( '' === $template_name || 0 !== strpos( $template_name, 'emails/' ) ) {
            return '';
        }

        $source = self::source_for( $default_path, $current );

        if ( '' === $source ) {
            return '';
        }

        /**
         * Filter the templates this plugin refuses to override.
         *
         * @since 1.0.0
         * @param array $ceded Map of source slug => array of template names.
         */
        $ceded = (array) apply_filters( 'lytrod_emails_ceded_templates', self::$ceded );

        if ( ! empty( $ceded[ $source ] ) && in_array( $template_name, (array) $ceded[ $source ], true ) ) {
            return '';
        }

        $candidate = LYTROD_EMAILS_DIR . 'templates/' . $source . '/' . $template_name;

        return file_exists( $candidate ) ? $candidate : '';
    }

    /**
     * Identify which plugin owns the template being resolved.
     *
     * Branching on `$default_path` rather than `$template_name` alone is
     * mandatory: `emails/email-order-details.php` exists in BOTH WooCommerce core
     * and WooCommerce Subscriptions with different markup, and both arrive under
     * the same template name.
     *
     * @param string $default_path Owning plugin's templates/ directory. May be empty.
     * @param string $current      Path resolved so far.
     * @return string Source slug, or an empty string when unrecognised.
     */
    private static function source_for( string $default_path, string $current = '' ): string {
        /**
         * Filter the `$default_path` fragment => templates/ subdirectory map.
         *
         * @since 1.0.0
         * @param array $sources Ordered most-specific-first.
         */
        $sources = (array) apply_filters( 'lytrod_emails_template_sources', self::$sources );

        // WooCommerce core passes no $default_path; it is recomputed inside
        // wc_locate_template(). Fall back to the already-resolved path, then to core.
        $haystack = wp_normalize_path( '' !== $default_path ? $default_path : $current );

        if ( '' === $haystack ) {
            return 'woocommerce';
        }

        foreach ( $sources as $fragment => $slug ) {
            if ( false !== strpos( $haystack, wp_normalize_path( $fragment ) ) ) {
                return (string) $slug;
            }
        }

        return '';
    }
}
