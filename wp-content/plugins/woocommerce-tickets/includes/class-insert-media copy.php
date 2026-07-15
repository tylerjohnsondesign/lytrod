<?php

class Class_Insert_Media {



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
	public function cvf_upload_files()
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
        $valid_formats = array("jpg", "png", "gif", "bmp", "jpeg"); // Supported file types
        $max_file_size = 1024 * 1000; // in kb
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


                
    
         
    
            }
        }
        // Loop through each error then output it to the screen
        // if ( isset( $upload_message ) ) :
        //     foreach ( $upload_message as $msg ){       
        //         printf( __('<p class="bg-danger">%s</p>', 'wp-trade'), $msg );
        //     }
        // endif;
       
        // If no error, show success message
        if( $count != 0 ){
            // printf( __('<p class = "bg-success">%d files added successfully!</p>', 'wp-trade'), $count ); 

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
                        'file' => json_encode($results,true)
                        
                )
            );
            
            // Insert the post into the database
           $dataPost =  wp_insert_post( $my_post );

           print_r($dataPost);
         
        }else{
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
                        'file' => json_encode($results,true)
                        
                )
            );
            
            // Insert the post into the database
           $dataPost =  wp_insert_post( $my_post );

           print_r($dataPost);


        }
    
        
       
        exit();
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
