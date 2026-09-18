<?php
/**
 * Renewal reminder: 3 days out, no payment method on file.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * The last warning before a licence lapses.
 */
class Lytrod_Emails_Renewal_3_Unstored extends Lytrod_Emails_Renewal_Reminder {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id          = 'lytrod_renewal_3_unstored';
        $this->title       = __( 'License expiring — 3 days, no card on file', 'lytrod-emails' );
        $this->description = __( 'Sent 3 days before a license expires, when no payment method is stored.', 'lytrod-emails' );
        $this->days        = 3;
        $this->has_payment = false;

        parent::__construct();
    }

    /**
     * Default subject.
     *
     * @return string
     */
    public function get_default_subject(): string {
        return __( 'Action Required: {product_name} License Expiring in 3 Days', 'lytrod-emails' );
    }

    /**
     * Default heading.
     *
     * @return string
     */
    public function get_default_heading(): string {
        return __( 'Your license expires {renewal_date}', 'lytrod-emails' );
    }

    /**
     * Default body.
     *
     * @return string
     */
    public function get_default_body(): string {
        return __(
            "Hello {first_name},\n\n"
            . "Your licensing for {product_license_line} expires in 3 days on {renewal_date}.\n\n"
            . "We do not currently have payment information on file, and your license will expire unless billing details are added.\n\n"
            . "To renew your license and prevent service interruption, please log in and add payment information as soon as possible:\n"
            . "{subscriptions_url}\n\n"
            . "If you have any questions or require assistance, please contact us at {admin_email}.\n\n"
            . "Regards,\n"
            . "Lytrod Software Licensing Team",
            'lytrod-emails'
        );
    }
}
