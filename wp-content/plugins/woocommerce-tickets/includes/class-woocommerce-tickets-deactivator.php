<?php

/**
 * Fired during plugin deactivation
 *
 * @link       lighthousethemes.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/includes
 * @author     Alexander Lytle <alex.joe.lytle@gmail.com>
 */
class Woocommerce_Tickets_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		$page_id = get_option('ps_frontendposts_page_id');
		wp_delete_post($page_id);
		delete_option('ps_frontendposts_page_id');

		$page_id_form = get_option('woocomerce_tickets_form_id');
		wp_delete_post($page_id_form);
		delete_option('woocomerce_tickets_form_id');

	}

}
