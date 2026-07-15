<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       lighthousethemes.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/admin
 * @author     Alexander Lytle <alex.joe.lytle@gmail.com>
 */
class Woocommerce_Tickets_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-tickets-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$my_current_screen = get_current_screen();
	
			
		


		if($my_current_screen->base == 'woocommerce-tickets_page_form-builder'){
			// wp_enqueue_style('bootstrap',  plugin_dir_url( __FILE__ ) .'vendor/bootstrap-grid.css',array(),'1.0',false);
			wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css',array(),'1.0',false);
			wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css',array(),'1.0',false);

			wp_enqueue_script( 'Vue', 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.min.js', array(), $this->version, true );
	
			wp_enqueue_script( 'sortable', 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.6.0/Sortable.min.js', array(), $this->version, true );
	
			wp_enqueue_script( 'draggable', 'https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.15.0/vuedraggable.min.js', array(), $this->version, true );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-tickets-admin.js', array( 'jquery' ), $this->version, true );

			wp_localize_script($this->plugin_name, 'woocommerceTickets', array(
				'root_url' => get_site_url(),
				//create a nonce for crud
				'nonce' => wp_create_nonce('wp_rest')
			  ));
		}
		if($my_current_screen->base == 'toplevel_page_tickets'){
			wp_enqueue_script( 'Vue', 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.min.js', array(), $this->version, true );

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-tickets-admin.js', array( 'jquery' ), $this->version, true );

			wp_localize_script($this->plugin_name, 'woocommerceTickets', array(
				'root_url' => get_site_url(),
				//create a nonce for crud
				'nonce' => wp_create_nonce('wp_rest')
			  ));
		}
		


	

	}
	public function add_admin_menu()
	{



	add_menu_page('Woocommerce Tickets', 'Woocommerce Tickets', 'manage_options', 'tickets',
		array($this, 'admin_page_display')
	, 'dashicons-tickets-alt',20);
	
	add_submenu_page('tickets', // Parent slug
		'Tickets Home', // Page title
		'Settings', // Menu title
		'manage_options', // Capability
		'tickets', // Slug
		false, //function
	);

	add_submenu_page('tickets', // Parent slug
			'Ticket Form Builder', // Page title
			'Ticket Form Builder', // Menu title
			'manage_options', // Capability
			'form-builder', // Slug
			array($this, 'admin_form_builder')
			);




	}

	public function admin_form_builder()
	{
		include 'partials/form-builder-display.php';
	}

	public function admin_page_display()
	{
		include 'partials/tickets-display.php';
	}


	public function tickets_customize_register($wp_customize)
	{
	
		$goback = '<h2><a href = ' . site_url() .'/wp-admin/edit.php?post_type=tickets_woocommerce&page=woocommerce-tickets' . '>Go Back To Dashboard</a>
		</h2>';

		$wp_customize->add_section('dashboard', array(
			'title'   => __('Dashboard', 'wpbootstrap'),
			'description' =>  $goback,
			'priority'    => 130
		  ));
	  //------------------------Individual settings-------------------------
	  //Text Every setting variable i.e (showcase_heading) needs a control
	  
		  $wp_customize->add_setting('create_heading', array(
			'default'   => 'Create New Ticket',
			'type'      => 'theme_mod'
		  ));
	  //Label and settings
		$wp_customize->add_control('create_heading', array(
			'label'   => __('Create Ticket Heading', 'wpbootstrap'),
			'section' => 'dashboard',
			'priority'  => 1
		  ));

	


		$wp_customize->add_setting('ticket_history', array(
			'default'   => _x('Ticket History', 'wpbootstrap'),
			'type'      => 'theme_mod'
		  ));
	  
		  $wp_customize->add_control('ticket_history', array(
			'label'   => 'Ticket History',
			'section' => 'dashboard',
			'priority'  => 2
		  ));

		   	//Submit a ticket

			   
		  $wp_customize->add_setting( 'dashboard_title_color', array(
			'default' => '#000000',
		));


		  $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_title_color', array(
			'label' => 'Title Header',
			'section' => 'dashboard',
			'settings' => 'dashboard_title_color',
			'priority'  => 3
	 
		)));

			// open close background
			$wp_customize->add_setting( 'open_close_background_color', array(
				'default' => '#e5e5e5',
			));
	
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'open_close_background_color', array(
				'label' => 'Background Color: ',
				'section' => 'dashboard',
				'settings' => 'open_close_background_color',
				'priority'  => 4
			
			)));

		// Submit form button button background
		$wp_customize->add_setting( 'submit_form_button_color_background', array(
			'default' => '#ffffff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'submit_form_button_color_background', array(
			'label' => 'Form Button Background Color : ',
			'section' => 'dashboard',
			'settings' => 'submit_form_button_color_background',
			'priority'  => 4
		
		)));
	  
		// Submit form button text background
		$wp_customize->add_setting( 'submit_form_button_color_text', array(
			'default' => '#000',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'submit_form_button_color_text', array(
			'label' => 'Form Button Color Text: ',
			'section' => 'dashboard',
			'settings' => 'submit_form_button_color_text',
			'priority'  => 4
		
		)));


		// Submit form label text color
		$wp_customize->add_setting( 'submit_form_label_text_color', array(
			'default' => '#ffffff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'submit_form_label_text_color', array(
			'label' => 'Submit Form Label Text Color : ',
			'section' => 'dashboard',
			'settings' => 'submit_form_label_text_color',
			'priority'  => 4
		
		)));

		// Submit form label input color
		$wp_customize->add_setting( 'submit_form_input_background_color', array(
			'default' => '#ffffff',
		));

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'submit_form_input_background_color', array(
			'label' => 'Submit Form Input Background : ',
			'section' => 'dashboard',
			'settings' => 'submit_form_input_background_color',
			'priority'  => 4
		
		)));
		// NO tickets Created
		$wp_customize->add_setting( 'no_tickets_created_text_color', array(
			'default' => '#ffffff',
		));

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'no_tickets_created_text_color', array(
			'label' => 'No Tickets Created : ',
			'section' => 'dashboard',
			'settings' => 'no_tickets_created_text_color',
			'priority'  => 4
		
		)));
		



			  	  
	}




	public function tickets_customize_admin($wp_customize){
		$goback = '<h2><a href = ' . site_url() .'/wp-admin/edit.php?post_type=tickets_woocommerce&page=woocommerce-tickets' . '>Go Back To Dashboard</a>
		</h2>';

		$wp_customize->add_section('dashboard_admin', array(
			'title'   => __('Dashboard Admin', 'wpbootstrap'),
			'description' =>  $goback,
			'priority'    => 130
		  ));
	  //------------------------Individual settings-------------------------
	  
	  /*Ticket header*/ 
		  $wp_customize->add_setting('create_heading', array(
			'default'   => 'All Ticket',
			'type'      => 'theme_mod'
		  ));

		  $wp_customize->add_control('create_heading', array(
			'label'   => 'Ticket Header',
			'section' => 'dashboard_admin',
			'priority'  => 1
		  ));

		/*Ticket heading color */
		$wp_customize->add_setting( 'dashboard_admin_heading_color', array(
			'default' => '#000000',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_admin_heading_color', array(
			'label' => 'Ticket Header Color: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_admin_heading_color',
			'priority'  => 4
		
		)));

		/*Background Color*/
		  $wp_customize->add_setting( 'dashboard_admin_background_color', array(
			'default' => '#ffffff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_admin_background_color', array(
			'label' => 'Background Color: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_admin_background_color',
			'priority'  => 4
		
		)));

		/*Background Color Box Color*/
		$wp_customize->add_setting( 'dashboard_box_color', array(
			'default' => '#262626',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_box_color', array(
			'label' => 'Background Color Box: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_box_color',
			'priority'  => 4
		
		)));

		
		/*Background Color Border Color*/
		$wp_customize->add_setting( 'dashboard_box_border_color', array(
			'default' => '#ffffff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_box_border_color', array(
			'label' => 'Background Border Box: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_box_border_color',
			'priority'  => 4
		
		)));
	
		/*Background Ticket Open color*/
		$wp_customize->add_setting( 'dashboard_open_status_color', array(
			'default' => '#ffffff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_open_status_color', array(
			'label' => 'Ticket Open Color: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_open_status_color',
			'priority'  => 4
		
		)));
		/*Background Ticket Close color*/
		$wp_customize->add_setting( 'dashboard_close_status_color', array(
			'default' => '#ffffff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_close_status_color', array(
			'label' => 'Ticket Close Color: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_close_status_color',
			'priority'  => 4
		
		)));

		/*Ticket Number Color*/
		$wp_customize->add_setting( 'dashboard_ticket_number_color', array(
			'default' => '#0a0a0a',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_ticket_number_color', array(
			'label' => 'Ticket Number Color: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_ticket_number_color',
			'priority'  => 4
		
		)));


		/*Ticket Status Color*/
		$wp_customize->add_setting( 'dashboard_status_color', array(
			'default' => '#000',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_status_color', array(
			'label' => 'Ticket Status Color: ',
			'section' => 'dashboard_admin',
			'settings' => 'dashboard_status_color',
			'priority'  => 4
		
		)));

	}

	public function tickets_single_ticket($wp_customize)
	{
		$goback = '<h2><a href = ' . site_url() .'/wp-admin/edit.php?post_type=tickets_woocommerce&page=woocommerce-tickets' . '>Go Back To Dashboard</a>
		</h2>';

		$wp_customize->add_section('single_ticket', array(
			'title'   => __('Single Ticket', 'wpbootstrap'),
			'description' =>  $goback,
			'priority'    => 130
		  ));
	  //------------------------Individual settings-------------------------
	  
	 	 /**
		  * Ticket header
		 */ 
	
			$wp_customize->add_setting( 'single_ticket_header_color', array(
				'default' => '#000000',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'single_ticket_header_color', array(
				'label' => 'Ticket Number Color: ',
				'section' => 'single_ticket',
				'settings' => 'single_ticket_header_color',
				'priority'  => 4
			
			)));

			/**
			 * background color
			*/
			$wp_customize->add_setting( 'single_ticket_background_color', array(
				'default' => '#f5f5f5',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'single_ticket_background_color', array(
				'label' => 'Ticket Number  Background Color: ',
				'section' => 'single_ticket',
				'settings' => 'single_ticket_background_color',
				'priority'  => 4
			
			)));


			/**
			 * question box background
			*/
			$wp_customize->add_setting( 'question_box_background_color', array(
				'default' => '#ffffff',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'question_box_background_color', array(
				'label' => 'Question Background Color: ',
				'section' => 'single_ticket',
				'settings' => 'question_box_background_color',
				'priority'  => 4
			
			)));
			/**
			 * question box text
			*/
			$wp_customize->add_setting( 'question_box_text_color', array(
				'default' => '#0a0a0a',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'question_box_text_color', array(
				'label' => 'Question Text Color: ',
				'section' => 'single_ticket',
				'settings' => 'question_box_text_color',
				'priority'  => 4
			
			)));


			/**
			 * answer box background
			*/
			$wp_customize->add_setting( 'answer_box_background_color', array(
				'default' => '#000',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'answer_box_background_color', array(
				'label' => 'Answer Background Color: ',
				'section' => 'single_ticket',
				'settings' => 'answer_box_background_color',
				'priority'  => 4
			
			)));

			/**
			 * answer box text
			*/
			$wp_customize->add_setting( 'answer_box_text_color', array(
				'default' => '#000',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'answer_box_text_color', array(
				'label' => 'Answer Text Color: ',
				'section' => 'single_ticket',
				'settings' => 'answer_box_text_color',
				'priority'  => 4
			
			)));


			

			/**
			 * admin message box
			*/
			$wp_customize->add_setting( 'answer_message_box_color', array(
				'default' => '#fff',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'answer_message_box_color', array(
				'label' => 'Message Box : ',
				'section' => 'single_ticket',
				'settings' => 'answer_message_box_color',
				'priority'  => 4
			
			)));

			
			/**
			 * Icon send icon
			*/
			$wp_customize->add_setting( 'answer_message_icon_color', array(
				'default' => '#fff',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'answer_message_icon_color', array(
				'label' => 'Icon Send Color : ',
				'section' => 'single_ticket',
				'settings' => 'answer_message_icon_color',
				'priority'  => 4
			
			)));

			/**
			 * Stop message box
			*/
			$wp_customize->add_setting( 'stop_message_text', array(
				'default' => '#0a0a0a',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'stop_message_text', array(
				'label' => 'Stop Message Text Color : ',
				'section' => 'single_ticket',
				'settings' => 'stop_message_text',
				'priority'  => 4
			
			)));

			/**
			 * Close Ticket Button
			*/
			$wp_customize->add_setting( 'close_ticket_background_color', array(
				'default' => '#c1c1c1',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'close_ticket_background_color', array(
				'label' => 'Close Ticket Background Color : ',
				'section' => 'single_ticket',
				'settings' => 'close_ticket_background_color',
				'priority'  => 4
			
			)));

			/**
			 * Close Ticket Color Text
			*/
			$wp_customize->add_setting( 'close_ticket_text_color', array(
				'default' => '#0a0a0a',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'close_ticket_text_color', array(
				'label' => 'Close Ticket Text Color : ',
				'section' => 'single_ticket',
				'settings' => 'close_ticket_text_color',
				'priority'  => 4
			
			)));
		

	}

	public function admin_json_routes()
	{
		register_rest_route('adminForm/v1', 'add_settings_route', array(
            array(
                    'methods' => 'POST',
                    'callback' =>array($this,'add_settings_route')
                
            ),
  		));

		  register_rest_route('formBuilder/v1', 'add_form_route', array(
            array(
                    'methods' => 'POST',
                    'callback' =>array($this,'add_form_route')
                
            ),
  		));

		  register_rest_route('onlineStatus/v1', 'change_status', array(
            array(
                    'methods' => 'GET',
                    'callback' =>array($this,'change_status')
                
            ),
  		));
	}

	public function add_settings_route($data)
	{
		# code...

		// return delete_option( 'email' );

		$email = $data['email'];
		$font = $data['google_font'];
		$roles = $data['defaultRoles'];
		$onlineStatus = $data['onlineStatus'];

		if(get_option('woo_tickets_email') !== null){
			 update_option('woo_tickets_email',$email);
		}else{
			add_option('woo_tickets_email',$email);
		}

		if(get_option('tickets_font') !== null){
			update_option('tickets_font',$font);
		}else{
			add_option('tickets_font',$font);
		}

		if(get_option('defaultRoles') !== null){
			update_option('defaultRoles',$roles);
		}else{
			add_option('defaultRoles',$roles);
		}

		if(get_option('onlineStatus') !== null){
			update_option('onlineStatus',$onlineStatus);
		}else{
			add_option('onlineStatus',$onlineStatus);
		}


		return $data;

		


	}
	public function add_form_route($data)
	{

		  $exist_query = new WP_Query(array(
			'author' => get_current_user_id(),
			'post_type' => 'ticket_form',
			'meta_query' => array(
			  array(
				'key' => 'id',
				'compare' => '=',
				'value' => 19787
			  )
			)
		  ));
		if($exist_query->found_posts == 0){
		  //create
		  $insertForm = array(
			'post_title'    => 'Form',
			'post_content'  => 'tester',
			'post_type' => 'ticket_form',
			'post_status' => 'publish',
			'meta_input' => array(
				  'json' => json_encode($data['json_data'],true)
				)
		);
		return wp_insert_post( $insertForm );


		}else{
			$updateForm = array(
				'ID'=>$exist_query->post->ID,
				'post_title'    => 'Form',
				'post_content'  => 'tester',
				'post_type' => 'ticket_form',
				'post_status' => 'publish',
				'meta_input' => array(
					  'json' => json_encode($data['json_data'],true)
					)
			);
			return wp_update_post( $updateForm );
		}
	
	}

	public function change_status()
	{


	
			return array('online_status'=> get_option('onlineStatus'));
		
		// return (get_option('onlineStatus') == null)? "null":'not null';
		


	}




}
