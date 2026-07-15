<?php

// Add the meta box to all post editing screens
function lc_add_dynamic_template_meta_box() {
    
    global $post;

    if(!lc_plugin_option_is_set("enable-dynamic-templating")) return;

	$lc_settings = get_option('lc_settings');

    // Get all public post types
    foreach(array_merge(array('post','page'), (get_post_types(  array('public'   => true,'_builtin' => false ), 'names', 'and' ))) as $post_type):
                            
        if (strpos($post_type, 'tangible') !== false) continue; 
        
        if ( isset($post) && lc_post_is_using_livecanvas($post->ID) )  continue;

        add_meta_box(
            'lc_dynamic_template_meta_box', // Unique ID for the meta box
            'Custom Dynamic Template', // Title of the meta box
            'lc_render_dynamic_template_meta_box', // Callback to render the meta box
            $post_type, // Post type where this meta box should appear
            'advanced', // Placement ('side', 'normal', 'advanced')
            'low' // Priority of the meta box
        );

    endforeach;
}
add_action('add_meta_boxes', 'lc_add_dynamic_template_meta_box');

// Render the meta box
function lc_render_dynamic_template_meta_box($post) {
    // Retrieve the currently saved custom field value
    $selected_template = get_post_meta($post->ID, 'lc_use_template_of_slug', true);

    // Fetch all dynamic templates (posts of type 'lc_dynamic_template')
    $templates = get_posts(array(
        'post_type'   => 'lc_dynamic_template',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
    ));

    // Create a nonce for security
    wp_nonce_field('lc_dynamic_template_nonce_action', 'lc_dynamic_template_nonce');

    // Render the <select> dropdown
    echo '<p class="post-attributes-label-wrapper page-template-label-wrapper"><label class="post-attributes-label" for="page_template">Template</label>
		</p>';
    echo '<select name="lc_use_template_of_slug" id="lc_use_template_of_slug" style="width: 100%;">';
    echo '<option value="">' . esc_html__("Default for this post", 'textdomain') . '</option>'; // Default option
    foreach ($templates as $template) {
        // Use the post name (slug) as the value, and the post title as the visible option
        $selected = selected($selected_template, $template->post_name, false);
        echo '<option value="' . esc_attr($template->post_name) . '"' . $selected . '>' . esc_html($template->post_title) . '</option>';
    }
    echo '</select>';
    echo '<p class="post-attributes-help-text">Assign a specific LiveCanvas dynamic template</p>';
}

// Save the selected dynamic template when the post is saved
function lc_save_dynamic_template_meta_box($post_id) {
    // Verify the nonce for security
    if (!isset($_POST['lc_dynamic_template_nonce']) || 
        !wp_verify_nonce($_POST['lc_dynamic_template_nonce'], 'lc_dynamic_template_nonce_action')) {
        return;
    }

    // Prevent saving during an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check if the current user has the capability to edit this post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save or delete the custom field value
    if (isset($_POST['lc_use_template_of_slug']) && $_POST['lc_use_template_of_slug']!='') {
        update_post_meta($post_id, 'lc_use_template_of_slug', sanitize_text_field($_POST['lc_use_template_of_slug']));
    } else {
        delete_post_meta($post_id, 'lc_use_template_of_slug');
    }
}
add_action('save_post', 'lc_save_dynamic_template_meta_box');
