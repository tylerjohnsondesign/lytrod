<?php
/**
 * Plugin Name: Lytrod Licensing
 * Plugin URI:  https://tylerjohnsondesign.com
 * Description: Sells every licence by the seat from a single graduated rate table. Standard and Plus become the names for 1 and 3 seats; any seat count is purchasable.
 * Version:     1.0.0
 * Author:      Tyler Johnson
 * Author URI:  https://tylerjohnsondesign.com
 * License:     GPL-2.0-or-later
 * Text Domain: lytrod-licensing
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

define( 'LYTROD_LICENSING_VERSION', '1.0.0' );
define( 'LYTROD_LICENSING_FILE', __FILE__ );
define( 'LYTROD_LICENSING_DIR', plugin_dir_path( __FILE__ ) );
define( 'LYTROD_LICENSING_URL', plugin_dir_url( __FILE__ ) );

require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-rates.php';
require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-legacy.php';
require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-seats.php';
require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-cart.php';
require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-admin.php';
require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-provisioning.php';
require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-deploy.php';
    require_once LYTROD_LICENSING_DIR . 'includes/class-lytrod-licensing-cli.php';
}

add_action( 'plugins_loaded', array( 'Lytrod_Licensing', 'init' ) );
