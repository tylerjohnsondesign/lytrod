<?php get_header(); ?>

<?php
  $the_query_intellicut = new WP_Query(array(
    'category_name' => 'intellicut-video-lesson',
    'post_status' => 'publish',
    // 'posts_per_page' => 15,
    'orderby' => 'date' ,
    'order' => 'ASC' ,
    'post_type' => 'video' ,
));

$the_query_aerocut = new WP_Query(array(
  'category_name' => 'aerocut-video-lessons',
  'post_status' => 'publish',
  // 'posts_per_page' => 15,
  'orderby' => 'date' ,
  'order' => 'ASC' ,
  'post_type' => 'video' ,
));

$the_query_vrcut = new WP_Query(array(
  'category_name' => 'vrcut-video-lessons',
  'post_status' => 'publish',
  // 'posts_per_page' => 15,
  'orderby' => 'date' ,
  'order' => 'ASC' ,
  'post_type' => 'video' ,
));

$the_query_triumph = new WP_Query(array(
  'category_name' => 'Triumph-video-lessons',
  'post_status' => 'publish',
  // 'posts_per_page' => 15,
  'orderby' => 'date' ,
  'order' => 'ASC' ,
  'post_type' => 'video' ,
));

$the_query_focus = new WP_Query(array(
  'category_name' => 'Vision-focus-video-lessons',
  'post_status' => 'publish',
  // 'posts_per_page' => 15,
  'orderby' => 'date' ,
  'order' => 'ASC' ,
  'post_type' => 'video' ,
));

$the_query_direct = new WP_Query(array(
  'category_name' => 'Vision-direct-video-lessons',
  'post_status' => 'publish',
  // 'posts_per_page' => 15,
  'orderby' => 'date' ,
  'order' => 'ASC' ,
  'post_type' => 'video' ,
));


$the_query_dp = new WP_Query(array(
  'category_name' => ' VisionDP-video-lessons',
  'post_status' => 'publish',
  // 'posts_per_page' => 15,
  'orderby' => 'date' ,
  'order' => 'ASC' ,
  'post_type' => 'video' ,
));

$the_query_intellicut_global = new WP_Query(array(
  'category_name' => 'intellicut-global-video-lessons',
  'post_status' => 'publish',
  // 'posts_per_page' => 15,
  'orderby' => 'date' ,
  'order' => 'ASC' ,
  'post_type' => 'video' ,
));
?>
 

<!-- ---------------------------------------------------------SUB-MENU------------------------------------------------------------------ -->

<nav id="menu">
        <div id="topnav">
                    <a class="menu-links" data-menuanchor="first" id = "first" href="#Intellicut">INTELLICUT</a>
                    <a class="menu-links" data-menuanchor="second" id = "second"href="#IntellicutGlobal">INTELLICUT GLOBAL</a>
                    <a class="menu-links" data-menuanchor="third"  id = "three" href="#VRCut">VRCUT</a>
                    <a class="menu-links" data-menuanchor="fourth" id = "fourth" href="#Triumph">TRIUMPH</a>
                    <a class="menu-links" data-menuanchor="five" id = "five" href="#VisionFocus">VISION FOCUS</a>
                    <a class="menu-links" data-menuanchor="six" id = "six" href="#VisionDirect">VISION DIRECT</a>
                    <a class="menu-links" data-menuanchor="seven" id = "seven" href="#VisionDP">VISION DP</a>
                    <a class="menu-links" data-menuanchor="eight" id = "eight" href="#Aerocut">AEROCUT</a>
        </div>   
        <div class = "first video-center-hero">
            <div class = "center">
                <p class = "hero-title">Video Gallery</p>
                <p class ="sub-hero">Seeing is believing.</p>
            </div>
          </div> 
            
  
</nav>

<!-- ---------------------------------------------------------------INTELLICUT-------------------------------------- -->
<div id="fullpage">
  <section class="section ">

    <div class="swiper-container container-one">
      <div class="swiper-wrapper">
     
        
     <?php if ($the_query_intellicut->have_posts()) : ?>
      <?php while ($the_query_intellicut->have_posts()) : $the_query_intellicut->the_post(); ?>
          
		<div class="swiper-slide video-center" id=''>
                            <span class = "box1">
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                                 <video  controls poster="<?php the_field('video_poster')?>"width="400px" height="400px"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                         
                              Your browser does not support HTML5 video.
                            </video>
                          </span>
              
        </div>
            
         <?php endwhile; ?>
        </div>
		<?php wp_reset_postdata(); ?>
 
        <?php else : ?>
            <p><?php __('No News'); ?></p>
        <?php endif; ?>
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
   
    
  
</section>
  <!-- ------------------------------------------------------------------------------------------------------------------ -->
   <!-- ----------------------------------------------------IntellicutGlobal----------------------------------------------->

<section class="section">
   <div class="swiper-container">
      <div class="swiper-wrapper ">
          <?php if ($the_query_intellicut_global->have_posts()) : ?>
            <?php while ($the_query_intellicut_global->have_posts()) : $the_query_intellicut_global->the_post(); ?>
                    <div class="swiper-slide video-center">
                          <span class = "box1">
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                            <video  controls poster="<?php the_field('video_poster')?>"width="100%" height="100%"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                              <source src="mov_bbb.ogg" type="video/ogg">
                              Your browser does not support HTML5 video.
                            </video>
                          </span>
                    </div>
                    <?php endwhile; ?>
                  <?php wp_reset_postdata(); ?>
              <?php endif; ?>
        </div>
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
  </section>

  <!-- ------------------------------------------ VRCut --------------------------------------------- -->

<section class=" section ">
   <div class="swiper-container">
      <div class="swiper-wrapper">
          <?php if ($the_query_vrcut->have_posts()) : ?>
            <?php while ($the_query_vrcut->have_posts()) : $the_query_vrcut->the_post(); ?>
                    <div class="swiper-slide video-center">
                   
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                            <video  controls poster="<?php the_field('video_poster')?>"width="100%" height="100%"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                              <source src="mov_bbb.ogg" type="video/ogg">
                              Your browser does not support HTML5 video.
                            </video>
                          
                    </div>
                    <?php endwhile; ?>
                  <?php wp_reset_postdata(); ?>
              <?php endif; ?>
        </div>
		  
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
  </section>

<!-----------------------------------------------------Triumph------------------------------------------------------->
<section class="section">
   <div class="swiper-container">
      <div class="swiper-wrapper">
          <?php if ($the_query_triumph->have_posts()) : ?>
            <?php while ($the_query_triumph->have_posts()) : $the_query_triumph->the_post(); ?>
                    <div class="swiper-slide video-center">
                          
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                            <video  controls poster="<?php the_field('video_poster')?>"width="100%" height="100%"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                              <source src="mov_bbb.ogg" type="video/ogg">
                              Your browser does not support HTML5 video.
                            </video>
                          
                    </div>
                    <?php endwhile; ?>
                  <?php wp_reset_postdata(); ?>
              <?php endif; ?>
        </div>
		  
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
  </section>
  
 <!-- ----------------------------------------------------Vision Focus---------------------------------------------->
 <section class="section">
   <div class="swiper-container">
      <div class="swiper-wrapper">
          <?php if ($the_query_focus->have_posts()) : ?>
            <?php while ($the_query_focus->have_posts()) : $the_query_focus->the_post(); ?>
                    <div class="swiper-slide video-center">
                       
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                            <video  controls poster="<?php the_field('video_poster')?>"width="100%" height="100%"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                              <source src="mov_bbb.ogg" type="video/ogg">
                              Your browser does not support HTML5 video.
                            </video>
                         
                    </div>
                    <?php endwhile; ?>
                  <?php wp_reset_postdata(); ?>
              <?php endif; ?>
        </div>
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
  </section>
    <!-- ------------------------------------------VISION DIRECT--------------------------------------------- -->
<section class="section">
   <div class="swiper-container">
      <div class="swiper-wrapper">
          <?php if ($the_query_direct->have_posts()) : ?>
            <?php while ($the_query_direct->have_posts()) : $the_query_direct->the_post(); ?>
                    <div class="swiper-slide video-center">
                          <span class = "box1">
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                            <video  controls poster="<?php the_field('video_poster')?>"width="100%" height="100%"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                              <source src="mov_bbb.ogg" type="video/ogg">
                              Your browser does not support HTML5 video.
                            </video>
                          </span>
                    </div>
                    <?php endwhile; ?>
                  <?php wp_reset_postdata(); ?>
              <?php endif; ?>
        </div>
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
  </section>
  <!-- ----------------------------------------------------Vision DP----------------------------------------------->

<section class="section">
   <div class="swiper-container">
      <div class="swiper-wrapper ">
          <?php if ($the_query_dp->have_posts()) : ?>
            <?php while ($the_query_dp->have_posts()) : $the_query_dp->the_post(); ?>
                    <div class="swiper-slide video-center">
                          <span class = "box1">
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                            <video  controls poster="<?php the_field('video_poster')?>"width="100%" height="100%"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                              <source src="mov_bbb.ogg" type="video/ogg">
                              Your browser does not support HTML5 video.
                            </video>
                          </span>
                    </div>
                    <?php endwhile; ?>
                  <?php wp_reset_postdata(); ?>
              <?php endif; ?>
        </div>
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
  </section>

<!-- -------------------------------------------------------------------------------------------------------------->
<!-----------------------------------------------------Aerocut----------------------------------------------------- -->

<section class=" section ">
   <div class="swiper-container">
      <div class="swiper-wrapper">
          <?php if ($the_query_aerocut->have_posts()) : ?>
            <?php while ($the_query_aerocut->have_posts()) : $the_query_aerocut->the_post(); ?>
                    <div class="swiper-slide video-center">
                        
                            <h1 class = "video-title"> <?php the_title(); ?></h1>
                            <video  controls poster="<?php the_field('video_poster')?>"width="100%" height="100%"controls>
                              <source src="<?php the_field('video_url'); ?>" type="video/mp4">
                              <source src="mov_bbb.ogg" type="video/ogg">
                              Your browser does not support HTML5 video.
                            </video>
                         
                    </div>
                    <?php endwhile; ?>
                  <?php wp_reset_postdata(); ?>
              <?php endif; ?>
        </div>
		  
      <!-- Add Pagination -->
          <div class="swiper-pagination"></div>
  </div>
  </section>







  





</div>
<?php
get_footer();
?>
