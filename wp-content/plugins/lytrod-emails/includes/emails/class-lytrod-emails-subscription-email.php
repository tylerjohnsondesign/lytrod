<?php
/**
 * Shared base for the licence lifecycle emails.
 *
 * These five emails differ from the rest of the plugin: their body copy is edited by an
 * administrator rather than written in a template, so the template is generic and the copy lives
 * in the email's own settings. Everything that makes that work — the merge tags, the extra `body`
 * field, the sending identity — lives here, and the concrete classes carry only their id, title
 * and default copy.
 *
 * Tag substitution rides on WooCommerce's own `WC_Email::format_string()` / `$placeholders`
 * mechanism rather than a bespoke str_replace, which means tags resolve in the subject, the
 * heading, the body and the additional content with no extra code — and that
 * `Lytrod_Emails_Lexicon` relabels the result exactly as it does for every other email.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for subscription lifecycle emails with admin-editable bodies.
 */
abstract class Lytrod_Emails_Subscription_Email extends WC_Email {

    /**
     * Sending identity, carried over from the plugin these emails replace.
     *
     * The old plugin achieved this with unscoped `wp_mail_from` / `wp_mail_from_name` filters,
     * which changed the From address of every email the site sends. Overriding the getters keeps
     * it to these five.
     */
    const FROM_ADDRESS = 'lytrodlive@lytrod.com';
    const FROM_NAME    = 'Lytrod Software Licensing Team';
    const BCC_ADDRESS  = 'lytrodsoftwaredocs@gmail.com';

    /**
     * Set up shared state. Subclasses set $id/$title/$description before calling up.
     */
    public function __construct() {
        $this->customer_email = true;
        $this->template_base  = LYTROD_EMAILS_DIR . 'templates/woocommerce/';
        $this->template_html  = 'emails/lytrod-subscription-message.php';
        $this->template_plain = 'emails/plain/lytrod-subscription-message.php';

        $this->placeholders = array_merge( self::tag_defaults(), (array) $this->placeholders );

        parent::__construct();
    }

    /**
     * Every merge tag, empty, in display order.
     *
     * The first seven are the documented set. The rest are retained because the live copy being
     * migrated uses them — `{product_license_line}` in particular appears in all four reminder
     * bodies despite never having been documented by the plugin that introduced it.
     *
     * @return array<string, string>
     */
    public static function tag_defaults(): array {
        return array(
            // Documented.
            '{customer_name}'       => '',
            '{customer_email}'      => '',
            '{product}'             => '',
            '{subscription_id}'     => '',
            '{my_account_url}'      => '',
            '{resubscribe_url}'     => '',
            '{site_name}'           => '',

            // Carried over from the emails this replaces.
            '{renewal_date}'        => '',
            '{days_until_renewal}'  => '',
            '{serial_number}'       => '',
            '{product_license_line}' => '',
            '{admin_email}'         => '',

            // Aliases, so migrated copy renders unchanged.
            '{first_name}'          => '',
            '{display_name}'        => '',
            '{product_name}'        => '',
            '{subscriptions_url}'   => '',
        );
    }

    /**
     * Resolve every tag for a subscription.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return void
     */
    protected function fill_placeholders( $subscription ): void {
        $product  = self::product_label( $subscription );
        $serial   = (string) Lytrod_Emails_Content::product_id( $subscription );
        $account  = wc_get_account_endpoint_url( 'subscriptions' );

        $line = '' !== $serial
            /* translators: 1: product name, 2: licence serial number. */
            ? sprintf( __( '%1$s serial number %2$s', 'lytrod-emails' ), $product, $serial )
            : $product;

        $name = Lytrod_Emails_Content::first_name( $subscription );

        $values = array(
            '{customer_name}'        => $name,
            '{customer_email}'       => $subscription->get_billing_email(),
            '{product}'              => $product,
            '{subscription_id}'      => (string) $subscription->get_id(),
            '{my_account_url}'       => $account,
            '{resubscribe_url}'      => self::resubscribe_url( $subscription ),
            '{site_name}'            => get_bloginfo( 'name' ),

            '{renewal_date}'         => self::renewal_date( $subscription ),
            '{days_until_renewal}'   => (string) self::days_until_renewal( $subscription ),
            '{serial_number}'        => $serial,
            '{product_license_line}' => $line,
            '{admin_email}'          => Lytrod_Emails_Brand::support_email(),

            '{first_name}'           => $name,
            '{display_name}'         => trim( $subscription->get_billing_first_name() . ' ' . $subscription->get_billing_last_name() ),
            '{product_name}'         => $product,
            '{subscriptions_url}'    => $account,
        );

        foreach ( $values as $tag => $value ) {
            $this->placeholders[ $tag ] = (string) $value;
        }
    }

    /**
     * The date this licence next renews or expires, formatted for the customer.
     *
     * `get_time( $type, 'site' )` is the only correct way to do this. The emails this replaces
     * used `date_i18n( $fmt, strtotime( $utc_string ) )`, which shifts an already-UTC value by
     * the site offset a second time — at the current offset of -7 that puts 14 of 186 active
     * subscriptions a full day out.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return string
     */
    public static function renewal_date( $subscription ): string {
        $type = self::renewal_date_type( $subscription );

        if ( '' === $type ) {
            return __( 'N/A', 'lytrod-emails' );
        }

        return date_i18n( wc_date_format(), $subscription->get_time( $type, 'site' ) );
    }

    /**
     * Whole days from now until the renewal, floored, never negative.
     *
     * Exists so migrated copy need not hardcode "30 days" — a catch-up run can legitimately
     * send the 30-day reminder at 29.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return int
     */
    public static function days_until_renewal( $subscription ): int {
        $type = self::renewal_date_type( $subscription );

        if ( '' === $type ) {
            return 0;
        }

        $delta = $subscription->get_time( $type ) - time();

        return $delta > 0 ? (int) floor( $delta / DAY_IN_SECONDS ) : 0;
    }

    /**
     * Which date drives this subscription — `next_payment`, else `end`.
     *
     * WCS stores an unset schedule date as the string '0', which `get_time()` reports as 0.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return string Date type, or '' when neither is set.
     */
    public static function renewal_date_type( $subscription ): string {
        if ( ! $subscription instanceof WC_Subscription ) {
            return '';
        }

        if ( $subscription->get_time( 'next_payment' ) > 0 ) {
            return 'next_payment';
        }

        return $subscription->get_time( 'end' ) > 0 ? 'end' : '';
    }

    /**
     * Product name for the licence, cleaned the way the migrated copy expects.
     *
     * Reads the parent product rather than the line item so a variation's attribute suffix never
     * reaches the customer, then strips the "- Sign up License" / "- Registration License"
     * suffixes the legacy catalogue carries.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return string
     */
    public static function product_label( $subscription ): string {
        foreach ( $subscription->get_items() as $item ) {
            if ( ! $item instanceof WC_Order_Item_Product ) {
                continue;
            }

            $product = wc_get_product( $item->get_product_id() );
            $name    = $product ? $product->get_name() : $item->get_name();
            $name    = wp_strip_all_tags( (string) $name );
            $name    = preg_replace( '/\s*-\s*(Sign\s*up|Signup|Registration)\s*License/i', '', $name );

            return trim( (string) $name );
        }

        return '';
    }

    /**
     * Where "resubscribe" should send the customer.
     *
     * Deliberately not `wcs_get_users_resubscribe_link()`: that wraps the URL in
     * `wp_nonce_url()`, and a nonce minted during cron belongs to user 0 and expires within a
     * day, so the link would be dead before it was read. This lands them on the subscriptions
     * screen, where the real, freshly-nonced control lives.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return string
     */
    public static function resubscribe_url( $subscription ): string {
        return add_query_arg( 'resubscribe', $subscription->get_id(), wc_get_account_endpoint_url( 'subscriptions' ) );
    }

    /**
     * Settings fields. Adds `body` to WooCommerce's standard set.
     *
     * @return void
     */
    public function init_form_fields(): void {
        /* translators: %s: comma-separated list of merge tags. */
        $tags = sprintf(
            __( 'Available tags: %s', 'lytrod-emails' ),
            '<code>' . implode( '</code>, <code>', array_keys( self::tag_defaults() ) ) . '</code>'
        );

        $this->form_fields = array(
            'enabled'            => array(
                'title'   => __( 'Enable/Disable', 'lytrod-emails' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'lytrod-emails' ),
                // Off by default: the plugins these replace are still running.
                'default' => 'no',
            ),
            'subject'            => array(
                'title'       => __( 'Subject', 'lytrod-emails' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $tags,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __( 'Email heading', 'lytrod-emails' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $tags,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'body'               => array(
                'title'       => __( 'Email body', 'lytrod-emails' ),
                'type'        => 'textarea',
                'description' => __( 'The message itself. Blank lines become paragraphs and bare URLs become links.', 'lytrod-emails' ) . ' ' . $tags,
                'css'         => 'width:100%;height:320px;font-family:monospace;',
                'placeholder' => '',
                'default'     => $this->get_default_body(),
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'lytrod-emails' ),
                'description' => __( 'Text to appear below the main email content.', 'lytrod-emails' ) . ' ' . $tags,
                'css'         => 'width:400px;height:75px;',
                'placeholder' => __( 'N/A', 'lytrod-emails' ),
                'type'        => 'textarea',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'email_type'         => array(
                'title'       => __( 'Email type', 'lytrod-emails' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'lytrod-emails' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * The body copy, tags resolved.
     *
     * @return string
     */
    public function get_body(): string {
        $body = (string) $this->get_option( 'body', $this->get_default_body() );

        return $this->format_string( $body );
    }

    /**
     * Default copy. Subclasses supply these.
     *
     * @return string
     */
    abstract public function get_default_body(): string;

    /**
     * The call to action rendered as a button beneath the body.
     *
     * @return array{label: string, url: string}
     */
    public function cta(): array {
        $cta = array(
            'label' => __( 'Manage your license', 'lytrod-emails' ),
            'url'   => wc_get_account_endpoint_url( 'subscriptions' ),
        );

        /**
         * Filter the call to action on a licence lifecycle email.
         *
         * @since 1.1.0
         * @param array    $cta   {label, url}.
         * @param WC_Email $email Email instance.
         */
        return (array) apply_filters( 'lytrod_emails_subscription_cta', $cta, $this );
    }

    /**
     * Send, given a subscription id.
     *
     * @param int $subscription_id Subscription id.
     * @return bool Whether the message was handed off successfully.
     */
    public function trigger( $subscription_id ): bool {
        $subscription = function_exists( 'wcs_get_subscription' ) ? wcs_get_subscription( $subscription_id ) : false;

        if ( ! $subscription ) {
            return false;
        }

        $this->setup_locale();

        try {
            $this->object    = $subscription;
            $this->recipient = $subscription->get_billing_email();

            $this->fill_placeholders( $subscription );

            if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
                return false;
            }

            $sent = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

            $subscription->add_order_note(
                $sent
                    /* translators: 1: email title, 2: recipient address. */
                    ? sprintf( __( '%1$s sent to %2$s.', 'lytrod-emails' ), $this->title, $this->recipient )
                    /* translators: 1: email title, 2: recipient address. */
                    : sprintf( __( 'Attempt to send %1$s to %2$s failed.', 'lytrod-emails' ), $this->title, $this->recipient )
            );

            return (bool) $sent;
        } finally {
            $this->restore_locale();
        }
    }

    /**
     * HTML body.
     *
     * @return string
     */
    public function get_content_html(): string {
        return wc_get_template_html(
            $this->template_html,
            array(
                'subscription'       => $this->object,
                'email_heading'      => $this->get_heading(),
                'body'               => $this->get_body(),
                'cta'                => $this->cta(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Plain-text body.
     *
     * @return string
     */
    public function get_content_plain(): string {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'subscription'       => $this->object,
                'email_heading'      => $this->get_heading(),
                'body'               => $this->get_body(),
                'cta'                => $this->cta(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => true,
                'email'              => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Sending address, scoped to these emails.
     *
     * @param string $from_email Incoming.
     * @return string
     */
    public function get_from_address( $from_email = '' ): string {
        /**
         * Filter the From address on licence lifecycle emails.
         *
         * @since 1.1.0
         * @param string   $address From address.
         * @param WC_Email $email   Email instance.
         */
        return sanitize_email( apply_filters( 'lytrod_emails_subscription_from_address', self::FROM_ADDRESS, $this ) );
    }

    /**
     * Sending name, scoped to these emails.
     *
     * @param string $from_name Incoming.
     * @return string
     */
    public function get_from_name( $from_name = '' ): string {
        /**
         * Filter the From name on licence lifecycle emails.
         *
         * @since 1.1.0
         * @param string   $name  From name.
         * @param WC_Email $email Email instance.
         */
        $name = apply_filters( 'lytrod_emails_subscription_from_name', self::FROM_NAME, $this );

        return wp_specialchars_decode( esc_html( $name ), ENT_QUOTES );
    }

    /**
     * Headers, plus the archive BCC.
     *
     * Appended here rather than via `get_bcc_recipient()` because WooCommerce only emits a Bcc
     * header while the `email_improvements` feature is on.
     *
     * @return string
     */
    public function get_headers(): string {
        $headers = (string) parent::get_headers();

        /**
         * Filter the BCC address on licence lifecycle emails. Return '' to drop it.
         *
         * @since 1.1.0
         * @param string   $bcc   BCC address.
         * @param WC_Email $email Email instance.
         */
        $bcc = (string) apply_filters( 'lytrod_emails_subscription_bcc', self::BCC_ADDRESS, $this );

        if ( '' !== $bcc && is_email( $bcc ) && false === stripos( $headers, 'bcc:' ) ) {
            $headers .= 'Bcc: ' . sanitize_text_field( $bcc ) . "\r\n";
        }

        return $headers;
    }
}
