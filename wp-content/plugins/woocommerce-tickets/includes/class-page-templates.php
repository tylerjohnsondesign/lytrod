<?php

class WP_Page {



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($page_title,$page_path) {

    $this->page_title  = $page_title;
    $this->page_path = $page_path;
    // $this->handleInsert();

    add_action('init',array($this,'handleInsert'));
    add_filter('page_template' ,  array($this, 'template_include'));
	}

  public function handleInsert()
  {

    

        
            $exist = new WP_Query(array(
              'post_type' => 'page',
              's' => $this->page_title
            ));

                  //  die();
          if($exist->found_posts == 0){
            //create
            $page1 = array();
            $page1['post_title']= $this->page_title;
            $page1['post_content']= $this->page_title;
            $page1['post_status'] = "publish";
            $page1['post_slug'] = $this->page_title;
            $page1['post_type'] = "page";

            $page1_id=wp_insert_post($page1);

          
          }else{
            //update or die()
          }	
      
          # code...  
  
  }




  public function template_include($template)
  {
      global $post;
      $page_slug= $post->post_name;
      

 
          if($page_slug == $this->page_title)
          {
              return plugin_dir_path( dirname( __FILE__ ) ) . $this->page_path;
          }else{
              return $template;
          }
  }


}
