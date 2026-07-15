<?php get_header(); ?>
<?php
        // the query
        $the_query_vrcut = new WP_Query(array(
            'category_name' => 'learning_guide_vrcut',
            'post_status' => 'publish',
            // 'posts_per_page' => 15,
			'orderby' => 'date' ,
            'order' => 'ASC' ,
            'post_type' => 'event',
        ));
        ?>
<?php
        // the query
        $the_query_intellicut = new WP_Query(array(
            'category_name' => 'learning_guide_intellicut',
            'post_status' => 'publish',
            // 'posts_per_page' => 15,
			'orderby' => 'date' ,
            'order' => 'ASC' ,
            'post_type' => 'event',
        ));
        ?>

<?php
        // the query
        $the_query_design = new WP_Query(array(
            'category_name' => 'learning_guide_design',
            'post_status' => 'publish',
            // 'posts_per_page' => 15,
			'orderby' => 'date' ,
            'order' => 'ASC' ,
            'post_type' => 'event',
        ));
        ?>

<?php
        // the query
        $the_query_installation = new WP_Query(array(
            'category_name' => 'learning_guide_installation',
            'post_status' => 'publish',
            // 'posts_per_page' => 15,
			'orderby' => 'date' ,
            'order' => 'ASC' ,
            'post_type' => 'event',
        ));
        ?>
<?php
        // the query
        $the_query_global = new WP_Query(array(
            'category_name' => 'learning_guide_intellicutGlobal',
            'post_status' => 'publish',
            // 'posts_per_page' => 15,
			'orderby' => 'date' ,
            'order' => 'ASC' ,
            'post_type' => 'event',
        ));
        ?>

 

<!-- ---------------------------------------------------------SUB-MENU------------------------------------------------------------------ -->

<nav id="menu">
        <div id="topnav">
                    <a class="menu-links" data-menuanchor="first" id = "first" href="#Intellicut">Intellicut</a>
                    <a class="menu-links" data-menuanchor="second" id = "second" href="#intellicutglobal">Intellicut Global</a>
                    <a class="menu-links" data-menuanchor="three" id = "three"href="#VRCut">VRCut</a>
                    <a class="menu-links" data-menuanchor="fourth" id = "fourth" href="#Installation">Installation</a>
                    <a class="menu-links" data-menuanchor="fifth"  id = "fifth" href="#DocumentLayout">Design Tips</a>
                 
        </div>   
        
            
  
</nav>

<!-- ---------------------------------------------------------------INTELLICUT-------------------------------------- -->
<div id="fullpage">
  <section class="section">

    <div class="swiper-container container-one">
      <div class="swiper-wrapper">
     
        
     <?php if ($the_query_intellicut->have_posts()) : ?>
      <?php while ($the_query_intellicut->have_posts()) : $the_query_intellicut->the_post(); ?>
          
		<div class="swiper-slide">
            <div class="icon-image">
                <?php the_post_thumbnail(); ?>
            </div>
                <a class="title" target="_blank" href="/publications/learningGuide/<?php echo get_post_meta($post->ID, 'link', true); ?>" rel="noopener noreferrer">
                <?php the_title(); ?>
                </a>
                <div class="content">
                        <p><?php the_content(); ?></p>
                </div>
              
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


<!-- ------------------Intellicut Global------------------------- -->


<section class=" section s2">

            <div class="swiper-container">
            <div class="swiper-wrapper">

                <?php if ($the_query_global ->have_posts()) : ?>
                <?php while ($the_query_global ->have_posts()) : $the_query_global ->the_post(); ?>
                    
                    <div class="swiper-slide">
                        <div class="icon-image">
                                <?php the_post_thumbnail(); ?>
                        </div>
                        <a class="title" target="_blank" href="/publications/learningGuide/<?php echo get_post_meta($post->ID, 'link', true); ?>" rel="noopener noreferrer">
                        <?php the_title(); ?><br>
                        </a>
                        <div class="content">
                                    <p><?php the_content(); ?></p>
                        </div>
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
<!-----------------------------------------------------VRCut----------------------------------------------------- -->

<section class=" section s2">

<div class="swiper-container container-below">
<div class="swiper-wrapper">


  
     <?php if ($the_query_vrcut->have_posts()) : ?>
      <?php while ($the_query_vrcut->have_posts()) : $the_query_vrcut->the_post(); ?>
          
		<div class="swiper-slide">
            <div class="icon-image">
                <?php the_post_thumbnail(); ?>
            </div>
            <a class="title" target="_blank" href="/publications/learningGuide/<?php echo get_post_meta($post->ID, 'link', true); ?>" rel="noopener noreferrer">
            <?php the_title(); ?>
            </a>
            <div class="content">
                        <p><?php the_content(); ?></p>
            </div>
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


<!-- ---------------------------------------------------- Intstallation ----------------------------------------------->

<section class=" section s2">

<div class="swiper-container">
<div class="swiper-wrapper">

     <?php if ($the_query_installation ->have_posts()) : ?>
      <?php while ($the_query_installation ->have_posts()) : $the_query_installation ->the_post(); ?>
          
		<div class="swiper-slide">
            <div class="icon-image">
                    <?php the_post_thumbnail(); ?>
            </div>
            <a class="title" target="_blank" href="/publications/learningGuide/<?php echo get_post_meta($post->ID, 'link', true); ?>" rel="noopener noreferrer">
            <?php the_title(); ?><br>
            </a>
            <div class="content">
                        <p><?php the_content(); ?></p>
            </div>
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

<!-- ------------------------------------------ Design --------------------------------------------- -->

<section class=" section s2">
 
<div class="swiper-container">
<div class="swiper-wrapper">
  
        <?php if ($the_query_design->have_posts()) : ?>
            <?php while ($the_query_design->have_posts()) : $the_query_design->the_post(); ?>
          
		<div class="swiper-slide">
            <div class="icon-image">
                    <?php the_post_thumbnail(); ?>
            </div>
            <a class="title" target="_blank" href="/publications/learningGuide/<?php echo get_post_meta($post->ID, 'link', true); ?>" rel="noopener noreferrer">
                <?php the_title(); ?>
            </a>
            <div class="content">
                        <p><?php the_content(); ?></p>
            </div>
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









</div>
<?php
get_footer();
?>
