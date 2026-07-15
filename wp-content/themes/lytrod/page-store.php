<?php
 get_header();
?>

    <div class="d-xl-flex hero-1 align-items-center">
        <div class="containery main">
            <div class="rowy d-xl-flex row-0">
                <div class="coly-12">
                    <p style="margin-top: 55px;" class="store-header">
                        To purchase select from the product options below<br>  
                        For license subscription renewals select the <a style = 'color:#519bd3' href = "<?php echo site_url('/my-account'); ?>"> Subscriptions Tab</a>
                  </p>
                    <!-- <ul class="bullets">
                        <li>Custom VRCut templates</li>
                        <li>Renew your license&nbsp;</li>
                        <li>Order new licenses&nbsp;</li>
                    </ul> -->
                </div>
            </div>
        </div>
    </div>
    <div class="containery storeproductcontainer">
        
    <!-- <h1 class="storeheader">New Licenses</h1> -->
    <?php
                                $args = array(
                                    'post_type' => 'product',
                                    'product_cat'=>'New License',
                                    'orderby'        => 'menu_order',
                                    'order'       => 'ASC',
                                    );
                                $loop = new WP_Query( $args );
                                if ( $loop->have_posts() ) {
                                   ?> 
                                   <div class="rowy">    
                                       <?php
                                            while ( $loop->have_posts() ) : $loop->the_post();
                                        ?>
                                              <div class="coly-sm-8 coly-md-8 coly-lg-4 coly-xl-<?php echo get_field( 'product_box' );?>">
                                                    <div class="box-1">
                                                    <a class = "center" href="<?php echo get_permalink( $loop->post->ID );?>">
                                                                <?php $product = wc_get_product( get_the_ID() ); ?>
                                                                <div style = "height:auto;">
                                                                        <?php 
                                                                             the_post_thumbnail(); 
                                                                             ?>  
                                                                    </div>
                                                                <p class="producttext">  <?php echo get_field( 'product_description' );?></p>
                                                        </a>  
                                                        <p ><?php echo $product->get_price_html(); ?></p>
                                                        <?php if(is_user_logged_in()):?>
                                                            <a class = 'add-to-cart' href="<?php echo get_permalink( $loop->post->ID ) ?>">    
                                                            Add to cart
                                                                                                                     
                                                            </a>        
                                                        <?php else: ?>
                                                            <a class = 'add-to-cart' href="<?php echo site_url() . '/my-account/' .'?add_from_cart=' . str_replace(' ','',strip_tags($product->get_name())) ?>">    
                                                            Add to cart
                                                                                                                   
                                                            </a>  
                                                        <?php endif ?>
                                                    </div>
                                             
                                                    
                                                    </div>
                                             
                                              
                                            
                                            <?php endwhile;?>
                                            </div>
                                   
                                    <?php
                                } else {
                                    echo __( 'No products found' );
                                }
                                wp_reset_postdata();
                            ?>
  
            </div>
      
 




<?php
 get_footer();
?>
