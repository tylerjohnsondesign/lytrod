<?php
/**
 * Guards against WooCommerce features that would bypass this plugin's templates.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Feature-flag pinning and one-time option seeding.
 */
class Lytrod_Emails_Compat {

    /**
     * WooCommerce email colour options seeded on activation, so that
     * WooCommerce > Settings > Emails shows values consistent with what actually
     * ships. The stylesheet does not read these, so an admin editing them cannot
     * break the design either way.
     *
     * @return array<string, string>
     */
    private static function seed_options(): array {
        return array(
            'woocommerce_email_base_color'            => Lytrod_Emails_Brand::c( 'primary' ),
            'woocommerce_email_background_color'      => Lytrod_Emails_Brand::c( 'surface_alt' ),
            'woocommerce_email_body_background_color' => Lytrod_Emails_Brand::c( 'surface' ),
            'woocommerce_email_text_color'            => Lytrod_Emails_Brand::c( 'ink' ),
            'woocommerce_email_footer_text_color'     => Lytrod_Emails_Brand::c( 'ink_muted' ),
        );
    }

    /**
     * Register the guards. Called at file load so the pin is in place before
     * `woocommerce_init`.
     *
     * @return void
     */
    public static function init(): void {
        if ( ! defined( 'LYTROD_EMAILS_ALLOW_BLOCK_EDITOR' ) || ! LYTROD_EMAILS_ALLOW_BLOCK_EDITOR ) {
            add_filter( 'pre_option_woocommerce_feature_block_email_editor_enabled', array( __CLASS__, 'disable_block_editor' ) );
        }

        add_action( 'admin_notices', array( __CLASS__, 'features_screen_notice' ) );
    }

    /**
     * Force WooCommerce's block email editor off.
     *
     * The block editor is not a cosmetic variation. When it is on and a
     * `woo_email` post exists for an email, WC_Email::get_content() returns early
     * (woocommerce/includes/emails/class-wc-email.php:858-861) and never loads the
     * classic template at all — so email-header.php, email-footer.php and every
     * per-email template in this plugin are skipped. Worse,
     * WooContentProcessor::prepare_css() (src/Internal/EmailEditor/WooContentProcessor.php:58-64)
     * regex-strips every `color:` and `font-family:` declaration from the
     * stylesheet, silently deleting the brand palette.
     *
     * 17 published `woo_email` posts (IDs 29756-29772) and their
     * `woocommerce_email_templates_*_post_id` option rows already exist on this
     * install, and the flag has been toggled before. Deleting the posts is not a
     * fix: WCTransactionalEmailPostsGenerator::initialize() recreates them the next
     * time anyone loads a WooCommerce admin screen.
     *
     * `pre_option_` short-circuits get_option() entirely, so this also survives an
     * admin flipping the toggle on the Features screen. Define
     * LYTROD_EMAILS_ALLOW_BLOCK_EDITOR as true in wp-config.php to lift it.
     *
     * @return string
     */
    public static function disable_block_editor(): string {
        return 'no';
    }

    /**
     * Explain the pin on the screen where an admin would try to undo it.
     *
     * Without this, toggling "Block Email Editor" appears to silently do nothing.
     *
     * @return void
     */
    public static function features_screen_notice(): void {
        if ( defined( 'LYTROD_EMAILS_ALLOW_BLOCK_EDITOR' ) && LYTROD_EMAILS_ALLOW_BLOCK_EDITOR ) {
            return;
        }

        if ( ! function_exists( 'get_current_screen' ) ) {
            return;
        }

        $screen = get_current_screen();

        if ( ! $screen || 'woocommerce_page_wc-settings' !== $screen->id ) {
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
        $tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
        $section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ( 'advanced' !== $tab || 'features' !== $section ) {
            return;
        }

        echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Lytrod Emails', 'lytrod-emails' ) . '</strong> ';
        echo esc_html__( 'keeps the Block Email Editor switched off. The block editor bypasses this plugin\'s email templates entirely and strips brand colours from the stylesheet, so toggling it here will have no effect.', 'lytrod-emails' ) . ' ';
        echo esc_html__( 'To lift the lock, define LYTROD_EMAILS_ALLOW_BLOCK_EDITOR as true in wp-config.php.', 'lytrod-emails' );
        echo '</p></div>';
    }

    /**
     * Seed the WooCommerce email colour options on activation.
     *
     * @return void
     */
    public static function activate(): void {
        foreach ( self::seed_options() as $option => $value ) {
            update_option( $option, $value );
        }
    }
}
