<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lytrod
 */
$ID = get_the_id();
$class = get_post_status($ID) == 'private' ? 'hidepost' : '';
$hideProduct =  get_post_type($ID) == 'product'? 'hidepost':'';

?>

		<div class="coly-md-4" id="post-<?php the_ID(); ?>" >
					<div class="searchboxcontent">
						<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

						<?php if ( 'post' === get_post_type() ) : ?>
						<div class="entry-meta">
							<?php
							lytrod_posted_on();
							lytrod_posted_by();
							?>
						</div><!-- .entry-meta -->
						<?php endif; ?>


						<?php lytrod_post_thumbnail(); ?>

						<div class="entry-summary">
							<?php the_excerpt(); ?>
						</div><!-- .entry-summary -->

							<footer class="entry-footer">
								<?php 
								lytrod_entry_footer(); 
								?>
							</footer>
					</div> 
			</div><!-- #post-<?php the_ID(); ?> -->

