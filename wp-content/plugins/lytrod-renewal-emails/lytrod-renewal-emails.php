<?php
/**
 * Plugin Name: Lytrod Subscription Renewal Emails
 * Plugin URI:  https://lytrod.com
 * Description: Sends automated renewal reminder emails at 30 days and 3 days before WooCommerce Subscriptions expire, with separate templates for accounts with and without payment info on file.
 * Version:     1.0.0
 * Author:      Lytrod Software
 * Text Domain: lytrod-renewal-emails
 * Requires Plugins: woocommerce, woocommerce-subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LRE_VERSION', '1.0.0' );
define( 'LRE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LRE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once LRE_PLUGIN_DIR . 'includes/class-lre-settings.php';
require_once LRE_PLUGIN_DIR . 'includes/class-lre-scheduler.php';
require_once LRE_PLUGIN_DIR . 'includes/class-lre-mailer.php';

/**
 * Initialise the plugin once WooCommerce & Subscriptions are loaded.
 */
add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Subscriptions' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>Lytrod Renewal Emails</strong> requires WooCommerce and WooCommerce Subscriptions to be active.</p></div>';
        } );
        return;
    }

    LRE_Settings::init();
    LRE_Scheduler::init();
    LRE_Mailer::init();
} );

register_activation_hook( __FILE__, [ 'LRE_Scheduler', 'schedule_cron' ] );
register_deactivation_hook( __FILE__, [ 'LRE_Scheduler', 'unschedule_cron' ] );
