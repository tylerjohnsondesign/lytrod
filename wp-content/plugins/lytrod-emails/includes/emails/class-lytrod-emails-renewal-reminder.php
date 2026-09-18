<?php
/**
 * Shared base for the four renewal reminders.
 *
 * The reminders vary along two axes only — how far out the renewal is (30 or 3 days) and whether
 * the customer has a payment method stored. Everything else is inherited.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * A renewal reminder for one window and one payment state.
 */
abstract class Lytrod_Emails_Renewal_Reminder extends Lytrod_Emails_Subscription_Email {

    /**
     * Days before the renewal that this reminder is sent.
     *
     * @var int
     */
    protected $days = 30;

    /**
     * Whether this variant addresses customers who have a card on file.
     *
     * @var bool
     */
    protected $has_payment = true;

    /**
     * How many days out this reminder fires.
     *
     * @return int
     */
    public function days(): int {
        return $this->days;
    }

    /**
     * Whether this variant is the one for customers with a stored payment method.
     *
     * @return bool
     */
    public function expects_stored_payment(): bool {
        return $this->has_payment;
    }

    /**
     * Whether a subscription has a usable stored payment method.
     *
     * Hardened relative to the implementation this replaces, which checked only that a token
     * existed under the subscription's own gateway id. A manual-renewal subscription can still
     * carry a stale gateway id, and that check would have misread it as automatic.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return bool
     */
    public static function has_stored_payment( $subscription ): bool {
        $stored = false;

        if ( $subscription instanceof WC_Subscription && ! $subscription->is_manual() ) {
            $gateway     = (string) $subscription->get_payment_method();
            $customer_id = (int) $subscription->get_customer_id();

            if ( '' !== $gateway && $customer_id > 0 ) {
                $stored = (bool) WC_Payment_Tokens::get_customer_tokens( $customer_id, $gateway );
            }
        }

        /**
         * Filter whether a subscription counts as having a stored payment method.
         *
         * @since 1.1.0
         * @param bool            $stored       Whether a token was found.
         * @param WC_Subscription $subscription Subscription.
         */
        return (bool) apply_filters( 'lytrod_emails_has_stored_payment', $stored, $subscription );
    }

    /**
     * Reminders point the customer at their subscription.
     *
     * @return array{label: string, url: string}
     */
    public function cta(): array {
        $cta = array(
            'label' => $this->has_payment
                ? __( 'Review your license', 'lytrod-emails' )
                : __( 'Add payment information', 'lytrod-emails' ),
            'url'   => wc_get_account_endpoint_url( 'subscriptions' ),
        );

        /** This filter is documented in includes/emails/class-lytrod-emails-subscription-email.php */
        return (array) apply_filters( 'lytrod_emails_subscription_cta', $cta, $this );
    }
}
