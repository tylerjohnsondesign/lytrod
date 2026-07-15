<?php

class Woocommerce_Tickets_Json_Routes {



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

    public function submit_ticket_question()
    {
        # code...

        register_rest_route('form/v1', 'submitQuestion', array(
                array(
                        'methods' => 'POST',
                        'callback' =>array($this,'submitQuestion')
                ),
        ));  
register_rest_route('form/v1', 'submitQuestionEmail', array(
        array(
                'methods' => 'POST',
                'callback' =>array($this,'submitQuestionEmail')
        ),
));  
  register_rest_route('form/v1', 'submitQuestion', array(
            array(
                    'methods' => 'GET',
                    'callback' => array($this,'submitQuestionGet')
            ),
    ));  

    register_rest_route('form/v1', 'addQuestion', array(
            array(
                    'methods' => 'POST',
                    'callback' => array($this,'addQuestion')
            ),
    ));  

    register_rest_route('form/v1', 'updateQuestion', array(
            array(
                    'methods' => 'POST',
                    'callback' => array($this,'updateQuestion')
            ),
    )); 
    register_rest_route('form/v1', 'deleteQuestion', array(
            array(
                    'methods' => 'POST',
                    'callback' => array($this,'deleteQuestion')
            ),
    )); 
    register_rest_route('form/v1', 'closeQuestion', array(
            array(
                    'methods' => 'POST',
                    'callback' => array($this,'closeQuestion')
            ),
    ));
    register_rest_route('form/v1', 'stopEmail', array(
            array(
                    'methods' => 'POST',
                    'callback' => array($this,'stopEmail')
            ),
    ));
    register_rest_route('form/v1', 'restartEmail', array(
            array(
                    'methods' => 'POST',
                    'callback' =>array($this,'restartEmail') 
            ),
    ));

//     add_acton('wp_ajax_cvf_upload_files',array($this,'cvf_upload_files'));

    }



public function submitQuestion($data){



        $date = new DateTime();
       
                $my_post = array(
                'post_type' => 'tickets_woocommerce',
                'post_title'    => $data['name'],
                'post_status'   => 'publish',
                'meta_input'   =>array(
                        'name'=> $data['name'],
                        'email'=> $data['email'],
                        'question'=> $data['question'],
                        'question_time' => $date->getTimestamp(),
                        'question_file' => json_encode($data['question_file'],true),
                        'serial' =>$data['ticket_serial_number'],
                        'reason'=>$data['reason'],
                        'status'=>'Open'
                        
                )
            );

        
        //     wp_insert_post( $my_post )
              
        return array(
                wp_insert_post( $my_post )
        );
           
}

public function submitQuestionEmail($data)
{  
        
        $to_admin = get_option('woo_tickets_email');
        $subject_admin = 'Message from ' . $data['name'] . ' about ' .$data['reason'];
        $body_user = '
		<body topmargin="50px" rightmargin="0" bottommargin="50px" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
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
                        Lytrod Software Tickets 	
                        </div>

			<!-- LOGO -->
			<!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2. URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content=logo&utm_campaign={{Campaign-Name}} -->
			
				
				<img border="0" vspace="0" hspace="0"
				src="https://www.lytrod.com/custom_code/brand_assets/assets/img/Lytrod%20Customer%20Success-rgb.png"
				width="250" 
				alt="Logo" title="Logo" style="
				color: #000000;
				font-size: 10px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;" />
			
			

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
			<b style="color: #333333;">Name:</b>
				 ' .$data['name'].'
		</td>
	</tr>
	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			color: #000000;
			font-family: sans-serif;
			text-align:left;" class="paragraph">
			<b style="color: #333333;">Email:</b>
			 '. $data['email'].'
		</td>
	</tr>
		<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			color: #000000;
			font-family: sans-serif;
			text-align:left;" class="paragraph">
			<b style="color: #333333;">Product:</b>
			'. $data['reason'] .'
		</td>
	</tr>
	<tr>
		<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			color: #000000;
			font-family: sans-serif;
			text-align:left;" class="paragraph">
			<b style="color: #333333;">Question:</b>
			'. $data['question'].'
		</td>
	</tr>

        <tr>
                <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
                        color: #000000;
                        font-family: sans-serif;
                        text-align:left;" class="paragraph">
                        <b style="color: #333333;">Product ID:</b>
                        '. $data['ticket_serial_number'].'
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
					href='.site_url() .'/singleticket/' . $data['ticket_id'] .'>
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


$body_admin = '
<body topmargin="50px" rightmargin="0" bottommargin="50px" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
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
                        Lytrod Software Tickets   
        </div>

        <!-- LOGO -->
        <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2. URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content=logo&utm_campaign={{Campaign-Name}} -->
   
                
                <img border="0" vspace="0" hspace="0"
                src="https://www.lytrod.com/custom_code/brand_assets/assets/img/Lytrod%20Customer%20Success-rgb.png"
                width="250" 
                alt="Logo" title="Logo" style="
                color: #000000;
                font-size: 10px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;" />
        
       

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
        '.$data['name'].' just submitted a ticket
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
        <b style="color: #333333;">Name:</b>
                 ' .$data['name'].'
</td>
</tr>
<tr>
<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
        color: #000000;
        font-family: sans-serif;
        text-align:left;" class="paragraph">
        <b style="color: #333333;">Email:</b>
         '. $data['email'].'
</td>
</tr>
<tr>
<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
        color: #000000;
        font-family: sans-serif;
        text-align:left;" class="paragraph">
        <b style="color: #333333;">Product:</b>
        '. $data['reason'] .'
</td>
</tr>
<tr>
<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
        color: #000000;
        font-family: sans-serif;
        text-align:left;" class="paragraph">
        <b style="color: #333333;">Question:</b>
        '. $data['question'].'
</td>
</tr>
<tr>
<td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
        color: #000000;
        font-family: sans-serif;
        text-align:left;" class="paragraph">
        <b style="color: #333333;">Product ID:</b>
        '. $data['ticket_serial_number'].'
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
                        href='.site_url() .'/singleticket/' . $data['ticket_id'] .'>
                        View Ticket
                        </a>
        </td></tr></table></a>
        Email: support@lytrod.com <br>
        Password: honeywell12341234
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

        $to_user = $data['email'];
        // $subject_user = 'Thank you we have recieved your ticket about ' .$data['reason'];
        // $body_user = 'Name: ' . $data['name'] .'<br>'.
        //         'Email: ' . $data['email']. '<br>'.
        //         'Reason: ' .$data['reason'] . '<br>'. 
        //         'Question: ' .$data['question'] . '<br>';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        return array(
                wp_mail( $to_admin, $subject_admin, $body_admin, $headers ),
                wp_mail( $to_user, $subject_admin, $body_user, $headers ),
        ); 
}

public function submitQuestionGet($data){

          
        $mainQuery = new WP_Query(array(
                'author' => get_current_user_id(),
                'post_type' => 'tickets_woocommerce',
                'p' => sanitize_text_field($data['term'])
              ));
        //      $finalTime =  wp_date( get_option( 'date_format' ), get_post_timestamp(get_the_id()) ).' ' .  wp_date( get_option( 'time_format' ), get_post_timestamp(get_the_id()) );


              $submitQuestionResults = array(
                'ticket'=>array()
                        );   
            
              while($mainQuery->have_posts()) {
                $mainQuery->the_post();
            
                 array_push($submitQuestionResults['ticket'],array(
                    'title' => get_the_title(),
                    'id'=>get_the_id(),
                    'question_file'=>json_encode(get_post_meta(get_the_id(),'question_file',false)),
                    'question'=>get_post_meta(get_the_id(),'question',false),
                    'answer_file'=>json_encode(get_post_meta(get_the_id(),'answer_file',false)),
                    'answer'=>get_post_meta(get_the_id(),'answer',false),
                    'question_time' => get_post_meta(get_the_id(),'question_time',false),
                    'answer_time' => get_post_meta(get_the_id(),'answer_time',false),
                    'name' => get_post_meta(get_the_id(),'name',true),
                    'serial' => get_post_meta(get_the_id(),'serial',true),
                    'reason' => get_post_meta(get_the_id(),'reason',true)
                  ));
                
              }
              return $submitQuestionResults;
      

}

public function addQuestion($data){

        $headers = array('Content-Type: text/html; charset=UTF-8');



        $to_admin = get_option('woo_tickets_email');
        $subject_admin = 'Message added to ticket from ' . wp_get_current_user()->user_login;
    


        $body_admin = '
        <body topmargin="50px" rightmargin="0" bottommargin="50px" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
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
                                Lytrod Software Tickets   
                </div>
        
                <!-- LOGO -->
                <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2. URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content=logo&utm_campaign={{Campaign-Name}} -->
           
                        
                        <img border="0" vspace="0" hspace="0"
                        src="https://www.lytrod.com/custom_code/brand_assets/assets/img/Lytrod%20Customer%20Success-rgb.png"
                        width="250" 
                        alt="Logo" title="Logo" style="
                        color: #000000;
                        font-size: 10px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;" />
                
               
        
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
               New response from '.wp_get_current_user()->user_login.'
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
                <b style="color: #333333;"Question:</b>
                         ' . $data['metaValQuestionAnswer'] .'
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
                                href='.site_url() .'/singleticket/' . $data['postId'] .'>
                                View Ticket
                                </a>
                </td></tr></table></a>
                Email: support@lytrod.com <br>
                Password: honeywell12341234
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
        //-----------Email sent to user------------------

        $to_user = get_post_meta($data['postId'], 'email',true );
        $subject_user = 'A Message Has been added to your ticket ';
        $body_user = 'Added: ' . $data['metaValQuestionAnswer'] .' <br>to reply please go to your <a href ="https://lytrod.com/my-acount">account</a>';
     


        $body_user = '
        <body topmargin="50px" rightmargin="0" bottommargin="50px" leftmargin="0" marginwidth="0" marginheight="0" width="100%" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
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
                                Lytrod Software Tickets   
                </div>
        
                <!-- LOGO -->
                <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2. URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content=logo&utm_campaign={{Campaign-Name}} -->
           
                        
                        <img border="0" vspace="0" hspace="0"
                        src="https://www.lytrod.com/custom_code/brand_assets/assets/img/Lytrod%20Customer%20Success-rgb.png"
                        width="250" 
                        alt="Logo" title="Logo" style="
                        color: #000000;
                        font-size: 10px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;" />
                
               
        
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
               New response from Lytrod
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
                <b style="color: #333333;">Answer:</b>
                         ' .$data['metaValQuestionAnswer'].'
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
                                href='.site_url() .'/singleticket/' . $data['postId'] .'>
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
        

               

        if(get_post_meta($data['postId'],'stop_email',true) == 'true'){
                $date = new DateTime();

                if($data['timeStampKeyQuestionAnswer'] =='answer_time'){
                        return array(
                                'answer_time',
                                add_post_meta( $data['postId'], $data['metaKeyQuestionAnswer'], $data['metaValQuestionAnswer'],false),
                                add_post_meta( $data['postId'], $data['timeStampKeyQuestionAnswer'], $date->getTimestamp(),false),
                                add_post_meta( $data['postId'], $data['fileKeyQuestionAnswer'], json_encode($data['fileValQuestionAnswer'],true),false),
                              );
                }else{
                        return array(
                                'question_time',
                                add_post_meta( $data['postId'], $data['metaKeyQuestionAnswer'], $data['metaValQuestionAnswer'],false),
                                add_post_meta( $data['postId'], $data['timeStampKeyQuestionAnswer'], $date->getTimestamp(),false),
                                add_post_meta( $data['postId'], $data['fileKeyQuestionAnswer'], json_encode($data['fileValQuestionAnswer'],true) ,false),
                              );
                }
               
        }else{
                $date = new DateTime();

                if($data['timeStampKeyQuestionAnswer'] =='answer_time'){
                        return array(
                                'answer_time',
                                wp_mail( $to_admin, $subject_admin, $body_admin, $headers ),
                                wp_mail( $to_user, $subject_user, $body_user, $headers ),
                                add_post_meta( $data['postId'], $data['metaKeyQuestionAnswer'], $data['metaValQuestionAnswer'],false),
                                add_post_meta( $data['postId'], $data['timeStampKeyQuestionAnswer'], $date->getTimestamp(),false),
                                add_post_meta( $data['postId'], $data['fileKeyQuestionAnswer'], json_encode($data['fileValQuestionAnswer'],true) ,false),
                                );
                }else{
                        return array(
                               'question_time',
                                wp_mail( $to_admin, $subject_admin, $body_admin, $headers ),
                                wp_mail( $to_user, $subject_user, $body_user, $headers ),
                                add_post_meta( $data['postId'], $data['metaKeyQuestionAnswer'], $data['metaValQuestionAnswer'],false),
                                add_post_meta( $data['postId'], $data['timeStampKeyQuestionAnswer'], $date->getTimestamp(),false),
                                add_post_meta( $data['postId'], $data['fileKeyQuestionAnswer'], json_encode($data['fileValQuestionAnswer'],true) ,false),
                                ); 
                                // add_post_meta( $data['postId'], 'file', json_encode($data['file'],true),false),
                }

        }

      
}

public function updateQuestion($data){
        $to_admin = get_option('woo_tickets_email');
        $subject_admin = 'Ticket Updated from '. wp_get_current_user()->user_login;
        $body_admin = 'New Ticket: ' . $data['newValue'] . ' Old Ticket: ' .$data['oldValue'];

        $to_user = wp_get_current_user()->user_email;
        // $to_user = 'alex.joe.lytle@gmail.com';
        $subject_user = 'Your Ticket Has Been Updated';
        $body_user = 'Ticket Updated From: ' . $data['oldValue'] . '<br> To: ' .$data['newValue'];
     
        $headers = array('Content-Type: text/html; charset=UTF-8');
      return array(
                   wp_mail( $to_admin, $subject_admin, $body_admin, $headers ),
                   wp_mail( $to_user, $subject_user, $body_user, $headers ),
                   update_post_meta($data['postId'], $data['keyVal'],$data['newValue'],$data['oldValue']),
                   
                );
}

public function deleteQuestion($data){
     return delete_post_meta( $data['postId'], $data['metaKey'], $data['metaVal']);
}

public function closeQuestion($data){
        return update_post_meta( $data['postId'], 'status', 'Close');
}
public function stopEmail($data){
        return add_post_meta( $data['postId'], 'stop_email', 'true');
}
public function restartEmail($data){
        return delete_post_meta($data['postId'], 'stop_email');
}






  



}
