<?php
get_header();

?>
<header class="entry-header"></header>
<h1>hello</h1>
<?php  

    while ( have_posts() ) :
        the_post();

        the_content();

    endwhile; // End of the loop.
    ?>

<?php get_footer();?>