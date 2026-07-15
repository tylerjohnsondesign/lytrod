<?php

// EXIT IF ACCESSED DIRECTLY.
defined( 'ABSPATH' ) || exit;

// ADD META BOXES TO POST / PAGE / CPT EDITING SCREENS
function lc_add_meta_boxes( $post ){

	//ADD LC ON / OFF RADIO SWITCH TO PAGES
	add_meta_box( 'lca_meta_boxes_switch', __( 'LiveCanvas', 'livecanvas' ), 'lc_build_meta_box_lc_switch', 'page', 'side', 'high' );
	
	//ADD LC ON / OFF RADIO SWITCH TO POSTS
	if (lc_plugin_option_is_set('enable-on-post-type-post')) add_meta_box( 'lca_meta_boxes_switch', __( 'LiveCanvas', 'livecanvas' ), 'lc_build_meta_box_lc_switch', 'post', 'side', 'high' );
	
	//determine post type
	if(isset($_GET['post_type'])) $the_post_type=$_GET['post_type']; // for new screen cpt
	if(isset($_GET['post'])) $the_post_type=get_post_type($_GET['post']); // for edit cpt screen
	
	//ADD LC ON / OFF RADIO SWITCH TO OTHER opt-in CPTs 
	if (isset($the_post_type) &&  lc_plugin_option_is_set('enable-on-post-type-'.$the_post_type) )  
		add_meta_box( 'lca_meta_boxes_switch', __( 'LiveCanvas', 'livecanvas' ), 'lc_build_meta_box_lc_switch', $the_post_type, 'side', 'high' );

	//ADD OTHER CUSTOM META BOXES (NO SWITCH)

	//add custom meta box for gt blocks
	if (isset($the_post_type) && $the_post_type== 'lc_gt_block' )
		add_meta_box( 'lca_meta_boxes_gtb', __( 'LiveCanvas', 'livecanvas' ), 'lc_build_meta_box_lc_gt_block', $the_post_type, 'side', 'high' );
	
	//add the meta box for 'lc_block','lc_section','lc_partial' for shortcode get_post and php example to pull content
	if (isset($the_post_type) && in_array($the_post_type, array('lc_block','lc_section','lc_partial')) )
		add_meta_box( 'lca_meta_boxes_sam', __( 'Use as Partial', 'livecanvas' ), 'lc_build_meta_box_lc_get_post', $the_post_type, 'side', 'high' );
		
}
add_action( 'add_meta_boxes', 'lc_add_meta_boxes' );

 
function lc_build_meta_box_lc_switch( $post ){
	// make sure the form request comes from WordPress
	wp_nonce_field( basename( __FILE__ ), 'lc_meta_box_nonce' );

	// retrieve the _lc_livecanvas_enabled current value
	$current_livecanvas_enabled = get_post_meta( $post->ID, '_lc_livecanvas_enabled', true );
 
	?>
	<div class='inside'>
		<h4 style="margin-bottom:0"><?php _e( 'Enable the LiveCanvas Editor', 'livecanvas' ); ?></h4>
        
        <?php if (lc_plugin_option_is_set("enable-dynamic-templating")): ?>
            <small style="margin-bottom:15px;display:block"><?php _e( '(Disables LC Dynamic Templating)', 'livecanvas' ); ?> </small>
        <?php endif ?>

		<p>
			<input type="radio" name="livecanvas_enabled" value="1" <?php checked( $current_livecanvas_enabled, '1' ); ?> /> Yes<br />
			<input type="radio" name="livecanvas_enabled" value="" <?php checked( $current_livecanvas_enabled, '' ); ?> /> No
		</p>
	</div>
	<?php
}

function lc_save_meta_box_data( $post_id ){
	// verify meta box nonce
	if ( !isset( $_POST['lc_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['lc_meta_box_nonce'], basename( __FILE__ ) ) ){		return;	}

	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ return; }

	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ){	return;	}
	
	// If we have some data from the radio switch,  handle the situation
	if ( isset( $_REQUEST['livecanvas_enabled'] ) ):
		
		///// CASE LC RADIO SWITCHED FROM OFF TO ON /////////
		if ( get_post_meta($_REQUEST['post_ID'], '_lc_livecanvas_enabled', true) != '1' && $_REQUEST['livecanvas_enabled']==1) {

			//set the right template for LC, if present
			$template = 'page-templates/empty.php';

			if ( locate_template($template) ) {
				update_post_meta( $post_id, '_wp_page_template', $template );
			}
		}
		
		///// CASE LC RADIO SWITCHED FROM ON TO OFF /////////
		if ( get_post_meta($_REQUEST['post_ID'], '_lc_livecanvas_enabled', true) == '1' && $_REQUEST['livecanvas_enabled']!=1) { 
			
			//reset page template
			delete_post_meta( $post_id, '_wp_page_template'  );
		}
		
		//SAVE THE CUSTOM FIELD VALUE
		update_post_meta( $post_id, '_lc_livecanvas_enabled', sanitize_text_field( $_REQUEST['livecanvas_enabled'] ) );

	endif;

}
add_action( 'save_post', 'lc_save_meta_box_data' );


//// GT BLOCKS SIDE META BOX
function lc_build_meta_box_lc_gt_block( $post ){
	?>
	<div class='inside'>
		<h4><?php 
		if($post->post_status!='publish') _e('After publishing this post, ');
		_e( 'You can embed this content using the shortcode:', 'livecanvas' ); ?></h4>
		<h2>[lc_get_gt_block slug="<?php echo esc_attr($post->post_name); ?>"]</h2>

		<h4><?php _e( 'Alternatively, you can use this PHP code in your templates:', 'livecanvas' ); ?></h4>
		<h2>echo do_shortcode("[lc_get_gt_block slug='<?php echo esc_attr($post->post_name); ?>']");</h2>
		 
	</div>
	<?php
}


//// LC SIDE META BOX for LC's own post types
function lc_build_meta_box_lc_get_post( $post ){ 
	$shortcode_string = '[lc_get_post post_type="'.   esc_attr($post->post_type).'" slug="'. esc_attr($post->post_name).'"]';
	if($post->post_type=='lc_partial') {
        $shortcode_string = '[lc_get_partial slug="'. esc_attr($post->post_name).'"]';
    }
    ?>
	<div class='inside'>
		<h4><?php 
		if($post->post_status!='publish') _e('After publishing this post, ');
		_e( 'You can embed this content using the shortcode:', 'livecanvas' ); ?></h4>
		<h2><?php echo $shortcode_string ?></h2>
		
		<h4><?php _e( 'Alternatively, you can use this PHP code in your templates:', 'livecanvas' ); ?></h4>
		<h2>echo do_shortcode('<?php echo $shortcode_string ?>');</h2>

	</div>
	<?php
}

//ADMIN COLUMN FOR LC PARTIALS
// Add a custom column to the admin screen to show shortcode
function lc_partial_add_shortcode_column($columns) {
    // Insert the "Shortcode" column after the title column
    $columns['lc_partial_shortcode'] = __('Shortcode', 'textdomain');
    return $columns;
}
add_filter('manage_lc_partial_posts_columns', 'lc_partial_add_shortcode_column');

// Display the shortcode with slug in the custom column
function lc_partial_shortcode_column_content($column, $post_id) {
    if ($column === 'lc_partial_shortcode') {
        // Get the post slug
        $post_slug = get_post_field('post_name', $post_id);
        // Display the shortcode with slug
        echo '[lc_get_partial slug="' . esc_attr($post_slug) . '"]';
    }
}
add_action('manage_lc_partial_posts_custom_column', 'lc_partial_shortcode_column_content', 10, 2);



//////////////////////////////////  SUGGESTION /////////////////////////////////
 
/////SUGGEST IN WP ADMIN TO ENABLE LC FOR PAGES, IF NOT ENABLED YET ON THAT PAGE
add_action( 'current_screen', 'lc_tweak_wp_interface_page' ); 
function lc_tweak_wp_interface_page() { 
	
	global $pagenow;
	
	//exit if we're not in the editing page of wp-admin
	if (!in_array($pagenow, array('post.php','post-new.php')) ) return;
	
	if(isset( $_GET['post']))
		$already_using_lc = lc_post_is_using_livecanvas($_GET['post']);
			else
		$already_using_lc = FALSE;  
	
	if ($already_using_lc) { 
		  //remove_post_type_support('page', 'editor'); //commented these 3 lines to restore the editor for YOAST SEO compatibility
		  //remove_post_type_support('post', 'editor');
		  //if(isset( $_GET['post'])) remove_post_type_support(get_post_type($_GET['post']), 'editor'); //for saved cpt posts
		  add_action('admin_notices', 'lc_template_admin_notice_using_lc');
    } else {
	 //not using lc template (yet)
	add_action('admin_notices', 'lc_template_admin_notice_not_using_lc_yet');
	}
}

 
function lc_template_admin_notice_not_using_lc_yet(){

	global $post; 
	?>
	 
	<style>
		#wpbody-content .wrap .lc-add-editing-icon {  margin: 10px 0 0 45px;} /*no gut */
		.edit-post-header-toolbar .lc-add-editing-icon {  margin: 0 0 0 45px;} /* gut */
	</style> 
	
	<script>
 
		function isGutenbergActive() {    return typeof wp !== 'undefined' && typeof wp.blocks !== 'undefined';}
		
		jQuery(document).ready(function($) {
			
			if(isGutenbergActive()) { ///////ONLY FOR GUTENBERG - future useful link: https://github.com/WordPress/gutenberg/issues/17632
				wp.data.subscribe(function () { 
					if (wp.data.select('core/editor') && wp.data.select('core/editor').didPostSaveRequestSucceed() && !wp.data.select('core/editor').isAutosavingPost()    ) {  
						//console.log("Guten is doing something");
						postsaving = wp.data.select('core/editor').isSavingPost();
						autosaving = wp.data.select('core/editor').isAutosavingPost();
						success = wp.data.select('core/editor').didPostSaveRequestSucceed();

						if (!(postsaving && !autosaving && success)) return;
						
						console.log('Saving: '+postsaving+' - Autosaving: '+autosaving+' - Success: '+success);
						
						//check if radio button of LC is enabled
						if ($('input[name=livecanvas_enabled][value=1]').prop("checked") == true ) {
							//LC is enabled!
							if ($("#lc-guten-trigger-editing").length==0) { 
								//button is not there, but its needed: let's append it 
								var lc_button_url="<?php echo get_site_url() ?>?lc_redirect_to_edit_post_id="+wp.data.select('core/editor').getCurrentPostId();  
								var lc_button_html="<a id='lc-guten-trigger-editing' class='lc-add-editing-icon button button-primary button-large' href='"+lc_button_url+"' ><?php _e(( (!lc_plugin_option_is_set("whitelabel")) ?  'Edit with LiveCanvas': 'Edit in Frontend'), 'livecanvas') ?></a>";
								
								$(".edit-post-header-toolbar").append(lc_button_html);
									
							}
						}	else {
							//LC is not enabled, button is not necessary
							$("#lc-guten-trigger-editing").remove();
						}				 
						
					} //end if
				
			  	});  //end subscribe
			} //end if Gutenberg
			else {
				//no gutenberg
			}
			
		});//end document ready
	
	
	</script>
			
		    
	<?php 
 
}
 
function lc_template_admin_notice_using_lc(){
	///ADDS THE BUTTON TO LAUNCH LIVECANVAS EDITOR
	global $post;	 
    ?>
	<script>
		jQuery( document ).ready(function() {
			 
			//no guten 
			var lc_button_url="<?php echo esc_url( add_query_arg( array('lc_action_launch_editing' => '1', 'from_url' => lc_urlencode(lc_get_current_url()),'from_page_edit' =>'1'), get_permalink($post->ID))) ?>"
			jQuery("#titlediv").append("<br><a class='lc-add-editing-icon button button-primary button-hero' href='"+lc_button_url+"' ><?php _e(( (!lc_plugin_option_is_set("whitelabel")) ?  'Edit with LiveCanvas': 'Edit in Frontend'), 'livecanvas') ?></a>");
		});
		 
	</script>
	<?php 
		//hide the editor on posts and pages, not on LC's CPTs
		if ( !(in_array($post->post_type, array('lc_block', 'lc_section', 'lc_partial', 'lc_dynamic_template')))) {
			?>
			<style>
				#postdivrich {display: none} /* hide the editor */
			</style>
		
			<?php } else {
			?>
			<style>
				#postdivrich {margin-top:20px;} /* add some spacing from the button */
			</style>
		
			<?php }

 
}


///ADD TEMPLATE CHOICE META BOX TO posts / CPTS where LC is enabled /////////////
// This also prevents WordPress losing the "Template" setting as well,
// moreover providing clear feedback on the situation

add_action('add_meta_boxes', function () {
    global $post;
    if (!$post || !isset($post->post_type)) return;

    if (!lc_plugin_option_is_set('enable-on-post-type-' . $post->post_type)) return;

    add_meta_box(
        'lc_manual_template_box',
        'Template',
        'lc_render_manual_template_box',
        $post->post_type,
        'side',
        'high'
    );
});

function lc_render_manual_template_box($post) {
    wp_nonce_field('lc_save_manual_template', 'lc_manual_template_nonce');

    // Current saved template
    $current = get_post_meta($post->ID, '_wp_page_template', true) ?: 'default';

    // Get available templates
    $templates = wp_get_theme()->get_page_templates($post);

    // Add 'page-templates/empty.php' manually if it exists
    $manual_template_file = 'page-templates/empty.php';
    $manual_template_label = 'Empty Page Template';
    if (
        locate_template('page-templates/empty.php')
        && !in_array($manual_template_file, $templates)
    ) {
        $templates[$manual_template_label] = $manual_template_file;
    }

    // Output label and select
    echo '<label for="lc_page_template_select">Template</label><br>';
    echo '<select name="lc_page_template" id="lc_page_template_select" class="widefat">';

    // Show current value if it's not listed
    if ($current !== 'default' && !in_array($current, $templates)) {
        echo '<option value="' . esc_attr($current) . '" selected>(Current: ' . esc_html($current) . ')</option>';
    }

    // Default option
    echo '<option value="default"' . selected($current, 'default', false) . '>Default</option>';

    // Output all available templates
    foreach ($templates as $label => $file) {
        echo '<option value="' . esc_attr($file) . '"' . selected($current, $file, false) . '>' . esc_html($label) . '</option>';
    }

    echo '</select>';
}

add_action('save_post', function ($post_id) {
    if (!isset($_POST['lc_manual_template_nonce']) || !wp_verify_nonce($_POST['lc_manual_template_nonce'], 'lc_save_manual_template')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['lc_page_template'])) {
        $template = sanitize_text_field($_POST['lc_page_template']);
        update_post_meta($post_id, '_wp_page_template', $template);
    }
}, 0);


////// ALTERNATIVE TO CODE ABOVE [TEMPLATE CHOICE META BOX] TO PREVENT WP BUG LOSING TEMPLATE setting on unsupported post types
// USE IN CASE WE WANT TO GET RID OF THE TEMPLATE SELECT
/*
// Step 1: Capture original template before saving
add_filter('wp_insert_post_empty_content', function($maybe_empty, $postarr) {
    if (!empty($postarr['ID'])) {
        $GLOBALS['lc_original_templates'][$postarr['ID']] = get_post_meta($postarr['ID'], '_wp_page_template', true);
    }
    return $maybe_empty;
}, 10, 2);

// Step 2: Restore it after save if WP deleted it
add_action('save_post', function($post_id, $post, $update) {
    if (!$update) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;
    
	if (!lc_plugin_option_is_set('enable-on-post-type-'.$post->post_type) )  return;

    if (!isset($_POST['page_template'])) {
        $old_template = $GLOBALS['lc_original_templates'][$post_id] ?? null;

        if (!empty($old_template) && $old_template !== 'default') {
            update_post_meta($post_id, '_wp_page_template', $old_template);
        }
    }
}, 1, 3);
*/
