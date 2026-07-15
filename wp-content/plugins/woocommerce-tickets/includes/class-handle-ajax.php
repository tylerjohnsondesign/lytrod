<?php

class Class_Handle_Ajax {



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($page_title,$page_path) {

			$this->page_title  = $page_title;
			$this->page_path = $page_path;
    
	}

    public function submit_ticket()
    {
        
        $to_admin = get_option('woo_tickets_email');
        $subject_admin = 'Message from ' . $_POST['name'] . ' about ' .$_POST['reason'];
        $body_admin = '
		<body topmargin="0" rightmargin="0" bottommargin="0" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
	background-color: #F0F0F0;
	color: #000000;"
	bgcolor="#F0F0F0"
	text="#000000">

<!-- SECTION / BACKGROUND -->
<!-- Set message background color one again -->
<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%;" class="background"><tr><td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;"
	bgcolor="#F0F0F0">

<!-- WRAPPER -->
<!-- Set wrapper width (twice) -->
<table border="0" cellpadding="0" cellspacing="0" align="center"
	width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
	max-width: 560px;" class="wrapper">

	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 10px;
			padding-bottom: 5px;">

			<!-- PREHEADER -->
			<!-- Set text color to background color -->
			<div style="display: none; visibility: hidden; overflow: hidden; opacity: 0; font-size: 1px; line-height: 1px; height: 0; max-height: 0; max-width: 0;
			color: #F0F0F0;" class="preheader">
				Available on&nbsp;GitHub and&nbsp;CodePen. Highly compatible. Designer friendly. More than 50%&nbsp;of&nbsp;total email opens occurred on&nbsp;a&nbsp;mobile device&nbsp;— a&nbsp;mobile-friendly design is&nbsp;a&nbsp;must for&nbsp;email campaigns.</div>

			<!-- LOGO -->
			<!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2. URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content=logo&utm_campaign={{Campaign-Name}} -->
			<a target="_blank" style="text-decoration: none;"
				href="https://github.com/konsav/email-templates/">
				
				<img border="0" vspace="0" hspace="0"
				src="https://www.lytrod.com/custom_code/brand_assets/assets/img/Lytrod%20Customer%20Success-rgb.png"
				width="250" 
				alt="Logo" title="Logo" style="
				color: #000000;
				font-size: 10px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;" />
			
			</a>

		</td>
	</tr>

<!-- End of WRAPPER -->
</table>

<!-- WRAPPER / CONTEINER -->
<!-- Set conteiner background color -->
<table border="0" cellpadding="0" cellspacing="0" align="center"
	bgcolor="#FFFFFF"
	width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
	max-width: 560px;" class="container">

	<!-- HEADER -->
	<!-- Set text color and font family ("sans-serif" or "Georgia, serif") -->
	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 24px; font-weight: bold; line-height: 130%;
			padding-top: 25px;
			color: #000000;
			font-family: sans-serif;" class="header">
			Thank you for creating a ticket
		</td>
	</tr>
	
	<!-- SUBHEADER -->
	<!-- Set text color and font family ("sans-serif" or "Georgia, serif") -->
	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-bottom: 0px; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 18px; font-weight: 300; line-height: 150%;
			padding-top: 5px;
			color: #000000;
			font-family: sans-serif;" class="subheader">
			We will get back to your shortly
		</td>
	</tr>

	<!-- HERO IMAGE -->
	<!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2 (wrapper x2). Do not set height for flexible images (including "auto"). URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content={{Ìmage-Name}}&utm_campaign={{Campaign-Name}} -->


	<!-- PARAGRAPH -->
	<!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			color: #000000;
			font-family: sans-serif;
			text-align:left;" class="paragraph">
			<span></span>
			<b style="color: #333333;">Name:</b>
				 ' .$_POST['name'].'
		</td>
	</tr>
	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			color: #000000;
			font-family: sans-serif;
			text-align:left;" class="paragraph">
			<span></span>
			<b style="color: #333333;">Email:</b>
			 '. $_POST['email'].'
		</td>
	</tr>
		<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			color: #000000;
			font-family: sans-serif;
			text-align:left;" class="paragraph">
			<span></span>
			<b style="color: #333333;">Reason:</b>
			'. $_POST['reason'] .'
		</td>
	</tr>
		<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			color: #000000;
			font-family: sans-serif;
			text-align:left;" class="paragraph">
			<span></span>
			<b style="color: #333333;">Question:</b>
			'. $_POST['question'].'
		</td>
	</tr>
	<!-- BUTTON -->
	<!-- Set button background color at TD, link/text color at A and TD, font family ("sans-serif" or "Georgia, serif") at TD. For verification codes add "letter-spacing: 5px;". Link format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content={{Button-Name}}&utm_campaign={{Campaign-Name}} -->
		<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 25px;" class="line"><hr
			color="#E0E0E0" align="center" width="100%" size="1" noshade style="margin: 0; padding: 0;" />
		</td>
	</tr>

	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 25px;
			padding-bottom: 5px;" class="button"><a
			href="" target="_blank" style="text-decoration: underline;">
				<table border="0" cellpadding="0" cellspacing="0" align="center" style="max-width: 240px; min-width: 120px; border-collapse: collapse; border-spacing: 0; padding: 0;"><tr><td align="center" valign="middle" style="padding: 12px 24px; margin: 0; text-decoration: underline; border-collapse: collapse; border-spacing: 0; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; -khtml-border-radius: 4px;"
					bgcolor="#0d4d96"><a target="_blank" style="text-decoration: underline;
					color: #FFFFFF; font-family: sans-serif; font-size: 17px; font-weight: 400; line-height: 120%;"
					href="#">
					View Ticket
					</a>
			</td></tr></table></a>
		</td>
	</tr>

	<!-- LINE -->
	<!-- Set line color -->
	<tr>	
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 25px;" class="line"><hr
			color="#E0E0E0" align="center" width="100%" size="1" noshade style="margin: 0; padding: 0;" />
		</td>
	</tr>

	<!-- LIST -->
	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%;" class="list-item"><table align="center" border="0" cellspacing="0" cellpadding="0" style="width: inherit; margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">
			




</table>

		</td>
	</tr>



<!-- End of WRAPPER -->
</table>

<!-- End of SECTION / BACKGROUND -->
</td></tr>
	
	
</table>

</body>';

        $to_user = $_POST['email'];
        $subject_user = 'Thank you we have recieved your ticket about ' .$_POST['reason'];
        $body_user = 'Name: ' . $_POST['name'] .'<br>'.
                'Email: ' . $_POST['email']. '<br>'.
                'Reason: ' .$_POST['reason'] . '<br>'. 
                'Question: ' .$_POST['question'] . '<br>';

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $date = new DateTime();
       
                $my_post = array(
                'post_type' => 'tickets_woocommerce',
                'post_title'    => $_POST['name'],
                'post_status'   => 'publish',
                'meta_input'   =>array(
                        'name'=> $_POST['name'],
                        'email'=> $_POST['email'],
                        'question'=> $_POST['question'],
                        'question_time' => $date->getTimestamp(),
                        'question_file' => '' . json_encode($_POST['question_file'],true) .'',
                        'serial' =>$_POST['ticket_serial_number'],
                        'reason'=>$_POST['reason'],
                        'status'=>'Open'
                        
                )
            );

        
                
              
        return array(
                wp_insert_post( $my_post ),
                wp_mail( $to_admin, $subject_admin, $body_admin, $headers ),
                wp_mail( $to_user, $subject_user, $body_user, $headers ),
                
        );


    }
	public function cvf_upload_files()
	{
        //question
        $question_json = $_POST['question'];
        //reason
        $reason_json = $_POST['reason'];
        //email
        $email_json = $_POST['email'];
        //name
        $name_json = $_POST['name'];
        //reason
        $reason_json = $_POST['reason'];

        $file_json = $_POST['file'];
        $date = new DateTime();
            $my_post = array(
                'post_type' => 'tickets_woocommerce',
                'post_title'    =>  $name_json,
                'post_status'   => 'publish',
                'meta_input'   =>array(
                        'name'=>  $name_json,
                        'email'=>  $email_json,
                        'reason'=> $reason_json,
                        'question'=> $question_json,
                        'question_time' => $date->getTimestamp(),
                        'file' => json_encode($file_json,true)
                        
                )
            );
            
            // Insert the post into the database
          $finalId =  wp_insert_post( $my_post );
        //   print_r($finalId);

      
   
	}
	
	
	public function cvf_delete_files()
	{

		$id  = $_POST['id'];
		$attach_image_id = $_POST['attach_id'];
		wp_delete_attachment( $attach_image_id, true );
		wp_delete_post($id,true);



	}

	public function cvf_update_all_files()
	{
		$finalArray = array(
	
			'post_id'=>explode(',',$_POST['post_id_array']),
			
		);
	
		for ($i=0; $i <count($finalArray['post_id']); $i++) { 
			# code...
			
			$my_post = array(
				'post_type'=>'note',
				'ID' =>$finalArray['post_id'][$i],
				'post_status'   => 'publish',
				'meta_input'=>array(
					'currentIndex' => $i,
					
				)
			);
			wp_update_post($my_post);
		}
	
		 
		
	}

	public function cvf_delete_all_files()
	{
		$finalArray = array(
			'post_id'=>explode(',',$_POST['post_id_array']),
		);
		for ($i=0; $i <count(explode(',',$_POST['title_array'])); $i++) { 
	
			wp_delete_post( $finalArray['post_id'][$i]);
		}

		
	}

    public function cvf_update_tickets()
    {

  

        function cvf_td_generate_random_code($length=10) {

            $string = '';
            $characters = "23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz";
          
            for ($p = 0; $p < $length; $p++) {
                $string .= $characters[mt_rand(0, strlen($characters)-1)];
            }
          
            return $string;
          
         }
        $parent_post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0;  // The parent ID of our attachments
        $valid_formats = array("jpg", "png", "gif", "bmp", "jpeg","pdf"); // Supported file types
        $max_file_size = 1024 * 2000; // in kb
        $max_image_upload = 200; // Define how many images can be uploaded to the current post
        $wp_upload_dir = wp_upload_dir();
        $path = $wp_upload_dir['path'] . '/';
        $count = 0;
    
            //question
            $question_json = $_POST['question'];
            //reason
            $reason_json = $_POST['reason'];
            //email
            $email_json = $_POST['email'];
            //name
            $name_json = $_POST['name'];
            //reason
            $reason_json = $_POST['reason'];

            //Add To Ticket

            $post_id = $_POST['postId'];

            $meta_val = $_POST['metaVal'];

            $meta_key = $_POST['metaKey'];
            
            $time_stamp_key = $_POST['timeStampKey'];
    
        $attachments = get_posts( array(
            'post_type'         => 'attachment',
            'posts_per_page'    => -1,
            'post_parent'       => $parent_post_id,
            'exclude'           => get_post_thumbnail_id() // Exclude post thumbnail to the attachment count
        ) );
    
    
    
        // Image upload handler
        if( $_SERVER['REQUEST_METHOD'] == "POST" ){
           
            // Check if user is trying to upload more than the allowed number of images for the current post
            if( ( count( $attachments ) + count( $_FILES['files']['name'] ) ) > $max_image_upload ) {
                $upload_message[] = "Sorry you can only upload " . $max_image_upload . " images for each Ad";
            } else {
                $results = array(
                    'image' => array(),
                    'attach_id'=>array()
                );
    
                foreach ( $_FILES['files']['name'] as $f => $name ) {
                    $extension = pathinfo( $name, PATHINFO_EXTENSION );
                    // Generate a randon code for each file name
                    $new_filename = cvf_td_generate_random_code( 20 )  . '.' . $extension;
                   
                    if ( $_FILES['files']['error'][$f] == 4 ) {
                        continue;
                    }
                   
                    if ( $_FILES['files']['error'][$f] == 0 ) {
                        // Check if image size is larger than the allowed file size
                        if ( $_FILES['files']['size'][$f] > $max_file_size ) {
                            $upload_message[] = "$name is too large!.";
                            continue;
                       
                        // Check if the file being uploaded is in the allowed file types
                        } elseif( ! in_array( strtolower( $extension ), $valid_formats ) ){
                            $upload_message[] = "$name is not a valid format";
                            continue;
                       
                        } else{
                            // If no errors, upload the file...
                            if( move_uploaded_file( $_FILES["files"]["tmp_name"][$f], $path.$new_filename ) ) {
                               
                                $count++;
    
                                $filename = $path.$new_filename;
                                $filetype = wp_check_filetype( basename( $filename ), null );
                                $wp_upload_dir = wp_upload_dir();
                                $attachment = array(
                                    'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
                                    'post_mime_type' => $filetype['type'],
                                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                                );
                                // Insert attachment to the database
                                $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
    
                                require_once( ABSPATH . 'wp-admin/includes/image.php' );
								require_once(ABSPATH . 'wp-admin/includes/file.php');
								require_once(ABSPATH . 'wp-admin/includes/media.php');
                               
                                // Generate meta data
                                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                                wp_update_attachment_metadata( $attach_id, $attach_data );
                             
                                array_push( $results['image'] , $wp_upload_dir['url'] . '/' . basename( $filename ));
                                array_push( $results['attach_id'] , $attach_id);
                       
                 
                     
                               
                            }
                        }
                    }
                }
                $date = new DateTime();


                // $my_post = array(
                //     'post_type' => 'tickets_woocommerce',
                //     'post_title'    =>  $name_json,
                //     'post_status'   => 'publish',
                //     'meta_input'   =>array(
                //             'name'=>  $name_json,
                //             'email'=>  $email_json,
                //             'reason'=> $reason_json,
                //             'question'=> $question_json,
                //             'question_time' => $date->getTimestamp(),
                //             'file' => json_encode($results,true)
                            
                //     )
                // );
                
                // // Insert the post into the database
                // wp_insert_post( $my_post );
                
    
         
    
            }
        }
        $post_id = $_POST['postId'];

        $meta_val = $_POST['metaVal'];

        $meta_key = $_POST['metaKey'];
        
        $time_stamp_key = $_POST['timeStampKey'];

        $to_admin = get_option('woo_tickets_email');
        // $to_admin = 'alex.joe.lytle@gmail.com';
        $subject_admin = 'Message added to ticket from ' . wp_get_current_user()->user_login;
        $body_admin = 'Question Added: ' . $meta_val . '<br> To respond to ticket please click here
        <a href =' . site_url('ticketadmin') .' > Tickets</a><br> The password is tickets2904.
        '; 

        $to_user = get_post_meta($post_id, 'email',true );
        $subject_user = 'A Message Has been added to your ticket ';
        $body_user = 'Added: ' . $meta_val .' <br>to reply please go to your <a href ="https://lytrod.com/my-acount">account</a>';
     
        $headers = array('Content-Type: text/html; charset=UTF-8');
        // if(count(get_post_meta( $post_id,'question',false))<=20){
               

        if(get_post_meta($post_id,'stop_email',true) == 'true'){
                $date = new DateTime();

                if($time_stamp_key =='answer_time'){
                        return array(
                                'answer_time',
                                add_post_meta( $post_id, $meta_key, $meta_val,false),
                                add_post_meta( $post_id, $time_stamp_key, $date->getTimestamp(),false),
                              );
                }else{
                        return array(
                                'question_time',
                                add_post_meta( $post_id, $meta_key, $meta_val,false),
                                add_post_meta( $post_id, $time_stamp_key, $date->getTimestamp(),false)
                              );
                }
               
        }else{
                $date = new DateTime();

                if($time_stamp_key =='answer_time'){
                        return array(
                                'answer_time',
                                // wp_mail( $to_admin, $subject_admin, $body_admin, $headers ),
                                // wp_mail( $to_user, $subject_user, $body_user, $headers ),
                                add_post_meta( $post_id, $meta_key, $meta_val,false),
                                add_post_meta( $post_id, $time_stamp_key, $date->getTimestamp(),false),
                                add_post_meta( $post_id, 'file', json_encode($results,true),false),
                                );
                }else{
                        return array(
                               'question_time',
                                // wp_mail( $to_admin, $subject_admin, $body_admin, $headers ),
                                // wp_mail( $to_user, $subject_user, $body_user, $headers ),
                                add_post_meta( $post_id, $meta_key, $meta_val,false),
                                add_post_meta( $post_id, $time_stamp_key, $date->getTimestamp(),false),
                                add_post_meta( $post_id, 'file', json_encode($results,true),false),
                                ); 
                }

        }
    }


	

 


}
