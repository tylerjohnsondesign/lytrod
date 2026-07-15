<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package lytrod
 */

?>
</div><!-- #page -->
<footer class=" footercontainer">
		<div class="containery">
			<div  style="padding: 25px;" class="rowy">
				<div class="coly-6" style="
			    display: flex;
    align-items: end;
    flex-direction: column;
    margin-top: 10px;
				">
				<img style="width: 200px;margin-bottom: 10px;" src="/brand_assets/LytrodSoftwareInc/Lytrod-logo-white.png" alt="">
				<small style="color: white;">Copyright © <?php echo date("Y"); ?> Lytrod Software Inc. All Rights Reserved.</small>
				</div>
				<div class="coly-6">
			
					<div  style = "margin-top:0" class="lytrodcompanyinfo">
					
						<p style="margin: 0!important;">Lytrod Software is a technology company established in 1985. Our software simplifies complex tasks and consolidates the steps required to produce highly optimized documents in PDF format for efficient printing and finishing.</p>
							<div class ="d-flex">
								<a href="/privacy-policy/">Privacy Policy</a>|
								<a href="/terms-of-service/">Terms of Service</a>|
							</div>
						
						<br>
					</div>	
				
				</div>
				
				
				
			</div>
		</div>


	</footer><!-- #colophon -->


<nav class="nav-bar">
      <div class="containery">
		  <div class="rowy navrow">
			  <div class="coly-md-6 coly-sm-12">
			  <?php
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'menu_id'        => 'primary-menu',
								'after' => '<i class="fas fa-chevron-down lytroddownmenu"></i>',
							)
						);
						?>
			  </div>
		  </div>
	  </div>

      </ul>
      
    </nav>


<?php wp_footer(); ?>

</body>
</html>
