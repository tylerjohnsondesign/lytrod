<?php

function lc_ai_settings_page() {
    ?>
    <div class="wrap">
        <img src="<?php echo plugins_url("/livecanvas/images/lc-logo.svg") ?>" style="width:200px;height: auto;margin:20px 0 10px;">
      
        <form method="post" action="options.php">
            <?php
            // Security field
            settings_fields('lc_ai_settings_group');
            // Output settings sections and their fields
            do_settings_sections('lc_ai_settings');
            // Save settings button
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
// Hook to initialize the settings
add_action('admin_init', 'lc_register_ai_settings');

function lc_register_ai_settings() {
    // Register new settings for 'lc_ai_settings_group'
    register_setting('lc_ai_settings_group', 'lc_openai_key');
    register_setting('lc_ai_settings_group', 'lc_openrouter_key');
    register_setting('lc_ai_settings_group', 'lc_website_info');

    // Add settings section
    add_settings_section(
        'lc_ai_settings_section',       // ID
        'AI Settings',                  // Title
        null,                           // Callback (optional)
        'lc_ai_settings'                // Page
    );

    // Add OpenAI key field
    add_settings_field(
        'lc_openai_key',                // ID
        'OpenAI API Key',               // Label
        'lc_openai_key_field_callback', // Callback to display the field
        'lc_ai_settings',               // Page
        'lc_ai_settings_section'        // Section
    );

    // Add OpenRouter key field
    add_settings_field(
        'lc_openrouter_key',                // ID
        'OpenRouter API Key',              // Label
        'lc_openrouter_key_field_callback',// Callback to display the field
        'lc_ai_settings',                  // Page
        'lc_ai_settings_section'           // Section
    );

    // Add website info text area
    add_settings_field(
        'lc_website_info',
        'Website Information',
        'lc_website_info_field_callback',
        'lc_ai_settings',
        'lc_ai_settings_section'
    );
}

// Callback to display OpenAI Key field
function lc_openai_key_field_callback() {
    $openai_key = get_option('lc_openai_key');
    echo '
        <input type="password" name="lc_openai_key" value="' . esc_attr($openai_key) . '" class="regular-text" style="width:100%;">
        <small>Get an OpenAI API key <a target="_blank" href="https://platform.openai.com/api-keys">here</a>.</small>
    ';
}

// Callback to display OpenRouter Key field
function lc_openrouter_key_field_callback() {
    $openrouter_key = get_option('lc_openrouter_key');
    echo '
        <input type="password" name="lc_openrouter_key" value="' . esc_attr($openrouter_key) . '" class="regular-text" style="width:100%;">
        <small>Get an OpenRouter API key <a target="_blank" href="https://openrouter.ai/keys">here</a>.</small>
    ';
}

// Callback to display Website Information text area
function lc_website_info_field_callback() {
    $website_info = get_option('lc_website_info');
    echo '
        <textarea placeholder="The company website for a business of a Flower delivery service in Miami, Florida..." name="lc_website_info" rows="25" cols="50" class="large-text">' . esc_textarea($website_info) . '</textarea>
        <small style="font-size:14px">
        What the website / business is about. You might want to include company information such as your services, opening hours, address, FAQS, desired language and tone.</small>
        ';
}
