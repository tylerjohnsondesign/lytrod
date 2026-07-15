<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       lighthousethemes.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/public
 * @author     Alexander Lytle <alex.joe.lytle@gmail.com>
 */
class Woocommerce_Tickets_Post_Type {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}



    public function add_ticket_post_type()
    {
        # code...
        register_post_type('tickets_woocommerce',array(

            'map_meta_cap'=>true,
            'public'=>true,
            'show_in_rest' => true,
            'labels'=>array(
                'name'=>'Tickets',
                'add_new_item'=>'Add new ticket',
                'edit_item'=>'Edit new ticket',
                'all_items'=>'All tickets',
            ),
            'supports' => array( 
                'title', 
                'editor', 
                'excerpt', 
                'thumbnail', 
                'custom-fields', 
                'revisions',
                'author'
            ),
            'menu_icon'=>'dashicons-admin-users',
            // 'taxonomies' => array('topics', 'category' ),
        ));

       
    }

	public function add_ticket_form_post_type()
    {
        # code...
        register_post_type('ticket_form',array(

            'map_meta_cap'=>true,
            'public'=>false,
            'show_in_rest' => true,
            'labels'=>array(
                'name'=>'Tickets Form',
                'add_new_item'=>'Add new ticket Form',
                'edit_item'=>'Edit new ticket Form',
                'all_items'=>'All tickets Form',
            ),
            'supports' => array( 
                'title', 
                'editor', 
                'excerpt', 
                'thumbnail', 
                'custom-fields', 
                'revisions',
                'author'
            ),
            'menu_icon'=>'dashicons-admin-users',
            // 'taxonomies' => array('topics', 'category' ),
        ));

       
    }
	public function add_ticket_status()
    {
        # code...
        register_post_type('ticket_status',array(

            'map_meta_cap'=>true,
            'public'=>false,
            'show_in_rest' => true,
            'labels'=>array(
                'name'=>'Tickets Status',
                'add_new_item'=>'Add new ticket Form',
                'edit_item'=>'Edit new ticket Form',
                'all_items'=>'All tickets Form',
            ),
            'supports' => array( 
                'title', 
                'editor', 
                'excerpt', 
                'thumbnail', 
                'custom-fields', 
                'revisions',
                'author'
            ),
            'menu_icon'=>'dashicons-admin-users',
            // 'taxonomies' => array('topics', 'category' ),
        ));

       
    }


}
