
<!---->

	<?php
				
					$args = array(
						'post_type' => 'ticket_form',
						'posts_per_page' => -1,
						'meta_query' => array(
						
								array(
									'key' => 'id',
									'value' => '19787',
									'compare' => '='
								),
						)

					);
				$query = new WP_Query($args);
							
				// The Loop
				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) : $query->the_post();
				
						$formData  = json_decode( get_post_meta( get_the_id(), 'json')[0]);	

					endwhile;
				endif;
				wp_reset_postdata();
							
			
				

	?>
<!---->
<div class="container"  id="accordian1">
    <div class="row">
        <div class="col-6">
			<h2 style="color: <?php echo get_theme_mod('dashboard_title_color')?>" class="header_ticket">Create New Ticket</h2>
		
			<div style="display: none; background-color: <?php echo get_theme_mod('open_close_background_color');?>">
			<span @click="show = !show">
				
				<p style="color:<?php echo get_theme_mod('open_close_color');?>">{{openOrClose}}</p>
		
			<i style="color: <?php echo get_theme_mod('open_close_color_icon_text');?>" ref= 'chevron' class="fas fa-chevron-down"></i>

		</span>

		<?php
			// echo '<pre>';
			// 	 print_r($formData);
			//  echo'</pre>';
				//    die();
		?>
		
	
			
			<transition
			
				@enter="enter"
				@leave="leave"
				:css="false"
			>
				<div v-if="show" class="box">
				<form class="submitaskaquestion"style=" background-color: <?php echo get_theme_mod('open_close_background_color');?>">
				<?php for ($i=0; $i < count($formData); $i++):?>
					
						<?php if($formData[$i]->form == 'singleLineText'):?>
							<div class="form-group">
								<label style="color: <?php echo get_theme_mod('submit_form_label_text_color')?>" for="lytrodname">
									<?php echo $formData[$i]->label ?>
								</label>
								<input type="text" class="form-control" id="lytrod-<?php echo $formData[$i]->default ?>" aria-describedby="emailHelp" placeholder="<?php echo $formData[$i]->default ?>">
							</div>
						<?php endif; ?>
						<?php if($formData[$i]->form == 'select'):?>
							<?php
						
								?>
							<div class="form-group">
							<label  style="color: <?php echo get_theme_mod('submit_form_label_text_color').'!important'?>" for="lytrod-reason-option">
								<?php echo $formData[$i]->label;?>
							</label>
							<select class="form-control" id="lytrod-reason-option">
							<?php for ($s=0; $s <count($formData[$i]->options); $s++):?>
										<option> <?php echo $formData[$i]->options[$s]->label; ?></option>
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
							<ckeditor :editor="editor" v-model="editorData" :config="editorConfig" class="form-control" id="lytrod-question" ></ckeditor>
							
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
										<label class="button" for="fileElem">Select some files</label>
										<!-- <div id="fileImage" style="display: none;"> -->
											{{finalImages}}
										<!-- </div> -->
										<div id="gallery" v-for="images in finalImages">
												<!-- {{images}} -->
												<div style="cursor: pointer;" @click="removeFile(images)">X</div>
												<div v-if="images.fileType == 'image/png' ">
													<img :src="images.src" alt="">
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
											</div>
							</div>
						
						<?php endif; ?>

						<?php if($formData[$i]->form == 'submit'):?>
							
							<a  style="
							background:<?php echo get_theme_mod('submit_form_button_color_background');?>;
							color:<?php echo get_theme_mod('submit_form_button_color_text');?>;	
						" @click = "submitTicket($event)" class="btn lytrod-form-button">
							<?php echo $formData[$i]->label;  ?>
							</a>
						<?php endif; ?>
				<?php endfor; ?>
		</form>
				</div>
			</transition>

			</div>

        </div>

		<div class="col-6">
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
			<div class="spinner-loader"></div>
		<div id="accordian2" style="display: none; background-color: <?php echo get_theme_mod('open_close_background_color');?>">
			<span @click="show = !show" >
				
					<p style="color:<?php echo get_theme_mod('open_close_color');?>">{{openOrClose}}</p>
			
				<i style="color: <?php echo get_theme_mod('open_close_color_icon_text');?>"  ref= 'chevron'  class="fas fa-chevron-down"></i>

			</span>
		
				
				<transition
				
					@enter="enter"
					@leave="leave"
					:css="false"
				>
					<div v-if="show" class="box">
						<div class="wrapper_ticket_history" style="background-color: <?php echo get_theme_mod('open_close_background_color');?>">
					
						<table class="table">
						<?php 
								
				if($queryName->post_count == 0){
					echo "<h1 style ='color:" . get_theme_mod('no_tickets_created_text_color') ."' class = 'header_ticket'>No Tickets Created</h1>";
				}
									
									// die();
						?>
												<tbody>
						<?php 
                                    foreach ($queryName->posts as $value) {
                                        # code...
                                        ?>
													<tr>
															<th>
										
								<a style="color: <?php  echo  get_theme_mod('ticket_history_link_color') ?>" href="<?php echo site_url() . '/singleticket/' .  $value->ID ?>">
                                            Ticket Number #  <?php echo $value->ID;?>
                                        </a></th>
											<td>	
												<?php 
														if(get_post_meta( $value->ID, 'close_ticket',true ) == "true"){
														array_push($stats['closed'],'Yes');
														echo '<p style = "color:red;">Ticket Closed</p>';
													}else{
														echo '<p style = "color: '.  get_theme_mod('tickets_open_text_color') .'">Ticket Open</p>';
                                                    }
                                                    ?>
											</td>

													</tr>
									
                                        <?php 
                                    }
                                ?>
									</tbody>
								</table>
						</div>
				
					</div>
				</transition>
		</div>

							
		</div>
    </div>

</div>

 
               
                   
                 
                         
                    
               
      
 