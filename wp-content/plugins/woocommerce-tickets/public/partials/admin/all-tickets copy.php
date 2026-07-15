<?php get_header();?>
<header class="entry-header"></header>
<?php 
  //CONTROLLER

  $status =  get_query_var('status');
  $value =   get_query_var('value');

  //all

if($status == '' ){
  $queryName =new WP_Query(array(
    'post_type'=>'tickets_woocommerce',
    'posts_per_page' => -1,
      )
  );
}

  //open

 if($status == 'ticket_status' && $value == 'open' ){
        $queryName =new WP_Query(
                array( 
                'post_type'=>'tickets_woocommerce',
                'posts_per_page' => -1,
                'meta_query'=> array(
                    'relation' => 'OR',
                    array(
                        'key'=>'close_ticket',
                        'compare'=>'NOT EXISTS'
                    ),
                    array(
                        'key'=>'close_ticket',
                        'value'=>'false',
                        'compare'=>'='
                    ),
                )
            )
        );
    }

//close

if($status == 'ticket_status' && $value == 'close' ){
    $queryName =new WP_Query(
            array( 
            'post_type'=>'tickets_woocommerce',
            'posts_per_page' => -1,
            'meta_query'=> array(
                'relation' => 'OR',
                array(
                    'key'=>'close_ticket',
                    'value'=>'true',
                    'compare'=>'='
                ),
            )
        )
    );
    
}


//reason

if($status == 'reason' ){
    $queryName =new WP_Query(
            array( 
            'post_type'=>'tickets_woocommerce',
            'posts_per_page' => -1,
            'meta_query'=> array(
                'relation' => 'OR',
                array(
                    'key'=>$status,
                    'value'=>$value ,
                    'compare'=>'='
                ),
            )
        )
    );
}







//END
$current_user = wp_get_current_user();
if (is_user_logged_in()) {

        $allowed_roles = get_option('defaultRoles');
  if (array_intersect($allowed_roles, $current_user->roles ) ) {
            ?>
     
        <div class="ticketwrapper" style="background-color: <?php echo get_theme_mod('dashboard_admin_background_color')?>;">
    
                <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                    <h1 class="ticket_header" style="color: <?php echo get_theme_mod('dashboard_admin_heading_color');?>;">
                                        <?php 
                                        // echo get_theme_mod('create_heading');
                                        ?>
                                        Admin Tickets
                                    </h1>
                                    </div>
                                    <div class="col-12">
                                       <?php 
                                         $stats = array(
                                            'closed' =>array()
                                        );
                              
                                
                                        //   die();
                                       ?>
                                        <table class="table">
                                        <thead>
                                            <tr>
                                            
                                            <th scope="col">Ticket Number</th>
                                            <th scope="col">Ticket Author</th>
                                            <th scope="col">Ticket Status</th>
                                            <th scope="col">Ticket Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                foreach ($queryName->posts as $value) {
                                            ?>
                                            <tr>
                                            
                                                <td> 
                                                    <a href="<?php echo site_url() .'/singleticket/' . $value->ID; ?>"> 
                                                        <?php echo $value->ID;?>
                                                    </a>
                                                </td>
                                                <td><?php echo get_post_meta( $value->ID, 'name')[0]?></td>
                                       
                                                <td><?php echo (get_post_meta( $value->ID, 'close_ticket',true  == "true"))?'closed':'open'; ?></td>
                                                <td><?php echo get_post_meta( $value->ID, 'reason')[0]?></td>
                                                <td></td>

                                            </tr>
                                        <?php }?>
                                        </tbody>
                                        </table>
                                     
                              
                                       <h3 style="color:<?php echo get_theme_mod('dashboard_status_color') ?>;">You have <?php echo count($stats['closed']);?> out of <?php  echo $queryName->post_count;?> ticket(s) closed</h3>
                                    </div>
                                    
                                </div>
                        </div>
        </div>
   
            <?php
        }else{
          ?>
	
                <div class="container" style="min-height: 75vh;">
                    <div class="row">
                        <div class="col-12" style="margin-top: 20px; text-align:center;">
                               Sorry you dont have the right permission to view this page.
                                <a href="<?php echo site_url()?>/wp-admin">
                                        Login here
                                </a>
                        </div>
                    </div>
                </div>
          <?php
            
        }

    }else{
        echo 'You must be logged in';
    }
            ?>


     

 

<?php get_footer();?>