<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package lytrod
 */

get_header();
?>
<header class="entry-header"></header>
	<main id="primary" class="site-main ">	
	<?php get_search_form();?>
	<div class="containery">
	<header class="page-header">
				<h1 class="page-title">
					<?php
					/* translators: %s: search query. */
					printf( esc_html__( 'Search Results for: %s', 'lytrod' ), '<span>' . get_search_query() . '</span>' );
					?>
				</h1>
			</header><!-- .page-header -->
	<div class="rowy" style="margin-top: 20px;">

		<?php if ( have_posts() ) : ?>

		

			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				if(get_post_type()!=='product' && get_post_type()!=='video' && get_post_status()!=='private'){
					get_template_part( 'template-parts/content', 'search' );
				}
			

			endwhile;

			the_posts_navigation(array(
				'prev_text' => __('Next', 'theme_textdomain'),
				'next_text' => __('Previous', 'theme_textdomain'),
				// 'screen_reader_text' => __('Posts navigation', 'theme_textdomain')
			));

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>
</div>
	</div>
	</main><!-- #main -->

<?php
// get_sidebar();
get_footer();
