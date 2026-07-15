<?php


class Custom_Route {


	public $route_name;

	public $query_name;

	public $route_path;
	
	public $params;

	public $forch_flush;

	public function __construct($route_name,$query_name,$route_path,$forch_flush) 
	{

	
		$this->route_name = $route_name;
		$this->query_name = $query_name;
		$this->route_path = $route_path;
		$this->forch_flush = $forch_flush;
		$this->query_name_array = $query_name;
	
	
			add_action('init' , array($this,'add_custom_rewrite'));

		

	
		add_filter('query_vars' ,  array($this, 'add_custom_query_vars'));
		add_action('template_include' , array($this, 'add_custom_template_include'));
		add_action('init', array($this,'change_permalinks_option'));

		add_action('after-switch-theme', array($this,'change_permalinks_option'));
	
		
	}

    public function add_custom_rewrite()
    {

		$str = '';
		$keys=1;
	   foreach ($this->query_name_array  as $value) {
			   $str .= $value .'=$matches['.$keys.']&';
			   $keys++;
	   
	   }
	   $regexRoute = '';
	   for ($i=0; $i < count($this->query_name_array); $i++) { 
		   # code...
		   $regexRoute .= '/([a-zA-Z0-9\_\-\%]+)';
	   }

		add_rewrite_rule( $this->route_name . $regexRoute .'[/]?$', 'index.php?'. $str , 'top' );
	
	
    }




    public function add_custom_query_vars($query_vars)
    {
			foreach ($this->query_name_array as $value) {
				$query_vars[] = $value;
			}
			
			return $query_vars;
    }


    public function add_custom_template_include($template)
    {

		$str = '';
		foreach ($this->query_name_array as $value) {
			$str .= get_query_var( $value ) == false || get_query_var( $value ) == '' . '&&';
		
		}

		if ( $str ) {
			return $template;
		}else{
				return  plugin_dir_path( dirname( __FILE__ ) ) . $this->route_path;
		}
    }



	public function change_permalinks_option()
	{
		if($this->forch_flush == true){
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure('/%postname%/');
			$wp_rewrite->flush_rules();
		}
	
	}



}
