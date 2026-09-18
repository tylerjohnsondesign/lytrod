<?php
/**
 * Sent when a licence stops working.
 *
 * Fires on the status transitions rather than on a schedule, so it lands when the customer
 * actually loses access. It replaces WooCommerce Subscriptions' separate on-hold and expired
 * customer emails with one message, which is how the plugin it supersedes behaved.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Your license has ended."
 */
class Lytrod_Emails_License_Ended extends Lytrod_Emails_Subscription_Email {

    /**
     * Marker recording that this customer has already been told.
     */
    const META_SENT = '_lytrod_ended_email_sent';

    /**
     * The marker written by the plugin this replaces.
     *
     * Honoured as well as our own. Eight subscriptions already carry it; without this they would
     * be told a second time the next time they transitioned.
     */
    const META_SENT_LEGACY = '_su_ended_email_sent';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id          = 'lytrod_license_ended';
        $this->title       = __( 'License ended', 'lytrod-emails' );
        $this->description = __( 'Sent when a license is suspended or expires. Replaces the separate on-hold and expired emails.', 'lytrod-emails' );

        parent::__construct();
    }

    /**
     * Default subject.
     *
     * @return string
     */
    public function get_default_subject(): string {
        return __( 'Your {product} subscription has ended', 'lytrod-emails' );
    }

    /**
     * Default heading.
     *
     * @return string
     */
    public function get_default_heading(): string {
        return __( 'Your license has ended', 'lytrod-emails' );
    }

    /**
     * Default body.
     *
     * @return string
     */
    public function get_default_body(): string {
        return __(
            "Hello {customer_name},\n\n"
            . "Your subscription to {product} has ended.\n\n"
            . "Click below to resubscribe in one step — choose your term and we'll take you straight to a secure payment page:\n"
            . "{resubscribe_url}\n\n"
            . "Or visit your account to manage everything: {my_account_url}\n\n"
            . "If you have any questions, please reply to this email.\n\n"
            . "Best regards,\n"
            . "{site_name}",
            'lytrod-emails'
        );
    }

    /**
     * Resubscribing is the point of this email.
     *
     * @return array{label: string, url: string}
     */
    public function cta(): array {
        $cta = array(
            'label' => __( 'Resubscribe', 'lytrod-emails' ),
            'url'   => $this->object instanceof WC_Subscription
                ? self::resubscribe_url( $this->object )
                : wc_get_account_endpoint_url( 'subscriptions' ),
        );

        /** This filter is documented in includes/emails/class-lytrod-emails-subscription-email.php */
        return (array) apply_filters( 'lytrod_emails_subscription_cta', $cta, $this );
    }

    /**
     * Whether this customer has already been told their licence ended.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return bool
     */
    public static function already_notified( $subscription ): bool {
        foreach ( array( self::META_SENT, self::META_SENT_LEGACY ) as $key ) {
            if ( '' !== (string) $subscription->get_meta( $key, true ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record that we have told them.
     *
     * Written with save_meta_data() rather than a full save(): a full save bumps
     * `date_modified`, which WooCommerce Subscriptions' own notification batch processor uses as
     * its cursor.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return void
     */
    public static function mark_notified( $subscription ): void {
        $subscription->update_meta_data( self::META_SENT, current_time( 'mysql' ) );
        $subscription->save_meta_data();
    }
}
