<?php
/**
 * Renewal reminder: 30 days out, payment method on file.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * "Your licence renews in 30 days and your card will be charged."
 */
class Lytrod_Emails_Renewal_30_Stored extends Lytrod_Emails_Renewal_Reminder {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id          = 'lytrod_renewal_30_stored';
        $this->title       = __( 'License renewal — 30 days, card on file', 'lytrod-emails' );
        $this->description = __( 'Sent 30 days before a license renews, when the customer has a payment method stored.', 'lytrod-emails' );
        $this->days        = 30;
        $this->has_payment = true;

        parent::__construct();
    }

    /**
     * Default subject.
     *
     * @return string
     */
    public function get_default_subject(): string {
        return __( 'Upcoming License Renewal for {product_name}', 'lytrod-emails' );
    }

    /**
     * Default heading.
     *
     * @return string
     */
    public function get_default_heading(): string {
        return __( 'Your license renews in {days_until_renewal} days', 'lytrod-emails' );
    }

    /**
     * Default body.
     *
     * @return string
     */
    public function get_default_body(): string {
        return __(
            "Hello {first_name},\n\n"
            . "This is a courtesy reminder that your licensing for {product_license_line} is set to expire in 30 days.\n\n"
            . "Your existing credit card on file will be automatically charged on {renewal_date} to ensure uninterrupted access. No action is required at this time.\n\n"
            . "If you would like to review your subscription details or update billing information, you may do so at the link below:\n"
            . "{subscriptions_url}\n\n"
            . "If you have any questions, please feel free to contact us at {admin_email}.\n\n"
            . "Best regards,\n"
            . "Lytrod Software Licensing Team",
            'lytrod-emails'
        );
    }
}
