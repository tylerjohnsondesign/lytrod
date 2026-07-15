<?php
/**
 * lytrod functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package lytrod
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.1' );
}

if ( ! function_exists( 'lytrod_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function lytrod_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on lytrod, use a find and replace
		 * to change 'lytrod' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'lytrod', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'lytrod' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'lytrod_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'lytrod_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function lytrod_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'lytrod_content_width', 640 );
}
add_action( 'after_setup_theme', 'lytrod_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function lytrod_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'lytrod' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'lytrod' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'lytrod_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function lytrod_scripts() {

	
	
	wp_enqueue_style( 'lytrod-style', get_template_directory_uri() . '/dist/app.css?v='.time() , '' );
	wp_enqueue_style( 'font-awesome-six', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css' , _S_VERSION );


	wp_register_script('my-amazing-script', get_template_directory_uri() . '/dist/app.js','','1.1', true);
	wp_enqueue_script('my-amazing-script');
	wp_localize_script('my-amazing-script', 'lytrodData', array(
		'root_url' => get_site_url(),
		'nonce' => wp_create_nonce('wp_rest')
	  ));

	wp_register_script('lytrod-gsap','https://cdnjs.cloudflare.com/ajax/libs/gsap/3.6.1/gsap.min.js','','1.1', true);
	wp_enqueue_script('lytrod-gsap');

	// wp_register_script('lytrod-menu', get_template_directory_uri() .'/src/menu.js','','1.1', true);
	// wp_enqueue_script('lytrod-menu');


	wp_enqueue_style( 'lytrod-fonts', 'https://fonts.googleapis.com/css?family=Roboto%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic%7CRoboto+Slab%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic%7CRaleway%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic&display=auto&ver=5.7.2' , _S_VERSION );


	wp_enqueue_style( 'lytrod-bootstrapy', get_template_directory_uri() . '/src/bootstrapy.css' , _S_VERSION );
	if(!is_page('checkout')&&!is_page('my-account')&& !is_page('completeyouraccount')){
		wp_enqueue_style( 'lytrod-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css' , _S_VERSION );
	}

	wp_enqueue_style( 'lytrod-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' , _S_VERSION );

	// bootstrap pages
	if(is_page('product-chart')){
	
		wp_enqueue_style( 'lytrod-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css' , _S_VERSION );
		wp_enqueue_style( 'lytrod-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' , _S_VERSION );
	}

	if(is_page('my-account')){
		wp_enqueue_style( 'lytrod-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css' , _S_VERSION );
	}
	if(is_product()){
		
		wp_enqueue_style( 'lytrod-store', get_template_directory_uri() . '/src/store/style.css?v=' . time() , _S_VERSION );
		wp_enqueue_style( 'lytrod-fonts', 'https://fonts.googleapis.com/css?family=Roboto%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic%7CRoboto+Slab%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic%7CRaleway%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic&display=auto&ver=5.7.2' , _S_VERSION );
	}

	if(is_checkout()){
		wp_enqueue_style( 'lytrod-store-checkout', get_template_directory_uri() . '/src/store/checkout/style.css?v=26' , _S_VERSION );
	}



	
	if(!is_user_logged_in()){
		wp_enqueue_style( 'not-logged-in', get_template_directory_uri() . '/src/styleparts/not-logged-in.css?v=21' , _S_VERSION );
	}




			if ( strpos( $_SERVER['REQUEST_URI'], 'product/customer-success-digital-workflow-services/' ) !== false ) {
			
					wp_enqueue_script( 'store-sub', get_template_directory_uri() . '/js/main.js?v='.time() , array(), '',true  );
			}



		if(is_page('learning-guides')){
			// $time = time();

			wp_enqueue_style( 'swiperstyle',get_template_directory_uri() . '/src/learningguide/style.css', array(), '4.3.1', 'all' );
			// wp_enqueue_style( 'ionicicons', 'http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
			wp_enqueue_style( 'bootstrapswiper', get_template_directory_uri() . '/src/vendor/bootstrap.min.css', array(), '4.3.1', 'all');

			wp_enqueue_style( 'swipervendor', get_template_directory_uri() . '/src/vendor/swiper.min.css', array(), '4.3.1', 'all');
			// wp_enqueue_style( 'swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.7/css/swiper.min.css');
			wp_enqueue_script( 'fullpagemin', get_template_directory_uri() . '/src/vendor/fullpage.min.js' , array(), '',true  );
			
			//JS
			wp_enqueue_script( 'swipermin', get_template_directory_uri() . '/src/vendor/swiper.min.js' , array(), '',true  );
			// wp_enqueue_script( 'fullpagejs', '/wp-content/themes/astra-child/assets/learning_guide/js/fullpage.min.js',array(),'',true);
			// wp_enqueue_script( 'bootstrap-js-guide', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js', array(), '4.3.4',true  );
			wp_enqueue_script( 'fullpage', get_template_directory_uri() . '/src/learningguide/fullpage.js?v=4', array(), '',true  );
			wp_enqueue_script( 'theswiper',  get_template_directory_uri() . '/src/learningguide/swiper.js?v=4', array(), '',true  );
		}


		if(is_page('videolearningcenter')){


			wp_enqueue_style( 'swiperstyle',get_template_directory_uri() .'/src/videolearning/style.css' , array(), '4.3.1', 'all' );
			// wp_enqueue_style( 'ionicicons', 'http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
			wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/src/vendor/bootstrap.min.css', array(), '4.3.1', 'all');

			wp_enqueue_style( 'swipervendor', get_template_directory_uri() . '/src/vendor/swiper.min.css', array(), '4.3.1', 'all');
			wp_enqueue_style( 'fullpagemin', get_template_directory_uri() . '/src/vendor/fullpage.min.css', array(), '4.3.1', 'all');
			wp_enqueue_style( 'raleway',"https://fonts.googleapis.com/css2?family=Raleway&display=swap");
			//JS


			wp_enqueue_script( 'swipermin', get_template_directory_uri() . '/src/vendor/swiper.min.js' , array(), '',true  );
			wp_enqueue_script( 'fullpagemin', get_template_directory_uri() . '/src/vendor/fullpage.min.js' , array(), '',true  );
			
			wp_enqueue_script( 'appjs',  get_template_directory_uri() . '/src/videolearning/app.js', array(),'',true);
			wp_enqueue_script( 'swiperappjs',  get_template_directory_uri() . '/src/videolearning/swiperapp.js', array(),'',true);
			// wp_enqueue_script( 'theswiper', '/wp-content/themes/astra-child/assets/learning_guide/js/swiper.js', array(), '',true  );        
       }

		if(is_page('checkout')){
			wp_enqueue_script( 'checkoutjs',  get_template_directory_uri() . '/src/store/checkout/checkout.js', array(), '',true  );
			wp_enqueue_style( 'checkoutsytle', get_template_directory_uri() . '/src/checkout/checkout.css', array(), '4.3.6', 'all');
		}
		if(is_page('demonstration-files')){
			wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/src/vendor/bootstrap.min.css', array(), '4.3.1', 'all');
			wp_enqueue_style( 'Petit', 'https://fonts.googleapis.com/css?family=Petit+Formal+Script', array(), '4.3.1', 'all');
			wp_enqueue_style( 'Raleway', 'https://fonts.googleapis.com/css?family=Raleway', array(), '4.3.1', 'all');
			wp_enqueue_style( 'Demofilesfinal', get_template_directory_uri() . '/src/demofiles/app.css?v=3', array(), '4.3.1', 'all');
			wp_enqueue_style( 'font-awesome-files','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.css', array(), '4.3.1', 'all');
			wp_enqueue_style( 'positions', get_template_directory_uri() . '/src/demofiles/positions.css', array(), '4.3.1', 'all');

			//script js
			wp_enqueue_script( 'jquerydemo',  'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js', array(),'',true);
			wp_enqueue_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', array(), '',true  );
			wp_enqueue_script( 'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js', array(), '',true  );
			wp_enqueue_script( 'hoverbox',  get_template_directory_uri() .  '/src/demofiles/hoverbox.js', array(), '',true  );
			wp_enqueue_script( 'appjs',  get_template_directory_uri() . '/src/demofiles/app.js?v=' .time(), array(),'',true);
			
		}

		if(is_page('corporate-brand-assets')){
			wp_enqueue_style( 'brandingcss', get_template_directory_uri() . '/src/branding/assets/css/styles.css', array(), '4.3.1', 'all');
			wp_enqueue_style( 'tabs', get_template_directory_uri() . '/src/branding/assets/css/tabs.css?v=' .time() , array(), '4.3.1', 'all');

			wp_enqueue_script( 'bootstrap-js',  'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.bundle.min.js', array(), '',true  );
			wp_enqueue_script( 'gsap-js',  'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.2.6/gsap.min.js', array(), '',true  );
			wp_enqueue_script( 'appjs',  get_template_directory_uri() . '/src/branding/assets/js/script.js', array(),'',true);

		}

		if (is_page('intellicutinstallation')) {
			wp_enqueue_style( 'regsiteration', get_template_directory_uri() . '/src/store/registration/style.css?v=4', array(), '4.3.1', 'all');
			
		}

	
}
add_action( 'wp_enqueue_scripts', 'lytrod_scripts' );




// function admin_scripts(){

	// wp_enqueue_style('wy_day_css', get_template_directory_uri() .'/src/wyday/app.css',array(),'1.0',false);

	// wp_enqueue_script('axious','https://cdnjs.cloudflare.com/ajax/libs/axios/0.25.0/axios.min.js', array(), '2.0', true);

	// wp_enqueue_script('wy_day',get_template_directory_uri() .'/src/wyday/app.js', array(), '2.0', true);

	// wp_localize_script('wy_day', 'backend_js', array(
	// 	'root_url' => get_site_url(),
	// 	'nonce' => wp_create_nonce('wp_rest')
	// ));

// }

// add_action( 'admin_enqueue_scripts', 'admin_scripts' );




/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';


require get_template_directory() . '/registration/index.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/*
Load wyday data
*/

// include('update_wyday/index.php');
// require_once get_template_directory() .'/src/wyday/template.php';


// function my_logged_in_redirect() {
     
//     if ( is_page( 'demonstration-files' ) ) 
//     {
//         header('Location:./#home' );
//         die;
//     }
     
// }
// add_action( 'template_redirect', 'my_logged_in_redirect' );

// include('shortcodes/storeproducts.php');


function mytheme_add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );


add_filter( 'woocommerce_product_tabs', 'bbloomer_remove_product_tabs', 9999 );
  
function bbloomer_remove_product_tabs( $tabs ) {
    unset( $tabs['additional_information'] ); 
    return $tabs;
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );




//middleware
// include('routes/middleware/index.php');


// //php routes
// include('routes/php/index.php');

// include('routes/api/subscription-route.php');
// include('routes/api/search-route.php');
// //woocommerce emails
// include('email_headers/lytrod_emails.php');

// //php routes
// include('woocomerce_edits/index.php');

// //product redirects

// include('product_redirects/redirects.php');





add_action('wp_insert_post', 'kg_set_default_custom_fields');

function kg_set_default_custom_fields($post_id)
{
    if ( $_GET['post_type'] == 'shop_subscription' ) {
        add_post_meta($post_id, 'meta-description', '', true);
    }

    return true;
}


add_filter('acf/settings/remove_wp_meta_box', '__return_false');


function my_custom_menu_item($items, $args)
{

    if(is_user_logged_in() && $args->theme_location == 'menu-1')
    {
        $user=wp_get_current_user();
        $name=$user->display_name; // or user_login , user_firstname, user_lastname
		if(strlen($name)>=6){
			$shortName = substr($name, 0, 6). "... ";
			$name = '';
		}
		//hide logged in menu
		echo "<style> .menu li:nth-child(6) { display: none; } </style>";

        $items .= '<li class ="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children " >
						<a href="'. site_url() . '/my-account' .'">WELCOME '.strtoupper($name).strtoupper($shortName).'
						<i style="margin-left:5px" class="fas fa-user"></i>
					
						</a>
						<ul class="sub-menu">
							<li id="" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-267 ">
								<a href = "'. wp_logout_url("./my-account")  . '">LOGOUT</a>
							</li>
							

						</ul>
					</li>
				
				';
				
     
    }
   
    return $items;
}
add_filter( 'wp_nav_menu_items', 'my_custom_menu_item',10,2);










function store_mall_wc_empty_cart_redirect_url() {
	
	$url =  site_url() .'/'; // change this link to your need
	return esc_url( $url );
}
add_filter( 'woocommerce_return_to_shop_redirect', 'store_mall_wc_empty_cart_redirect_url' );


add_filter( 'gettext', 'change_woocommerce_return_to_shop_text', 20, 3 );

function change_woocommerce_return_to_shop_text( $translated_text, $text, $domain ) {

		// echo '<pre>';
		// 	print_r($translated_text);
		// echo'</pre>';
		// die();

        switch ( $translated_text ) {

            case 'Return to shop' :

                $translated_text = __( 'Return to home', 'woocommerce' );
                break;

        }

    return $translated_text;
}





add_filter( 'woocommerce_checkout_fields' , 'njengah_simplify_checkout_virtual' );

function njengah_simplify_checkout_virtual( $fields ) {

    // Ensure WC()->cart is available
    if ( ! function_exists( 'WC' ) || is_admin() || ! WC()->cart ) {
        return $fields;
    }

    $only_virtual = true;

    foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( ! $cart_item['data']->is_virtual() ) {
            $only_virtual = false;
            break;
        }
    }

//     if ( $only_virtual ) {
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_address_1']);
        unset($fields['billing']['billing_address_2']);
        unset($fields['billing']['billing_city']);
        unset($fields['billing']['billing_postcode']);
        unset($fields['billing']['billing_country']);
        unset($fields['billing']['billing_state']);
        unset($fields['billing']['billing_phone']);
        
        add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
//     }

    return $fields;
}




if (strpos($_SERVER['HTTP_HOST'] ?? '', 'staging') !== false) {
    add_filter( 'woocommerce_subscriptions_is_duplicate_site', '__return_true' );
}



/**
 * Plugin Name: Disable Auto Subscription Payments
 * Description: Disables automatic payments for WooCommerce Subscriptions.
 * Version: 1.0
 * Author: Your Name
 */

add_filter('wcs_renewal_order_created', 'disable_auto_payments_for_subscriptions', 10, 2);

/**
 * Historically this filter stripped the gateway off EVERY renewal order, forcing
 * every subscription onto manual payment — this was the "failed auto-renewal" bug:
 * saved cards were never charged and subscriptions silently dropped to on-hold.
 *
 * It now only strips the gateway for subscriptions that are NOT enabled for real
 * automatic renewal. Enabled subs keep their gateway, so WooCommerce Subscriptions
 * fires woocommerce_scheduled_subscription_payment_{gateway} and charges the saved
 * card as intended.
 *
 * Rollout is gated by lytrod_subscription_auto_renew_enabled() (below) so this can
 * be deployed with ZERO behavior change — by default no subscription auto-renews —
 * then enabled per-subscription for a pilot and finally site-wide.
 */
function disable_auto_payments_for_subscriptions($renewal_order, $subscription) {
    // Auto-renewal enabled for this sub → leave the gateway intact so WCS charges it.
    if ( function_exists( 'lytrod_subscription_auto_renew_enabled' )
        && lytrod_subscription_auto_renew_enabled( $subscription ) ) {
        return $renewal_order;
    }

    // Otherwise preserve the historical manual behavior: strip the gateway so the
    // customer pays the renewal order by hand.
    $renewal_order->set_payment_method('');
    delete_post_meta($renewal_order->get_id(), '_payment_method');
    delete_post_meta($renewal_order->get_id(), '_payment_method_title');
    $renewal_order->set_status('pending');
    $renewal_order->save();

    return $renewal_order;
}

/**
 * Whether a subscription should be charged automatically on renewal.
 *
 * Controls the staged rollout of the auto-renewal fix. Order of precedence:
 *   1. Manual/PO subscriptions (_requires_manual_renewal) never auto-charge.
 *   2. Option 'lytrod_auto_renew_all' = 'yes'  → all card-backed subs auto-renew (final rollout).
 *   3. Option 'lytrod_auto_renew_subs' (CSV of subscription IDs) → pilot allowlist.
 *   4. Filter 'lytrod_subscription_auto_renew_enabled' for any custom override.
 *
 * Defaults to FALSE for everything, so deploying the code changes nothing until an
 * operator opts subscriptions in. Recommended rollout (pick a current, chargeable
 * active sub from the Phase 0 audit — e.g. #29137, source + saved token):
 *   update_option('lytrod_auto_renew_subs', '29137');   // pilot one known-good sub
 *   ...force a renewal (WP-CLI / admin "Process renewal") and verify the Stripe
 *      charge + license expiry extension, since real renewals are months out...
 *   update_option('lytrod_auto_renew_all', 'yes');       // then enable site-wide
 */
function lytrod_subscription_auto_renew_enabled( $subscription ) {
    $enabled = false;

    if ( $subscription instanceof WC_Subscription && ! $subscription->is_manual() ) {
        if ( 'yes' === get_option( 'lytrod_auto_renew_all' ) ) {
            $enabled = true;
        } else {
            $allow = get_option( 'lytrod_auto_renew_subs', '' );
            $ids   = array_filter( array_map( 'absint', explode( ',', (string) $allow ) ) );
            $enabled = in_array( (int) $subscription->get_id(), $ids, true );
        }
    }

    return (bool) apply_filters( 'lytrod_subscription_auto_renew_enabled', $enabled, $subscription );
}

// add_filter( 'woocommerce_subscriptions_is_duplicate_site', '__return_true' );



add_filter( 'woocommerce_add_to_cart_redirect', function( $url ) {
    return wc_get_checkout_url();
});



add_action('admin_init', function () {
    remove_all_actions('admin_notices');
    remove_all_actions('all_admin_notices');
});


// Change "Add to cart" text to "Proceed to Checkout"
add_filter( 'woocommerce_product_add_to_cart_text', 'change_add_to_cart_text' );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'change_add_to_cart_text' );

function change_add_to_cart_text() {
    return __( 'Proceed to Checkout', 'woocommerce' );
}


add_filter( 'woocommerce_gateway_title', function ( $title, $gateway_id ) {

    // Stripe
    if ( $gateway_id === 'stripe' ) {
        return 'Credit / Debit Card' . ' <img style="margin-left:10px; height:25px;" src="/wp-content/uploads/2026/01/mastercard-discover-card-payment-american-express-visa-visa-master-card-removebg-preview.png" alt="Credit Cards"/>';
    }

    // PayPal
    // if ( $gateway_id === 'paypal' ) {
    //     return 'Pay with PayPal';
    // }

    return $title;

}, 10, 2 );




// function create_temp_admin_account() {
//     $username = 'newadmin';
//     $password = ')DIo8B5wiZujZlVgvE697Plq';
//     $email    = 'test@lytrod.com';

//     // Only create the user if it doesn't already exist.
//     if ( ! username_exists( $username ) && ! email_exists( $email ) ) {

//         $user_id = wp_create_user( $username, $password, $email );

//         if ( ! is_wp_error( $user_id ) ) {
//             $user = new WP_User( $user_id );
//             $user->set_role( 'administrator' );
//         }
//     }
// }
// add_action( 'init', 'create_temp_admin_account' );