<?php
/**
 * Relabels WooCommerce Subscriptions vocabulary as licensing vocabulary.
 *
 * Lytrod sells software licenses; Subscriptions is only the billing engine behind
 * them. Its wording ("subscription") is wrong for the product, so it is rewritten
 * on the way out.
 *
 * This happens at the TRANSLATION layer, never on rendered HTML. A whole-body
 * str_replace would corrupt the site: real rendered output puts a URL and its copy
 * on the same line —
 *
 *     <a href=".../my-account/view-subscription/29746/">Manage subscription</a>
 *
 * — so rewriting the token would 404 every "Manage license" CTA. It would also
 * break `?subscription_renewal_early=`, `/my-account/subscriptions/`, the
 * `woocommerce-subscriptions` text-domain slug, the
 * `woocommerce_subscriptions_email_order_details` hook name, and the
 * `'cancelled_subscription' !== $email->id` comparisons inside the templates.
 * Filtering translatable strings touches none of those by construction.
 *
 * Two seams are needed because a subject is computed before its body renders:
 * every WC_Email::trigger() calls
 * `send( $recipient, $this->get_subject(), $this->get_content(), … )` and PHP
 * evaluates arguments left to right, so get_subject() finishes long before
 * `woocommerce_email_header` fires.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vocabulary rewriter.
 */
class Lytrod_Emails_Lexicon {

    /**
     * Whether we are currently rendering an email body.
     *
     * @var bool
     */
    private static $in_body = false;

    /**
     * Text domains whose translatable strings may be rewritten.
     *
     * @var string[]
     */
    private static $domains = array(
        'woocommerce-subscriptions',
        'woocommerce-subscriptions-gifting',
        'woocommerce-gateway-stripe',
        'woocommerce',
        'lytrod-emails',
    );

    /**
     * Register both seams.
     *
     * @return void
     */
    public static function init(): void {
        /*
         * Subject, heading, preheader and additional content all route through
         * WC_Email::format_string(), which fires this one generic filter
         * (woocommerce/includes/emails/class-wc-email.php:403). One registration
         * covers all 40 emails instead of 80 per-id filters, and it runs outside the
         * body scope below — which is exactly what a subject needs.
         */
        add_filter( 'woocommerce_email_format_string', array( __CLASS__, 'filter_format_string' ), 99 );

        // Body copy: open the scope as the header renders, close it after the footer.
        add_action( 'woocommerce_email_header', array( __CLASS__, 'open' ), 1 );
        add_action( 'woocommerce_email_footer', array( __CLASS__, 'close' ), 99 );

        // Safety net: if a template fatals mid-render the footer never fires.
        add_action( 'woocommerce_email_sent', array( __CLASS__, 'close' ), 99 );

        /*
         * _x() and _n() do NOT pass through the `gettext` filter, and the strings that
         * need rewriting use all three forms — so all four hooks are required.
         */
        add_filter( 'gettext', array( __CLASS__, 'filter_gettext' ), 99, 3 );
        add_filter( 'gettext_with_context', array( __CLASS__, 'filter_gettext_with_context' ), 99, 4 );
        add_filter( 'ngettext', array( __CLASS__, 'filter_ngettext' ), 99, 5 );
        add_filter( 'ngettext_with_context', array( __CLASS__, 'filter_ngettext_with_context' ), 99, 6 );
    }

    /**
     * Enter the email body scope.
     *
     * @return void
     */
    public static function open(): void {
        self::$in_body = true;
    }

    /**
     * Leave the email body scope.
     *
     * Takes no typed parameters on purpose. It is hooked to both
     * `woocommerce_email_footer` — which templates fire with an `$email` argument that
     * subscriptions-upgrader's admin-new-order.php has already clobbered into a string —
     * and `woocommerce_email_sent`, which passes three unrelated arguments.
     *
     * @return void
     */
    public static function close(): void {
        self::$in_body = false;
    }

    /**
     * Rewrite subject / heading / preheader / additional content.
     *
     * @param string $string Formatted string.
     * @return string
     */
    public static function filter_format_string( $string ) {
        return is_string( $string ) ? self::relabel( $string ) : $string;
    }

    /**
     * Rewrite a `__()` string.
     *
     * @param string $translation Translated text.
     * @param string $text        Original text.
     * @param string $domain      Text domain.
     * @return string
     */
    public static function filter_gettext( $translation, $text, $domain ) {
        return self::maybe_relabel( $translation, $domain );
    }

    /**
     * Rewrite an `_x()` string.
     *
     * @param string $translation Translated text.
     * @param string $text        Original text.
     * @param string $context     Gettext context.
     * @param string $domain      Text domain.
     * @return string
     */
    public static function filter_gettext_with_context( $translation, $text, $context, $domain ) {
        return self::maybe_relabel( $translation, $domain );
    }

    /**
     * Rewrite an `_n()` string.
     *
     * @param string $translation Translated text.
     * @param string $single      Singular source.
     * @param string $plural      Plural source.
     * @param int    $number      Count.
     * @param string $domain      Text domain.
     * @return string
     */
    public static function filter_ngettext( $translation, $single, $plural, $number, $domain ) {
        return self::maybe_relabel( $translation, $domain );
    }

    /**
     * Rewrite an `_nx()` string.
     *
     * @param string $translation Translated text.
     * @param string $single      Singular source.
     * @param string $plural      Plural source.
     * @param int    $number      Count.
     * @param string $context     Gettext context.
     * @param string $domain      Text domain.
     * @return string
     */
    public static function filter_ngettext_with_context( $translation, $single, $plural, $number, $context, $domain ) {
        return self::maybe_relabel( $translation, $domain );
    }

    /**
     * Rewrite a translated string when we are inside an email body and the domain is
     * one we are allowed to touch.
     *
     * @param mixed  $translation Translated text.
     * @param string $domain      Text domain.
     * @return mixed
     */
    private static function maybe_relabel( $translation, $domain ) {
        if ( ! self::$in_body || ! is_string( $translation ) ) {
            return $translation;
        }

        /**
         * Filter the text domains eligible for relabelling.
         *
         * @since 1.0.0
         * @param string[] $domains Text domain slugs.
         */
        $domains = (array) apply_filters( 'lytrod_emails_lexicon_domains', self::$domains );

        if ( ! in_array( $domain, $domains, true ) ) {
            return $translation;
        }

        return self::relabel( $translation );
    }

    /**
     * Term map: lowercase singular source => lowercase singular replacement.
     *
     * Plurals and capitalisation are derived, so only the base form is listed.
     *
     * @return array<string, string>
     */
    private static function terms(): array {
        /**
         * Filter the relabelling term map.
         *
         * Keys and values are lowercase singular forms; the plural `s` and the original
         * capitalisation are applied automatically.
         *
         * @since 1.0.0
         * @param array<string, string> $terms Source => replacement.
         */
        return (array) apply_filters( 'lytrod_emails_lexicon', array( 'subscription' => 'license' ) );
    }

    /**
     * Rewrite vocabulary in a string, leaving anything machine-readable alone.
     *
     * HTML tags, URLs, `{placeholder}` tokens, shortcodes and printf specifiers are
     * split out and passed through untouched, so the transform stays safe even if it
     * is ever handed rendered markup.
     *
     * @param string $text Text to rewrite.
     * @return string
     */
    public static function relabel( string $text ): string {
        if ( '' === $text ) {
            return $text;
        }

        $terms = self::terms();

        if ( empty( $terms ) ) {
            return $text;
        }

        // Cheap bail-out: most strings never contain the token.
        $hit = false;

        foreach ( array_keys( $terms ) as $term ) {
            if ( false !== stripos( $text, $term ) ) {
                $hit = true;
                break;
            }
        }

        if ( ! $hit ) {
            return $text;
        }

        $protected = '#(<[^>]*+>|https?://\S++|www\.\S++|\{[^}\s]*+\}|\[[^\]\s]*+\]|%[\d$]*[sdfx])#i';
        $segments  = preg_split( $protected, $text, -1, PREG_SPLIT_DELIM_CAPTURE );

        if ( ! is_array( $segments ) ) {
            return $text;
        }

        $search = '/\b(' . implode( '|', array_map( 'preg_quote', array_keys( $terms ) ) ) . ')(s?)\b/i';

        foreach ( $segments as $index => $segment ) {
            // With one capture group, odd indices are the protected delimiters.
            if ( 1 === $index % 2 || '' === $segment ) {
                continue;
            }

            $segments[ $index ] = preg_replace_callback(
                $search,
                static function ( array $match ) use ( $terms ): string {
                    $replacement = $terms[ strtolower( $match[1] ) ] ?? $match[1];

                    if ( '' !== $match[2] ) {
                        $replacement .= 's';
                    }

                    if ( $match[0] === strtoupper( $match[0] ) ) {
                        return strtoupper( $replacement );
                    }

                    if ( $match[0] !== lcfirst( $match[0] ) ) {
                        return ucfirst( $replacement );
                    }

                    return $replacement;
                },
                $segment
            );
        }

        return implode( '', $segments );
    }
}
