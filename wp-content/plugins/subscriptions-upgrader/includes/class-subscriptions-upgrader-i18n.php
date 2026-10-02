<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://webfielddesign.com/
 * @since      1.0.0
 *
 * @package    Subscriptions_Upgrader
 * @subpackage Subscriptions_Upgrader/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Subscriptions_Upgrader
 * @subpackage Subscriptions_Upgrader/includes
 * @author     talktaimor <talktaimor@gmail.com>
 */
class Subscriptions_Upgrader_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'subscriptions-upgrader',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
