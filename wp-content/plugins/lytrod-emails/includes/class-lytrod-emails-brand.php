<?php
/**
 * Design tokens for the Lytrod email system.
 *
 * Every token is filterable as `lytrod_emails_token_{$key}`. Colours are
 * deliberately NOT read from the `woocommerce_email_*_color` options — those are
 * seeded on activation for the sake of an accurate WooCommerce settings preview,
 * but the stylesheet is independent of them so an admin edit cannot drift the
 * design.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Brand token registry.
 */
class Lytrod_Emails_Brand {

    /**
     * Token defaults.
     *
     * The first five colours are the supplied brand palette. `ink_muted`,
     * `hairline`, `danger` and `danger_bg` are documented additions: text
     * hierarchy needs a muted tone, bordered materials need a hairline, and a
     * payment-failure notice rendered in brand blue reads as success. `ink_muted`
     * reuses the site's existing nav-link grey (themes/lytrod/src/styleparts/_header.scss:70).
     *
     * @var array<string, string>
     */
    private static $defaults = array(

        // Palette.
        'surface'      => '#ffffff',
        'ink'          => '#222222',
        'primary'      => '#0d4e96',
        'accent'       => '#4494d0',
        'surface_alt'  => '#eff3f9',

        // Derived.
        'ink_muted'    => '#5b5b5b',
        'hairline'     => '#dbe5f0',
        'danger'       => '#b3261e',
        'danger_bg'    => '#fdecea',
        'on_primary'   => '#ffffff',

        // Materials. "6px or 100%" per the brief.
        'radius'       => '6px',
        'radius_pill'  => '999px',
        'rail'         => '4px',

        // Type.
        'font'         => "'Raleway','Helvetica Neue',Helvetica,Arial,sans-serif",
        'font_mono'    => "'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace",

        // Layout.
        'width'        => '600',
        'pad'          => '32px',
        'pad_mobile'   => '20px',
    );

    /**
     * Fetch a single token.
     *
     * @param string $key     Token key.
     * @param string $default Fallback when the key is unknown.
     * @return string
     */
    public static function token( string $key, string $default = '' ): string {
        $value = self::$defaults[ $key ] ?? $default;

        /**
         * Filter an individual brand token.
         *
         * @since 1.0.0
         * @param string $value Token value.
         * @param string $key   Token key.
         */
        return (string) apply_filters( 'lytrod_emails_token_' . $key, $value, $key );
    }

    /**
     * Shorthand alias for token().
     *
     * @param string $key Token key.
     * @return string
     */
    public static function c( string $key ): string {
        return self::token( $key );
    }

    /**
     * Absolute URL to a bundled asset, forced onto the site's own scheme.
     *
     * plugins_url() derives its scheme from is_ssl(), which is always false under
     * WP-CLI and WP-Cron. Since the renewal reminder emails are sent from cron, the
     * unpatched value embeds `http://` image URLs into mail on an https site —
     * producing a redirect hop, and a blocked image in clients that refuse to follow
     * one. Deriving the scheme from home_url() instead makes it correct in every
     * execution context.
     *
     * @param string $file Path relative to the plugin's assets/img/ directory.
     * @return string
     */
    private static function asset_url( string $file ): string {
        $scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );

        return set_url_scheme( LYTROD_EMAILS_URL . 'assets/img/' . $file, $scheme ? $scheme : 'https' );
    }

    /**
     * Absolute URL to the light-background logo.
     *
     * Bundled with the plugin rather than read from `woocommerce_email_header_image`,
     * which is stored as an absolute staging URL and would hotlink staging from
     * production.
     *
     * @return string
     */
    public static function logo_url(): string {
        /**
         * Filter the light-background logo URL.
         *
         * @since 1.0.0
         * @param string $url Logo URL.
         */
        return (string) apply_filters( 'lytrod_emails_logo_url', self::asset_url( 'lytrod-logo.png' ) );
    }

    /**
     * Absolute URL to the reversed (white) logo used on the blue footer band.
     *
     * @return string
     */
    public static function logo_white_url(): string {
        /**
         * Filter the reversed logo URL.
         *
         * @since 1.0.0
         * @param string $url Logo URL.
         */
        return (string) apply_filters( 'lytrod_emails_logo_white_url', self::asset_url( 'lytrod-logo-white.png' ) );
    }

    /**
     * Absolute URL to a bundled icon.
     *
     * @param string $name Icon slug, e.g. `check-circle`.
     * @return string
     */
    public static function icon_url( string $name ): string {
        /**
         * Filter a bundled icon URL.
         *
         * @since 1.0.0
         * @param string $url  Icon URL.
         * @param string $name Icon slug.
         */
        return (string) apply_filters( 'lytrod_emails_icon_url', self::asset_url( 'icon-' . $name . '.png' ), $name );
    }

    /**
     * Display width of the light logo, in px.
     *
     * The source asset is 251x84 and no larger version exists anywhere in
     * wp-content/uploads (both "originals" there are byte-identical double-crops).
     * Rendering it at 125px yields 251/125 = 2.008x density, i.e. retina for free.
     *
     * @return int
     */
    public static function logo_width(): int {
        return (int) apply_filters( 'lytrod_emails_logo_width', 125 );
    }

    /**
     * Display width of the reversed logo, in px.
     *
     * @return int
     */
    public static function logo_white_width(): int {
        return (int) apply_filters( 'lytrod_emails_logo_white_width', 130 );
    }

    /**
     * Store name used for logo alt text and the document title.
     *
     * @return string
     */
    public static function store_name(): string {
        return get_bloginfo( 'name', 'display' );
    }

    /**
     * Support email address surfaced in the footer and support blocks.
     *
     * @return string
     */
    public static function support_email(): string {
        $email = get_option( 'woocommerce_email_from_address' );

        if ( ! is_email( $email ) ) {
            $email = get_option( 'admin_email' );
        }

        /**
         * Filter the support address shown to customers.
         *
         * @since 1.0.0
         * @param string $email Support address.
         */
        return (string) apply_filters( 'lytrod_emails_support_email', $email );
    }

    /**
     * Footer navigation links.
     *
     * @return array<int, array{label: string, url: string}>
     */
    public static function footer_links(): array {
        $links = array(
            array(
                'label' => __( 'My Account', 'lytrod-emails' ),
                'url'   => wc_get_page_permalink( 'myaccount' ),
            ),
            array(
                'label' => __( 'Support', 'lytrod-emails' ),
                'url'   => 'mailto:' . self::support_email(),
            ),
            array(
                'label' => __( 'Lytrod.com', 'lytrod-emails' ),
                'url'   => home_url( '/' ),
            ),
        );

        /**
         * Filter the footer link set.
         *
         * @since 1.0.0
         * @param array $links Each entry has `label` and `url`.
         */
        $links = (array) apply_filters( 'lytrod_emails_footer_links', $links );

        return array_values(
            array_filter(
                $links,
                static function ( $link ): bool {
                    return ! empty( $link['label'] ) && ! empty( $link['url'] );
                }
            )
        );
    }

    /**
     * Small print rendered beneath the footer links.
     *
     * Deliberately ignores the `woocommerce_email_footer_text` option, which is
     * stored as an empty string on this install and therefore rendered an empty
     * but still-padded table cell at the bottom of every email.
     *
     * @return string
     */
    public static function footer_note(): string {
        $address = WC()->countries ? WC()->countries->get_base_address() : '';
        $city    = WC()->countries ? WC()->countries->get_base_city() : '';
        $state   = WC()->countries ? WC()->countries->get_base_state() : '';
        $postal  = WC()->countries ? WC()->countries->get_base_postcode() : '';

        $locality = trim( implode( ' ', array_filter( array( $city, $state, $postal ) ) ) );
        $parts    = array_filter( array( self::store_name(), $address, $locality ) );

        $note = implode( ' &middot; ', array_map( 'esc_html', $parts ) );

        /**
         * Filter the footer small print. Expect `<br>`-separated markup, not
         * paragraphs.
         *
         * @since 1.0.0
         * @param string $note Footer note markup.
         */
        return (string) apply_filters( 'lytrod_emails_footer_note', $note );
    }
}
