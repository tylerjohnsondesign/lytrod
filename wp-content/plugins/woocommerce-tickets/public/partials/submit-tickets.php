
<!---->

<?php
				
				$args = array(
					'post_type' => 'ticket_form',
					'posts_per_page' => 1,
				

				);
			$query = new WP_Query($args);
						
			// The Loop
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) : $query->the_post();
			
					$formData  = json_decode( get_post_meta( get_the_id(), 'json')[0]);	

				endwhile;
			endif;
			wp_reset_postdata();


			/*
				if serial number is not blank show values
					Set key to intellicut
			*/

				
		$intellicutargs = array(
					'author' =>get_current_user_id(),
					'post_type' => 'lytrod_subscriptions',
					'posts_per_page' =>-1,
					'meta_query'=>array(
						array(
							'key'=>'intellicut_product_id',
							'compare'=>'!=',
							'value'=>'',
						)	
					)
				);
		$intellicutQuery = new WP_Query($intellicutargs);

		
				//    die();
		$intellicutGlobalArgs = array(
				'author' =>get_current_user_id(),
				'post_type' => 'lytrod_subscriptions',
				'posts_per_page' =>-1,
				'meta_query'=>array(
					array(
						'key'=>'intellicut_serial_number_global',
						'compare'=>'!=',
						'value'=>'',
					)
				)
			);
		$intellicutGlobalQuery = new WP_Query($intellicutGlobalArgs);

		$vrcutImposeQueryArgs = array(
			'author' =>get_current_user_id(),
			'post_type' => 'lytrod_subscriptions',
			'posts_per_page' =>-1,
			'meta_query'=>array(
				array(
					'key'=>'vrcut_impose_product_id',
					'compare'=>'!=',
					'value'=>'',
				)
			)
		);
	$vrcutImposeQuery = new WP_Query($vrcutImposeQueryArgs);

$vrcutControllerQueryArgs = array(
		'author' =>get_current_user_id(),
		'post_type' => 'lytrod_subscriptions',
		'posts_per_page' =>-1,
		'meta_query'=>array(
			array(
				'key'=>'vrcut_controller_product_id',
				'compare'=>'!=',
				'value'=>'',
			)
		)
	);
$vrcutControllerQuery = new WP_Query($vrcutControllerQueryArgs);


$visionFocusQueryArgs = array(
	'author' =>get_current_user_id(),
	'post_type' => 'lytrod_subscriptions',
	'posts_per_page' =>-1,
	'meta_query'=>array(
		array(
			'key'=>'vision_focus_serial_number',
			'compare'=>'!=',
			'value'=>'',
		)
	)
);
$visionFocusQuery = new WP_Query($visionFocusQueryArgs);

$visionDirectQueryArgs = array(
	'author' =>get_current_user_id(),
	'post_type' => 'lytrod_subscriptions',
	'posts_per_page' =>-1,
	'meta_query'=>array(
		array(
			'key'=>'vision_direct_serial_number',
			'compare'=>'!=',
			'value'=>'',
		)
	)
);
$visionDirectQuery = new WP_Query($visionDirectQueryArgs);



		


		
?>
<!---->
<div class="container-fluid"  id="app">

<div class="" style="height: 50px;">
	<div class="spinner-loader" style="display: none;"></div>
</div>

<div class="row">
<div class="tab-trigger-container">
	<div class="tabs-trigger" v-for="(item, index) in categories" :class="[index === activeTab ? 'tabs-trigger--active' : '']" 
			@click="activate(index)">
		{{categories[index]}}
	</div>
</div>


  <div style="background-color: <?php echo get_theme_mod('open_close_background_color');?>" class="tabs-content" v-if="activeTab === 0" >
	  

		<h2 style="color: <?php echo get_theme_mod('dashboard_title_color')?>" class="header_ticket">Create New Ticket</h2>
	
		<div class="eventBox" >
	
		

					
	<?php 	
	if(count($intellicutQuery->posts) !== 0 ||count($intellicutGlobalQuery->posts) !== 0 ||count($vrcutImposeQuery->posts) !== 0 || 
			count($vrcutControllerQuery->posts) !==0 || count($visionFocusQuery->posts) !==0 || count($visionDirectQuery->posts) !== 0){
				?>
	
		<?php if( current_user_can('administrator') ) {  ?>
				<h4>Admin cant create support tickets to themselves.</h4>
			<?php }else{?>

				
				<div class="box">
			<form class="submitaskaquestion"style=" background-color: <?php echo get_theme_mod('open_close_background_color');?>">
				
			<div class="form-group">
				<label for="">Product:</label>
					
					<select v-model = "registerProduct"  id="register-product" class="form-control">

					<option disabled value=""> Choose Product</option>

				<?php if(count($intellicutQuery->posts) !== 0): ?>
					
							<option  value="<?php echo (count($intellicutQuery->posts)>0)?('Intellicut'):('')?>">
							
								<?php 
									echo (count($intellicutQuery->posts)>0)?('Intellicut'):('');
							?>
							</option>	
				<?php endif;?>	
				<?php if(count($intellicutGlobalQuery->posts) !== 0): ?>
							<option  value="Intellicut Global">
								Intellicut Global
							</option>	
				<?php endif;?>	

				<?php if(count($vrcutImposeQuery->posts) !== 0): ?>
							<option  value="VRCutImpose">
								VRCut Impose
							</option>	
				<?php endif;?>	
				<?php if(count($vrcutControllerQuery->posts) !== 0): ?>
							<option  value="VRCutController">
								VRCut Controller
							</option>	
				<?php endif;?>	
				<?php if(count($visionFocusQuery->posts) !== 0): ?>
							<option  value="visionFocus">
								Vision Focus
							</option>	
				<?php endif;?>			
				
				<?php if(count($visionDirectQuery->posts) !== 0): ?>
							<option  value="visiondirect">
								Vision Direct
							</option>	
				<?php endif;?>		
					</select>
				</div>

		
				<div class="form-group" v-if="registerProduct == 'Intellicut'">
					<label for="">Intellicut Serial</label>
					<select  class="form-control serial-value" name="" id="">
					
					<?php
					 for ($i=0; $i < count($intellicutQuery->posts); $i++) { 
						?>
		
					<option value="<?php echo get_post_meta($intellicutQuery->posts[$i]->ID,'intellicut_product_id',true);?>">
						<?php echo  get_post_meta($intellicutQuery->posts[$i]->ID,'intellicut_product_id',true);?>
						</option>
					<?php
					 }
					?>
					</select>
				</div>

				<div class="form-group" v-if="registerProduct == 'VRCutImpose'">
					<label for="">VRCut Impose Serial: </label>
					<select  class="form-control serial-value" name="" id="">
					
					<?php
					 for ($i=0; $i < count($vrcutImposeQuery->posts); $i++) { 
						?>
		
					<option value="<?php echo get_post_meta($vrcutImposeQuery->posts[$i]->ID,'vrcut_impose_product_id',true);?>">
						<?php echo  get_post_meta($vrcutImposeQuery->posts[$i]->ID,'vrcut_impose_product_id',true);?>
						</option>
					<?php
					 }
					?>
					</select>
				</div>


				<div class="form-group" v-if="registerProduct == 'Intellicut Global'">
					<label for="">Intellicut Global</label>
					<select  class="form-control serial-value" name="" id="">
						<?php
						for ($i=0; $i < count($intellicutGlobalQuery->posts); $i++) { 
					
							?>
							
						<option value="<?php echo  get_post_meta($intellicutGlobalQuery->posts[$i]->ID,'intellicut_serial_number_global',true);?>">
							
							<?php echo  get_post_meta($intellicutGlobalQuery->posts[$i]->ID,'intellicut_serial_number_global',true);?>
							</option>
						<?php
						}
						?>
					</select>
				</div>

				<div class="form-group" v-if="registerProduct == 'visionFocus'">
					<label for="">Vision Focus</label>
					<select  class="form-control serial-value" name="" id="">
						<?php
						for ($i=0; $i < count($visionFocusQuery->posts); $i++) { 
					
							?>
							
						<option value="	<?php echo  get_post_meta($visionFocusQuery->posts[$i]->ID,'vision_focus_serial_number',true);?>">
							
							<?php echo  get_post_meta($visionFocusQuery->posts[$i]->ID,'vision_focus_serial_number',true);?>
							</option>
						<?php
						}
						?>
					</select>
				</div>

				<div class="form-group" v-if="registerProduct == 'VRCutController'">
					<label for="">VRCut Controller</label>
					<select  class="form-control serial-value" name="" id="">
						<?php
						for ($i=0; $i < count($vrcutControllerQuery->posts); $i++) { 
					
							?>
							
						<option value="<?php echo  get_post_meta($vrcutControllerQuery->posts[$i]->ID,'vrcut_controller_product_id',true);?>">
							
							<?php echo  get_post_meta($vrcutControllerQuery->posts[$i]->ID,'vrcut_controller_product_id',true);?>
							</option>
						<?php
						}
						?>
					</select>
				</div>

				<div class="form-group" v-if="registerProduct == 'visiondirect'">
					<label for="">Vision Direct</label>
					<select  class="form-control serial-value" name="" id="">
						<?php
						for ($i=0; $i < count($visionDirectQuery->posts); $i++) { 
					
							?>
							
						<option value="<?php echo  get_post_meta($visionDirectQuery->posts[$i]->ID,'vision_direct_serial_number',true);?>">
							
							<?php echo  get_post_meta($visionDirectQuery->posts[$i]->ID,'vision_direct_serial_number',true);?>
							</option>
						<?php
						}
						?>
					</select>
				</div>


			
				</div>	
		
			<?php for ($i=0; $i < count($formData); $i++):?>
				
					<?php if($formData[$i]->form == 'singleLineText'):?>
						<div class="form-group">
							<label style="color: <?php echo get_theme_mod('submit_form_label_text_color')?>" for="lytrodname">
								<?php echo $formData[$i]->label ?>
							</label>
							<input  style="background: <?php echo get_theme_mod('submit_form_input_background_color')?>"  type="text" class="form-control" id="lytrod-<?php echo $formData[$i]->default ?>" aria-describedby="emailHelp" placeholder="<?php echo $formData[$i]->default ?>">
						</div>
					<?php endif; ?>
					<?php if($formData[$i]->form == 'select'):?>
					
						<div class="form-group">
						<label  style="color: <?php echo get_theme_mod('submit_form_label_text_color').'!important'?>" for="lytrod-reason-option">
							<?php echo $formData[$i]->label;?>
						</label>
						<select   style="background: <?php echo get_theme_mod('submit_form_input_background_color')?>" class="form-control" id="lytrod-reason-option">
						<?php for ($s=0; $s <count($formData[$i]->options); $s++):?>
									<option  style="background: <?php echo get_theme_mod('submit_form_input_background_color')?>" > <?php echo $formData[$i]->options[$s]->label; ?></option>
								 <?php endfor; ?>

						</select>
					</div>
					<?php endif; ?>

					<?php if($formData[$i]->form == 'paragraphText'):?>
						
						<div class="form-group">
						<label style="color: <?php echo get_theme_mod('submit_form_label_text_color')?>" for="lytrod-question">
								<?php echo $formData[$i]->label;  ?>
								<?php get_theme_mod('submit_form_button_color_background')?>	
						</label>
						<ckeditor  style="background: <?php echo get_theme_mod('submit_form_input_background_color')?>" :editor="editor" v-model="editorData" :config="editorConfig" class="form-control" id="lytrod-question" ></ckeditor>
						
					</div>
					<?php endif; ?>

					<?php if($formData[$i]->form == 'fileUpload'):?>
						<div 
						@dragenter="enterHighLight($event)"  
						@dragover="enterHighLight($event)"id="drop-area" 
						:class="{highlight:showHighlight}" 
						@dragleave = "leaveHighLight($event)" 
						@drop = "leaveHighLight($event),handleDrop($event)" 
						id="drop-area">
								<input type="file" id="fileElem" multiple  @change="handleChange($event)">
									<label class="button-drag" for="fileElem">Drag files here.</label>
								
									<div id="gallery" v-for="images in finalImages">
											<!-- {{images}} -->
									
											<div v-if="images.fileType == 'application/x-zip-compressed' ">
											 <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a>
										</div>

											<div v-if="images.fileType == 'image/png' ">
												<img :src="images.src" :download="images.fileName" alt="">
											</div>
											<div v-else-if="images.fileType == 'application/pdf' " >
												<a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a>
											</div>
											<div v-else-if = "images.fileType == 'video/mp4' ">
												<video width="320" height="240" controls>
												<source :src="images.src" type="video/mp4">
												Your browser does not support the video tag.
												</video>   
											</div>
											<div v-else-if="images.fileType == 'image/jpeg' " >
												<img :src="images.src" alt="">
											</div>
											<div v-else-if = "images.fileType == 'audio/mpeg' " >
											
												<audio controls>
												<source :src="images.src" type="audio/mpeg">
												Your browser does not support the audio element.
												</audio>
											</div>
											<div style="cursor: pointer;" @click="removeFile(images)">
                                                <i class="fas fa-times-circle"></i>
                                                </div>

										</div>
						</div>
					
					<?php endif; ?>
				
				<?php endfor; ?>
			

				
				<a  style="
						background:black;
						color:white;	
					" @click = "submitTicket($event)" class="btn lytrod-form-button">
						<?php echo 'Submit';  ?>
						</a>
			
			
	</form>

			</div>
	




			<?php }

					}else{
						?>
						<p>Please register a product  <a href="<?php echo site_url('/my-account/register/');?>">here</a> to submit a ticket.</p>
						<?php
					}
			//hero

			
			
			?>




		



		</div>

  

</div>
<div  style="background-color: <?php echo get_theme_mod('open_close_background_color');?>"class="tabs-content" v-if="activeTab === 1">
  

		<?php 
		$queryName =new WP_Query(
			array(
			'author'=>get_current_user_id(),
			'post_type'=>'tickets_woocommerce',
			'posts_per_page' => -1,
				)
		);
		?>
		<h1 style="color: <?php echo get_theme_mod('dashboard_title_color')?>" class="header_ticket">Ticket History</h1>
		
	<div class="eventBoxTwo" style=" background-color: <?php echo get_theme_mod('open_close_background_color');?>">
	
			

		
			
	
			
	
				<div class="box">
					<div class="wrapper_ticket_history" style="background-color: <?php echo get_theme_mod('open_close_background_color');?>">
				
					<table class="table">
					<?php 
							
			if($queryName->post_count == 0){
				echo "<h1 style ='color:" . get_theme_mod('no_tickets_created_text_color') ."' class = 'header_ticket'>No Tickets Created</h1>";
			}
								
								// die();
					?>
					  <thead>
<tr>
  <th scope="col">#</th>
  <th scope="col">Subject</th>
  <th scope="col">Status</th>
  <th scope="col">Created</th>
</tr>
</thead>
											<tbody>
					<?php 
						
					
					
					// The Loop
				if ( $queryName->have_posts() ) :
					while ( $queryName->have_posts() ) : $queryName->the_post();
					  // Do Stuff
					  ?>
						<tr>

							<td>
								#<?php echo get_the_id();?>
							</td>
							<td>
								<a style="color: <?php  echo  get_theme_mod('ticket_history_link_color') ?>" href="<?php echo site_url() . '/singleticket/' .  
								get_the_id();?>">
										<?php echo get_post_meta(get_the_id(), 'reason',false )[0]?>
								</a>
							</td>
							<td>	
								<?php 
										if(get_post_meta( get_the_id(), 'close_ticket',true ) == "true"){
										// array_push($stats['closed'],'Yes');
										echo '<p style = "color:red;">Ticket Closed</p>';
									}else{
										echo '<p style = "color: '.  get_theme_mod('tickets_open_text_color') .'">Ticket Open</p>';
									}
									?>
							</td>
							
							<td>
								<?php
								 echo get_the_date( 'Y-m-d' );
								?>
							</td>

						</tr>
					  <?php
					  
					endwhile;
				endif;
					
					// Reset Post Data
					wp_reset_postdata();
					
						 ?>
								</tbody>
							</table>
					</div>
			
				</div>
		
	</div>

						



</div>
 


</div>

</div>


		   
			   
			 
					 
				
		   
  
