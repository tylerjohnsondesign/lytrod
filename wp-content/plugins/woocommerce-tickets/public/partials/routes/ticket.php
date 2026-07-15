
<?php get_header();?>

<header class="entry-header"></header>

<?php 
// $id = get_query_var('ticketID');
$id = get_query_var('ticket');

// echo $id;

// echo admin_url( 'admin-ajax.php' ); 

?>

<?php 
  $author_id = get_post_field ('post_author', $id);

        //  die();

 
        $current_user = wp_get_current_user();

        $allowed_roles = get_option('defaultRoles');


            //    die();
       

  if($author_id == wp_get_current_user()->ID  ||array_intersect($allowed_roles, $current_user->roles )){
   ?>
   <div class="ticket_wrapper" style="background-color: <?php echo get_theme_mod('single_ticket_background_color');?>;">


<div class="container ticket_history_container">
<div style="display: none;" id="current-user-id"><?php echo  wp_get_current_user()->ID?></div>
    <?php 
        if(wp_get_current_user()->ID == 1){
            ?>
   <audio id="myAudioAnswer">
            <source src="https://www.fesliyanstudios.com/play-mp3/387" type="audio/mp3">
        </audio>

            <?php 
        }else{
            ?>

     

        <audio id="myAudioQuestion">
            <source src="https://www.fesliyanstudios.com/play-mp3/387" type="audio/mp3">
        </audio>
            <?php
        }
            ?>


        <div class="row">
       
      
            <div class="col-4">
                <?php 
                if(array_intersect($allowed_roles, $current_user->roles) ){
                    ?>
                        <h1>  <a class="return-dashboard" href="<?php echo site_url() . '/tickets-dashboard'?>">Return to dashboard</a></h1>
                    <?php
                }else{
                    ?>
                        <h1>  <a class="return-dashboard" href="<?php echo site_url() . '/my-account/submitaticket'?>">Return to dashboard</a></h1>
                    <?php
                }
                ?>
            
               

              
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h3 style="color:<?php echo get_theme_mod('single_ticket_header_color');?>">Ticket # <?php echo $id;?> | Ticket status: 
                <?php echo (get_post_meta($id,'status',true) == 'Close')?'<span style = "color:red" >Close</span>':'<span style = "color:green" >Open</span>'; ?>
              
            </h3>
                
            </div>
        </div>
        <div class="spinner-loader"></div>
  
    <div  style="display: none;" id="post_ticket_id"><?php echo $id;?></div>
   
    <div id = "app_conversation" class="container ticket_conversation_container" style="min-height: 60vh;opacity:0;">

                <div v-if="onlineStatus == true">
                        <p style="text-align: center;color:green;">Lytrod: Online</p>
                </div>
                <div v-else>
                      <p style="text-align: center;color:red;">Lytrod: Offline</p>
                </div>
        <div class="row app_conversation_row">
            <div class="col-8">
                <div class="conversation_box">

              
                    <div :class = "box.answer !== undefined ?'answerbox':'questionbox' " 
                    :style = "box.answer !== undefined ?'background-color:<?php echo get_theme_mod('answer_box_background_color'); ?>;':'background-color: <?php echo get_theme_mod('question_box_background_color')?>' "
                    v-for="(box,index) in datas">

                    <div class="d-flex">

                  
                    <div v-if="box.answer !== undefined">
                                <img style="width:60px" src="https://lytrod.com/wp-content/uploads/2021/06/cropped-cropped-logo-251x84-1.png" alt="">
                             </div>
                             <div v-else>
                                 	
                             <img style="width:50px" src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" alt="">
                                 
                             </div>
                        <span>
                       
                            <!-- <p>Message # {{index +1}}</p> -->
                            <p :style = "box.answer !== undefined ?'color:<?php echo get_theme_mod('answer_box_text_color'); ?>;':'color: <?php echo get_theme_mod('question_box_text_color')?>' ">
                            Created At: {{new Date(box.time*1000).toLocaleString('en-US', {hour12: true })}}</p> 
                           
                            <div v-if="box.answer !== undefined">
                                <?php echo '<p style = "color:black;" >' .'  Created By: Admin </p>';?>
                             </div>
                             <div v-else>
                                     <p>Created By:  {{box.title}}</p> 
                             </div>
                            
                        </span>
                      
                    </div>
                    <hr>
                       <p style="color: <?php echo get_theme_mod('question_box_text_color'); ?>">
                       <span v-html="box.question"></span>
                    </p> 
                       <p style="color: <?php echo get_theme_mod('answer_box_text_color'); ?>"> 
                       <span v-html="box.answer"></span>
                        </p>
                     
                                        <!-- {{box.file}} -->
                        <div v-for = "(images,index) in box.question_file">   

                                <div v-if="images.fileType == 'application/x-zip-compressed' ">
                                        <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a>
                                </div>

                            <!-- <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a> -->
                                        <div v-if="images.fileType == 'image/png' ">
                                        <a :download="images.fileName" :href="images.src" alt="">{{images.fileName}}</a>
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
                    
                        </div>
                        <div v-for = "(images,index) in box.answer_file">   

                                <div v-if="images.fileType == 'application/x-zip-compressed' ">
                                        <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a>
                                </div>

                                <!-- <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a> -->
                                        <div v-if="images.fileType == 'image/png' ">
                                        <a :download="images.fileName" :href="images.src" alt="">{{images.fileName}}</a>
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

                            </div>
                       
               
                      
                    
                      
                  
                   
                           
                       <!-- <img :src="box.file" alt="" srcset=""> -->
                    
                        <!-- {{new Date(parseInt(box.time) *1000}} -->
                    </div>

                    <?php   if(wp_get_current_user()->ID !== 1  ){?>

                    <?php }?>

                    <?php 
                   if(array_intersect($allowed_roles, $current_user->roles )){
                 ?>          
                 <div @click = 'closeTicket($event)' class="btn btn_ticket" style="background:<?php echo  get_theme_mod('close_ticket_background_color');?>" >
                    Close Ticket
                </div>     
                <div style=" background-color: <?php echo  get_theme_mod('answer_message_box_color');?>" class="chat_add_message chatboxEven" data-from="answer"  data-time = "answertime"  data-file = "fileanswer">
                <div 
							@dragenter="enterHighLight($event)"  
							@dragover="enterHighLight($event)"id="drop-area" 
							:class="{highlight:showHighlight}" 
							@dragleave = "leaveHighLight($event)" 
							@drop = "leaveHighLight($event),handleDrop($event)" 
							id="drop-area">
									<input type="file" id="fileElem" multiple  @change="handleChange($event)">
										<label class="button-drag"  for="fileElem">Drag files here</label>
										<div id="fileImage" style="display: none;">
											{{finalImages}}
										</div>
										<div id="gallery" v-for="images in finalImages">
												<!-- {{images}} -->

                                                <div v-if="images.fileType == 'application/x-zip-compressed' ">
                                                    <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a>
                                                </div>
											
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
                                                <div style="cursor: pointer;" @click="removeFile(images)">
                                                <i class="fas fa-times-circle"></i>
                                                </div>
											</div>
						</div>
                <div class="message-box-spinner" style="display: none;"></div>

                
                           

            <ckeditor :editor="editor" v-model="editorData" :config="editorConfig"></ckeditor>
                <i @click = 'createTicket($event)' class="fas fa-paper-plane" style="color:<?php echo get_theme_mod('answer_message_icon_color');?>"></i>
            <span>
                    
            <input  @click = "stopEmailTicket($event)" <?php echo (get_post_meta($id,'stop_email',true) == 'true')?'checked':''?> type="checkbox">
            <label style="color: <?php echo get_theme_mod('stop_message_text')?>;">Stop email messages</label>
                  
                    </span>        
                </div>
                   
               
                <?php 
                   }else{

                    ?>

<div  style=" background-color: <?php echo  get_theme_mod('answer_message_box_color');?>" 
                    class="chat_add_message chatboxEven" data-from="question" data-time = "questiontime" data-file = "filequestion" >

                    <div 
							@dragenter="enterHighLight($event)"  
							@dragover="enterHighLight($event)"id="drop-area" 
							:class="{highlight:showHighlight}" 
							@dragleave = "leaveHighLight($event)" 
							@drop = "leaveHighLight($event),handleDrop($event)" 
							id="drop-area">
									<input type="file" id="fileElem" multiple  @change="handleChange($event)">
										<label class="button-drag"  for="fileElem">Drag files here</label>
										<div id="fileImage" style="display: none;">
											{{finalImages}}
										</div>
										<div id="gallery" v-for="images in finalImages">
												<!-- {{images}} -->

                                                <div v-if="images.fileType == 'application/x-zip-compressed' ">
                                                 <a :href = "images.src"  :download="images.fileName" >{{images.fileName}}</a>
											</div>
											
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
                                                <div style="cursor: pointer;" @click="removeFile(images)">
                                                <i class="fas fa-times-circle"></i>
                                                </div>
											</div>
						</div>

                        <div class="message-box-spinner" style="display: none;"></div>

                        <ckeditor  @keyup.enter = 'createTicket($event)' :editor="editor" v-model="editorData" :config="editorConfig"></ckeditor>
                        
                        <i style="color:<?php echo get_theme_mod('answer_message_icon_color');?>"  @click = 'createTicket($event)'   class="fas fa-paper-plane"></i>
                       
                    </div>
                    <?php
                   }
                ?>
                </div>
            </div>
        </div>
    </div>
    </div>
   <?php
  }else{
      ?>
        <div class="container" style="min-height: 75vh;">
                            <div class="row">
                                <div class="col-12" style="margin-top: 20px; text-align:center;">
                                    Please login to continue.
	
                                <?php wp_login_form(); ?>
                                </div>
                            </div>
                </div>
   

    <?php
  }
 
?>


<?php get_footer();?>