<?php
/**
 * Plugin Name: Lytrod Emails
 * Plugin URI:  https://tylerjohnsondesign.com
 * Description: Replaces every WooCommerce, Subscriptions and Stripe transactional email with a modern, SaaS-oriented Lytrod-branded design system.
 * Version:     1.0.0
 * Author:      Tyler Johnson
 * Author URI:  https://tylerjohnsondesign.com
 * License:     GPL-2.0-or-later
 * Text Domain: lytrod-emails
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

define( 'LYTROD_EMAILS_VERSION', '1.0.0' );
define( 'LYTROD_EMAILS_FILE', __FILE__ );
define( 'LYTROD_EMAILS_DIR', plugin_dir_path( __FILE__ ) );
define( 'LYTROD_EMAILS_URL', plugin_dir_url( __FILE__ ) );

require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails-brand.php';
require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails-context.php';
require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails-content.php';
require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails-lexicon.php';
require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails-payment.php';
require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails-templates.php';
require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails-compat.php';
require_once LYTROD_EMAILS_DIR . 'includes/functions.php';
require_once LYTROD_EMAILS_DIR . 'includes/class-lytrod-emails.php';

/*
 * Template routing is registered at file load rather than on a hook.
 *
 * wc_get_template() memoises the *filtered* wc_locate_template() result
 * (woocommerce/includes/wc-core-functions.php:302-311), so if any email template
 * were resolved before our filter existed, the stock path would be cached for the
 * rest of the request. There is no object-cache.php drop-in on this install today,
 * which keeps that per-request — but it becomes a persistent cache the moment
 * Kinsta Redis is enabled.
 */
Lytrod_Emails_Templates::init();
Lytrod_Emails_Compat::init();

add_action( 'plugins_loaded', array( 'Lytrod_Emails', 'init' ) );

register_activation_hook( __FILE__, array( 'Lytrod_Emails_Compat', 'activate' ) );
