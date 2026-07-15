<h1>Customize</h1>


<br>
<a href="<?php echo site_url() .'/wp-admin/customize.php?url=' . site_url() .'/my-account/submitaticket?customizerdash=yes'  ?>">
    Customize Ticket Dashboard
</a>
<br>
<a href="<?php echo site_url() .'/tickets-dashboard'  ?>">
    Ticket Dashboard
</a>


<form action="" id="AdminDashboard">

<?php
global $wp_roles;
$roles = $wp_roles->roles; 


?>




    <table class="form-table" role="presentation">
        <div v-if="formSuccess == true">
            <h3 style="color:red">Success</h3>
        </div>
        <tbody> 
          
            <tr>
                <th scope="row"><label for="blogname">Email Address for recieved tickets: </label></th>
                <td><input name="blogname" ref="email" type="text" id="blogname" value = "<?php echo get_option('woo_tickets_email');?>" class="regular-text" ></td>
            </tr>

            <tr>
                <th scope="row"><label for="blogdescription">Google Font family CDN:</label></th>
                <td>
                    <input name="blogdescription" ref = "google_font" type="text" value = "<?php echo get_option('tickets_font');?>" id="blogdescription" aria-describedby="tagline-description"class="regular-text">

                    <p class="description" id="tagline-description">Please copy and paste a font family from  <a href = "https://fonts.google.com/">Google Fonts</a> </p>
                </td>
            </tr>
            <tr>
                 <th scope="row">
                     <label for="blogdescription">Roles and Permissions:</label><br>
                        <a href="<?php echo site_url('/wp-admin/user-new.php'); ?>">Create User</a>
                        <small>Who can respond back to support tickets</small>
                    </th>
                 <td>
                    <select multiple id = "defaultRoles" >
                        <option  <?php echo (in_array('contributor',get_option('defaultRoles') ))?'selected':'' ?> value="contributor">Contributor</option>
                        <option  <?php  echo (in_array('author',get_option('defaultRoles') ))?'selected':'' ?> value="author">Author</option>
                        <option  <?php  echo (in_array('editor',get_option('defaultRoles') ))?'selected':'' ?>  value="editor">Editor</option>
                        <option   <?php  echo (in_array('administrator',get_option('defaultRoles') ))?'selected':'' ?>  value="administrator">Administrator</option>
                    </select>
                    </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="blogname"> 
                        Change Online Status:
                    </label>
                </th>
                <td>
                <select ref = "onlineStatus" >
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </td>
            </tr>

           
        </tbody>

        </table>
    <p class="submit">
      
    <button  @click= "handleAdminChanges" type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">Save Changes</button>
    </p>
    </form>