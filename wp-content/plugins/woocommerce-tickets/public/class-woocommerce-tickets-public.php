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
class Woocommerce_Tickets_Public {

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

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

	
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-tickets-public.css?v=3', array(), $this->version, 'all' );
		if(get_query_var('ticket_one') !== '' || is_page('tickets-dashboard') ){
			wp_enqueue_style( $this->plugin_name .'dash', plugin_dir_url( __FILE__ ) . 'css/woocommerce-tickets-admin-dash.css', array(), $this->version, 'all' );
		}
		

		


	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		global $wp_query;

		if(get_query_var('ticket') !== ''   || is_page('admin-tickets')){

			wp_enqueue_script( 'Vue', 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.min.js', array(), $this->version, true );
			wp_enqueue_script( $this->plugin_name.'ckeditor_vue','https://unpkg.com/@ckeditor/ckeditor5-vue@1.0.1/dist/ckeditor.js',array(), $this->version, true );
			wp_enqueue_script( $this->plugin_name.'ckeditor','https://cdn.ckeditor.com/ckeditor5/17.0.0/classic/ckeditor.js',array(), $this->version, true );
		
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-tickets-public.js',array(), $this->version, true );
			
		}

		if($wp_query->query_vars['pagename']=='my-account' ){

			wp_enqueue_script( 'Vue', 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.min.js', array(), $this->version, false );
			// wp_enqueue_script( 'Vue2', 'https://unpkg.com/vue@next', array(), $this->version, false );
			wp_enqueue_script( $this->plugin_name.'ckeditor_vue','https://unpkg.com/@ckeditor/ckeditor5-vue@1.0.1/dist/ckeditor.js',array(), $this->version, false );
			wp_enqueue_script( $this->plugin_name.'ckeditor','https://cdn.ckeditor.com/ckeditor5/17.0.0/classic/ckeditor.js',array(), $this->version, false );
			// wp_enqueue_script( $this->plugin_name.'axious', plugin_dir_url( __FILE__ ) . 'vendor/axious.js', $this->version, false );
			wp_enqueue_script( $this->plugin_name.'vuedash', plugin_dir_url( __FILE__ ) . 'js/dashboardvue.js',array(), $this->version, false );	
		}
		
		wp_enqueue_script( 'Vue', 'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.js', array(), $this->version, true );
		if( get_query_var('ticket_one') !== '' || is_page('tickets-dashboard')  ){

			wp_enqueue_script( $this->plugin_name . 'barba', 'https://unpkg.com/@barba/core',array(), $this->version, true );
			wp_enqueue_script( 'Vue', 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.min.js', array(), $this->version, true );
	
		
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/admin-dashboard.js',array(), $this->version, true );
			
		}
		wp_enqueue_script( 'axios', 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.25.0/axios.min.js', array(), $this->version, false );
	
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/app.js',array(), $this->version, true );

		wp_localize_script($this->plugin_name, 'woocommerceTickets', array(
			'root_url' => get_site_url(),
			//create a nonce for crud
			'nonce' => wp_create_nonce('wp_rest')
		  ));
	

	

	}


	public function add_customize_controls_print_footer_scripts()
	{
		# code...
	
			// die();

		// if(get_query_var('customizer_dash') == 'yes'){
	
			wp_enqueue_script( 'customizer_dash_script',  plugin_dir_url( __FILE__ ) . 'js/customizer.js', array(), $this->version, false );

			wp_localize_script('customizer_dash_script', 'woocommerceTickets', array(
				'root_url' => get_site_url(),
				//create a nonce for crud
				'nonce' => wp_create_nonce('wp_rest')
			  ));
		// }
	}

	public function init_account_menu()
	{
	
		add_filter('woocommerce_account_menu_items', array($this, 'account_menu_items'));
	
		add_action('woocommerce_account_submitaticket_endpoint',array($this,'add_url_end_point'));
		
	}

	public function account_menu_items($items)
	{

	
		$items = array(
			'dashboard'          => __( 'Dashboard', 'woocommerce' ),
			'submitaticket'      => __( 'Submit A Ticket', 'woocommerce' ),
			'register'           => __('Register License','woocomerce'),
			'subscriptions'      => __( 'Subscriptions', 'woocommerce' ),
			'downloads'          => __( 'Downloads', 'woocommerce' ),
			'request'            => 'Request VRCut Controller Hardware Kit',
			'edit-account'       => __( 'Account', 'woocommerce' ),
			'payment-methods'    => __( 'Payment Methods', 'woocommerce' ),
		
			// 'profile'         => __( 'Profile', 'woocommerce' ),
			'customer-logout'    => __( 'Logout', 'woocommerce' ),
		);
//	'request'         => wp_kses_post( nl2br( __( 'Request VRCut Controller<br>Hardware Kit', 'woocommerce' ) ) ),
		// $items['request-url'] = 'https://lytrod.com/requestakit';
		return $items;
	}

	public function add_account_rewrite()
	{

	
            add_rewrite_endpoint( 'submitaticket', EP_ROOT | EP_PAGES );
	}

	public function add_url_end_point()
	{
		// wc_get_template_part('myaccount/submitaticket');
		// ob_start();
			include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/submit-tickets.php';
		// return ob_get_clean();
	}

	// public function add_front_admin_page()
	// {
	// 	global $post;
    //     $page_slug= $post->post_name;

    //     if($page_slug=="ticket-admin")
    //     {
    //         $page_template=  plugin_dir_path( dirname( __FILE__ ) ) .'public/partials/front-admin-page.php';
    //     }

    //     // return $page_template;
	// }



}
