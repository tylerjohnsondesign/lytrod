<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://webfielddesign.com/
 * @since      1.0.0
 *
 * @package    Subscriptions_Upgrader
 * @subpackage Subscriptions_Upgrader/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Subscriptions_Upgrader
 * @subpackage Subscriptions_Upgrader/admin
 * @author     talktaimor <talktaimor@gmail.com>
 */
class Subscriptions_Upgrader_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		//add_action('woocommerce_variation_options_pricing', [$this,'add_custom_variation_label_field'], 10, 3);
		//add_action('woocommerce_save_product_variation', [$this,'save_custom_variation_label_field'], 10, 2);

		add_action('add_meta_boxes', [$this, 'add_meta_box']);
		//add_action('add_meta_boxes', [$this,'add_subscription_meta_box']);
		add_action('save_post', [$this, 'save_subscription_meta']);
		//add_action('save_post', [$this,'save_subscription_meta_box']);
		//add_action('woocommerce_thankyou', [$this,'update_product_with_subscription_id'], 10, 1);

		add_action('wp_ajax_add_more_group', [$this, 'handle_add_more_group']);
		add_action('wp_ajax_add_more_group_subscription', [$this, 'handle_add_more_group_subscription']);
		
	}

	public function add_meta_box() {
        add_meta_box(
            'subscription_settings', // ID
            'Subscription Settings',  // Title
            [$this, 'render_meta_box'], // Callback
            'product', // Post type
            'normal', // Context
            'high' // Priority
        );
    }

	/**
	 * Update the product post meta with the subscription ID when an order is placed.
	 *
	 * @param int $order_id The ID of the order that was just placed.
	 */
	public function update_product_with_subscription_id($order_id) {
		// Ensure the order exists
		if (!$order_id) {
			return;
		}

		// Get the order object
		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		// Check if the order contains a subscription
		if (wcs_order_contains_subscription($order_id)) {
			// Get subscriptions for the order
			$subscriptions = wcs_get_subscriptions_for_order($order_id);

			// Loop through the subscriptions and update product post meta
			foreach ($subscriptions as $subscription) {
				// Get the subscription ID
				$subscription_id = $subscription->get_id();

				// Loop through each item in the order
				foreach ($order->get_items() as $item_id => $item) {
					// Get the product ID
					$product_id = $item->get_product_id();

					// Update the product post meta with the subscription ID
					update_post_meta($product_id, '_subscription_id', $subscription_id);
				}
			}
		}
	}

	public function render_meta_box($post) {
		wp_nonce_field('save_subscription_meta', 'subscription_meta_nonce');
		if (!isset($_COOKIE['wcs_switch_request_nonce'])) {
			$nonce = wp_create_nonce('wcs_switch_request'); // Generate the nonce
			setcookie('wcs_switch_request_nonce', $nonce, time() + (60 * 60 * 24 * 365 * 10), "/"); // 10 years expiration
		} else {
			$nonce = $_COOKIE['wcs_switch_request_nonce']; // Use the existing nonce
		}
		// Get the product if it's a variable product
		$product = wc_get_product($post->ID);
		if ($product && $product->is_type('variable')) {
			$variations = $product->get_available_variations();
			// Retrieve saved variation groups and account labels from post meta
			$variation_groups = get_post_meta($post->ID, '_custom_variation_groups', true) ?: [];
			echo '<div id="variation-container" class="subscription_upgradr_container">';
			// Function to render a variation row for each group
			function render_variation_row($group_index, $variations, $selected_id = '', $label = '') {
				ob_start();
				?>
				<div class="variation-row form-field _variation_field" style="display:flex; margin-bottom: 10px;">
					<label for="variation_select" style="width:15%;">Select Variation:</label>
					<select class="variation_select short" name="variation_select[<?php echo $group_index; ?>][]">
						<option value="">Select Variation</option>
						<?php foreach ($variations as $variation) : ?>
							<option value="<?php echo esc_attr($variation['variation_id']); ?>" <?php selected($selected_id, $variation['variation_id']); ?>>
								<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="button" class="remove-variation button">Remove</button>
				</div>
				<?php
				return ob_get_clean();
			}

			function render_variation_row_account($group_index, $index, $variations, $selected_id = [], $label = '', $variationgroupswitch, $postid) {
				ob_start();
				   $is_first_row = ($index === 1);
				?>
				<div class="variation-row-account form-field _variation_field" style="display:flex; margin-bottom: 10px;" data-group-index="<?php echo $group_index; ?>">
					<label for="variation_select" style="width:40%;">Select Variation:</label>
					<select class="variation_select_account short" name="variation_select_account[<?php echo $group_index; ?>][<?php echo $index; ?>][]" multiple>
						<option value="">Select Variation</option>
						<?php foreach ($variations as $variation) : ?>
							<option value="<?php echo esc_attr($variation['variation_id']); ?>" 
								<?php echo in_array($variation['variation_id'], $selected_id) ? 'selected' : ''; ?>>
								<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="text" class="short" name="custom_labels-account[<?php echo $group_index; ?>][<?php echo $index; ?>]" placeholder="Custom Label" value="<?php echo esc_attr($label); ?>" />
					
					<button type="button" class="remove-variation-account button" style="<?php echo $is_first_row ? 'display:none;' : ''; ?>">Remove</button>
					<button type="button" class="copy-link-product button" data-group="<?php echo $group_index; ?>" data-index="<?php echo $index; ?>" >Copy Product Link</button>
					
					<?php 
					$variationgroups = get_post_meta($postid, '_custom_variation_groups', true) ?: [];
					if($variationgroups) {
						?>
						<select class="form-control-select" name="switch_variation-group[<?php echo $group_index; ?>][<?php echo $index; ?>]">
							<option value="">Select Group</option>
							<?php 
								$groupcount = 1;
								foreach ($variationgroups as $group_key => $group_data) {
									$selected = '';
									if($variationgroupswitch == $groupcount) {
										$selected = 'selected';
									}
									echo '<option '.$selected.' value='.$groupcount.'>Variation Group ' . $groupcount . '</option>';
									$groupcount++;
								}
							?>
						</select>	
						<?php
					}
					?>
				</div>
				<?php
				return ob_get_clean();
			}			
	
			$group_count = 1;
			foreach ($variation_groups as $group_key => $group_data) {
				echo '<div class="variation-group" data-group="' . $group_count . '">';
				echo '<h3>Variation Group ' . esc_html($group_count) . '</h3>';
	
				// Render group label
				$group_label = isset($group_data['group_label']) ? $group_data['group_label'] : '';
				echo '<input type="text" style="width: 50%;margin-bottom: 10px;" name="group_labels[' . $group_count . ']" value="' . esc_attr($group_label) . '" placeholder="Group Label" />';
				echo '<input type="hidden" style="width: 50%;margin-bottom: 10px;" name="countergroup['.$group_count.']" value="' . esc_attr($group_count) . '">';
				// Loop through variations for this group and render each row
				foreach ($group_data['variations'] as $variation_id) {
					$selected_id = $variation_id;
					$label = $variation['custom_label'];
					echo render_variation_row($group_count, $variations, $selected_id, $label);
				}
	
				// Add buttons for adding/removing variations and groups
				echo '<button type="button" class="add-more-variations button">Add More</button>';
				//echo '<button type="button" class="copy-link-product button" data-group="' . $group_count . '">Copy Product Link</button>';
				echo '<button type="button" class="copy-link-checkout button" data-group="' . $group_count . '">Copy Checkout Link</button>';
				echo '<button type="button" class="remove-group button">Remove Group</button><br><br/>';
	
				echo '<div class="account-group-container" id="account_group_container_' . $group_count . '">';
				echo '<h2><b>Upgrade Subscription Settings</b></h2>';
		
				if (isset($group_data['variations_account']) && is_array($group_data['variations_account']) && !empty($group_data['variations_account'])) {
					foreach ($group_data['variations_account'] as $key => $account_variation) {
						$selected_ids = isset($account_variation['account_variation_ids']) ? $account_variation['account_variation_ids'] : [];
						$label = isset($account_variation['account_label']) ? $account_variation['account_label'] : '';
						$variationgroupswitch = isset($account_variation['variation-group-switch']) ? $account_variation['variation-group-switch'] : '';
						$pid = $post->ID;
						echo render_variation_row_account($group_count, $key + 1, $variations, $selected_ids, $label, $variationgroupswitch, $pid);
					}
				} else {
					$pid = $post->ID;
					echo render_variation_row_account($group_count, 1, $variations, [], '', '', $pid);
				}
				
				
				//echo '</div>';
				echo '<button type="button" class="add-more-variations-account button">Add More</button>';
				echo '</div>'; // Close account group container
				echo '</div>'; // Close the current group
	
				$group_count++;
			}
	
			// Button to add more variation groups
			echo '<button type="button" id="add-more-group" class="button" style="margin-top:10px;">Add More Group</button>';
	
			echo '</div>'; // Close variation-container
			
			?>
			<script>
				jQuery(document).ready(function($) {
					// Initialize select2 for existing selects
					$('.variation_select_account').select2();
					$('.select2').css('width', '100%');
	
					// Add more group functionality
					$('#add-more-group').on('click', function() {
						var groupCount = $('.variation-group').length + 1;
						var postID = <?php echo $post->ID; ?>;
						$.ajax({
							url: "<?php echo admin_url('admin-ajax.php'); ?>",
							method: 'POST',
							data: {
								action: 'add_more_group',
								nonce: '<?php echo wp_create_nonce('add_more_group_nonce'); ?>',
								post_id: postID,
								group_count: groupCount
							},
							success: function(response) {
								if ($('#variation-container').find('.variation-group').length > 0) {
									$('#variation-container').find('.variation-group').last().after(response.data);
								} else {
									$('#variation-container').append(response.data);
								}
								// Reinitialize select2 for newly added elements
								jQuery('.variation_select_account').select2();
								jQuery('.select2-container').css('width', '100%');
							},
							error: function(xhr, status, error) {
								alert('Error adding the group: ' + error);
							}
						});
					});
	
					// Add more variations (product or account)
					$(document).on('click', '.add-more-variations', function() {
						var group = $(this).closest('.variation-group');
						var groupCount = group.data('group');
						var newVariationRow = group.find('.variation-row').first().clone();
						newVariationRow.find('select').val('');
						newVariationRow.find('input[type="text"]').val('');
						group.find('.variation-row:last').after(newVariationRow);
						$('.variation_select_account').select2();
						jQuery('.select2-container').css('width', '100%');
					});
	
					$(document).on('click', '.add-more-variations-account', function() {
						var group = $(this).closest('.variation-group');
						var groupCount = group.find('.variation-row-account').length; // Count of rows in the current group

						// Clone the first variation-row-account and reset select2 container
						var newAccountRow = group.find('.variation-row-account').first().clone();

						// Reset the values of select and input fields in the new row
						newAccountRow.find('select').val(null).trigger('change'); // Reset select2 value
						newAccountRow.find('input[type="text"]').val(''); // Reset the input field

						// Remove the extra select2 container that gets created (this is the extra rendered select box)
						newAccountRow.find('.select2').remove(); // Remove the extra select2 container

						// Re-initialize select2 on the new select element
						newAccountRow.find('.variation_select_account').select2({
							placeholder: '',
							allowClear: true // Optionally allow clearing the selection
						});

						// Ensure that the name attribute for the cloned select and input are unique
						var groupIndex = group.attr('data-group'); // Assuming you have a data attribute for group index
						var rowIndex = groupCount + 1; // Increment row index for the current group

						newAccountRow.find('select').attr('name', 'variation_select_account[' + groupIndex +']['+rowIndex+'][]');
						newAccountRow.find('input[type="text"]').attr('name', 'custom_labels-account[' + groupIndex + ']['+rowIndex+']');
						newAccountRow.find('.select2-container').css('width', '100%');

						// Append the new row to the container
						group.find('.variation-row-account:last').after(newAccountRow);

						// Update group indices for all rows within the group
						updateGroupIndices(group);
						
						group.find('.variation-row-account').each(function(index) {
							var removeButton = $(this).find('.remove-variation-account');
							if (index === 0) {
								removeButton.hide(); // Hide the "Remove" button for the first row
							} else {
								removeButton.show(); // Show the "Remove" button for all other rows
							}
						});
					});

					$(document).on('click', '.remove-variation-account', function() {
						var group = $(this).closest('.variation-group');
						
						// Remove the row
						$(this).closest('.variation-row-account').remove();

						// Update group indices after removing a row
						updateGroupIndices(group);
					});

					// Function to update group indices for all rows within the group
					function updateGroupIndices(group) {
						group.find('.variation-row-account').each(function(index) {
							var groupIndex = group.attr('data-group'); // Get the group index from the data attribute
							// Update the index to start from 1 instead of 0
							var newIndex = index + 1;

							// Update name attributes of the select and input fields based on the new index
							$(this).find('select').attr('name', 'variation_select_account[' + groupIndex +']['+newIndex+'][]');
							$(this).find('input[type="text"]').attr('name', 'custom_labels-account[' + groupIndex + ']['+newIndex+']');
						});
					}

	
					// Remove variation row
					$(document).on('click', '.remove-variation', function() {
						$(this).closest('.variation-row').remove();
					});

					$(document).on('click', '.remove-variation-account', function() {
						$(this).closest('.variation-row-account').remove();
					});
	
	
					// Remove group
					$(document).on('click', '.remove-group', function() {
						$(this).closest('.variation-group').remove();
						adjustGroupCount();
					});
	
					// Adjust group count after removal
					function adjustGroupCount() {
						var groupIndex = 1;
						$(".variation-group").each(function() {
							$(this).attr("data-group", groupIndex);
							$(this).find("h3").text("Variation Group " + groupIndex);
							groupIndex++;
						});
					}
	
					// Copy product link functionality
					$(document).on("click", ".copy-link-product", function() {
						var group = $(this).closest(".variation-group");
						var groupId = group.data("group");
						var index = $(this).data("index");
						var rowselected = $(this).closest('.variation-row-account');
						var variations = [];
						rowselected.find('.variation_select_account option:selected').each(function() {
							var variationId = $(this).val();
							if (variationId && !variations.includes(variationId)) {
								variations.push(variationId);
							}
						});
	
						var productId = <?php echo $post->ID; ?>;
						var productPermalink = "<?php echo get_permalink($post->ID); ?>";
						var groupLabel = group.find("input[name='group_labels[" + groupId + "]']").val();
						var groupLabelAccount = group.find("input[name='custom_labels-account[" + groupId + "][]']").val();
						 var switchVariationGroup = rowselected.find("select[name='switch_variation-group[" + groupId + "][" + index + "]']").val();
						var url = productPermalink + "?product_id=" + productId;
	
						if (variations.length) {
							url += "&variation_id=" + variations.join(",");
						}
	
						if (groupLabel) {
							url += "&product_title=" + encodeURIComponent(groupLabel);
						}
	
						if (groupLabelAccount) {
							url += "&account_title=" + encodeURIComponent(groupLabelAccount);
						}
	
						if (groupId) {
							url += "&groupcount=" + encodeURIComponent(groupId);
						}
						
						if (switchVariationGroup) {
							url += "&switch_variation_group=" + encodeURIComponent(switchVariationGroup);
						}
	
						var tempInput = $("<input>");
						$("body").append(tempInput);
						tempInput.val(url).select();
						document.execCommand("copy");
						tempInput.remove();
	
						alert("Product Link Copied: " + url);
					});

					$(document).on("click", ".copy-link-checkout", function() {
						var group = $(this).closest(".variation-group");
						var groupId = group.data("group");
						var variations = [];
						group.find(".variation-row").each(function() {
							var variationId = $(this).find(".variation_select").val();
							if (variationId && !variations.includes(variationId)) {
								variations.push(variationId);
							}
						});
	
						var productId = <?php echo $post->ID; ?>;
						var baseCheckoutUrl = "<?php echo get_site_url() ?>/";
						var groupLabel = group.find("input[name='group_labels[" + groupId + "]']").val();
						var groupLabelAccount = group.find("input[name='custom_labels-account[" + groupId + "][]']").val();
						var url = baseCheckoutUrl + "?add-to-cart-variation=" + productId;
						var subscription_id = "<?php echo $subscription_id ?>";
						var itemid = "<?php echo $item_id; ?>";
						//var nonce = "<?php //echo wp_create_nonce( 'wcs_switch_request' ); ?>";
						var nonce = "<?php echo esc_js($nonce); ?>";
						if (variations.length) {
							url += "&variation_id=" + variations.join(",")+"&_wcsnonce="+nonce;
						}
	
						if (groupLabel) {
							url += "&product_title=" + encodeURIComponent(groupLabel);
						}
	
						if (groupLabelAccount) {
							url += "&account_title=" + encodeURIComponent(groupLabelAccount);
						}
	
						if (groupId) {
							url += "&groupcount=" + encodeURIComponent(groupId);
						}
						
						var tempInput = $("<input>");
						$("body").append(tempInput);
						tempInput.val(url).select();
						document.execCommand("copy");
						tempInput.remove();
	
						alert("Product Link Copied: " + url);
					});
				});
			</script>
			<?php
		}
	}

    public function save_subscription_meta($post_id) {
		// Check if nonce is valid
		if (!isset($_POST['subscription_meta_nonce']) || !wp_verify_nonce($_POST['subscription_meta_nonce'], 'save_subscription_meta')) {
			return;
		}
	
		// If it's autosave, don't save
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
	
		// If it's not a product, don't save
		if ('product' !== get_post_type($post_id)) {
			return;
		}
	
		// Initialize an array to store variation groups
		$variation_groups = [];
	
		// Process 'variation_select' (main product variations)
		if (isset($_POST['variation_select']) && is_array($_POST['variation_select'])) {
			foreach ($_POST['variation_select'] as $group_index => $variations) {
				// Initialize group-level data
				$group_label = isset($_POST['group_labels'][$group_index]) ? sanitize_text_field($_POST['group_labels'][$group_index]) : '';
				$group_variations = array_map('intval', (array) $variations);
	
				// Process account variations for the group
				$group_variations_account = [];
				if (isset($_POST['variation_select_account'][$group_index]) && is_array($_POST['variation_select_account'][$group_index])) {
					foreach ($_POST['variation_select_account'][$group_index] as $account_index => $account_variations) {
						$account_variation_ids = array_map('intval', (array) $account_variations);
	
						$account_label = isset($_POST['custom_labels-account'][$group_index][$account_index]) 
							? sanitize_text_field($_POST['custom_labels-account'][$group_index][$account_index]) 
							: '';
						$switchvariationgroup = isset($_POST['switch_variation-group'][$group_index][$account_index]) 
							? sanitize_text_field($_POST['switch_variation-group'][$group_index][$account_index])
							: '';
	
						if (!empty($account_variation_ids)) {
							$group_variations_account[] = [
								'account_variation_ids' => $account_variation_ids,
								'account_label' => $account_label,
								'variation-group-switch' => $switchvariationgroup
							];
						}
					}
				}
	
				// Save the group if it has relevant data
				if (!empty($group_variations) || !empty($group_variations_account) || !empty($group_label)) {
					$variation_groups[] = [
						'group_label' => $group_label,
						'variations' => $group_variations,
						'variations_account' => $group_variations_account
					];
				}
			}
		}
		
		// Save the variation groups data
		if (!empty($variation_groups)) {
			update_post_meta($post_id, '_custom_variation_groups', $variation_groups);
		} else {
			delete_post_meta($post_id, '_custom_variation_groups');
		}
	}


	public function handle_add_more_group() {
		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'add_more_group_nonce')) {
			wp_send_json_error('Invalid nonce.');
		}
	
		// Get the post ID and group count
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$group_count = isset($_POST['group_count']) ? intval($_POST['group_count']) : 0;
	
		// Ensure the post exists
		if (!$post_id || !get_post($post_id)) {
			wp_send_json_error('Invalid post ID.');
		}
	
		// Get available variations for the product (variable product)
		$product = wc_get_product($post_id);
		if (!$product || !$product->is_type('variable')) {
			wp_send_json_error('Invalid product.');
		}
	
		$variations = $product->get_available_variations();
	
		// Prepare the HTML for the new group with wp_editor
		ob_start();
		?>
		<div class="variation-group" data-group="<?php echo esc_attr($group_count); ?>">
			<h3>Variation Group <?php echo esc_html($group_count); ?></h3>
			<input type="text" style="width: 50%; margin-bottom: 10px;" name="group_labels[<?php echo esc_attr($group_count); ?>]" value="" placeholder="Group Label" />
	        <input type="hidden" style="width: 50%;margin-bottom: 10px;" name="countergroup[<?php echo esc_attr($group_count); ?>]" value="<?php echo esc_attr($group_count); ?>">
			<!-- Variation Select Field -->
			<div class="variation-row form-field _variation_field" style="display:flex;margin-bottom:10px;">
				<label for="variation_select_<?php echo esc_attr($group_count); ?>" style="width:15%;">Select Variation:</label>
				<select class="variation_select" name="variation_select[<?php echo esc_attr($group_count); ?>][]">
					<option value="">Select Variation</option>
					<?php foreach ($variations as $key => $variation) : ?>
						<option value="<?php echo esc_attr($variation['variation_id']); ?>">
							<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
						</option>
					<?php endforeach; ?>
				</select>
	
				<button type="button" class="remove-variation button">Remove</button>
			</div>
			<button type="button" class="add-more-variations button">Add More Variations</button>
			<button type="button" class="copy-link-product button" data-group="<?php echo esc_attr($group_count); ?>">Copy Product Link</button>
			<button type="button" class="copy-link-checkout button" data-group="<?php echo esc_attr($group_count); ?>">Copy Checkout Link</button> 
			<button type="button" class="remove-group button">Remove Group</button>
	
			<!-- Account Group Section -->
			<div class="account-group-container" id="account_group_container_<?php echo esc_attr($group_count); ?>">
				<h2><b>Upgrade Subscription Settings</b></h2>
				<div class="variation-row-account form-field _variation_field" style="display:flex; margin-bottom: 10px;">
					<label for="variation_select_account_<?php echo esc_attr($group_count); ?>" style="width:40%;">Select Variation:</label>
					<select class="variation_select_account short" name="variation_select_account[<?php echo esc_attr($group_count); ?>][1][]" multiple>
						<option value="">Select Variation</option>
						<?php foreach ($variations as $variation) : ?>
							<option value="<?php echo esc_attr($variation['variation_id']); ?>">
								<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="text" class="short" name="custom_labels-account[<?php echo esc_attr($group_count); ?>][1]" placeholder="Custom Label" />
				</div>
				<button type="button" class="add-more-variations-account button">Add More</button>
			</div>
		</div>
		<?php
		// Capture the generated HTML
		$group_html = ob_get_clean();
	
		// Return the HTML response
		wp_send_json_success($group_html);
	}
	

	public function handle_add_more_group_subscription() {
		// Verify nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'add_more_group_nonce')) {
			wp_send_json_error('Invalid nonce.');
		}
	
		// Get the post ID and group count
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
		$group_count = isset($_POST['group_count']) ? intval($_POST['group_count']) : 0;
	
		// Ensure the post exists
		if (!$post_id || !get_post($post_id)) {
			wp_send_json_error('Invalid post ID.');
		}
	
		// Get available variations for the product (variable product)
		$product = wc_get_product($post_id);
		if (!$product || !$product->is_type('variable')) {
			wp_send_json_error('Invalid product.');
		}
	
		$variations = $product->get_available_variations();
	
		// Prepare the HTML for the new group with wp_editor
		ob_start();
		?>
		<div class="variation-group" data-group="<?php echo esc_attr($group_count); ?>">
			<h3>Variation Group <?php echo esc_html($group_count); ?></h3>
			<input type="text" style="width: 50%; margin-bottom: 10px;" name="group_labels[<?php echo esc_attr($group_count); ?>]" value="" placeholder="Group Label" />
	
			<!-- Variation Select Field -->
			<div class="variation-row form-field _variation_field" style="display:flex;margin-bottom:10px;">
				<label for="variation_select_<?php echo esc_attr($group_count); ?>" style="width:15%;">Select Variation:</label>
				<select class="variation_select" name="variation_select[<?php echo esc_attr($group_count); ?>][]">
					<option value="">Select Variation</option>
					<?php foreach ($variations as $variation) : ?>
						<option value="<?php echo esc_attr($variation['variation_id']); ?>">
							<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<input type="text" class="short" name="custom_labels[<?php echo esc_attr($group_count); ?>][]" placeholder="Custom Label" />
				<button type="button" class="remove-variation button">Remove</button>
			</div>
			<button type="button" class="add-more-variations button">Add More Variations</button>
			<button type="button" class="copy-link-product button" data-group="<?php echo esc_attr($group_count); ?>">Copy Product Link</button>
			<button type="button" class="copy-link-checkout button" data-group="<?php echo esc_attr($group_count); ?>">Copy Checkout Link</button>
			<button type="button" class="remove-group button">Remove Group</button>
	
			<!-- Account Group Section -->
			<div class="account-group-container" id="account_group_container_<?php echo esc_attr($group_count); ?>">
				<h2><b>Upgrade Subscription Settings</b></h2>
				<div class="variation-row-account form-field _variation_field" style="display:flex; margin-bottom: 10px;">
					<label for="variation_select_account_<?php echo esc_attr($group_count); ?>" style="width:40%;">Select Variation:</label>
					<select class="variation_select_account short" name="variation_select_account[<?php echo esc_attr($group_count); ?>][1][]" multiple>
						<option value="">Select Variation</option>
						<?php foreach ($variations as $variation) : ?>
							<option value="<?php echo esc_attr($variation['variation_id']); ?>">
								<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="text" class="short" name="custom_labels-account[<?php echo esc_attr($group_count); ?>][1][]" placeholder="Custom Label" />
				</div>
			</div>
		</div>
		<?php
		// Capture the generated HTML
		$group_html = ob_get_clean();
	
		// Return the HTML response
		wp_send_json_success($group_html);
	}

	public function save_subscription_meta_box($post_id) {
		// Check if this is a subscription post type
		if (get_post_type($post_id) !== 'shop_subscription') {
			return;
		}
	
		// Check nonce for security
		if (!isset($_POST['subscription_meta_nonce']) || !wp_verify_nonce($_POST['subscription_meta_nonce'], 'save_subscription_meta')) {
			return;
		}
	
		// Check if this is an autosave, if so, our form has not been submitted
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
	
		// Check user permissions
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	
		// Get the subscription
		$subscription = wcs_get_subscription($post_id);
		$items = $subscription->get_items();
	
		// Initialize an array for the account group labels
		$variation_groups = [];

		if($items) {
			foreach($items as $item) {
				$post_id = $item['product_id'];
				$order_id = $item->get_order_id();
				$groupcount = get_post_meta($order_id, '_group_count_subscription', true);
				$all_filtered_labels = [];
				if (isset($_POST['variation_select']) && is_array($_POST['variation_select'])) {
					foreach ($_POST['variation_select'] as $group_index => $variations) {
						// Initialize group-level data
						$group_label = isset($_POST['group_labels'][$group_index]) ? sanitize_text_field($_POST['group_labels'][$group_index]) : '';
						$group_variations = array_map('intval', (array) $variations);
			
						// Process account variations for the group
						$group_variations_account = [];
						if (isset($_POST['variation_select_account'][$group_index]) && is_array($_POST['variation_select_account'][$group_index])) {
							foreach ($_POST['variation_select_account'][$group_index] as $account_index => $account_variations) {
								$account_variation_ids = array_map('intval', (array) $account_variations);
			
								$account_label = isset($_POST['custom_labels-account'][$group_index][$account_index]) 
									? sanitize_text_field($_POST['custom_labels-account'][$group_index][$account_index]) 
									: '';
			
								if (!empty($account_variation_ids)) {
									$group_variations_account[] = [
										'account_variation_ids' => $account_variation_ids,
										'account_label' => $account_label
									];
								}
							}
						}
			
						// Save the group if it has relevant data
						if (!empty($group_variations) || !empty($group_variations_account) || !empty($group_label)) {
							$variation_groups[] = [
								'group_label' => $group_label,
								'variations' => $group_variations,
								'variations_account' => $group_variations_account
							];
						}
					}
				}

				if (isset($groupcount)) {
					foreach ($variation_groups as $key => $val) {
						if ($groupcount == $key+1) {
							$all_filtered_labels[] = $val['variations_account'];
						}
					}
				}

				if (!empty($all_filtered_labels)) {
					update_post_meta($order_id, '_custom_labels_subscription', $all_filtered_labels);
				}
			
				// Save the variation groups data
				if (!empty($variation_groups)) {
					update_post_meta($post_id, '_custom_variation_groups', $variation_groups);
				} else {
					delete_post_meta($post_id, '_custom_variation_groups');
				}
				update_post_meta($post_id, 'variationsubscription', '1');
			}
		}		
	}
	
	
	public function add_subscription_meta_box() {
		add_meta_box(
			'subscription_variation_meta_box', // Unique ID
			'Subscription Variation Settings', // Box title
			[$this,'render_subscription_meta_box'], // Content callback
			'shop_subscription', // Post type
			'normal', // Context
			'default' // Priority
		);
	}

	public function render_subscription_meta_box($post) {
		wp_nonce_field('save_subscription_meta', 'subscription_meta_nonce');
		if (!isset($_COOKIE['wcs_switch_request_nonce'])) {
			$nonce = wp_create_nonce('wcs_switch_request'); // Generate the nonce
			setcookie('wcs_switch_request_nonce', $nonce, time() + (60 * 60 * 24 * 365 * 10), "/"); // 10 years expiration
		} else {
			$nonce = $_COOKIE['wcs_switch_request_nonce']; // Use the existing nonce
		}
	
		// Get the associated subscription
		$subscription = wcs_get_subscription($post->ID);
		$subscription_id = $subscription->get_id();
		$items = $subscription->get_items();
	
		// Check if any item is a variable product
		foreach ($items as $item_id => $item) {
			$product_id = $item['product_id'];
			$product = wc_get_product($product_id);
			$order_id = $item->get_order_id();
			$groupcount = get_post_meta($order_id, '_group_count_subscription', true);
	
			if ($product && $product->is_type('variable')) {
				$variations = $product->get_available_variations();
				// Retrieve saved variation groups and account labels from post meta

				// $checksubscription = get_post_meta($post->ID, 'variationsubscription', true);
				// if($checksubscription == 1) {
				// 	$id = $post->ID;
				// } else {
				// 	$id = $product_id;
				// }
				$variation_groups = get_post_meta($product_id, '_custom_variation_groups', true) ?: [];
				echo '<div id="variation-container" class="subscription_upgradr_container">';
				// Function to render a variation row for each group
				function render_variation_row1($group_index, $variations, $selected_id = '', $label = '') {
					ob_start();
					?>
					<div class="variation-row form-field _variation_field" style="display:flex; margin-bottom: 10px;">
						<label for="variation_select" style="width:15%;">Select Variation:</label>
						<select class="variation_select short" name="variation_select[<?php echo $group_index; ?>][]">
							<option value="">Select Variation</option>
							<?php foreach ($variations as $variation) : ?>
								<option value="<?php echo esc_attr($variation['variation_id']); ?>" <?php selected($selected_id, $variation['variation_id']); ?>>
									<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="remove-variation button">Remove</button>
					</div>
					<?php
					return ob_get_clean();
				}
	
				function render_variation_row_account($group_index, $index, $variations, $selected_id = [], $label = '') {
					ob_start();
					?>
					<div class="variation-row-account form-field _variation_field" style="display:flex; margin-bottom: 10px;" data-group-index="<?php echo $group_index; ?>">
						<label for="variation_select" style="width:40%;">Select Variation:</label>
						<select class="variation_select_account short" name="variation_select_account[<?php echo $group_index; ?>][<?php echo $index; ?>][]" multiple>
							<option value="">Select Variation</option>
							<?php foreach ($variations as $variation) : ?>
								<option value="<?php echo esc_attr($variation['variation_id']); ?>" 
									<?php echo in_array($variation['variation_id'], $selected_id) ? 'selected' : ''; ?>>
									<?php echo esc_html(implode(', ', $variation['attributes'])); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<input type="text" class="short" name="custom_labels-account[<?php echo $group_index; ?>][<?php echo $index; ?>]" placeholder="Custom Label" value="<?php echo esc_attr($label); ?>" />
						
						<button type="button" class="remove-variation-account button">Remove</button>
						<button type="button" class="copy-link-product button" data-group="<?php echo $group_index; ?>">Copy Product Link</button>
					</div>
					<?php
					return ob_get_clean();
				}			
		
				$group_count = 1;
				foreach ($variation_groups as $group_key => $group_data) {
					
					if($groupcount != $group_key + intval(1)) {
						$style = 'display:none';
					} else {
						$style = 'display:block';	
					}
					echo '<div class="variation-group" data-group="' . $group_count . '" style="'.$style.'">';
					echo '<h3>Variation Group ' . esc_html($group_count) . '</h3>';
		
					// Render group label
					$group_label = isset($group_data['group_label']) ? $group_data['group_label'] : '';
					echo '<input type="text" style="width: 50%;margin-bottom: 10px;" name="group_labels[' . $group_count . ']" value="' . esc_attr($group_label) . '" placeholder="Group Label" />';
					echo '<input type="hidden" style="width: 50%;margin-bottom: 10px;" name="countergroup['.$group_count.']" value="' . esc_attr($group_count) . '">';
					// Loop through variations for this group and render each row
					foreach ($group_data['variations'] as $variation_id) {
						$selected_id = $variation_id;
						$label = $variation['custom_label'];
						echo render_variation_row1($group_count, $variations, $selected_id, $label);
					}
		
					// Add buttons for adding/removing variations and groups
					echo '<button type="button" class="add-more-variations button">Add More</button>';
					//echo '<button type="button" class="copy-link-product button" data-group="' . $group_count . '">Copy Product Link</button>';
					echo '<button type="button" class="copy-link-checkout button" data-group="' . $group_count . '">Copy Checkout Link</button>';
					echo '<button type="button" class="remove-group button">Remove Group</button><br><br/>';
		
					echo '<div class="account-group-container" id="account_group_container_' . $group_count . '">';
					echo '<h2><b>Upgrade Subscription Settings</b></h2>';
					if (isset($group_data['variations_account']) && is_array($group_data['variations_account'])) {
						foreach ($group_data['variations_account'] as $key => $account_variation) {
							$selected_ids = isset($account_variation['account_variation_ids']) ? $account_variation['account_variation_ids'] : [];
							$label = isset($account_variation['account_label']) ? $account_variation['account_label'] : '';
							echo render_variation_row_account($group_count, $key + 1, $variations, $selected_ids, $label);
						}
					} else {
						echo render_variation_row_account($group_count, 1, $variations, '', '');
					}
					
					//echo '</div>';
					echo '<button type="button" class="add-more-variations-account button">Add More</button>';
					echo '</div>'; // Close account group container
					echo '</div>'; // Close the current group
		
					$group_count++;
					//}
				}
		
				// Button to add more variation groups
				echo '<button type="button" id="add-more-group" class="button" style="margin-top:10px;">Add More Group</button>';
		
				echo '</div>'; // Close variation-container
		
				?>
				<script>
					jQuery(document).ready(function($) {
						// Initialize select2 for existing selects
						$('.variation_select_account').select2();
						$('.select2').css('width', '100%');
		
						// Add more group functionality
						$('#add-more-group').on('click', function() {
							var groupCount = $('.variation-group').length + 1;
							var postID = <?php echo $product_id; ?>;
							$.ajax({
								url: "<?php echo admin_url('admin-ajax.php'); ?>",
								method: 'POST',
								data: {
									action: 'add_more_group',
									nonce: '<?php echo wp_create_nonce('add_more_group_nonce'); ?>',
									post_id: postID,
									group_count: groupCount
								},
								success: function(response) {
									if ($('#variation-container').find('.variation-group').length > 0) {
										$('#variation-container').find('.variation-group').last().after(response.data);
									} else {
										$('#variation-container').append(response.data);
									}
									// Reinitialize select2 for newly added elements
									jQuery('.variation_select_account').select2();
									jQuery('.select2-container').css('width', '100%');
								},
								error: function(xhr, status, error) {
									alert('Error adding the group: ' + error);
								}
							});
						});
		
						// Add more variations (product or account)
						$(document).on('click', '.add-more-variations', function() {
							var group = $(this).closest('.variation-group');
							var groupCount = group.data('group');
							var newVariationRow = group.find('.variation-row').first().clone();
							newVariationRow.find('select').val('');
							newVariationRow.find('input[type="text"]').val('');
							group.find('.variation-row:last').after(newVariationRow);
							$('.variation_select_account').select2();
							jQuery('.select2-container').css('width', '100%');
						});
		
						$(document).on('click', '.add-more-variations-account', function() {
							var group = $(this).closest('.variation-group');
							var groupCount = group.find('.variation-row-account').length; // Count of rows in the current group
	
							// Clone the first variation-row-account and reset select2 container
							var newAccountRow = group.find('.variation-row-account').first().clone();
	
							// Reset the values of select and input fields in the new row
							newAccountRow.find('select').val(null).trigger('change'); // Reset select2 value
							newAccountRow.find('input[type="text"]').val(''); // Reset the input field
	
							// Remove the extra select2 container that gets created (this is the extra rendered select box)
							newAccountRow.find('.select2').remove(); // Remove the extra select2 container
	
							// Re-initialize select2 on the new select element
							newAccountRow.find('.variation_select_account').select2({
								placeholder: '',
								allowClear: true // Optionally allow clearing the selection
							});
	
							// Ensure that the name attribute for the cloned select and input are unique
							var groupIndex = group.attr('data-group'); // Assuming you have a data attribute for group index
							var rowIndex = groupCount + 1; // Increment row index for the current group
	
							newAccountRow.find('select').attr('name', 'variation_select_account[' + groupIndex +']['+rowIndex+'][]');
							newAccountRow.find('input[type="text"]').attr('name', 'custom_labels-account[' + groupIndex + ']['+rowIndex+']');
							newAccountRow.find('.select2-container').css('width', '100%');
	
							// Append the new row to the container
							group.find('.variation-row-account:last').after(newAccountRow);
	
							// Update group indices for all rows within the group
							updateGroupIndices(group);
						});
	
						$(document).on('click', '.remove-variation-account', function() {
							var group = $(this).closest('.variation-group');
							
							// Remove the row
							$(this).closest('.variation-row-account').remove();
	
							// Update group indices after removing a row
							updateGroupIndices(group);
						});
	
						// Function to update group indices for all rows within the group
						function updateGroupIndices(group) {
							group.find('.variation-row-account').each(function(index) {
								var groupIndex = group.attr('data-group'); // Get the group index from the data attribute
								// Update the index to start from 1 instead of 0
								var newIndex = index + 1;
	
								// Update name attributes of the select and input fields based on the new index
								$(this).find('select').attr('name', 'variation_select_account[' + groupIndex +']['+newIndex+'][]');
								$(this).find('input[type="text"]').attr('name', 'custom_labels-account[' + groupIndex + ']['+newIndex+']');
							});
						}
	
		
						// Remove variation row
						$(document).on('click', '.remove-variation', function() {
							$(this).closest('.variation-row').remove();
						});
	
						$(document).on('click', '.remove-variation-account', function() {
							$(this).closest('.variation-row-account').remove();
						});
		
		
						// Remove group
						$(document).on('click', '.remove-group', function() {
							$(this).closest('.variation-group').remove();
							adjustGroupCount();
						});
		
						// Adjust group count after removal
						function adjustGroupCount() {
							var groupIndex = 1;
							$(".variation-group").each(function() {
								$(this).attr("data-group", groupIndex);
								$(this).find("h3").text("Variation Group " + groupIndex);
								groupIndex++;
							});
						}
		
						// Copy product link functionality
						$(document).on("click", ".copy-link-product", function() {
							var group = $(this).closest(".variation-group");
							var groupId = group.data("group");
							var rowselected = $(this).closest('.variation-row-account');
							var variations = [];
							rowselected.find('.variation_select_account option:selected').each(function() {
								var variationId = $(this).val();
								if (variationId && !variations.includes(variationId)) {
									variations.push(variationId);
								}
							});
		
							var productId = <?php echo $product_id; ?>;
							var productPermalink = "<?php echo get_permalink($product_id); ?>";
							var groupLabel = group.find("input[name='group_labels[" + groupId + "]']").val();
							var groupLabelAccount = group.find("input[name='custom_labels-account[" + groupId + "][]']").val();
							var url = productPermalink + "?product_id=" + productId;
		
							if (variations.length) {
								url += "&variation_id=" + variations.join(",");
							}
		
							if (groupLabel) {
								url += "&product_title=" + encodeURIComponent(groupLabel);
							}
		
							if (groupLabelAccount) {
								url += "&account_title=" + encodeURIComponent(groupLabelAccount);
							}
		
							if (groupId) {
								url += "&groupcount=" + encodeURIComponent(groupId);
							}
		
							var tempInput = $("<input>");
							$("body").append(tempInput);
							tempInput.val(url).select();
							document.execCommand("copy");
							tempInput.remove();
		
							alert("Product Link Copied: " + url);
						});

						$(document).on("click", ".copy-link-checkout", function() {
							var group = $(this).closest(".variation-group");
							var groupId = group.data("group");
							var variations = [];
							group.find(".variation-row").each(function() {
								var variationId = $(this).find(".variation_select").val();
								if (variationId && !variations.includes(variationId)) {
									variations.push(variationId);
								}
							});
		
							var productId = <?php echo $product_id; ?>;
							var baseCheckoutUrl = "<?php echo get_site_url() ?>/";
							var groupLabel = group.find("input[name='group_labels[" + groupId + "]']").val();
							var groupLabelAccount = group.find("input[name='custom_labels-account[" + groupId + "][]']").val();
							var url = baseCheckoutUrl + "?add-to-cart-variation=" + productId;
							var subscription_id = "<?php echo $subscription_id ?>";
							var itemid = "<?php echo $item_id; ?>";
							//var nonce = "<?php //echo wp_create_nonce( 'wcs_switch_request' ); ?>";
							var nonce = "<?php echo esc_js($nonce); ?>";
							if (variations.length) {
								url += "&variation_id=" + variations.join(",") + '&switch-subscription='+subscription_id+'&item='+itemid+'&_wcsnonce='+nonce+'&redirect_to_checkout=false';
							}
		
							if (groupLabel) {
								url += "&product_title=" + encodeURIComponent(groupLabel);
							}
		
							if (groupLabelAccount) {
								url += "&account_title=" + encodeURIComponent(groupLabelAccount);
							}
		
							if (groupId) {
								url += "&groupcount=" + encodeURIComponent(groupId);
							}
		
							var tempInput = $("<input>");
							$("body").append(tempInput);
							tempInput.val(url).select();
							document.execCommand("copy");
							tempInput.remove();
		
							alert("Product Link Copied: " + url);
						});
					});
				</script>
				<?php
			} else {
				echo '<p>No variable products found in this subscription.</p>';
			}
		}
	}

	public function add_custom_variation_label_field($loop, $variation_data, $variation) {
		woocommerce_wp_text_input(array(
			'id' => "custom_variation_label_{$loop}",
			'label' => __('Custom Label', 'woocommerce'),
			'value' => get_post_meta($variation->ID, '_custom_variation_label', true),
			'wrapper_class' => 'form-row form-row-full',
		));
	}

	public function save_custom_variation_label_field($variation_id, $i) {
		if (isset($_POST["custom_variation_label_{$i}"])) {
			update_post_meta($variation_id, '_custom_variation_label', sanitize_text_field($_POST["custom_variation_label_{$i}"]));
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscriptions_Upgrader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscriptions_Upgrader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/subscriptions-upgrader-admin.css?v=65', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscriptions_Upgrader_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscriptions_Upgrader_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/subscriptions-upgrader-admin.js', array( 'jquery' ), $this->version, false );

	}

}

// Hook to add the custom meta box for the 'shop_subscription' post type
add_action('add_meta_boxes', 'add_subscription_renewal_orders_meta_box');

function add_subscription_renewal_orders_meta_box() {
    add_meta_box(
        'subscription_renewal_orders_',             // Meta box ID
        'Related Orders',                          // Title
        'display_subscription_renewal_orders',      // Callback function
        'shop_subscription',                       // Post type (Shop Subscription)
        'normal',                                  // Context (Position in the admin panel)
        'high'                                     // Priority
    );
}

function getlast_order_id_by_subscription($subscription_id) {
    // Get the subscription object using the subscription ID
    $subscription = wcs_get_subscription($subscription_id);
    
    if ( ! $subscription ) {
        return null; // If no subscription found
    }

    // Get all related orders (initial + renewals)
    $related_orders = $subscription->get_related_orders();

    if ( ! empty( $related_orders ) ) {
        // Sort the orders by the date created (newest first)
        usort($related_orders, function($a, $b) {
            $order_a = wc_get_order($a);
            $order_b = wc_get_order($b);
            return $order_b->get_date_created()->getTimestamp() - $order_a->get_date_created()->getTimestamp();
        });

        // The first item in the sorted array is the most recent order
        $last_order_id = $related_orders[0];
        
        // Return the ID of the last order
        return $last_order_id;
    }

    return null; // No related orders found
}

function find_variation_match($variation_id, $psettings) {
	
	if(empty($psettings)){
		return null;
	}
	
    foreach ($psettings as $group) {
        foreach ($group['variations_account'] as $account) {
				if (in_array($variation_id, $account['account_variation_ids'])) {
					
					return [
						'group_label' => $group['group_label'],
						'account_label' => $account['account_label'],
						'variation-group-switch' => $account['variation-group-switch'],
						'account_variation_ids' => $account['account_variation_ids'],
					];
				}
        }
    }

    return null; 
}


add_action('save_post_shop_subscription', 'save_group_label_hidden_fields', 10, 3);
function save_group_label_hidden_fields($post_id, $post, $update) {
    // Basic checks
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== 'shop_subscription') return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    // Get last order ID and subscription object
    $last_order_id = getlast_order_id_by_subscription($post_id);
    $subscription = wcs_get_subscription($post_id);
    
    if (empty($subscription)) return;

    // Get previous saved variation IDs as array (stored as serialized maybe)
    $last_variation_ids = get_post_meta($post_id, 'last_variation_ids', true);
    if (!is_array($last_variation_ids)) {
        $last_variation_ids = [];
    }
    
    // Get current variation IDs from subscription
    $current_variation_ids = [];
    foreach ($subscription->get_items() as $item_id => $item) {
        $variation_id = $item->get_variation_id();
        if ($variation_id > 0) {
            $current_variation_ids[] = $variation_id;
        }
    }

    // Determine if subscription items changed (added, removed, or changed)
    $items_changed = (count(array_diff($last_variation_ids, $current_variation_ids)) > 0) || (count(array_diff($current_variation_ids, $last_variation_ids)) > 0);

    // Save current variation IDs for future comparison
    update_post_meta($post_id, 'last_variation_ids', $current_variation_ids);

    // Get the old next payment date
    $old_next_payment_date_str = $subscription->get_date('next_payment'); 
    $old_next_payment_date = $old_next_payment_date_str ? new DateTime($old_next_payment_date_str) : null;

    // Get new next payment date from POST (admin input)
    $new_next_payment_date_str = !empty($_POST['next_payment']) ? sanitize_text_field($_POST['next_payment']) : null;
    $new_next_payment_hour = isset($_POST['next_payment_hour']) ? intval($_POST['next_payment_hour']) : 0;
    $new_next_payment_minute = isset($_POST['next_payment_minute']) ? intval($_POST['next_payment_minute']) : 0;

    $new_next_payment_datetime = null;
    if ($new_next_payment_date_str) {
        $new_next_payment_datetime = DateTime::createFromFormat('Y-m-d H:i:s', sprintf('%s %02d:%02d:00', $new_next_payment_date_str, $new_next_payment_hour, $new_next_payment_minute));
        if (!$new_next_payment_datetime) {
            // fallback if format fails
            $new_next_payment_datetime = new DateTime($new_next_payment_date_str);
            $new_next_payment_datetime->setTime($new_next_payment_hour, $new_next_payment_minute, 0);
        }
        
        // Update expiration_date, calculated_end_date, and switch_base_date when admin changes next_payment
        if ($new_next_payment_datetime && $old_next_payment_date && $new_next_payment_datetime != $old_next_payment_date) {
            $expiration_date = $new_next_payment_datetime->format('Y-m-d H:i:s');
            $expiration_date_formatted = $new_next_payment_datetime->format('Y-m-d'); // Format for custom expiration date field
            $calculated_end_timestamp = $new_next_payment_datetime->getTimestamp() + (24 * 60 * 60); // Add 1 day
            $calculated_end_date = date('Y-m-d H:i:s', $calculated_end_timestamp);
            
            // Update all expiration-related meta fields
            update_post_meta($post_id, 'expiration_date', $expiration_date);
            update_post_meta($post_id, 'calculated_end_date', $calculated_end_date);
            update_post_meta($post_id, '_custom_expiration_date', $expiration_date_formatted); // Update custom expiration date field
            update_post_meta($post_id, '_schedule_trial_end', $expiration_date);
            
            // Update switch_base_date to match next_payment date (for resubscribe calculations)
            update_post_meta($post_id, 'switch_base_date', $expiration_date);
            
            // Update timestamp meta
            $expiration_timestamp_gmt = strtotime(get_gmt_from_date($expiration_date));
            update_post_meta($post_id, '_schedule_trial_end_timestamp_gmt', $expiration_timestamp_gmt);
            update_post_meta($post_id, '_schedule_next_payment_timestamp_gmt', $expiration_timestamp_gmt);
        }
    }

    // Process subscription items as before (your existing code for group label etc.)
    foreach ($subscription->get_items() as $item_id => $item) {
        $prod_id = $item->get_product_id();
        $variation_id = $item->get_variation_id();
        $psettings = get_post_meta($prod_id, '_custom_variation_groups', true);
        $matched_array = find_variation_match($variation_id, $psettings);
        
        if ($matched_array) {
            // Group label handling
            $switch_value = $matched_array['variation-group-switch'];
            
            foreach ($psettings as $key => $group) {
                if ($key + 1 == $switch_value && isset($group['group_label'])) {
                    update_post_meta($last_order_id, '_custom_product_title', sanitize_text_field($group['group_label']));
                    update_post_meta($last_order_id, '_group_count_subscription', intval($key + 1));
                }
            }

            if ($switch_value == '') {
                update_post_meta($last_order_id, '_custom_product_title', get_the_title($variation_id));
            }

            $lastvariation_id = get_post_meta($post_id, 'last_variation_id', true);
            if ($lastvariation_id == '') {
                update_post_meta($post_id, 'last_variation_id', $variation_id);
            }

            if ($variation_id > 0 && $lastvariation_id != $variation_id) {
                update_post_meta($post_id, 'last_variation_id', $variation_id);
                $variation = wc_get_product($variation_id);
                if ($variation && $variation->is_type('variation')) {
                    $variation_attributes = $variation->get_attributes();
                    $attributes_text = [];
                    foreach ($variation_attributes as $attribute_value) {
                        $attributes_text[] = $attribute_value;
                    }

                    // Determine years to add based on attributes
                    $years_to_add = 0;
                    if (in_array('1 Year', $attributes_text) || in_array('Intellicut Plus 1 year', $attributes_text) || in_array('Intellicut AeroCut nano Edition 1 Year', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus Year', $attributes_text) || in_array('VRCut Plus 1 year', $attributes_text) || in_array('Bizcard Plus 1 year', $attributes_text)) {
                        $years_to_add = 1;
                    } elseif (in_array('2 Years', $attributes_text) || in_array('Intellicut Plus 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 2 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 2 Years', $attributes_text) || in_array('VRCut Plus 2 years', $attributes_text) || in_array('Bizcard Plus 2 years', $attributes_text)) {
                        $years_to_add = 2;
                    } elseif (in_array('3 Years', $attributes_text) || in_array('Intellicut Plus 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 3 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 3 Years', $attributes_text) || in_array('VRCut Plus 3 years', $attributes_text) || in_array('Bizcard Plus 3 years', $attributes_text)) {
                        $years_to_add = 3;
                    } elseif (in_array('4 Years', $attributes_text) || in_array('Intellicut Plus 4 years', $attributes_text) || in_array('Intellicut AeroCut nano Edition 4 Years', $attributes_text) || in_array('Intellicut AeroCut nano Edition Plus 4 years', $attributes_text) || in_array('VRCut Plus 4 years', $attributes_text) || in_array('Bizcard Plus 4 years', $attributes_text)) {
                        $years_to_add = 4;
                    } elseif (in_array('Sign up', $attributes_text)) {
                        update_post_meta($last_order_id, 'product_id', $variation_id);                    
                    }
                    
                    if ($years_to_add > 0 && $items_changed) { // Only if items changed
                        // IMPORTANT: Use different timing for the field updates
                        // This prevents conflicts with the admin form submission
                        add_action('shutdown', function() use ($subscription, $post_id, $last_order_id, $years_to_add, $old_next_payment_date, $new_next_payment_datetime) {
                            if (!$old_next_payment_date) return;

                            // If user manually changed next payment date to earlier, do NOT push forward
                            if ($new_next_payment_datetime && $new_next_payment_datetime < $old_next_payment_date) {
                                // Use user's date
                                $formatted_date = $new_next_payment_datetime->format('Y-m-d H:i:s');
                            } else {
                                // Add years to old next payment date
                                $expiration_date = clone $old_next_payment_date;
                                $expiration_date->modify("+{$years_to_add} years");
                                $formatted_date = $expiration_date->format('Y-m-d H:i:s');
                            }
                            
                            // Save to order meta
                            update_post_meta($last_order_id, 'variation_date', $formatted_date);
                            update_post_meta($post_id, 'expiration_date', $formatted_date);
                            
                            // Update the form fields
                            update_post_meta($post_id, '_schedule_next_payment', $formatted_date);
                            update_post_meta($post_id, '_schedule_trial_end', $formatted_date);
                            
                            $timestamp = strtotime($formatted_date);
                            update_post_meta($post_id, '_schedule_next_payment_timestamp_gmt', $timestamp);
                            update_post_meta($post_id, '_schedule_trial_end_timestamp_gmt', $timestamp);
                            
                            // Update post modified times
                            global $wpdb;
                            $wpdb->update(
                                $wpdb->posts, 
                                array(
                                    'post_modified' => current_time('mysql'),
                                    'post_modified_gmt' => current_time('mysql', 1)
                                ),
                                array('ID' => $post_id)
                            );
                            
                            clean_post_cache($post_id);
                            
                            do_action('woocommerce_subscription_date_updated', $subscription, 'next_payment', $formatted_date, $subscription->get_date('next_payment'));
                            do_action('woocommerce_subscription_date_updated', $subscription, 'trial_end', $formatted_date, $subscription->get_date('next_payment'));
                        }, 999);
                    }
                }
            }
        }
    }
}


// Callback function to display the content inside the meta box
function display_subscription_renewal_orders($post) {
	$subscription = wcs_get_subscription($post->ID);
	$related_orders = $subscription->get_related_orders();

	if (!empty($related_orders)) {
		$related_orders = array_reverse($related_orders);
	}

	$new_related_order = get_post_meta($post->ID, '_custom_related_orders', true);

	if (!empty($new_related_order) && is_array($new_related_order)) {
		$valid_orders = [];
		foreach ($new_related_order as $order_id) {
			if (wc_get_order($order_id)) {
				$valid_orders[] = $order_id;
			}
		}
		if (!empty($valid_orders)) {
			$related_orders = array_merge($related_orders, $valid_orders);
		}
	}

	$related_orders = array_unique($related_orders);

	if (empty($related_orders)) {
		$related_orders = [];
	}

    ?>
    <style>
		#subscription_renewal_orders {
			display:none !important;
		}
		#add_order_modal {
            display: none;
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding-top: 100px;
            text-align: center;
        }

        #add_order_modal > div {
            background: white;
            padding: 20px;
            width: 50%;
            margin: 0 auto;
        }
	</style>
    <div class="woocommerce_subscriptions_related_orders">
        <table>
            <thead>
                <tr>
                    <th><?php esc_html_e('Order Number', 'woocommerce-subscriptions'); ?></th>
                    <th><?php esc_html_e('Relationship', 'woocommerce-subscriptions'); ?></th>
                    <th><?php esc_html_e('Date', 'woocommerce-subscriptions'); ?></th>
                    <th><?php esc_html_e('Status', 'woocommerce-subscriptions'); ?></th>
                    <th><?php echo esc_html_x('Total', 'table heading', 'woocommerce-subscriptions'); ?></th>
					<th><?php esc_html_e('Add Order', 'woocommerce-subscriptions'); ?></th>
				</tr>
            </thead>
            <tbody>

                <?php if (!empty($related_orders)) : ?>
                    <?php foreach ($related_orders as $order_id) :
                        $related_order = wc_get_order($order_id);
                        if ($related_order) :
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($related_order->get_id())); ?>">
                                        #<?php echo esc_html($related_order->get_order_number()); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 

									$is_renewal = $related_order->get_meta('_subscription_renewal');

									if ($is_renewal) {
										esc_html_e('Renewal Order', 'woocommerce-subscriptions');
									} else {
										esc_html_e('Parent order', 'woocommerce-subscriptions');
									}
								
									?>
                                </td>
                                <td>
                                    <?php 
                                    $date_created = $related_order->get_date_created();
                                    if ($date_created) :
                                        $order_date = $date_created->date('Y/m/d g:i:s A');
                                    ?>
                                        <abbr title="<?php echo esc_attr($order_date); ?>">
                                            <?php echo esc_html(human_time_diff(strtotime($order_date), current_time('timestamp'))); ?> ago
                                        </abbr>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <mark class="order-status status-<?php echo esc_attr($related_order->get_status()); ?>">
                                        <span><?php echo esc_html(wc_get_order_status_name($related_order->get_status())); ?></span>
                                    </mark>
                                </td>
                                <td>
                                    <span class="amount"><?php echo wp_kses_post($related_order->get_formatted_order_total()); ?></span>
                                </td>
								<td>
                                    <input type="button" class="button add_related_order" data-order-id="<?php echo esc_attr($order_id); ?>" value="Add Order">
                                </td>
                            </tr>
                            <?php
                        endif;
                    endforeach;
                else : ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e('No related orders found for this subscription.', 'woocommerce-subscriptions'); ?></td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>
	<div id="add_order_modal">
        <div>
            <h4 style="margin-bottom:8px;"><?php esc_html_e('Add a Related Order', 'woocommerce-subscriptions'); ?></h4>
			<span class="errormsg" style="display:none;margin-bottom:8px;"></span>
            <form id="add_related_order_form" method="post">
                <input type="hidden" id="related_order_id" name="related_order_id" />
                <label for="order_id"><?php esc_html_e('Order ID:', 'woocommerce-subscriptions'); ?></label>
                <input type="number" id="orderid" name="orderid" placeholder="<?php esc_attr_e('Enter Order ID', 'woocommerce-subscriptions'); ?>" />
                <input type="button" class="add_related_order_form button-primary" value="<?php esc_attr_e('Add Order', 'woocommerce-subscriptions'); ?>" class="button" />
                <button type="button" id="close_modal" class="button"><?php esc_html_e('Cancel', 'woocommerce-subscriptions'); ?></button>
            </form>
        </div>
    </div>
	<script>

	jQuery(document).ready(function($) {

		$('.add_related_order').on('click', function() {
			var order_id = $(this).data('order-id');
			$('#related_order_id').val(order_id);
			$('#add_order_modal').fadeIn();
		});

		$('#close_modal').on('click', function() {
			$('#add_order_modal').fadeOut();
		});

		$('.add_related_order_form').on('click', function(event) {
			event.preventDefault();
			
			var order_id = $('#orderid').val();
			if(order_id == '') {
				jQuery('.errormsg').html('Order ID is Required Field!');
				jQuery('.errormsg').fadeIn();
				jQuery('.errormsg').css('color','red');
				jQuery('.errormsg').css('display','block');
				setTimeout(function() {
					$('.errormsg').fadeOut(1000);
				}, 3000);
				return false;
			}
			var subscription_id = <?php echo $subscription->get_id(); ?>;
			var related_order_id = $('#related_order_id').val();

			if (order_id && related_order_id) {
				var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
				$.ajax({
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'add_related_order',
						subscription_id: subscription_id,
						order_id: order_id,
						related_order_id: related_order_id
					},
					success: function(response) {
						console.log(response);
						if (response) {
						    location.reload();
						}
					}
				});
			}
		});
	});
	</script>

    <?php
}

function handle_add_related_order() {
    $subscription_id = isset($_POST['subscription_id']) ? absint($_POST['subscription_id']) : 0;
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $related_order_id = isset($_POST['related_order_id']) ? absint($_POST['related_order_id']) : 0;

    if ($subscription_id && $related_order_id) {
        $subscription = wcs_get_subscription($subscription_id);
		$order = wc_get_order($related_order_id);
        if ($subscription) {
            $existing_related_orders = $subscription->get_meta('_custom_related_orders', true) ?: [];
            if (!in_array($related_order_id, $existing_related_orders)) {
                $existing_related_orders[] = $related_order_id;
            }
            if (!in_array($order_id, $existing_related_orders)) {
                $existing_related_orders[] = $order_id;
            }
            $subscription->update_meta_data('_custom_related_orders', $existing_related_orders);
            $subscription->save();
            echo true;
        } else {
           echo false;
        }
    } 
	wp_die();
}

add_action('wp_ajax_add_related_order', 'handle_add_related_order');


add_action('woocommerce_process_shop_subscription_meta', function($post_id, $post) {
    if (!empty($_POST['trial_end'])) {

        $trial_end_date = sanitize_text_field($_POST['trial_end']); // e.g., 2031-09-29
        $hour = isset($_POST['trial_end_hour']) ? intval($_POST['trial_end_hour']) : 0;
        $minute = isset($_POST['trial_end_minute']) ? intval($_POST['trial_end_minute']) : 0;

        // Format full datetime string (with optional +5 seconds if needed)
        $datetime_str = sprintf('%s %02d:%02d:05', $trial_end_date, $hour, $minute); // +5s to avoid 1-year Woo bug
        $timestamp = strtotime($datetime_str);

        if ($timestamp) {
            $formatted = date('Y-m-d H:i:s', $timestamp);
            update_post_meta($post_id, '_schedule_trial_end', $formatted);
            
            // Also update expiration_date, calculated_end_date, and switch_base_date when trial_end is manually updated
            update_post_meta($post_id, 'expiration_date', $formatted);
            $calculated_end_timestamp = $timestamp + (24 * 60 * 60); // Add 1 day
            $calculated_end_date = date('Y-m-d H:i:s', $calculated_end_timestamp);
            update_post_meta($post_id, 'calculated_end_date', $calculated_end_date);
            update_post_meta($post_id, 'switch_base_date', $formatted); // Update switch_base_date to match trial_end
            
            // Update custom expiration date field
            update_post_meta($post_id, '_custom_expiration_date', date('Y-m-d', $timestamp));
            
            // Update timestamp meta
            $trial_end_timestamp_gmt = strtotime(get_gmt_from_date($formatted));
            update_post_meta($post_id, '_schedule_trial_end_timestamp_gmt', $trial_end_timestamp_gmt);
        }
    }
}, 10, 2);



