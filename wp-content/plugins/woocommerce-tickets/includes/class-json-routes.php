<?php


class WP_Json{

     public $request;
     public  $namespace;
     public  $route;
      public $callback;
    public function __construct($request,$namespace,$route,$callback){
        
        $this->request =  $request;
        $this->namespace =  $namespace;
        $this->route =  $route;
        $this->callback =  $callback;
        add_action('rest_api_init', array($this,'initFunctionName'));
    }
    public function initFunctionName() {
 
 
      
       register_rest_route($this->namespace, $this->route, array(
          'methods' => $this->request,
          'callback' => $this->callback
        ));
      }



}




