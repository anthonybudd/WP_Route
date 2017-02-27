# WP_AJAX

<p align="center"><img src="https://c1.staticflickr.com/1/415/31850480513_6cf2b5bdde_b.jpg"></p>

### A simple class for creating concise WordPress AJAX actions

```php

Class ExampleAJAX extends WP_AJAX
{
    protected $action = 'example';

    protected function run(){
        echo "Sucess!";
    }
}

ExampleAJAX::listen();

// http://example.com/wp-admin/admin-ajax.php?action=example

```

Introduction: [Medium Post](https://medium.com/@AnthonyBudd/wp-model-6887e1a24d3c)

***

### Features

* Simple to use
* Automatiocaaly dies when finished
* Lots of useful helpers


***

### Installation

Require WP_AJAX with composer

```
$ composer require anthonybudd/WP_AJAX
```

#### Or

Download the WP_AJAX class and require it at the top of your functions.php file.

```php
    require 'src/WP_AJAX.php';
```

***

### Setup
You will need to create a new class that extends WP_AJAX. This class must have one protected property called $action and one protected method named run(). $action will be the AJAX action name [See wp_ajax_(action)](https://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_(action)).
```php
Class Example extends WP_AJAX
{
    protected $action = 'example';
    
    protected function run(){
        echo "Success!";
    }
}
```

***

### Listen
Next you have to call the static method listen(). This will create all of the hooks so WordPress knows to call the run() method when the correct AJAX endpoint is hit. Note: You will need to call the listen() method for each of your AJAX actions.
```php
ExampleAJAX::listen();
```

***

### JSON Response

```php
Class ExampleAJAX extends WP_AJAX{
    ..

    protected function run(){
        $post5 = get_post(5);

        $this->JSONResponse($post5);
    }
}

```

***

### Helper Methods

```php
$this->isLoggedIn(); // Returns (bool) if the current visitor is a logged in user.

$this->has($key); // Returns (bool) 

$this->get($key, $default = NULL); // Returns 

$this->requestType(); // Returns 'PUT', 'POST', 'GET', 'DELETE' depending on HTTP request type

$this->requestType('POST'); // Returns (bool) 

$this->requestType(['POST', 'PUT']); // Returns (bool)  
```

***

### Example
```php
Class CreatePost extends WP_AJAX
{
    protected $action = 'create_post';

    protected function run(){
        if($this->isLoggedIn()){
            $post = [
                'post_status' => 'publish'
            ];
            
            if( $this->requestType(['POST', 'put']) ){
                $post['post_content'] = 'This requiest was either POST or PUT';
            }else if( $this->requestType('get') ){
                $post['post_content'] = 'This requiest was GET';
            }

            $post['post_title'] = sprintf('This post was created by %s', $this->user->data->user_nicename);
            
            wp_insert_post($post);

            $this->JSONResponse($post);
        }
    }
}

CreatePost::listen();

// http://example.com/wp-admin/admin-ajax.php?action=create_post

```