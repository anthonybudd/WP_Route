<?php

/**
 * WP_AJAX
 *
 * A simple class for creating active
 * record, eloquent-esque models of WordPress Posts.
 *
 * @author     AnthonyBudd <anthonybudd94@gmail.com>
 */
Abstract Class WP_AJAX
{	
	protected $action;
	public $request;
	public $wp;
	public $user;

	
	abstract protected function run();

	public function __construct()
	{ 	
		global $wp;
		$this->wp = $wp;
		$this->request = $_REQUEST;

		if($this->isLoggedIn()){
			$this->user = wp_get_current_user();
		}
	}

	public function boot()
	{ 	
		$class = Self::getClassName();
		$action = new $class;
		$action->run();
		die();
	}

	public static function listen(){
		$actionName = Self::getActionName();
		$className = Self::getClassName();
		add_action("wp_ajax_{$actionName}", [$className, 'boot']);
		add_action("wp_ajax_nopriv_{$actionName}", [$className, 'boot']);
	}


	// -----------------------------------------------------
	// UTILITY METHODS
	// -----------------------------------------------------
	public static function getClassName()
	{
		return get_called_class();
	}

	public static function getActionName()
	{
		$class = Self::getClassName();
		$reflection = new ReflectionClass($class);
		$action = $reflection->newInstanceWithoutConstructor();
		if(!isset($action->action)){
			throw new Exception("Public property \$action not provied");
		}

		return $action->action;
	}

	// -----------------------------------------------------
	// JSONResponse
	// -----------------------------------------------------
	public function JSONResponse($data){
		header('Content-Type: application/json');
		echo json_encode($data);
	}

	// -----------------------------------------------------
	// Helpers
	// -----------------------------------------------------
	public function isLoggedIn(){
		return is_user_logged_in();
	}

	public function has($key){
		if(isset($this->request[$key])){
			return TRUE;
		}
		return FALSE;
	}

	public function get($key, $default = NULL){
		if($this->has($key)){
			return $this->request[$key];
		}

		return $default;
	}

	public function is($requestType){
		if(is_array($requestType)){
			return in_array($_SERVER['REQUEST_METHOD'], array_map('strtoupper', $requestType));
		}else{
			return ($_SERVER['REQUEST_METHOD'] === strtoupper($requestType));
		}
	}

	public function requestType(){
		return $_SERVER['REQUEST_METHOD'];
	}
}