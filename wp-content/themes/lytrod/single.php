<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package lytrod
 */

get_header();
?>

	<main id="primary" class="site-main">

		<div class="container">
			<div class="row" style="
			min-height: 80vh;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
	">
				<div class="col-md-4">
					<h1>No Post to show</h1>
				</div>
			</div>
		</div>
		<?php
		// while ( have_posts() ) :
		// 	the_post();

		// 	get_template_part( 'template-parts/content', get_post_type() );

		// 	the_post_navigation(
		// 		array(
		// 			'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'lytrod' ) . '</span> <span class="nav-title">%title</span>',
		// 			'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'lytrod' ) . '</span> <span class="nav-title">%title</span>',
		// 		)
		// 	);

		// 	// If comments are open or we have at least one comment, load up the comment template.
		// 	if ( comments_open() || get_comments_number() ) :
		// 		comments_template();
		// 	endif;

		// endwhile; // End of the loop.
		// ?>

	</main><!-- #main -->

<?php
// get_sidebar();
get_footer();
