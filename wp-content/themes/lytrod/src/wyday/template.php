<?php

function register_my_custom_submenu_page() {
    add_submenu_page( 'woocommerce', 'Wyday Data', 'Wyday Data', 'manage_options', 'wy-day-data', function(){

		$vrcut_json = new WP_Query(array(
			'meta_query' => array(
				array(
					'key' => 'jsonforwydayvrcut',
					'compare' => '=',
					'value' =>'jsonforwydayvrcut'
				)
			)
	  ));

	  $intellicut_json = new WP_Query(array(
		'meta_query' => array(
			array(
				'key' => 'jsonforwydayintellicut',
				'compare' => '=',
				'value' =>'jsonforwydayintellicut'
			)
		)
  	));

	  $intellicut_global_json = new WP_Query(array(
		'meta_query' => array(
			array(
				'key' => 'jsonforwydayintellicutglobal',
				'compare' => '=',
				'value' =>'jsonforwydayintellicutglobal'
			)
		)
  	));

		?>
<div class="spinner-wrapper">
	<div class="spinner-loader"></div>
	<div class="spinner-message">
	  		This may take a while.
	</div>
</div>
		

<form>
			<table class="form-table">
											<tbody>

					<tr>
						<th>
								<label for="copy_billing">Add JSON Data to VRCut</label>
							</th>
							<td>
						
									<a class="button" href="<?php echo site_url() . '/wp-admin/post.php?post=' . $vrcut_json->posts[0]->ID.' &action=edit' ?>">
										Go to post
									</a>
								
							
							
							</td>
						</tr>
						<tr>
							<th>
									<label for="copy_billing">Add JSON Data to Intellicut</label>
								</th>
								<td>
										<a class="button" href="<?php echo site_url() . '/wp-admin/post.php?post=' . $intellicut_json->posts[0]->ID.' &action=edit' ?>">
											Go to post
										</a>
								
								</td>
						</tr>

						<tr>
							<th>
									<label for="copy_billing">Add JSON Data to Intellicut Global</label>
								</th>
								<td>
										<a class="button" href="<?php echo site_url() . '/wp-admin/post.php?post=' . $intellicut_global_json->posts[0]->ID.' &action=edit' ?>">
											Go to post
										</a>
								
								</td>
						</tr>
						
				
					
					
						
									</tbody>
								</table>
		
</form>
<hr>
<form>
			<table class="form-table">
											<tbody>

				
						<tr>
							<th>
								<label for="clear_all_vrcut">Clear all VRCut Post Data</label>
							</th>
							<td>
								<button type="button" id="clear_all_vrcut" class="button ">Clear</button>
							
							</td>
						</tr>
						<tr>
							<th>
								<label for="clear_all_intellicut_global">Clear all Intellicut Global Post Data</label>
							</th>
							<td>
								<button type="button" id="clear_all_intellicut_global" class="button">Clear</button>
							
							</td>
						</tr>
						<tr>
							<th>
								<label for="clear_all_intellicut">Clear all Intellicut Post Data</label>
							</th>
							<td>
								<button type="button" id="clear_all_intellicut" class="button">Clear</button>
							
							</td>
						</tr>
					
					
						
									</tbody>
								</table>
		
</form>
<hr>
<form>
			<table class="form-table">
											<tbody>

				
						<tr>
							<th>
								<label for="add_vrcut_post_data">Add all VRCut Post Data</label>
							</th>
							<td>
								<a  class="button" id="add_vrcut_post_data" >Add</a>
							
							</td>
						</tr>
						<tr>
							<th>
								<label for="add_intellicut_global_post_data">Add all Intellicut Global Post Data</label>
							</th>
							<td>
								<a class="button"  id="add_intellicut_global_post_data" >Add</a>
							
							</td>
						</tr>
						<tr>
							<th>
								<label for="add_intellicut_post_data">Add all Intellicut Post Data</label>
							</th>
							<td>
								<button class="button" type="button" id="add_intellicut_post_data" >Add</button>
							
							</td>
						</tr>
					
					
						
									</tbody>
								</table>
		
</form>
<hr>
<form id="handleWyday">
			<table class="form-table">
											<tbody>

					

						<tr>
							<th scope="row">
								<label for="show_vrcut_post_data">Show VRCut Data</label>
							</th>
							<td>
								<select name="show_vrcut_post_data" id="show_vrcut_post_data">
								<option value="none" selected disabled  hidden>Select an Option</option>
								<option value="true" <?php echo (get_option('yes_no_vrcut')=='true')?'selected':''; ?>>Yes</option>
									<option value="false" <?php echo (get_option('yes_no_vrcut')=='false')?'selected':''; ?>>No</option>
								</select>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<label for="show_intellicut_data">Show Intellicut Data</label>
							</th>
							<td>
								<select name="show_intellicut_data" id="show_intellicut_data">
									<option value="none" selected disabled  hidden>Select an Option</option>
									<option value="true" <?php echo (get_option('yes_no_intellicut')=='true')?'selected':''; ?>>Yes</option>
									<option value="false" <?php echo (get_option('yes_no_intellicut')=='false')?'selected':''; ?> >No</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="show_intellicut_global_data">Show Intellicut Global Data</label>
							</th>
							<td>
								<select name="show_intellicut_global_data" id="show_intellicut_global_data">
								<option value="none" selected disabled  hidden>Select an Option</option>
									<option value="true" <?php echo (get_option('yes_no_intellicut_global')=='true')?'selected':''; ?>>Yes</option>
									<option value="false" <?php echo (get_option('yes_no_intellicut_global')=='false')?'selected':''; ?>>No</option>
								</select>
							</td>
						</tr>
					
						
									</tbody>
								</table>
		<button  class="button" type="submit">Submit</button>
</form>

<?php
	} ); 
}

add_action('admin_menu', 'register_my_custom_submenu_page',99);