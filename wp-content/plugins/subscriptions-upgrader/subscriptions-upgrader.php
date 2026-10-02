<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://webfielddesign.com/
 * @since             1.0.0
 * @package           Subscriptions_Upgrader
 *
 * @wordpress-plugin
 * Plugin Name:       Subscription Upgrades
 * Plugin URI:        https://https://webfielddesign.com/
 * Description:       "Enhanced Subscription Upgrades for WooCommerce: Streamlined upgrade feature allowing selection from existing products, customizable link names, and seamless transfer of subscription dates."
 * Version:           1.0.1
 * Author:            talktaimor
 * Author URI:        https://https://webfielddesign.com//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       subscriptions-upgrader
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SUBSCRIPTIONS_UPGRADER_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-subscriptions-upgrader-activator.php
 */
function activate_subscriptions_upgrader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subscriptions-upgrader-activator.php';
	Subscriptions_Upgrader_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-subscriptions-upgrader-deactivator.php
 */
function deactivate_subscriptions_upgrader() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subscriptions-upgrader-deactivator.php';
	Subscriptions_Upgrader_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_subscriptions_upgrader' );
register_deactivation_hook( __FILE__, 'deactivate_subscriptions_upgrader' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-subscriptions-upgrader.php';


add_filter('woocommerce_add_to_cart_validation', 'bypass_subscription_validation', 10, 3);

function bypass_subscription_validation($valid, $product_id, $quantity) {
    // Check if the product is a subscription product
    if (has_term('subscription', 'product_cat', $product_id) || get_post_meta($product_id, '_subscription', true)) {
        // Force product to be valid for adding to cart
        $valid = true;
    }
    
    return $valid;
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_subscriptions_upgrader() {

	$plugin = new Subscriptions_Upgrader();
	$plugin->run();

}
run_subscriptions_upgrader();

function handle_delete_payment_method_ajax() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'delete_payment_method_nonce' ) ) {
        wp_send_json_error( 'Nonce verification failed' );
    }

    $last4_digits = isset( $_POST['payment_method_last4'] ) ? sanitize_text_field( $_POST['payment_method_last4'] ) : '';

    // Without something to match on we cannot identify a token, and deleting an
    // arbitrary one is exactly the bug this guard exists to prevent.
    if ( '' === $last4_digits ) {
        wp_send_json_error( 'No payment method specified.' );
    }

    $user_id = get_current_user_id();

    if ( ! $user_id ) {
        wp_send_json_error( 'You must be logged in to delete a payment method.' );
    }

    $tokens = WC_Payment_Tokens::get_tokens( array( 'user_id' => $user_id ) );

    if ( empty( $tokens ) ) {
        wp_send_json_error( 'No payment methods found for this user.' );
    }

    foreach ( $tokens as $token ) {
        // Ownership is already scoped by the get_tokens() query above, but check
        // again so a future refactor of that call cannot widen the blast radius.
        if ( (int) $token->get_user_id() !== (int) $user_id ) {
            continue;
        }

        // Resolve the token's own last4 whatever its type. Tokens that have no
        // last4 at all (Stripe Link, Cash App) simply never match, rather than
        // being deleted as a fallback - the previous else-branch deleted the first
        // non-CC token regardless of which card the customer actually asked to
        // remove, which could destroy the only saved method on the account.
        $token_last4 = '';

        if ( is_callable( array( $token, 'get_last4' ) ) ) {
            $token_last4 = (string) $token->get_last4();
        }

        if ( '' === $token_last4 ) {
            $token_last4 = (string) $token->get_meta( 'last4' );
        }

        if ( '' !== $token_last4 && $token_last4 === $last4_digits ) {
            // WC_Payment_Tokens::delete() fires woocommerce_payment_token_deleted,
            // which is what lets WooCommerce Subscriptions reassign any subscription
            // still pointing at this token.
            WC_Payment_Tokens::delete( $token->get_id() );
            wp_send_json_success( 'Payment method deleted successfully.' );
        }
    }

    wp_send_json_error( 'No matching payment method found.' );
}

add_action( 'wp_ajax_delete_payment_method', 'handle_delete_payment_method_ajax' );


