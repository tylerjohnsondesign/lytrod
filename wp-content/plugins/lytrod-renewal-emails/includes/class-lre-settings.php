<?php
/**
 * Admin settings page: enable/disable emails and edit all four templates.
 */
class LRE_Settings {

    const OPTION_KEY = 'lre_settings';

    /** Email IDs */
    const EMAILS = [
        '30_with_payment'    => '30 Days — Payment Info on File',
        '3_with_payment'     => '3 Days — Payment Info on File',
        '30_without_payment' => '30 Days — No Payment Info on File',
        '3_without_payment'  => '3 Days — No Payment Info on File',
    ];

    /** Default values for each template — URLs and email address are generated dynamically at runtime via self::get_defaults() */
    const DEFAULTS = [];

    /**
     * Build defaults at runtime so site_url() and get_option() are available.
     */
    public static function get_defaults(): array {
        $subscriptions_url = site_url( '/my-account/subscriptions/' );
        $admin_email       = get_option( 'admin_email' );

        return [
            '30_with_payment' => [
                'enabled' => true,
                'subject' => 'Upcoming License Renewal for {product_name}',
                'body'    =>
"Hello {first_name},

This is a courtesy reminder that your licensing for {product_name} is set to expire in 30 days.

Your existing credit card on file will be automatically charged on {renewal_date} to ensure uninterrupted access. No action is required at this time.

If you would like to review your subscription details or update billing information, you may do so at the link below:
{subscriptions_url}

If you have any questions, please feel free to contact us at {admin_email}.

Best regards,
Lytrod Software Licensing Team",
            ],
            '3_with_payment' => [
                'enabled' => true,
                'subject' => '{product_name} License Renewal Processing Soon',
                'body'    =>
"Hello {first_name},

This is a reminder that your licensing for {product_name} expires in three days.

Your credit card on file will be automatically charged on {renewal_date} to renew your license and prevent any service interruption.

If you need to make changes to your payment method or review your subscription, please visit:
{subscriptions_url}

If you have any questions or require assistance, please contact us at {admin_email}.

Best regards,
Lytrod Software Licensing Team",
            ],
            '30_without_payment' => [
                'enabled' => true,
                'subject' => 'Action Required — {product_name} License Expiring in 30 Days',
                'body'    =>
"Hello {first_name},

Your licensing for {product_name} will expire in 30 days on {renewal_date}.

We do not currently have payment information on file for your account. To renew your license and avoid service interruption, payment details must be added before the expiration date.

Please log in to your account to review your subscription and add billing information:
{subscriptions_url}

If payment information is not added, your license will expire at the end of the current term and software access will be lost.

If you have any questions or require assistance, please contact us at {admin_email}.

Best regards,
Lytrod Software Licensing Team",
            ],
            '3_without_payment' => [
                'enabled' => true,
                'subject' => '{product_name} License Expiring in 3 Days',
                'body'    =>
"Hello {first_name},

Your licensing for {product_name} expires in 3 days on {renewal_date}.

We do not currently have payment information on file, and your license will expire unless billing details are added.

To renew your license and prevent service interruption, please log in and add payment information as soon as possible:
{subscriptions_url}

If you have any questions or require assistance, please contact us at {admin_email}.

Regards,
Lytrod Software Licensing Team",
            ],
        ];
    }

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function add_menu() {
        add_submenu_page(
            'woocommerce',
            'Renewal Reminder Emails',
            'Renewal Reminders',
            'manage_options',
            'lre-settings',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings() {
        register_setting( 'lre_settings_group', self::OPTION_KEY, [ __CLASS__, 'sanitize' ] );
    }

    public static function sanitize( $input ) {
        $clean = [];
        foreach ( array_keys( self::EMAILS ) as $id ) {
            $clean[ $id ]['enabled'] = ! empty( $input[ $id ]['enabled'] );
            $clean[ $id ]['subject'] = sanitize_text_field( $input[ $id ]['subject'] ?? '' );
            $clean[ $id ]['body']    = wp_kses_post( $input[ $id ]['body'] ?? '' );
        }
        return $clean;
    }

    /** Return saved settings, merged with defaults. */
    public static function get( string $email_id ): array {
        $saved    = get_option( self::OPTION_KEY, [] );
        $defaults = self::get_defaults();
        return wp_parse_args( $saved[ $email_id ] ?? [], $defaults[ $email_id ] );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $saved = get_option( self::OPTION_KEY, [] );

        // ----------------------------------------------------------------
        // Test email feedback notices
        // ----------------------------------------------------------------
        if ( isset( $_GET['lre_test'] ) ) {
            $result = $_GET['lre_test'];
            $sent_to = isset( $_GET['lre_test_to'] ) ? rawurldecode( $_GET['lre_test_to'] ) : '';
            if ( $result === 'sent' ) {
                echo '<div class="notice notice-success is-dismissible"><p>✅ Test email sent successfully to <strong>' . esc_html( $sent_to ) . '</strong>. A BCC copy was also sent to <strong>lytrodsoftwaredocs@gmail.com</strong>.</p></div>';
            } elseif ( $result === 'failed' ) {
                echo '<div class="notice notice-error is-dismissible"><p>❌ Test email <strong>failed to send</strong>. Please check your WP Mail SMTP configuration.</p></div>';
            } elseif ( $result === 'invalid' ) {
                echo '<div class="notice notice-error is-dismissible"><p>❌ Invalid recipient address or email template selected.</p></div>';
            }
        }
        ?>
        <div class="wrap">
            <h1>Lytrod — Subscription Renewal Reminder Emails</h1>

            <!-- ============================================================
                 TEST EMAIL PANEL
                 ============================================================ -->
            <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #1a3a5c;padding:20px 24px;margin-bottom:32px;border-radius:4px;max-width:700px;">
                <h2 style="margin-top:0;font-size:15px;text-transform:uppercase;letter-spacing:.5px;color:#1a3a5c;">🧪 Send a Test Email</h2>
                <p style="margin-top:0;color:#555;">Send a preview of any template to a specific address. Sample placeholder values will be used (e.g. customer name "Alex Smith", product "Lytrod Pro License"). A BCC copy will automatically go to <strong>lytrodsoftwaredocs@gmail.com</strong> as well.</p>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                    <?php wp_nonce_field( 'lre_send_test' ); ?>
                    <input type="hidden" name="action" value="lre_send_test">

                    <div>
                        <label for="lre_test_email_id" style="display:block;font-weight:600;margin-bottom:4px;">Email Template</label>
                        <select id="lre_test_email_id" name="lre_test_email_id" style="min-width:280px;">
                            <?php foreach ( self::EMAILS as $id => $label ) : ?>
                                <option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="lre_test_recipient" style="display:block;font-weight:600;margin-bottom:4px;">Send Test To</label>
                        <input type="email"
                               id="lre_test_recipient"
                               name="lre_test_recipient"
                               placeholder="you@example.com"
                               required
                               style="min-width:240px;"
                               value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                    </div>

                    <div>
                        <button type="submit" class="button button-primary" style="height:30px;">Send Test Email</button>
                    </div>
                </form>

                <p style="margin-bottom:0;margin-top:12px;font-size:12px;color:#888;">
                    📬 From address: <code>lytrodlive@lytrod.com</code> &nbsp;|&nbsp;
                    📋 BCC on every send (test &amp; live): <code>lytrodsoftwaredocs@gmail.com</code>
                </p>
            </div>

            <!-- ============================================================
                 PLACEHOLDER REFERENCE TABLE
                 ============================================================ -->
            <p>Configure the four automated reminder emails sent to customers before their WooCommerce Subscription renews. Use the placeholders below inside subjects and body text:</p>
            <table class="widefat striped" style="max-width:600px;margin-bottom:24px;">
                <thead><tr><th>Placeholder</th><th>Replaced with</th></tr></thead>
                <tbody>
                    <tr><td><code>{first_name}</code></td><td>Customer's first name</td></tr>
                    <tr><td><code>{display_name}</code></td><td>Customer's display name</td></tr>
                    <tr><td><code>{product_name}</code></td><td>Subscription product name</td></tr>
                    <tr><td><code>{renewal_date}</code></td><td>Next renewal/expiry date (formatted)</td></tr>
                    <tr><td><code>{subscription_id}</code></td><td>WooCommerce Subscription ID</td></tr>
                    <tr><td><code>{subscriptions_url}</code></td><td>My Account → Subscriptions URL (dynamic)</td></tr>
                    <tr><td><code>{admin_email}</code></td><td>WordPress admin email (dynamic)</td></tr>
                    <tr><td><code>{site_name}</code></td><td>WordPress site name (dynamic)</td></tr>
                </tbody>
            </table>

            <!-- ============================================================
                 TEMPLATE EDITOR FORM
                 ============================================================ -->
            <form method="post" action="options.php">
                <?php settings_fields( 'lre_settings_group' ); ?>

                <?php foreach ( self::EMAILS as $id => $label ) :
                    $defaults = self::get_defaults();
                    $settings = wp_parse_args( $saved[ $id ] ?? [], $defaults[ $id ] );
                    $enabled  = ! empty( $settings['enabled'] );
                    $subject  = esc_attr( $settings['subject'] );
                    $body     = esc_textarea( $settings['body'] );
                    $border   = $enabled ? '#00a32a' : '#ccc';
                ?>
                <div style="border-left:4px solid <?php echo $border; ?>;padding:16px 20px;margin-bottom:28px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <h2 style="margin-top:0;"><?php echo esc_html( $label ); ?></h2>

                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-weight:600;">
                        <input type="checkbox"
                               name="<?php echo esc_attr( self::OPTION_KEY . "[$id][enabled]" ); ?>"
                               value="1"
                               <?php checked( $enabled ); ?>>
                        Enable this email
                    </label>

                    <p>
                        <label for="lre_<?php echo esc_attr( $id ); ?>_subject"><strong>Subject line</strong></label><br>
                        <input type="text"
                               id="lre_<?php echo esc_attr( $id ); ?>_subject"
                               name="<?php echo esc_attr( self::OPTION_KEY . "[$id][subject]" ); ?>"
                               value="<?php echo $subject; ?>"
                               class="large-text">
                    </p>

                    <p>
                        <label for="lre_<?php echo esc_attr( $id ); ?>_body"><strong>Email body</strong></label><br>
                        <textarea id="lre_<?php echo esc_attr( $id ); ?>_body"
                                  name="<?php echo esc_attr( self::OPTION_KEY . "[$id][body]" ); ?>"
                                  rows="14"
                                  class="large-text code"><?php echo $body; ?></textarea>
                    </p>
                </div>
                <?php endforeach; ?>

                <?php submit_button( 'Save Email Settings' ); ?>
            </form>
        </div>
        <?php
    }
}