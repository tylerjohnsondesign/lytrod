<?php
/**
 * Renewal reminder: 3 days out, payment method on file.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Your card will be charged in a few days."
 */
class Lytrod_Emails_Renewal_3_Stored extends Lytrod_Emails_Renewal_Reminder {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id          = 'lytrod_renewal_3_stored';
        $this->title       = __( 'License renewal — 3 days, card on file', 'lytrod-emails' );
        $this->description = __( 'Sent 3 days before a license renews, when the customer has a payment method stored.', 'lytrod-emails' );
        $this->days        = 3;
        $this->has_payment = true;

        parent::__construct();
    }

    /**
     * Default subject.
     *
     * @return string
     */
    public function get_default_subject(): string {
        return __( '{product_name} License Renewal Processing Soon', 'lytrod-emails' );
    }

    /**
     * Default heading.
     *
     * @return string
     */
    public function get_default_heading(): string {
        return __( 'Your license renews on {renewal_date}', 'lytrod-emails' );
    }

    /**
     * Default body.
     *
     * @return string
     */
    public function get_default_body(): string {
        return __(
            "Hello {first_name},\n\n"
            . "This is a reminder that your licensing for {product_license_line} expires in three days.\n\n"
            . "Your credit card on file will be automatically charged on {renewal_date} to renew your license and prevent any service interruption.\n\n"
            . "If you need to make changes to your payment method or review your subscription, please visit:\n"
            . "{subscriptions_url}\n\n"
            . "If you have any questions or require assistance, please contact us at {admin_email}.\n\n"
            . "Best regards,\n"
            . "Lytrod Software Licensing Team",
            'lytrod-emails'
        );
    }
}
