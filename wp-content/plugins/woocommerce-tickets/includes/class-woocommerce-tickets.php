<?php




/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_Tickets
 * @subpackage Woocommerce_Tickets/includes
 * @author     Alexander Lytle <alex.joe.lytle@gmail.com>
 */
class Woocommerce_Tickets {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocommerce_Tickets_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOCOMMERCE_TICKETS_VERSION' ) ) {
			$this->version = WOOCOMMERCE_TICKETS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommerce-tickets';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_post_type();
		$this->define_routing();
		$this->define_json_post_routes();
		$this->page_templates();
		$this->json_routes();
		$this->handle_ajax();
		
		

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_Tickets_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_Tickets_i18n. Defines internationalization functionality.
	 * - Woocommerce_Tickets_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_Tickets_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-tickets-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-tickets-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-tickets-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-tickets-public.php';


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-tickets-post-type.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-route.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocomerce-json-post-routes.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-routes.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-page-route.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-json-routes.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-handle-ajax.php';

		$this->loader = new Woocommerce_Tickets_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_Tickets_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommerce_Tickets_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woocommerce_Tickets_Admin( $this->get_plugin_name(), $this->get_version() );


		$this->loader->add_action( 'customize_controls_print_footer_scripts', $plugin_admin, 'add_customize_controls_print_footer_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );


		//theme customizer

		// $this->loader->add_action( 'init', $plugin_admin, 'load_admin_dependencies' );

		$this->loader->add_action( 'customize_register', $plugin_admin, 'tickets_customize_register' );

		$this->loader->add_action( 'customize_register', $plugin_admin, 'tickets_customize_admin' );

		$this->loader->add_action( 'customize_register', $plugin_admin, 'tickets_single_ticket' );





		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'admin_json_routes' );


	

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocommerce_Tickets_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_filter( 'init', $plugin_public, 'init_account_menu' );

		$this->loader->add_action('init', $plugin_public, 'add_account_rewrite');


		// $this->loader->add_action('page_template', $plugin_public, 'add_front_admin_page');
		// if(get_query_var('customizer_dash') == 'yes'){

		$this->loader->add_action( 'customize_controls_print_footer_scripts', $plugin_public, 'add_customize_controls_print_footer_scripts' );
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_Tickets_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	public function define_post_type()
	{
		$plugin_public = new Woocommerce_Tickets_Post_Type( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action('init', $plugin_public, 'add_ticket_post_type');

		$this->loader->add_action('init', $plugin_public, 'add_ticket_form_post_type');

		$this->loader->add_action('init', $plugin_public, 'add_ticket_status');
	}

	public function define_routing()
	{
		new Custom_Route('singleticket',array('ticket'),'public/partials/routes/ticket.php',true);

		
		// new Custom_Route ('tickets-dashboard',[],'public/partials/admin/all-tickets.php',true);
		// one
		new Custom_Route ('tickets-dashboard',['ticket_one'],'public/partials/admin/one-tickets.php',true);
		// two
		new Custom_Route('tickets-dashboard',['ticket_one','ticket_two'],'public/partials/admin/two-tickets.php',true);
		// three
		new Custom_Route('tickets-dashboard',['ticket_one','ticket_two','ticket_three'],'public/partials/admin/three-tickets.php',true);
		// four
		new Custom_Route('tickets-dashboard',['ticket_one','ticket_two','ticket_three','ticket_four'],'public/partials/admin/four-tickets.php',true);
		// five 
		new Custom_Route('tickets-dashboard',['ticket_one','ticket_two','ticket_three','ticket_four','ticket_five'],'public/partials/admin/five-tickets.php',true);
	
	}

	public function page_templates()
	{
	

		new WP_Page('tickets-dashboard','public/partials/admin/all-tickets.php','lytrod');
	}
	
	public function define_json_post_routes()
	{
		$plugin_routing = new Woocommerce_Tickets_Json_Routes($this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action('rest_api_init' , $plugin_routing , 'submit_ticket_question');

	}

	public function json_routes()
	{

		new WP_Json('GET','namespace/v1','route',function(){

				 
					$args = array(
						
						'post_type' => 'ticket_form',
						'posts_per_page' => -1,
					);

					
						
					$query = new WP_Query($args);

							// The Loop
						if ( $query->have_posts() ) :
								while ( $query->have_posts() ) : $query->the_post();
									
							 	return get_post_meta( get_the_id(),'json');
									
								endwhile;
						endif;
						
		

			
		});

	
		
		
	}

	public function handle_ajax()
	{
		$plugin_insert_ajax = new Class_Handle_Ajax( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_ajax_nopriv_submit_ticket', $plugin_insert_ajax , 'submit_ticket' );
		$this->loader->add_action( 'wp_ajax_submit_ticket', $plugin_insert_ajax , 'submit_ticket' );
		// $this->loader->add_action( 'wp_ajax_nopriv_cvf_update_tickets', $plugin_insert_picture , 'cvf_update_tickets' );
	}



}
