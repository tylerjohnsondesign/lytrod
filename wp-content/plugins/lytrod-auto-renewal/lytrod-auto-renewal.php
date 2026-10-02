<?php
/**
 * Plugin Name: Lytrod Subscription Auto-Renewal
 * Plugin URI:  https://lytrod.com
 * Description: Makes WooCommerce Subscriptions auto-billing work for customers with a saved card: attaches account payment tokens to their subscriptions, and extends the custom licence expiry meta whenever a renewal is paid.
 * Version:     1.0.0
 * Author:      Lytrod Software
 * Text Domain: lytrod-auto-renewal
 * Requires Plugins: woocommerce, woocommerce-subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LAR_VERSION', '1.0.0' );
define( 'LAR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once LAR_PLUGIN_DIR . 'includes/class-lar-license.php';
require_once LAR_PLUGIN_DIR . 'includes/class-lar-token-sync.php';

/**
 * Initialise once WooCommerce & Subscriptions are loaded.
 */
add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Subscriptions' ) ) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p><strong>Lytrod Auto-Renewal</strong> requires WooCommerce and WooCommerce Subscriptions to be active.</p></div>';
				}
			);
			return;
		}

		LAR_License::init();
		LAR_Token_Sync::init();

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once LAR_PLUGIN_DIR . 'includes/class-lar-cli.php';
			WP_CLI::add_command( 'lytrod-billing', 'LAR_CLI' );
		}
	}
);

/**
 * Clear the orphaned 'check_and_expire_subscriptions' event.
 *
 * Its registration lived in subscriptions-upgrader and has been removed, but any
 * event already written to the cron array would otherwise sit there forever. This
 * runs once on activation and is also safe to call repeatedly.
 */
function lar_clear_orphaned_cron() {
	wp_clear_scheduled_hook( 'check_and_expire_subscriptions' );
}
register_activation_hook( __FILE__, 'lar_clear_orphaned_cron' );
