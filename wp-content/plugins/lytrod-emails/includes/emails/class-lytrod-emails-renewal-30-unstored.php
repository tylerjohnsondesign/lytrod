<?php
/**
 * Renewal reminder: 30 days out, no payment method on file.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Your licence expires in 30 days and we have no way to charge you."
 */
class Lytrod_Emails_Renewal_30_Unstored extends Lytrod_Emails_Renewal_Reminder {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id          = 'lytrod_renewal_30_unstored';
        $this->title       = __( 'License expiring — 30 days, no card on file', 'lytrod-emails' );
        $this->description = __( 'Sent 30 days before a license expires, when no payment method is stored.', 'lytrod-emails' );
        $this->days        = 30;
        $this->has_payment = false;

        parent::__construct();
    }

    /**
     * Default subject.
     *
     * @return string
     */
    public function get_default_subject(): string {
        return __( 'Action Required: {product_name} License Expiring in 30 Days', 'lytrod-emails' );
    }

    /**
     * Default heading.
     *
     * @return string
     */
    public function get_default_heading(): string {
        return __( 'Action required — your license expires {renewal_date}', 'lytrod-emails' );
    }

    /**
     * Default body.
     *
     * @return string
     */
    public function get_default_body(): string {
        return __(
            "Hello {first_name},\n\n"
            . "Your licensing for {product_license_line} will expire in 30 days on {renewal_date}.\n\n"
            . "We do not currently have payment information on file for your account. To renew your license and avoid service interruption, payment details must be added before the expiration date.\n\n"
            . "Please log in to your account to review your subscription and add billing information:\n"
            . "{subscriptions_url}\n\n"
            . "If payment information is not added, your license will expire at the end of the current term and software access will be lost.\n\n"
            . "If you have any questions or require assistance, please contact us at {admin_email}.\n\n"
            . "Best regards,\n"
            . "Lytrod Software Licensing Team",
            'lytrod-emails'
        );
    }
}
