<?php
/**
 * Builds and sends renewal reminder emails.
 */
class LRE_Mailer {

    const FROM_ADDRESS = 'lytrodlive@lytrod.com';
    const FROM_NAME    = 'Lytrod Software Licensing Team';
    const BCC_ADDRESS  = 'lytrodsoftwaredocs@gmail.com';

    public static function init() {
		add_filter( 'wp_mail_content_type', [ __CLASS__, 'set_html_content_type' ] );
		add_action( 'admin_post_lre_send_test', [ __CLASS__, 'handle_test_send' ] );

		// Force correct From name and address
		add_filter( 'wp_mail_from', function() {
			return self::FROM_ADDRESS;
		});
		add_filter( 'wp_mail_from_name', function() {
			return self::FROM_NAME;
		});
	}

    public static function set_html_content_type() {
        return 'text/html';
    }

    /**
     * Choose the correct template for a subscription and send the email.
     */
    public static function send_for_subscription( WC_Subscription $subscription, int $days ) {
        $customer_id = $subscription->get_customer_id();
        if ( ! $customer_id ) {
            return;
        }

        $has_payment = self::has_payment_method( $subscription );
        $email_id    = "{$days}_" . ( $has_payment ? 'with_payment' : 'without_payment' );
        $settings    = LRE_Settings::get( $email_id );

        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        $customer     = new WC_Customer( $customer_id );
        $to           = $customer->get_email();
        $placeholders = self::build_placeholders( $subscription, $customer );

        $subject = self::replace( $settings['subject'], $placeholders );
        $body    = self::replace( $settings['body'], $placeholders );

        // Convert plain-text line breaks to HTML paragraphs / <br>
        $html_body = wpautop( wp_kses_post( $body ) );
        $html_body = self::wrap_in_template( $html_body, $subject ); 

        $headers = self::build_headers();

        wp_mail( $to, $subject, $html_body, $headers );

        // Log the send
        self::log( "Sent [{$email_id}] to {$to} for subscription #{$subscription->get_id()}" );
    }

    /**
     * Send a test/preview email directly from the admin settings page.
     * Triggered by the "Send Test Email" form action.
     */
    public static function handle_test_send() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorised' );
		}

		check_admin_referer( 'lre_send_test' );

		$email_id = sanitize_key( $_POST['lre_test_email_id'] ?? '' );
		$to       = sanitize_email( $_POST['lre_test_recipient'] ?? '' );

		if ( ! $to || ! array_key_exists( $email_id, LRE_Settings::EMAILS ) ) {
			wp_redirect( add_query_arg(
				[ 'lre_test' => 'invalid', 'page' => 'lre-settings' ],
				admin_url( 'admin.php' )
			) );
			exit;
		}

		$settings = LRE_Settings::get( $email_id );

		/*
		-----------------------------------------
		Test subscription data
		-----------------------------------------
		*/

		$subscription_id = 28851;
		$subscription    = function_exists( 'wcs_get_subscription' )
			? wcs_get_subscription( $subscription_id )
			: false;

		$product_name = 'Test Product';
		$renewal_date = date_i18n( get_option( 'date_format' ), strtotime( '+30 days' ) );

		if ( $subscription ) {

			// Renewal date
			$next_payment = $subscription->get_date( 'next_payment' );
			$end_date     = $subscription->get_date( 'end' );
			$renewal      = $next_payment ?: $end_date;

			if ( $renewal ) {
				$renewal_date = date_i18n(
					get_option( 'date_format' ),
					strtotime( $renewal )
				);
			}

			// Get parent product name
			foreach ( $subscription->get_items() as $item ) {

				$parent_product_id = $item->get_product_id();
				$parent_product    = wc_get_product( $parent_product_id );

				if ( $parent_product ) {
					$product_name = $parent_product->get_name();
				}

				$product_name = wp_strip_all_tags( $product_name );
				$product_name = preg_replace(
					'/\s*-\s*(Sign\s*up|Signup|Registration)\s*License/i',
					'',
					$product_name
				);
				$product_name = trim( $product_name );

				break;
			}
		}

		/*
		-----------------------------------------
		Placeholders
		-----------------------------------------
		*/

		$placeholders = [
			'{first_name}'           => 'Alex',
			'{display_name}'         => 'Alex Smith',
			'{product_name}'         => $product_name,
			'{serial_number}'        => '2174432',
			'{product_license_line}' => $product_name . ' serial number 2174432',
			'{renewal_date}'         => $renewal_date,
			'{subscription_id}'      => $subscription_id,
			'{subscriptions_url}'    => site_url( '/my-account/subscriptions/' ),
			'{admin_email}'          => get_option( 'admin_email' ),
			'{site_name}'            => get_bloginfo( 'name' ),
		];

		/*
		-----------------------------------------
		Build email
		-----------------------------------------
		*/

		$subject = self::replace( $settings['subject'], $placeholders );
		$body    = self::replace( $settings['body'], $placeholders );

		$html_body = wpautop( esc_html( $body ) );
		$html_body = self::wrap_in_template( $html_body, $subject );

		$headers = self::build_headers();

		$sent = wp_mail(
			$to,
			'[TEST] ' . $subject,
			$html_body,
			$headers
		);

		self::log(
			"Test email [{$email_id}] " .
			( $sent ? 'sent' : 'FAILED' ) .
			" to {$to}"
		);

		wp_redirect( add_query_arg(
			[
				'lre_test'    => $sent ? 'sent' : 'failed',
				'lre_test_to' => rawurlencode( $to ),
				'page'        => 'lre-settings',
			],
			admin_url( 'admin.php' )
		) );

		exit;
	}

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Build the email headers array, including From and BCC.
     */
    private static function build_headers(): array {
        return [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . self::FROM_NAME . ' <' . self::FROM_ADDRESS . '>',
            'Bcc: ' . self::BCC_ADDRESS,
        ];
    }

    /**
     * Determine whether the subscription has a usable payment method token on file.
     */
    private static function has_payment_method( WC_Subscription $subscription ): bool {
        $payment_method = $subscription->get_payment_method();
        if ( empty( $payment_method ) || $payment_method === 'manual' ) {
            return false;
        }

        $customer_id = $subscription->get_customer_id();
        $tokens      = WC_Payment_Tokens::get_customer_tokens( $customer_id, $payment_method );

        return ! empty( $tokens );
    }

    /**
     * Retrieve the serial number / product ID from subscription meta.
     * Checks known meta keys in priority order and returns the first non-empty value.
     */
    private static function get_serial_number( int $sub_id ): string {
        $serial_keys = [
            'intellicut_serial_number',
            'intellicut_global_product_id',
            'vrcutimposeinstallation_product_id',
            'intellicutaneinstallation_product_id',
            'installation_product_id',
            'bizcard_product_id',
        ];

        foreach ( $serial_keys as $key ) {
            $value = get_post_meta( $sub_id, $key, true );
            if ( ! empty( $value ) ) {
                return (string) $value;
            }
        }

        return '';
    }

    private static function build_placeholders( WC_Subscription $subscription, WC_Customer $customer ): array {
        $next_payment = $subscription->get_date( 'next_payment' );
        $end_date     = $subscription->get_date( 'end' );
        $renewal_date = $next_payment ?: $end_date;
        $formatted    = $renewal_date
            ? date_i18n( get_option( 'date_format' ), strtotime( $renewal_date ) )
            : 'N/A';

       $product_name = '';
	   foreach ( $subscription->get_items() as $item ) {

			$parent_product_id = $item->get_product_id();
			$parent_product    = wc_get_product( $parent_product_id );

			if ( $parent_product ) {
				$product_name = $parent_product->get_name();
			}

			// Clean the name
			$product_name = wp_strip_all_tags( $product_name );
			$product_name = preg_replace('/\s*-\s*(Sign\s*up|Signup|Registration)\s*License/i', '', $product_name);
			$product_name = trim( $product_name );

			break;
		}

        $sub_id        = $subscription->get_id();
        $serial_number = self::get_serial_number( $sub_id );

        // Build the combined "Product Name License Serial Number XXXX" line.
        // If no serial number is found, falls back to just the product name.
      $product_license_line = ! empty( $serial_number )
		? "{$product_name} serial number {$serial_number}"
		: "{$product_name}";

        return [
            '{first_name}'           => $customer->get_first_name(),
            '{display_name}'         => $customer->get_display_name(),
            '{product_name}'         => $product_name,
            '{serial_number}'        => $serial_number,
            '{product_license_line}' => $product_license_line,
            '{renewal_date}'         => $formatted,
            '{subscription_id}'      => (string) $sub_id,
            '{subscriptions_url}'    => site_url( '/my-account/subscriptions/' ),
            '{admin_email}'          => get_option( 'admin_email' ),
            '{site_name}'            => get_bloginfo( 'name' ),
        ];
    }

    private static function replace( string $text, array $placeholders ): string {
        return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );
    }

    private static function wrap_in_template( string $content, string $subject = '' ): string {
        $site_url  = esc_url( get_site_url() );
        $site_name = esc_html( get_bloginfo( 'name' ) );
		$header_text = $subject ? esc_html( $subject ) : 'Lytrod Software';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<title>Lytrod License Renewal</title>
<style>
  body { margin:0; padding:0; background:#f3f4f6; font-family: Arial, sans-serif; color:#1f2937; }
  .wrapper { max-width:620px; margin:40px auto; background:#fff; border-radius:6px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
  .header { background:#1a3a5c; padding:28px 36px; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:600; letter-spacing:.5px; }
  .body { padding:32px 36px; font-size:15px; line-height:1.7; color:#374151; }
  .body-spacer { height:12px; }
  .body p { margin:0 0 16px; }
  .body a { color:#1a6fbf; }
  .footer { background:#f9fafb; padding:20px 36px; font-size:12px; color:#9ca3af; border-top:1px solid #e5e7eb; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{$header_text}</h1>
  </div>
  <div class="body">
    <div class="body-spacer"></div>
    {$content}
  </div>
  <div class="footer">
    &copy; Lytrod Software &mdash; <a href="{$site_url}" style="color:#9ca3af;">{$site_name}</a>
  </div>
</div>
</body>
</html>
HTML;
    }

    private static function log( string $message ) {
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( '[Lytrod Renewal Emails] ' . $message );
        }
    }
}