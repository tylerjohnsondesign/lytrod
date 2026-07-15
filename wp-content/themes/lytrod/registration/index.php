<?php


add_action('wp', 'check_page');
function check_page() {
	
	if ( function_exists('WC') && WC()->cart ) {
        WC()->cart->get_cart(); // Force cart init
    }
	
    if (is_page('intellicutinstallation') ) {
       // Add custom fields to the checkout page
        add_action( 'woocommerce_after_checkout_billing_form', 'add_custom_checkout_fields' );
        function add_custom_checkout_fields( $checkout ) {
            $custom_fields = array(
				'intellicut_phone_number' => array(
                    'type' => 'tel',
                    'label' => __('Phone Number ( Include Country Code )', 'woocommerce'),
                    'placeholder' => __('Enter Phone Number', 'woocommerce'),
                    'required' => true,
                ),
                'company_name' => array(
                    'type' => 'text',
                    'label' => __('Company Name:', 'woocommerce'),
                    'placeholder' => __('Enter Company Name', 'woocommerce'),
                    'required' => true,
                ),
                'product_id' => array(
                    'type' => 'text',
                    'label' => __('Intellicut Product ID:', 'woocommerce'),
                    'placeholder' => __('Enter Product ID', 'woocommerce'),
                    'required' => true,
                ),
                'machine_type' => array(
                    'type' => 'select',
                    'label' => __('Aerocut Type:', 'woocommerce'),
                    'options' => array(
                        '' => __('Please Select', 'woocommerce'),
                        'aerocut_velocity' => __('Aerocut Velocity', 'woocommerce'),
                        'aerocut_prime' => __('AeroCut Prime', 'woocommerce'),
                        'aerocut_x' => __('AeroCut X', 'woocommerce'),
                        'aerocut_x_pro' => __('AeroCut X Pro', 'woocommerce'),
                        'aerocut_one' => __('AeroCut One', 'woocommerce'),
                        'unknown' => __('Unknown', 'woocommerce'),
                    ),
                    'required' => true,
                ),
            );
			
			$show_checkbox = false;

			if (is_user_logged_in()) {
				// Check if current user is admin
				if (current_user_can('administrator')) {
					$show_checkbox = true;
				}

				// Check if switched from admin using User Switching plugin
				if (function_exists('current_user_switched') && current_user_switched()) {
					$old_user = current_user_switched();
					if ($old_user && user_can($old_user->ID, 'administrator')) {
						$show_checkbox = true;
					}
				}
			}
			
			// Add admin-only checkbox
			if ($show_checkbox) {
				$custom_fields['do_not_send_email'] = array(
					'type' => 'checkbox',
					'label' => __('Do not send email', 'woocommerce'),
					'class' => array('form-row-wide'),
					'required' => false,
					'default'  => '1', // This makes it checked by default
				);
			}

            foreach ( $custom_fields as $key => $field ) {
                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
            }
        }

        add_action( 'woocommerce_checkout_process', 'validate_custom_checkout_fields' );
        function validate_custom_checkout_fields() {
            $custom_fields = array(
				'intellicut_phone_number' => __('Phone Number', 'woocommerce'),
                'company_name' => __('Company Name', 'woocommerce'),
                'product_id' => __('Intellicut Product ID', 'woocommerce'),
                'machine_type' => __('Aerocut Type', 'woocommerce'),
            );
        
            foreach ( $custom_fields as $key => $label ) {
                if ( ! $_POST[$key] ) {
                    wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), $label ), 'error' );
                }
            }
        }

        //add_filter( 'woocommerce_order_button_text', 'custom_woocommerce_order_button_text' );
        function custom_woocommerce_order_button_text() {
            return __( 'Proceed to Checkout', 'woocommerce' );
        }
    }
	
	if (is_page('bizcardcut-installation')) {
       // Add custom fields to the checkout page
        add_action( 'woocommerce_after_checkout_billing_form', 'add_custom_checkout_fields' );
        function add_custom_checkout_fields( $checkout ) {
            $custom_fields = array(
				'bizcard_phone_number' => array(
                    'type' => 'tel',
                    'label' => __('Phone Number ( Include Country Code )', 'woocommerce'),
                    'placeholder' => __('Enter Phone Number', 'woocommerce'),
                    'required' => true,
                ),
                'bizcard_company_name' => array(
                    'type' => 'text',
                    'label' => __('Company Name:', 'woocommerce'),
                    'placeholder' => __('Enter Company Name', 'woocommerce'),
                    'required' => true,
                ),
                'bizcard_product_id' => array(
                    'type' => 'text',
                    'label' => __('Bizcard Product ID:', 'woocommerce'),
                    'placeholder' => __('Enter Product ID', 'woocommerce'),
                    'required' => true,
                ),
//                 'bizcard_machine_type' => array(
//                     'type' => 'select',
//                     'label' => __('Bizcard Type:', 'woocommerce'),
//                     'options' => array(
//                         '' => __('Please Select', 'woocommerce'),
//                         'bizcard_prime' => __('Bizcard Prime', 'woocommerce'),
//                         'bizcard_x' => __('Bizcard X', 'woocommerce'),
//                         'bizcard_x_pro' => __('Bizcard X Pro', 'woocommerce'),
//                         'bizcard_one' => __('Bizcard One', 'woocommerce'),
//                         'unknown' => __('Unknown', 'woocommerce'),
//                     ),
//                     'required' => true,
//                 ),
            );
			
			$show_checkbox = false;

			if (is_user_logged_in()) {
				// Check if current user is admin
				if (current_user_can('administrator')) {
					$show_checkbox = true;
				}

				// Check if switched from admin using User Switching plugin
				if (function_exists('current_user_switched') && current_user_switched()) {
					$old_user = current_user_switched();
					if ($old_user && user_can($old_user->ID, 'administrator')) {
						$show_checkbox = true;
					}
				}
			}
			
			// Add admin-only checkbox
			if ($show_checkbox) {
				$custom_fields['do_not_send_email'] = array(
					'type' => 'checkbox',
					'label' => __('Do not send email', 'woocommerce'),
					'class' => array('form-row-wide'),
					'required' => false,
					'default'  => '1', // This makes it checked by default
				);
			}

            foreach ( $custom_fields as $key => $field ) {
                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
            }
        }

        add_action( 'woocommerce_checkout_process', 'validate_custom_checkout_fields' );
        function validate_custom_checkout_fields() {
            $custom_fields = array(
				'bizcard_phone_number' => __('Phone Number', 'woocommerce'),
                'bizcard_company_name' => __('Company Name', 'woocommerce'),
                'bizcard_product_id' => __('Bizcard Product ID', 'woocommerce'),
                'bizcard_machine_type' => __('Aerocut Type', 'woocommerce'),
            );
        
            foreach ( $custom_fields as $key => $label ) {
                if ( ! $_POST[$key] ) {
                    wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), $label ), 'error' );
                }
            }
        }

        //add_filter( 'woocommerce_order_button_text', 'custom_woocommerce_order_button_text' );
        function custom_woocommerce_order_button_text() {
            return __( 'Proceed to Checkout', 'woocommerce' );
        }
    }
	
	

    if (is_page('intellicutglobalinstallation')) {
        add_action( 'woocommerce_after_checkout_billing_form', 'add_intellicutglobal_checkout_fields' );
        function add_intellicutglobal_checkout_fields( $checkout ) {
            
            $custom_fields = array(
                'intellicut_global_phone_number' => array(
                    'type' => 'tel',
                    'label' => __('Phone Number ( Include Country Code )', 'woocommerce'),
                    'placeholder' => __('Enter Phone Number', 'woocommerce'),
                    'required' => true,
                ),
                'ucidealer_field' => array(
                    'type'        => 'text',
                    'label'       => __('Uchida Dealer', 'woocommerce'),
                    'placeholder' => __('Enter Uchida Dealer', 'woocommerce'),
                    'required'    => true,
                    'class'       => array('form-row-wide'),
                    'clear'       => true,
                ),
                'intellicut_global_product_id' => array(
                    'type'        => 'text',
                    'label'       => __('Intellicut Global Product ID', 'woocommerce'),
                    'placeholder' => __('Enter Intellicut Global Product ID', 'woocommerce'),
                    'required'    => true, // Set to true if required
                    'class'       => array('form-row-wide'),
                    'clear'       => true,
                ),
                'intellicut_global_machine_type' => array(
                    'type' => 'select',
                    'label' => __('Aerocut Type:', 'woocommerce'),
                    'options' => array(
                        '' => __('Please Select', 'woocommerce'),
                        'aerocut_velocity' => __('Aerocut Velocity', 'woocommerce'),
                        'aerocut_prime' => __('AeroCut Prime', 'woocommerce'),
                        'aerocut_x' => __('AeroCut X', 'woocommerce'),
                        'aerocut_x_pro' => __('AeroCut X Pro', 'woocommerce'),
                        'aerocut_one' => __('AeroCut One', 'woocommerce'),
                        'unknown' => __('Unknown', 'woocommerce'),
                    ),
                    'required' => true,
                ),
                'intellicut_global_aerocut_machine_number' => array(
                    'type'        => 'text',
                    'label'       => __('Aerocut Machine Number', 'woocommerce'),
                    'placeholder' => __('Enter Aerocut Machine Number', 'woocommerce'),
                    'required'    => true, // Set to true if required
                    'class'       => array('form-row-wide'),
                    'clear'       => true,
                ),
            );
			
			$show_checkbox = false;

			if (is_user_logged_in()) {
				// Check if current user is admin
				if (current_user_can('administrator')) {
					$show_checkbox = true;
				}

				// Check if switched from admin using User Switching plugin
				if (function_exists('current_user_switched') && current_user_switched()) {
					$old_user = current_user_switched();
					if ($old_user && user_can($old_user->ID, 'administrator')) {
						$show_checkbox = true;
					}
				}
			}
			
			// Add admin-only checkbox
			if ($show_checkbox) {
				$custom_fields['do_not_send_email'] = array(
					'type' => 'checkbox',
					'label' => __('Do not send email', 'woocommerce'),
					'class' => array('form-row-wide'),
					'required' => false,
					'default'  => '1', // This makes it checked by default
				);
			}

            foreach ( $custom_fields as $key => $field ) {
                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
            }
        }
    }

    if (is_page('vrcutimposeinstallation')) {
        add_action( 'woocommerce_after_checkout_billing_form', 'add_vrcutimposeinstallation_checkout_fields' );
        function add_vrcutimposeinstallation_checkout_fields( $checkout ) {
            
            $custom_fields = array(
                'vrcutimposeinstallation_phone_number' => array(
                    'type' => 'tel',
                    'label' => __('Phone Number ( Include Country Code )', 'woocommerce'),
                    'placeholder' => __('Enter Phone Number', 'woocommerce'),
                    'required' => true,
                ),
				
				'vrcutimposeinstallation_company_name' => array(
                    'type' => 'text',
                    'label' => __('Company Name:', 'woocommerce'),
                    'placeholder' => __('Enter Company Name', 'woocommerce'),
                    'required' => true,
                ),
               
                'vrcutimposeinstallation_product_id' => array(
                    'type'        => 'text',
                    'label'       => __('VRCut Impose Product ID', 'woocommerce'),
                    'placeholder' => __('Enter VRCut Impose Product ID', 'woocommerce'),
                    'required'    => true, // Set to true if required
                    'class'       => array('form-row-wide'),
                    'clear'       => true,
                ),
                'vrcutimposeinstallation_triump_type' => array(
                    'type' => 'select',
                    'label' => __('Triumph Type:', 'woocommerce'),
                    'options' => array(
                        '' => __('Please Select', 'woocommerce'),
                        '5260' => __('5260', 'woocommerce'),
                        '5560' => __('5560', 'woocommerce'),
                        '6660' => __('6660', 'woocommerce'),
                        '7260' => __('7260', 'woocommerce'),
                        'unknown' => __('Unknown', 'woocommerce'),
                    ),
                    'required' => true,
                ),
            );
			
			$show_checkbox = false;

			if (is_user_logged_in()) {
				// Check if current user is admin
				if (current_user_can('administrator')) {
					$show_checkbox = true;
				}

				// Check if switched from admin using User Switching plugin
				if (function_exists('current_user_switched') && current_user_switched()) {
					$old_user = current_user_switched();
					if ($old_user && user_can($old_user->ID, 'administrator')) {
						$show_checkbox = true;
					}
				}
			}
			
			// Add admin-only checkbox
			if ($show_checkbox) {
				$custom_fields['do_not_send_email'] = array(
					'type' => 'checkbox',
					'label' => __('Do not send email', 'woocommerce'),
					'class' => array('form-row-wide'),
					'required' => false,
					'default'  => '1', // This makes it checked by default
				);
			}

            foreach ( $custom_fields as $key => $field ) {
                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
            }
        }
    }

    if (is_page('intellicutaneinstallation')) {
        add_action( 'woocommerce_after_checkout_billing_form', 'add_intellicutaneinstallation_checkout_fields' );
        function add_intellicutaneinstallation_checkout_fields( $checkout ) {
            
            $custom_fields = array(

                'intellicutaneinstallation_company_name' => array(
                    'type' => 'text',
                    'label' => __('Company Name:', 'woocommerce'),
                    'placeholder' => __('Enter Company Name', 'woocommerce'),
                    'required' => true,
                ),
                'intellicutaneinstallation_phone_number' => array(
                    'type' => 'tel',
                    'label' => __('Phone Number ( Include Country Code )', 'woocommerce'),
                    'placeholder' => __('Enter Phone Number', 'woocommerce'),
                    'required' => true,
                ),
               
                'intellicutaneinstallation_product_id' => array(
                    'type'        => 'text',
                    'label'       => __('IntellicutAeroCut nano Edition Product ID', 'woocommerce'),
                    'placeholder' => __('IntellicutAeroCut nano Edition Product ID', 'woocommerce'),
                    'required'    => true, // Set to true if required
                    'class'       => array('form-row-wide'),
                    'clear'       => true,
                ),
                'intellicutaneinstallation_aeroCut_type' => array(
                    'type' => 'select',
                    'label' => __('AeroCut Type:', 'woocommerce'),
                    'options' => array(
                        '' => __('Please Select', 'woocommerce'),
                        'AeroCut_Nano_Max' => __('AeroCut Nano Max', 'woocommerce'),
                        'AeroCut_Nano_Plus' => __('AeroCut Nano Plus', 'woocommerce'),
                        'AeroCut_Nano' => __('AeroCut Nano', 'woocommerce'),
                    ),
                    'required' => true,
                ),
            );
			
			$show_checkbox = false;

			if (is_user_logged_in()) {
				// Check if current user is admin
				if (current_user_can('administrator')) {
					$show_checkbox = true;
				}

				// Check if switched from admin using User Switching plugin
				if (function_exists('current_user_switched') && current_user_switched()) {
					$old_user = current_user_switched();
					if ($old_user && user_can($old_user->ID, 'administrator')) {
						$show_checkbox = true;
					}
				}
			}
			
			// Add admin-only checkbox
			if ($show_checkbox) {
				$custom_fields['do_not_send_email'] = array(
					'type' => 'checkbox',
					'label' => __('Do not send email', 'woocommerce'),
					'class' => array('form-row-wide'),
					'required' => false,
					'default'  => '1', // This makes it checked by default
				);
			}

            foreach ( $custom_fields as $key => $field ) {
                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
            }
        }
    }

    if (is_page('installation')) {
        add_action( 'woocommerce_after_checkout_billing_form', 'add_intellicutaneinstallation_checkout_fields' );
        function add_intellicutaneinstallation_checkout_fields( $checkout ) {
            
            $custom_fields = array(

                'installation_company_name' => array(
                    'type' => 'text',
                    'label' => __('Company Name:', 'woocommerce'),
                    'placeholder' => __('Enter Company Name', 'woocommerce'),
                    'required' => true,
                ),
                'installation_phone_number' => array(
                    'type' => 'tel',
                    'label' => __('Phone Number ( Include Country Code )', 'woocommerce'),
                    'placeholder' => __('Enter Phone Number', 'woocommerce'),
                    'required' => true,
                ),
               
                'installation_product_id' => array(
                    'type'        => 'text',
                    'label'       => __('Vison Direct Product ID', 'woocommerce'),
                    'placeholder' => __('Vison Direct Product ID', 'woocommerce'),
                    'required'    => true, // Set to true if required
                    'class'       => array('form-row-wide'),
                    'clear'       => true,
                ),
				
            );
			
			$show_checkbox = false;

			if (is_user_logged_in()) {
				// Check if current user is admin
				if (current_user_can('administrator')) {
					$show_checkbox = true;
				}

				// Check if switched from admin using User Switching plugin
				if (function_exists('current_user_switched') && current_user_switched()) {
					$old_user = current_user_switched();
					if ($old_user && user_can($old_user->ID, 'administrator')) {
						$show_checkbox = true;
					}
				}
			}
			// Add admin-only checkbox
			if ($show_checkbox) {
				$custom_fields['do_not_send_email'] = array(
					'type' => 'checkbox',
					'label' => __('Do not send email', 'woocommerce'),
					'class' => array('form-row-wide'),
					'required' => false,
					'default'  => '1', // This makes it checked by default
				);
			}
			
            foreach ( $custom_fields as $key => $field ) {
                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
            }
        }
    }
}

add_action( 'woocommerce_checkout_process', 'validate_intellicut_global_checkout_fields' );
function validate_intellicut_global_checkout_fields() {

    $custom_fields = array(
		'intellicut_phone_number' => __('Phone Number', 'woocommerce'),
		'company_name' => __('Company Name', 'woocommerce'),
		'product_id' => __('Intellicut Product ID', 'woocommerce'),
		'machine_type' => __('Aerocut Type', 'woocommerce'),
        'intellicut_global_phone_number' => __('Phone Number (Include Country Code)', 'woocommerce'),
        'ucidealer_field' => __('Ucidealer Field', 'woocommerce'),
        'intellicut_global_product_id' => __('Intellicut Global Product ID', 'woocommerce'),
        'intellicut_global_machine_type' => __('Aerocut Type', 'woocommerce'),
        'intellicut_global_aerocut_machine_number' => __('Aerocut Machine Number', 'woocommerce'),
        'vrcutimposeinstallation_phone_number' => __('Phone Number (Include Country Code', 'woocommerce'),
        'vrcutimposeinstallation_product_id' => __('VRCut Impose Product ID', 'woocommerce'),
		'vrcutimposeinstallation_company_name' => __('Company Name', 'woocommerce'),
        'vrcutimposeinstallation_triump_type' => __('Triumph Type', 'woocommerce'),
        'intellicutaneinstallation_company_name' => __('Company Name', 'woocommerce'),
        'intellicutaneinstallation_phone_number' => __('Phone Number (Include Country Code', 'woocommerce'),
        'intellicutaneinstallation_product_id' => __('Intellicut AeroCut nano Edittion Product Id', 'woocommerce'),
        'intellicutaneinstallation_aeroCut_type' => __('AeroCut Type', 'woocommerce'),
        'installation_company_name' => __('Company Name', 'woocommerce'),
        'installation_phone_number' => __('Phone Number (Include Country Code', 'woocommerce'),
        'installation_product_id' => __('Vison Direct Product Id', 'woocommerce'),
		'bizcard_phone_number' =>  __('Bizcard Phone Number', 'woocommerce'),
		'bizcard_company_name' =>  __('Bizcard Company Name', 'woocommerce'),
		'bizcard_product_id' =>  __('Bizcard Product ID', 'woocommerce'),
		'bizcard_machine_type' =>  __('Bizcard Type', 'woocommerce'),
        
    );

    foreach ( $custom_fields as $key => $label ) {
        if ( isset($_POST[$key]) && empty($_POST[$key]) ) {
            wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), $label ), 'error' );
        }
        if ($key === 'intellicut_global_product_id') {
            if ( isset($_POST[$key]) && !empty($_POST[$key]) && !preg_match('/(^464)\d{4}$/', $_POST[$key]) ) {
                wc_add_notice( sprintf( __( '%s is wrong.', 'woocommerce' ), $label ), 'error' );
            } else if ( isset($_POST[$key]) && !empty($_POST[$key])) {
				$serial = $_POST[$key];
				global $wpdb;
				
				$allowed_duplicates = array(4642242);
				if ( ! in_array($serial, $allowed_duplicates ) ) {

					$existing = $wpdb->get_var( $wpdb->prepare(
						"SELECT pm.meta_id
						 FROM {$wpdb->postmeta} pm
						 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
						 WHERE pm.meta_key = %s
						 AND pm.meta_value = %s
						 AND p.post_status != 'trash'
						 AND p.post_type IN ('product', 'shop_subscription')
						 LIMIT 1",
						'intellicut_global_product_id',
						$serial
					) );

					if ( $existing ) {
						wc_add_notice( 'Intellicut Global Product ID has already been registered.', 'error' );
					}
				}
			}
        }

        if ($key === 'vrcutimposeinstallation_product_id') {
            if ( isset($_POST[$key]) && !empty($_POST[$key]) && !preg_match('/(^217)\d{4}$/', $_POST[$key]) ) {
                wc_add_notice( sprintf( __( '%s is wrong.', 'woocommerce' ), $label ), 'error' );
            } else if ( isset($_POST[$key]) && !empty($_POST[$key])) {
				$serial = $_POST[$key];
				global $wpdb;
				$allowed_duplicates = array(2175410);
				if ( ! in_array($serial, $allowed_duplicates ) ) {
					$existing = $wpdb->get_var( $wpdb->prepare(
						"SELECT pm.meta_id
						 FROM {$wpdb->postmeta} pm
						 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
						 WHERE pm.meta_key = %s
						 AND pm.meta_value = %s
						 AND p.post_status != 'trash'
						 AND p.post_type IN ('product', 'shop_subscription')
						 LIMIT 1",
						'vrcutimposeinstallation_product_id',
						$serial
					) );

					if ( $existing ) {
						wc_add_notice( 'VRCut Impose Product ID has already been registered.', 'error' );
					}
				}
			}
        }

        if ($key === 'intellicutaneinstallation_product_id') {
            if ( isset($_POST[$key]) && !empty($_POST[$key]) && !preg_match('/(^173)\d{4}$/', $_POST[$key]) ) {
                wc_add_notice( sprintf( __( '%s is wrong.', 'woocommerce' ), $label ), 'error' );
            } else if ( isset($_POST[$key]) && !empty($_POST[$key])) {
				$serial = $_POST[$key];
				global $wpdb;
				$allowed_duplicates = array(1733162);
				
				if ( ! in_array($serial, $allowed_duplicates ) ) {
					$existing = $wpdb->get_var( $wpdb->prepare(
						"SELECT pm.meta_id
						 FROM {$wpdb->postmeta} pm
						 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
						 WHERE pm.meta_key = %s
						 AND pm.meta_value = %s
						 AND p.post_status != 'trash'
						 AND p.post_type IN ('product', 'shop_subscription')
						 LIMIT 1",
						'intellicutaneinstallation_product_id',
						$serial
					) );

					if ( $existing ) {
						wc_add_notice( 'IntellicutAeroCut nano Edition Product ID has already been registered.', 'error' );
					}
				}
			}
        }

        if ($key === 'installation_product_id') {
            if ( isset($_POST[$key]) && !empty($_POST[$key]) && !preg_match('/(^140)\d{4}$/', $_POST[$key]) ) {
                wc_add_notice( sprintf( __( '%s is wrong.', 'woocommerce' ), $label ), 'error' );
            }
        }
		
		if ($key === 'product_id') {
			
			if ( isset($_POST[$key]) && !empty($_POST[$key])) {
				$serial = $_POST[$key];
				global $wpdb;
				$allowed_duplicates = array(1995821, 1996053);
				
				if ( ! in_array($serial, $allowed_duplicates ) ) {
					$existing = $wpdb->get_var( $wpdb->prepare(
						"SELECT pm.meta_id
						 FROM {$wpdb->postmeta} pm
						 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
						 WHERE pm.meta_key = %s
						 AND pm.meta_value = %s
						 AND p.post_status != 'trash'
						 AND p.post_type IN ('product', 'shop_subscription')
						 LIMIT 1",
						'intellicut_serial_number',
						$serial
					) );

					if ( $existing ) {
						wc_add_notice( 'Intellicut Product ID has already been registered.', 'error' );
					}
				}
			}
		}
		
		if ($key === 'bizcard_product_id') {
            if ( isset($_POST[$key]) && !empty($_POST[$key]) && !preg_match('/(^739)\d{4}$/', $_POST[$key]) ) {
                wc_add_notice( sprintf( __( '%s is wrong.', 'woocommerce' ), $label ), 'error' );
            } else if ( isset($_POST[$key]) && !empty($_POST[$key])) {
				$serial = $_POST[$key];
				global $wpdb;
				$existing = $wpdb->get_var( $wpdb->prepare(
					"SELECT pm.meta_id
					 FROM {$wpdb->postmeta} pm
					 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					 WHERE pm.meta_key = %s
					 AND pm.meta_value = %s
					 AND p.post_status != 'trash'
					 AND p.post_type IN ('product', 'shop_subscription')
					 LIMIT 1",
					'bizcard_product_id',
					$serial
				) );
				
				if ( $existing ) {
					wc_add_notice( 'Bizcard Product ID has already been registered.', 'error' );
				}
			}
        }
    
//         if ($key === 'intellicut_global_aerocut_machine_number') {
//             if ( isset($_POST[$key]) && !empty($_POST[$key]) && !preg_match('/(^2)\d{7}$/', $_POST[$key]) ) {
//                 wc_add_notice( sprintf( __( '%s must follow the pattern: 2 followed by 7 digits.', 'woocommerce' ), $label ), 'error' );
//             }
//         }
    }

} 

add_action( 'woocommerce_checkout_update_order_meta', 'save_intellicut_global_checkout_fields' );
function save_intellicut_global_checkout_fields( $order_id ) {
   
    $custom_fields = array(
        'intellicut_global_phone_number',
        'uchidadealer_field',
        'intellicut_global_product_id',
        'intellicut_global_machine_type',
        'intellicut_global_aerocut_machine_number',
        'vrcutimposeinstallation_phone_number', 
		'vrcutimposeinstallation_company_name',
        'vrcutimposeinstallation_product_id', 
        'vrcutimposeinstallation_triumph_type',
        'intellicutaneinstallation_company_name',
        'intellicutaneinstallation_phone_number',
        'intellicutaneinstallation_product_id',
        'intellicutaneinstallation_aeroCut_type',
        'installation_company_name',
        'installation_phone_number',
        'installation_product_id',
		'bizcard_phone_number',
		'bizcard_company_name', 
		'bizcard_product_id',
		'bizcard_machine_type',
    );
	
    // Loop through the fields and save them to the order meta
    foreach ( $custom_fields as $key ) {
        if ( isset($_POST[$key]) && !empty($_POST[$key]) ) {
            update_post_meta( $order_id, $key, sanitize_text_field( $_POST[$key] ) );
        }
    }
}

add_filter( 'woocommerce_account_menu_items', function($items) {
 
    unset($items['edit-address']);
    if(is_page('intellicutinstallation')||is_page('vrcutimposeinstallation')||is_page('vrcutcontrollerinstallation') || is_page('intellicutaneinstallation') || is_page('installation') || is_page('bizcardcut-installation') ) {

        //unset($items['orders']);
        unset($items['edit-account']);
		    
    }

    $items = array(
        'dashboard'          => __( 'Dashboard', 'woocommerce' ),
        'register'  => __('Register License','woocomerce'),
		'createticket' => __('Submit a Ticket','woocomerce'),
        'subscriptions'             => __( 'Subscriptions', 'woocommerce' ),
        'downloads'          => __( 'Downloads', 'woocommerce' ),
		'vrcut-request'     => __('Request VRCut Controller Hardware Kit', 'woocommerce'),
		'orders' => __('Order History', 'woocommerce'),
        'edit-account'    	=> __( 'Account', 'woocommerce' ),
        'payment-methods'    => __( 'Payment Methods', 'woocommerce' ),
        'customer-logout'    => __( 'Logout', 'woocommerce' ),
        
    
    );
    return $items;
 });

 add_action( 'init', function() {
	add_rewrite_endpoint( 'register', EP_ROOT | EP_PAGES );
	 add_rewrite_endpoint( 'createticket', EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'payment-methods', EP_ROOT | EP_PAGES ); 
	add_rewrite_endpoint('vrcut-request', EP_ROOT | EP_PAGES); 
 });


add_action('woocommerce_account_register_endpoint', 'register_license_tab_content');
add_action('woocommerce_account_createticket_endpoint', 'create_ticket_tab_content');

function create_ticket_tab_content() {
	
	echo do_shortcode('[ninja_form id=4]');
}

function vrcut_request_redirect_tem() {
    $current_url = untrailingslashit($_SERVER['REQUEST_URI']);
    if (strpos($current_url, '/my-account/vrcut-request') !== false) {
        wp_redirect(home_url('/requestakit/')); 
        exit;
    }
}
add_action('template_redirect', 'vrcut_request_redirect_tem');

function custom_woocommerce_dashboard_buttons() {
	$current_user = wp_get_current_user();
    if ($current_user->exists()) {
        echo '<h2>Hello, ' . esc_html($current_user->display_name) . '!</h2>';
    }
    ?>
    <div class="dashboard-buttons">
<!--         <a href="<?php //echo esc_url( wc_get_endpoint_url( 'submit-ticket' ) ); ?>" class="dashboard-btn">Submit A Ticket</a> -->
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'register' ) ); ?>" class="dashboard-btn">Register License</a>
		 <a href="<?php echo esc_url( wc_get_endpoint_url( 'createticket' ) ); ?>" class="dashboard-btn">Submit a Ticket</a>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'subscriptions' ) ); ?>" class="dashboard-btn">Subscriptions</a>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'downloads' ) ); ?>" class="dashboard-btn">Downloads</a>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'vrcut-request' ) ); ?>" class="dashboard-btn">Request VRCut Controller Hardware Kit</a>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>" class="dashboard-btn">Account</a>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'payment-methods' ) ); ?>" class="dashboard-btn">Payment Methods</a>
        <a href="<?php echo esc_url( wc_get_endpoint_url( 'customer-logout' ) ); ?>" class="dashboard-btn">Log out</a>
    </div>
    <style>
		.woocommerce-MyAccount-content p {
			display: none;
		}
		.dashboard-buttons {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 15px;
			margin-top: 20px;
			align-items: center;
		}

		.dashboard-btn {
			display: inline-block;
			padding: 12px 16px;
			border: 1px solid #ddd;
			text-decoration: none;
			color: #0073aa;
			background: #fff;
			border-radius: 5px;
			text-align: center;
			transition: all 0.3s ease-in-out;
		}

		.dashboard-btn:hover {
			background: #0073aa;
			color: #fff;
		}

		/* Responsive: Mobile (1 column), Tablet (2 columns) */
		@media (max-width: 1024px) {
			.dashboard-buttons {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		@media (max-width: 768px) {
			.dashboard-buttons {
				display: flex;
				flex-direction: column;
				align-items: center;
			}

			.dashboard-btn {
				width: 100%;
				max-width: 300px;
			}
		}
	</style>
    <?php
}
add_action('woocommerce_account_dashboard', 'custom_woocommerce_dashboard_buttons');


function register_license_tab_content() {
    ?>
    <ul>
        <li><a href="/intellicutinstallation/?add-to-cart-variation=26480&variation_id=26486&_wcsnonce=4e0c1e822a&product_title=Intellicut&groupcount=1">Register IntelliCut license (US & Canada)</a></li>

        <li><a href="/intellicutglobalinstallation/?add-to-cart-variation=26818&variation_id=26979&_wcsnonce=d4284c6033&product_title=Intellicut%20Global&groupcount=1">Register IntelliCut Global license (Outside of US & Canada)</a></li>

        <li><a href="/vrcutimposeinstallation/?add-to-cart-variation=26805&variation_id=26810&_wcsnonce=d4284c6033&product_title=VRCut&groupcount=1">Register VRCut license</a></li>

        <li><a href="/intellicutaneinstallation/?add-to-cart-variation=26495&variation_id=26804&_wcsnonce=d4284c6033&product_title=Intellicut%3A%20AeroCut%20nano%20Edition&groupcount=1">Register IntelliCut AeroCut nano Edition license</a></li>

        <li><a href="/bizcardcut-installation/?add-to-cart-variation=28728&variation_id=28735&_wcsnonce=b061cd2f26&product_title=Bizcard&groupcount=1">Register Bizcard Cut license</a></li>
<!--         <li><a href="/product/vision-direct/">Register VisionDirect license</a></li> -->
    </ul>
    <p>
        After registration, software can be downloaded and installed immediately from the Downloads tab. 
        If you have an issue with registration, please contact <a href="mailto:admin@lytrod.com">admin@lytrod.com</a>. Please allow one business day for license subscription details and expiration date to be updated.
    </p>
    <?php
}

 //Intellicut Installation Page
add_action( 'template_redirect', 'custom_redirect_for_intellicut' );

function custom_redirect_for_intellicut() {
	
	if ( ! is_user_logged_in() ) return;

    // ✅ Ensure WooCommerce is loaded and cart/session is available
    if ( ! function_exists('WC') || ! WC()->session || ! WC()->cart ) return;
   
    // Check if the current page is 'intellicutinstallation' and if the query parameters are not already set
    if ( is_page( 'intellicutinstallation' ) && !isset($_GET['add-to-cart-variation']) ) {
         // Clear the cart
         WC()->cart->empty_cart();

         // Define the redirect URL
         $redirect_url = '/intellicutinstallation?add-to-cart-variation=26480&variation_id=26486&_wcsnonce=4e0c1e822a&product_title=Intellicut&groupcount=1';
         
         // Perform the redirect
         wp_safe_redirect( $redirect_url );
         exit;
    } else if ( is_page( 'bizcardcut-installation' ) && !isset($_GET['add-to-cart-variation']) ) {
         // Clear the cart
         WC()->cart->empty_cart();

         // Define the redirect URL
         $redirect_url = '/bizcardcut-installation/?add-to-cart-variation=28728&variation_id=28735&_wcsnonce=b061cd2f26&product_title=Bizcard&groupcount=1';
         
         // Perform the redirect
         wp_safe_redirect( $redirect_url );
         exit;
    }  else if ( is_page( 'intellicutglobalinstallation' ) && !isset($_GET['add-to-cart-variation']) ) {
         // Clear the cart
         WC()->cart->empty_cart();

         // Define the redirect URL
         $redirect_url = '/intellicutglobalinstallation?add-to-cart-variation=26818&variation_id=26979&_wcsnonce=d4284c6033&product_title=Intellicut%20Global%20Years&groupcount=1';
         
         // Perform the redirect
         wp_safe_redirect( $redirect_url );
         exit;
    } else if ( is_page( 'vrcutimposeinstallation' ) && !isset($_GET['add-to-cart-variation']) ) {
         // Clear the cart
         WC()->cart->empty_cart();

         // Define the redirect URL
         $redirect_url = '/vrcutimposeinstallation/?add-to-cart-variation=26805&variation_id=26810&_wcsnonce=d4284c6033&product_title=VRCut&groupcount=1';
         
         // Perform the redirect
         wp_safe_redirect( $redirect_url );
         exit;
    } else if ( is_page( 'intellicutaneinstallation' ) && !isset($_GET['add-to-cart-variation']) ) {
         // Clear the cart
         WC()->cart->empty_cart();

         // Define the redirect URL
         $redirect_url = '/intellicutaneinstallation/?add-to-cart-variation=26495&variation_id=26804&_wcsnonce=d4284c6033&product_title=Intellicut%3A%20AeroCut%20nano%20Edition&groupcount=1';
         
         // Perform the redirect
         wp_safe_redirect( $redirect_url );
         exit;
    }

}

add_filter( 'manage_edit-shop_subscription_columns', 'add_serial_number_column' );
function add_serial_number_column( $columns ) {
    $status_column_position = array_search( 'status', array_keys( $columns ) );
    $columns = array_slice( $columns, 0, $status_column_position + 1, true ) +
               array( 'serial_number' => __( 'Serial Number', 'textdomain' ) ) +
               array_slice( $columns, $status_column_position + 1, NULL, true );
    
    return $columns;
}

add_action( 'manage_shop_subscription_posts_custom_column', 'populate_serial_number_column', 10, 2 );
function populate_serial_number_column( $column, $post_id ) {
    if ( 'serial_number' === $column ) {
        $order = wc_get_order($post_id);
		$meta_keys = array(
			'intellicut_serial_number',
			'intellicut_global_product_id',
			'vrcutimposeinstallation_product_id',
			'intellicutaneinstallation_product_id',
			'installation_product_id',
			'bizcard_product_id'
		);

		foreach ($meta_keys as $meta_key) {
			$meta_value = $order->get_meta($meta_key);
			if (!empty($meta_value)) {
				echo '<b>'. $meta_value. '</b>';
			} 
		}
    }
}

// add_action('woocommerce_process_shop_subscription_meta', function($post_id) {
//     $next_payment = get_post_meta($post_id, '_schedule_next_payment', true);
//     if ($next_payment) {
//         $trial_end = strtotime($next_payment) - 1;
//         update_post_meta($post_id, '_trial_end', date('Y-m-d H:i:s', $trial_end));
//     }
// });

add_action('admin_footer', function () {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'shop_subscription') {
        ?>
        <script>
//         jQuery(function ($) {
//             // Toggle Trial End Visibility
//             const trialRow = $('#subscription-trial_end-date');
//             const toggleBtn = $('<button type="button" class="button">👁 Show Trial End</button>');
//             trialRow.hide();
//             trialRow.before($('<div style="margin: 10px 0;"></div>').append(toggleBtn));

//             toggleBtn.on('click', function () {
//                 trialRow.toggle();
//                 $(this).text(trialRow.is(':visible') ? '🙈 Hide Trial End' : '👁 Show Trial End');
//             });

//             // Remove leading zero if any
//             function removeLeadingZero(num) {
//                 const str = num.toString();
//                 return str.startsWith('0') ? str.substring(1) : str;
//             }

//             function updateDates() {
//                 // Get Trial End inputs (the date input id may be #subscription-trial_end-date)
//                 const trialDate = $('#subscription-trial_end-date').val();
//                 let trialHour = $('#trial_end_hour').val();
//                 let trialMinute = $('#trial_end_minute').val();

//                 // Get Next Payment inputs
//                 const nextDate = $('#next_payment').val();
//                 let nextHour = $('#next_payment_hour').val();
//                 let nextMinute = $('#next_payment_minute').val();

//                 // Sanity checks
//                 if (trialDate && trialHour && trialMinute) {
//                     trialHour = removeLeadingZero(trialHour);
//                     trialMinute = removeLeadingZero(trialMinute);

//                     // Set Trial End fields exactly as Trial End inputs
//                     $('#trial_end').val(trialDate).attr('value', trialDate);
//                     $('#trial_end_hour').val(trialHour).attr('value', trialHour);
//                     $('#trial_end_minute').val(trialMinute).attr('value', trialMinute);
//                 }

//                 if (nextDate && nextHour && nextMinute) {
//                     nextHour = removeLeadingZero(nextHour);
//                     nextMinute = removeLeadingZero(nextMinute);

//                     // Set Next Payment fields exactly as Next Payment inputs
//                     $('#next_payment').val(nextDate).attr('value', nextDate);
//                     $('#next_payment_hour').val(nextHour).attr('value', nextHour);
//                     $('#next_payment_minute').val(nextMinute).attr('value', nextMinute);

//                     // If you want to update End fields to next payment as well (optional)
//                     $('#end').val(nextDate).attr('value', nextDate);
//                     $('#end_hour').val(nextHour).attr('value', nextHour);
//                     $('#end_minute').val(nextMinute).attr('value', nextMinute);
//                 }
//             }

//             // Bind change events on all related inputs
//             $('#subscription-trial_end-date, #trial_end_hour, #trial_end_minute, #next_payment, #next_payment_hour, #next_payment_minute').on('change', updateDates);

//             // Initial call
//             updateDates();
//         });
        </script>
        <?php
    }
});



add_filter( 'woocommerce_subscriptions_switch_is_identical_product', '__return_false' );

// Remove restriction for same product switching
add_filter( 'wcs_user_has_subscription', function( $user_has_subscription, $user_id, $product_id ) {
    if ( isset( $_GET['switch-subscription'] ) ) {
        return false;
    }
    return $user_has_subscription;
}, 10, 3 );

add_filter( 'woocommerce_cart_needs_payment', function( $needs_payment, $cart ) {
    if ( ! $cart ) return $needs_payment;

    foreach ( $cart->get_cart() as $cart_item ) {
        if ( ! empty( $cart_item['subscription_switch'] ) ) {
            return true; // Force payment required for subscription switch
        }
    }
    return $needs_payment;
}, 10, 2 );

add_action('woocommerce_checkout_create_order', function($order, $data) {
	if (!empty($_POST['do_not_send_email'])) {
		$order->update_meta_data('_do_not_send_email', 'yes');
	}
}, 10, 2);

add_filter('woocommerce_email_enabled_customer_processing_order', 'suppress_order_email_if_checked', 10, 2);
add_filter('woocommerce_email_enabled_new_order', 'suppress_order_email_if_checked', 10, 2);

function suppress_order_email_if_checked($enabled, $order) {
	if (is_a($order, 'WC_Order') && $order->get_meta('_do_not_send_email') === 'yes') {
		return false;
	}
	return $enabled;
}
