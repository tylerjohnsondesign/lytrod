<?php


class Theme_Customizer_Tickets{

    
	public $page_title;

	public $page_path;


    public function __construct($page_title,$page_path){


            $this->page_title  = $page_title;
            $this->page_path = $page_path;


    }
    public function tickets_customize_register($wp_customize)
	{
	

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

		  // 
		  $wp_customize->add_setting( 'dashboard_title_color', array(
			'default' => '#000000',
		));

		  $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'dashboard_title_color', array(
			'label' => 'Title Header',
			'section' => 'dashboard',
			'settings' => 'dashboard_title_color',
			'priority'  => 3
	 
		)));
		// open close color 
		$wp_customize->add_setting( 'open_close_color', array(
			'default' => '#ffffff',
		));

		  $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'open_close_color', array(
			'label' => 'Open and Close Text: ',
			'section' => 'dashboard',
			'settings' => 'open_close_color',
			'priority'  => 4
	 
		)));

		// open close icon
		$wp_customize->add_setting( 'open_close_color_icon_text', array(
			'default' => '#ffffff',
		));

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'open_close_color_icon_text', array(
			'label' => 'Open and Close Icon Color: ',
			'section' => 'dashboard',
			'settings' => 'open_close_color_icon_text',
			'priority'  => 4
		
		)));

			// open close background
			$wp_customize->add_setting( 'open_close_background_color', array(
				'default' => '#ffffff',
			));
	
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'open_close_background_color', array(
				'label' => 'Open and Close Background Color: ',
				'section' => 'dashboard',
				'settings' => 'open_close_background_color',
				'priority'  => 4
			
			)));

		// Submit form button button background
		$wp_customize->add_setting( 'submit_form_button_color_background', array(
			'default' => '#ffffff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'submit_form_button_color_background', array(
			'label' => 'Submit Form Button Background Color : ',
			'section' => 'dashboard',
			'settings' => 'submit_form_button_color_background',
			'priority'  => 4
		
		)));
	  
		// Submit form button text background
		$wp_customize->add_setting( 'submit_form_button_color_text', array(
			'default' => '#000',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'submit_form_button_color_text', array(
			'label' => 'Submit Form Button Color Text: ',
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

		
		// Ticket History
		$wp_customize->add_setting( 'ticket_history_link_color', array(
			'default' => '#1a73e8',
		));

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ticket_history_link_color', array(
			'label' => 'Ticket History Link Color: ',
			'section' => 'dashboard',
			'settings' => 'ticket_history_link_color',
			'priority'  => 4
		
		)));

		// Submit form button text background
		$wp_customize->add_setting( 'tickets_open_text_color', array(
			'default' => '#fff',
		));

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tickets_open_text_color', array(
			'label' => 'Ticket Open: ',
			'section' => 'dashboard',
			'settings' => 'tickets_open_text_color',
			'priority'  => 4
		
		)));


			  	  
	}

    
	public function tickets_customize_admin($wp_customize){
	
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
			'default' => '#ffffff',
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
				'default' => '#ffffff',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'single_ticket_background_color', array(
				'label' => 'Ticket Number Color: ',
				'section' => 'single_ticket',
				'settings' => 'single_ticket_background_color',
				'priority'  => 4
			
			)));


			/**
			 * question box background
			*/
			$wp_customize->add_setting( 'question_box_background_color', array(
				'default' => '#fff',
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
				'default' => '#fff',
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
				'default' => '#fff',
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
				'default' => '#000000',
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
				'default' => '#fff',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'close_ticket_text_color', array(
				'label' => 'Close Ticket Text Color : ',
				'section' => 'single_ticket',
				'settings' => 'close_ticket_text_color',
				'priority'  => 4
			
			)));
			/**
			 * Close Ticket Color Text
			*/
			$wp_customize->add_setting( 'close_ticket_text_color', array(
				'default' => '#fff',
			));
	
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'close_ticket_text_color', array(
				'label' => 'Close Ticket Text Color : ',
				'section' => 'single_ticket',
				'settings' => 'close_ticket_text_color',
				'priority'  => 4
			
			)));

	}


  
}