<?php get_header();?>
<header class="entry-header"></header>
 <?php 
    //controller

    $pair_one = get_query_var('ticket_one');

    $pair_two = get_query_var('ticket_two');

    $pair_three = get_query_var('ticket_three');

    
    $key_one = explode('-', $pair_one)[0];
    $value_one =  str_replace('%20', ' ',  explode('-', $pair_one)[1]);

    $key_two = explode('-', $pair_two)[0];
    $value_two = str_replace('%20', ' ',  explode('-', $pair_two)[1]);

    $key_three = explode('-', $pair_three)[0];
    $value_three = str_replace('%20', ' ',  explode('-', $pair_three)[1]);

    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

    $ticketVars = array( $key_one ,  $key_two ,$key_three);




    $idArray  =  array();

    foreach (array($pair_one,$pair_two,$pair_three ) as $pair ){
        # code...
        $idVal = (explode('-', $pair)[0] == 'id')?explode('-', $pair)[1]:'';

        array_push($idArray,$idVal);
    }


    if($idArray[0] !== ''){
        $queryName =new WP_Query(array( 
            'p'=>$idArray[0],
            'post_type'=>'tickets_woocommerce',
            'posts_per_page' => 15,
            'paged' => $paged,
                'meta_query'=> array(
                    'relation' => 'AND',
                    array(
                        'key'=>$key_two,
                        'value'=>$value_two,
                        'compare'=>'='
                    ),
                    array(
                        'key'=>$key_three,
                        'value'=>$value_three,
                        'compare'=>'='
                    ),          
                )
            )
         );
    }else{

        $queryName =new WP_Query(array( 
            // 'p'=>$idArray[0],
            'post_type'=>'tickets_woocommerce',
            'posts_per_page' => 15,
            'paged' => $paged,
                'meta_query'=> array(
                    'relation' => 'AND',
                    array(
                        'key'=>$key_one,
                        'value'=>$value_one,
                        'compare'=>'='
                    ),
                    array(
                        'key'=>$key_two,
                        'value'=>$value_two,
                        'compare'=>'='
                    ),
                    array(
                        'key'=>$key_three,
                        'value'=>$value_three,
                        'compare'=>'='
                    ),          
                )
            )
         );

    }
    







?>


<div class="barba-wrapper" data-barba="wrapper">

            <div class="admin-ticket-wrapper" data-barba="container" data-barba-namespace="home">

            <?php 

            if (is_user_logged_in()) {
            // user is an admin

            $current_user = wp_get_current_user();

            $allowed_roles = get_option('defaultRoles');
            if (array_intersect($allowed_roles, $current_user->roles ) ) {
                        ?>


                        
                <a id="ticket_val" href=""></a>
                    <div class="ticketwrapper" id="admin-tickets-dash">
                
                            <div class="wrapper-dash">
                                    <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="dash-box <?php echo (in_array('id',$ticketVars))?' border-highlight-dash':''?> ">
                                                                <div class="d-flex align-items-center">
                                                                                    <input type="text" v-model="id" :placeholder="placeID">
                                                                                    <i @click="clearField('id')" v-if="id !== '' " class="fas fa-times exit-icon "></i>
                                                                        </div>            
                                                                        <select v-model="id"  >
                                                                            <option disabled value="">Select by Id</option>
                                                                            <option  v-for="id in data.ticket">{{id.id}}</option>
                                                                        </select>

                                                    </div>
                                                    
                                                </div>
                                                <div class="col-md-2">
                                                        <div class="dash-box <?php echo (in_array('name',$ticketVars))?' border-highlight-dash':''?>">
                                                                <div class="d-flex align-items-center">
                                                                        <input type="text" v-model="name" :placeholder="placeName">
                                                                        <i @click="clearField('name')" v-if="name !== '' " class="fas fa-times exit-icon "></i>
                                                                    </div>
                                                                        <select  v-model="name">
                                                                            <option disabled value="">Select by Name</option>
                                                                            <option  v-for="name in nameDups">{{name}}</option>
                                                                        </select>
                                                        </div>
                                                
                                                </div>
                                                <div class="col-md-2">
                                                            <div class="dash-box <?php echo (in_array('status',$ticketVars))?' border-highlight-dash':''?>">
                                                                    <div class="d-flex align-items-center">
                                                                        <input type="text" v-model="status" :placeholder="placeStatus">
                                                                        <i @click="clearField('status')" v-if="status !== '' " class="fas fa-times exit-icon "></i>
                                                                    </div>
                                                                
                                                                        <select v-model="status" >
                                                                                <option disabled value=""> Select by Status</option>
                                                                                <option>Open</option>
                                                                                <option>Close</option>
                                                                        </select>
                                                        
                                                            </div>
                                                            
                                                    </div>
                                                    <div class="col-md-2">
                                                            <div class="dash-box  <?php echo(in_array('reason',$ticketVars))?' border-highlight-dash':''?>">
                                                                    <div class="d-flex align-items-center">
                                                                        <input type="text" v-model="reason" :placeholder="placeProduct">
                                                                        <i @click="clearField('reason')" v-if="reason !== '' " class="fas fa-times exit-icon "></i>
                                                                    </div>
                                                                <select v-model="reason">
                                                                    <option disabled value="">Select by Product</option>
                                                                    <option v-for="reason in reasonDups" >{{reason}}</option>
                                                                    
                                                                </select>
                                                            </div>
                                                

                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="dash-box <?php echo (in_array('serial',$ticketVars))?'border-highlight-dash':''?>">
                                                                <div class="d-flex align-items-center">
                                                                    <input type="text" v-model="serial" :placeholder="placeserial">
                                                                    <i @click="clearField('serial')" v-if="serial !== '' " class="fas fa-times exit-icon "></i>
                                                                </div>
                                                                <select  v-model="serial">
                                                                    <option disabled value="">Select by Serial</option>
                                                                    <option  v-for="serial in data.ticket">{{serial.serial}}</option>
                                                                </select>
                                                        </div>
                                                    
                                                    </div>
                                                                    <div class="col-md-2 dash-box d-flex">
                                                                    
                                                                            <button @click="handleFilter" class="btn-filter">Filter</button>
                                                                    </div>
                                                    </div>
                                                </div>
                                </div>
                    
                                <div class="container ticket-body">
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
                                                                <th scope="col">Ticket Serial</th>
                                                                <th scope="col">Ticket Number</th>
                                                                <th scope="col">Ticket Author</th>
                                                                <th scope="col">Ticket Status</th>
                                                                <th scope="col">Ticket Product</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                    foreach ($queryName->posts as $value) {
                                                                ?>
                                                                <tr>
                                                                <td><?php echo get_post_meta( $value->ID, 'serial')[0]?></td>
                                                                    <td> 
                                                                        <a data-barba-prevent="self" href="<?php echo site_url() .'/singleticket/' . $value->ID; ?>"> 
                                                                            <?php echo $value->ID;?>
                                                                        </a>
                                                                    </td>
                                                                    <td><?php echo get_post_meta( $value->ID, 'name')[0];?></td>
                                                                    <td><?php echo get_post_meta( $value->ID, 'status')[0];?></td>
                                                                    <td><?php echo get_post_meta( $value->ID, 'reason')[0];?></td>
                                                                    

                                                                </tr>


                                                            <?php }?>

                                                    
                                                            </tbody>
                                                            </table>
                                                        
                                                
                                                            <?php
                                                                echo paginate_links(array(
                                                                    'current'=>max(1,get_query_var('paged')),
                                                                    'total'=>$queryName->max_num_pages,
                                                                    // 'type'=>'list', 
                                                                    'format' => '?paged=%#%',
                                                                ));
                                                            
                                                            ?>
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
                    ?>
                      <div class="container" style="min-height: 75vh;">
                            <div class="row">
                                <div class="col-12" style="margin-top: 20px; text-align:center;">
                                    Please login to continue.
	
                                <?php wp_login_form(); ?>
                                </div>
                            </div>
                </div>
                    <?php
                }
                        ?>

            </div>


</div>


 

<?php get_footer();?>