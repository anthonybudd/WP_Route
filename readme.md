# WP_Route

<p align="center"><img src="https://ideea.co.uk/static/wp_route.png"></p>

### A simple way to make custom routes in WordPress.
WP_Route is a simple way to create custom routes in WordPress for listening for webhooks, oAuth callbacks and basic routing. WP_Route is a single class solution that does not require any set-up and supports route parameters and redirects.

## Introduction: **[Medium Post](https://medium.com/@AnthonyBudd/wp-route-a-simple-way-to-make-custom-routes-in-wordpress-5ab1b3063115)**

```php

WP_Route::get('flights',                        'listFlights');
WP_Route::post('flights/{flight}',              'singleFlight');
WP_Route::put('flights/{flight}/book/{date}',   'bookFlight');
WP_Route::delete('flights/{flight}/delete',     'deleteFlight');

WP_Route::any('flights/{flight}',   array('Class', 'staticMethod'));
WP_Route::patch('flights/{flight}', array($object, 'method'));
WP_Route::match(['get', 'post'],    'flights/{flight}/confirm', 'confirmFlight');
WP_Route::redirect('from/here',     '/to/here', 301);


```

# Installation

Require WP_Route with composer

```
$ composer require anthonybudd/WP_Route
```

**Or**

Download the WP_Route class and require it at the top of your functions.php file. This is not recommended. 

```php
require 'WP_Route/src/WP_Route.php';
```


# GET Started
Simply define a route by calling any of the static methods get(), post(), put(), patch(), delete() or any(). This will bind the specified URL to a callable. When a HTTP request bound for that URL is detected, WP_Route will call the callable. 

```php
WP_Route::get('flights', 'listFlights');

// http://example.com/flights
function listFlights(){
  
   // Your Code Here!  
  
}
```

# Parameters
If you need to extract route parameters from the request URI you can do this by wrapping the value to be extracted in curly brackets. The extracted values will be provided to the callable as function arguments as shown below.
```php
WP_Route::get('flights/{flight}', 'singleFlight');

function singleFlight($flight){
    echo $flight; // 1
}
```

# Methods
### get($route, $callable)
### any(), post(), put(), patch(), delete()
All of these methods are used for binding a specific route and HTTP request method to a callable. The method any() will bind a route to a callable but will be HTTP method agnostic.
```php

WP_Route::get('flights',           'listFlights');
WP_Route::post('flights/{flight}', array('FlightController', 'singleFlight'));

function listFlights(){
	// Your Code Here
}
  
Class FlightController{
	public static function singleFlight($flight){
		// Your Code Here
	}
}
```

### match($methods, $route, $callable)
If you want to bind a callable to multiple HTTP methods but you do not want to use any(), you can use match(). The first parameter must be an array of HTTP request methods. The arguments $route and $callable work the same as get().
```php
WP_Route::match(['get', 'post'], 'flights/{flight}/confirm', 'confirmFlight');

function confirmFlight($flight){
	// Your Code Here
}
```

### redirect($route, $redirect, $code = 301)
The redirect() method will redirect the user to the argument $redirect when they navigate to the route. To set a custom HTTP response code use the 3rd argument $code.
```php
WP_Route::redirect('open-google', 'https://google.com', 301);
```


