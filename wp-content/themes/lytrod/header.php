<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package lytrod
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-148149848-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-148149848-1');
	</script>


</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wrappernav">
	<header class="nav-header">
			<div id="lytrodlogo">
			<?php the_custom_logo();?>
			</div>
		
			<div class="burger">
				<div class="line1"></div>
				<div class="line2"></div>
			</div>
			<div class="header-menu">
			<?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'menu_id'        => 'primary-menu',
								// 'before' => '<i class="fas fa-chevron-down"></i>',
     							'link_after' => '<i class="fas fa-chevron-down lytroddownmenu"></i>',
								//  'in_top_bar' => true,
								 
							)
						);
						?>
		</div>
			
    </header>

	</div>
<div id="page" class="site">

	
