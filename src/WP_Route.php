<?php

/**
 * WP_Route
 *
 * A simple class for binding
 * complex routes to functions
 * methods or WP_AJAX actions.
 *
 * @author     Anthony Budd
 */

final class WP_Route{

	private $hooked = FALSE;
	private $routes = array(
		'ANY' 		=> array(),
		'GET' 		=> array(),
		'POST' 		=> array(),
		'HEAD' 		=> array(),
		'PUT' 		=> array(),
		'DELETE' 	=> array(),
	);

    private function __construct(){}

	public static function instance(){
        static $instance = NULL;

        if($instance === NULL){
            $instance = new Self();
            $instance->hook();
        }

        return $instance;
    }


    // -----------------------------------------------------
	// CREATE ROUTE METHODS
	// -----------------------------------------------------
    public static function any($route, $callable){
    	$r = Self::instance();
    	$r->addRoute('ANY', $route, $callable);
    }

    public static function get($route, $callable){
    	$r = Self::instance();
    	$r->addRoute('GET', $route, $callable);
    }

    public static function post($route, $callable){
    	$r = Self::instance();
    	$r->addRoute('POST', $route, $callable);
    }

    public static function head($route, $callable){
    	$r = Self::instance();
    	$r->addRoute('HEAD', $route, $callable);
    }

    public static function put($route, $callable){
    	$r = Self::instance();
    	$r->addRoute('PUT', $route, $callable);
    }

    public static function delete($route, $callable){
    	$r = Self::instance();
    	$r->addRoute('DELETE', $route, $callable);
    }

    public static function match($methods, $route, $callable){
    	if(!is_array($methods)){
    		throw new Exception("\$methods must be an array");    		
    	}

    	$r = Self::instance();
    	foreach($methods as $method){
    		if(!in_array(strtoupper($method), array_keys($this->routes))){
    			throw new Exception("Unknown method {$method}");
    		}
    		
    		$r->addRoute(strtoupper($method), $route, $callable);
    	}
    }

    public static function redirect($route, $redirect, $code = 301){
    	$r = Self::instance();
    	$r->addRoute('ANY', $route, $redirect, array(
    		'code'     => $code,
    		'redirect' => $redirect,
    	));
    }


    // -----------------------------------------------------
	// INTERNAL UTILITY METHODS
	// -----------------------------------------------------
    private function addRoute($method, $route, $callable, $options = array()){
    	$this->routes[$method][] = (object) array_merge(array(
    		'route' 	=>  ltrim($route, '/'),
    		'callable'  =>  $callable,
    	), $options);
    }

    private function hook(){
    	if(!$this->hooked){
			add_filter('init', array('WP_Route', 'onInit'), 1, 0);
			$this->hooked = TRUE;
		}
    }

    public static function onInit(){
    	$r = Self::instance();
    	$r->handle();
    }

    private function getRouteParams($route){
    	$tokenizedRoute		 = $this->tokenize($route);
    	$tokenizedRequestURI = $this->tokenize($this->requestURI());
    	preg_match_all('/\{\s*.+?\s*\}/', $route, $matches);

    	$return = array();
    	foreach($matches[0] as $key => $match){
    		$search = array_search($match, $tokenizedRoute);
    		if($search !== FALSE){
    			$return[]  = $tokenizedRequestURI[$search];
    		}
    	}
    	
    	return $return;
    }


    // -----------------------------------------------------
    // GENERAL UTILITY METHODS
    // -----------------------------------------------------
    public static function routes(){
    	$r = Self::instance();
    	return $r->routes;
    }

    public function tokenize($url){
    	return array_filter(explode('/', ltrim($url, '/')));
    }

    public function requestURI(){
    	return ltrim($_SERVER['REQUEST_URI'], '/');
    }

    // -----------------------------------------------------
	// handle()
	// -----------------------------------------------------
        public function handle(){
        $method              = strtoupper($_SERVER['REQUEST_METHOD']);
        $routes              = array_merge($this->routes[$method], $this->routes['ANY']);  
        // only take path, ignore query string. remove slash from front and back. 
        // segments are created by splitting on slash
        // example: /this/should/work/ => Array ( [0] => this [1] => should [2] => work )
        // or example: /this/{anything}/work/ => Array ( [0] => this [1] => {anything} [2] => work ), with variable allowed

    	$requestURI 		 = trim(parse_url($this->requestURI(), PHP_URL_PATH), '/');
    	$tokenizedRequestURI = $this->tokenize($requestURI);

		// First, filter routes that do not have equal tokenized lengths
    	foreach($routes as $key => $route){
    		if(count($this->tokenize($route->route)) !== count($tokenizedRequestURI)){
    			unset($routes[$key]);
    			continue;
    		}    		
    	}

    	// add the tokenized route value
    	foreach($routes as $key => $route) {
    		$route->tokenized = $this->tokenize($route->route);
    	}

    	// assume no match
    	$match = false;

    	foreach($routes as $key => $route) {
	    	$match = array_reduce($tokenizedRequestURI, function($carry, $item) use($route) {
	    		static $i = -1;
	    		$i++;
	    		$route_token = $route->tokenized[$i];

	    		// if any match fails, carry will be false
	    		if ($carry === false) {
	    			return false;
	    		}

	    		// exact match returns the route as carry
	    		if ($item === $route_token) {
	    			return $route;
	    		}

    			// if this is a variable, let it pass through also
    			if (preg_match('/\{(.+)\}/', $route_token) === 1) {
    				return $route;
    			}

	    		// no match, no variable, returns false		
		    	return false;
	    	}, null);

	    	// on first match, exit
	    	if (is_object($match)) {
	    		break;
	    	}
	    }

	    // if a match was found, call it, and stop searching
	   	if (is_object($match) && is_callable($match->callable)) {
	   		return call_user_func_array($route->callable, $this->getRouteParams($route->route));
	   	}

    }
}
