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
	public $action;
	public $request;
	public $user;

	
	abstract public function run();

	public function __construct()
	{ 	
		if($this->isLoggedIn()){
			$this->user = 'GET CUR USER';
		}
	}

	public function boot()
	{ 	
		$class = get_called_class();
		$action = new $class;
		$action->run();
	}

	public static function listen(){
		$action = Self::getActionName();
		add_action("wp_ajax_nopriv_{$action}", [Self::getClassName(), 'boot']);
		add_action("wp_ajax_post_{$action}", [Self::getClassName(), 'boot']);
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
		if(isset($action->action)){
			return $action->action;
		}
	}


	// -----------------------------------------------------
	// Helpers
	// -----------------------------------------------------
	public function isLoggedIn(){

	}

}