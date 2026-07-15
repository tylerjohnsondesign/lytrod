<?php

function lc_is_staging_site($string){
    $staging_array = array('.dev', '.local', '.staging', '.test', '.example', '.invalid',
                        'dev.', 'local', 'test', 'staging'); 
    foreach ($staging_array as $url_part) { 
        if (strpos($string, $url_part) !== FALSE) { 
            return true;
        }
    } 
    return false;
}

function lc_get_apikey(){
    $apikey = get_site_option('lc_apikey');
    if ($apikey == "") return FALSE;
    return $apikey;
}

function lc_license_page_func(){
    if (!current_user_can('administrator')) return;
    ?>
    <div class="wrap">
    <img src="<?php echo plugins_url("/livecanvas/images/lc-logo.svg") ?>" style="width:200px;height: auto;margin:20px 0 10px;">
    <h1>Product Activation</h1>
    
    <div id="lc-message"></div> <!-- For displaying messages -->

    <?php
    if (!lc_get_apikey()) {
        ?>
        <p>Please retrieve your license from the <a target="_blank" href="https://livecanvas.com/members-area/">members area</a></p>
        <form id="lc-activation-form" style="margin:30px 0; width:400px; background: #ddd;padding: 20px">
            <?php wp_nonce_field('lc_ajax_nonce', 'lc_nonce'); ?>
            <input required name="license_code" id="lc-license-code" type="text" style="min-width: 100%;margin-bottom:10px;" value="" placeholder="Paste your license code here...">
            
            <label><input name="is_staging" id="lc-is-staging" type="checkbox" value="Y" <?php if (lc_is_staging_site(get_bloginfo("url"))) echo "checked" ?>> This is a staging / test site</label>

            <input class="button-primary" type="submit" style="min-width: 100%;margin-top:30px;" value="Activate Product">
        </form>
        <?php
    } else { 
        echo "<div class='notice notice-success'><p>Activation status: <strong style='margin-left:12px;color:green'>ACTIVE</strong></p></div>";
        ?>
        <form id="lc-deactivation-form" onsubmit="return false;">
            <?php wp_nonce_field('lc_ajax_nonce', 'lc_nonce'); ?>
            <input class="button" type="button" id="lc-deactivate-button" value="Disconnect Site">
        </form>    
        <?php
    }
    ?>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var lc_ajax_object = {
            ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('lc_ajax_nonce'); ?>',
            site_url: '<?php echo get_bloginfo('url'); ?>',
            is_staging: '<?php echo lc_is_staging_site(get_bloginfo('url')) ? 'Y' : 'N'; ?>'
        };

        $('#lc-activation-form').on('submit', function(e) {
            e.preventDefault();

            var licenseCode = $('#lc-license-code').val();
            var isStaging = $('#lc-is-staging').is(':checked') ? 'Y' : 'N';

            // Show a loading message
            $('#lc-message').html('<p>Activating, please wait...</p>');

            // Send the activation request directly to the remote server
            fetch('https://updater.livecanvas.com/issue-apikey/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'siteurl': lc_ajax_object.site_url,
                    'license_code': licenseCode,
                    'is_staging': isStaging,
                })
            })
            .then(response => response.text())
            .then(data => {
                if (data.startsWith('APIKEY:')) {
                    var apiKey = data.substring(7);

                    // Send the API key to the server to store it
                    $.post(lc_ajax_object.ajax_url, {
                        action: 'lc_save_apikey',
                        api_key: apiKey,
                        nonce: lc_ajax_object.nonce
                    }, function(response) {
                        if (response.success) {
                            $('#lc-message').html('<div class="notice notice-success"><h3>Success</h3><p>Product activated successfully.</p></div>');
                            // Optionally, refresh the page
                            location.reload();
                        } else {
                            $('#lc-message').html('<div class="notice notice-warning"><p>' + response.data + '</p></div>');
                        }
                    });
                } else {
                    $('#lc-message').html('<div class="notice notice-warning"><p>Error: ' + data + '</p></div>');
                }
            })
            .catch(error => {
                $('#lc-message').html('<div class="notice notice-error"><p>Error: ' + error.message + '</p></div>');
            });
        });

        $('#lc-deactivate-button').on('click', function() {
            if (!confirm('Do you really want to remove the product activation from this site?')) {
                return;
            }

            // Send the deactivation request via AJAX
            $.post(lc_ajax_object.ajax_url, {
                action: 'lc_deactivate',
                nonce: lc_ajax_object.nonce
            }, function(response) {
                if (response.success) {
                    $('#lc-message').html('<div class="notice notice-info"><p>' + response.data + '</p></div>');
                    // Optionally, refresh the page
                    location.reload();
                } else {
                    $('#lc-message').html('<div class="notice notice-warning"><p>' + response.data + '</p></div>');
                }
            });
        });
    });
    </script>
    <?php 
}

// AJAX handler for saving API key
add_action('wp_ajax_lc_save_apikey', 'lc_save_apikey');
function lc_save_apikey() {
    check_ajax_referer('lc_ajax_nonce', 'nonce');

    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access.');
    }

    $api_key = sanitize_text_field($_POST['api_key']);

    if (empty($api_key)) {
        wp_send_json_error('Invalid API key.');
    }

    update_site_option('lc_apikey', $api_key);

    wp_send_json_success('API key saved successfully.');
}

// AJAX handler for deactivation
add_action('wp_ajax_lc_deactivate', 'lc_deactivate');
function lc_deactivate() {
    check_ajax_referer('lc_ajax_nonce', 'nonce');

    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access.');
    }

    delete_site_option('lc_apikey');

    wp_send_json_success('Product deactivated successfully.');
}

