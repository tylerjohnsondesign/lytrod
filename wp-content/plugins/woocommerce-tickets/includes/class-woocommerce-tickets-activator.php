<?php

/**
 * Fired during plugin activation
 *
 * @link       lighthousethemes.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/includes
 * @author     Alexander Lytle <alex.joe.lytle@gmail.com>
 */
class Woocommerce_Tickets_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	

		// delete_option('rewrite_rules');
		delete_option('rewrite_rules');

		$page1 = array();
		$page1['post_title']= "tickets-admin";
		$page1['post_content']= "tickets-admin";
		$page1['post_status'] = "publish";
		$page1['post_slug'] = "tickets-admin";
		$page1['post_type'] = "page";
	
		$page1_id=wp_insert_post($page1);
		add_option('ps_frontendposts_page_id',$page1_id );

		$jsonForm = '[ { "default": "name", "form": "singleLineText", "label": "Name", "required": false }, { "default": "email", "form": "singleLineText", "label": "Email", "required": false }, { "form": "paragraphText", "label": "Question", "required": false, "default": "question" },{ "form": "fileUpload", "label": "File Upload", "required": false },{ "form": "submit", "label": "Submit" } ]';

		$addTicketFormData = array(
			'post_type'=>'ticket_form',
			'post_title' =>'Form',
			'post_status'=>'publish',
			'meta_input'=>array(
					'json'=> str_replace("'", "", $jsonForm)));
		$formID = wp_insert_post($addTicketFormData);

		$page1_id=wp_insert_post($page1);
		add_option('ps_frontendposts_page_id',$page1_id );

		add_option('woocomerce_tickets_form_id',$formID );
	
	}

}
