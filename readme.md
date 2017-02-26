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
You will then need to make a class that extends WP_Model. This class will need the public property $postType and $attributes, an array of strings.

If you need to prefix the model's data in your post_meta table add a public property $prefix. This will be added to the post meta so the attribute 'color' will be saved in the database using the meta_key 'wp_model_color'
```php
Class Product extends WP_Model
{
    public $postType = 'product';

    public $prefix = 'wp_model_';

    public $attributes = [
        'color',
        'weight'
    ];
}
```

***

### Listen
Before you can create a post you will need to register the post type. You can do this by calling the inherited static method register() in your functions.php file.
Optionally, you can also provide this method with an array of arguments, this array will be sent directly to the second argument of Wordpress's [See wp_ajax_(action)](https://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_(action)) function.
```php
ExampleAJAX::listen();
```

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

$this->is('POST'); // Returns (bool) 

$this->is(['POST', 'PUT']); // Returns (bool) 

$this->requestType(); // Returns 'PUT', 'POST', 'GET', 'DELETE' depending on http request type 
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
            
            if( $this->is(['POST', 'put']) ){
                $post['post_content'] = 'This requiest was either POST or PUT';
            }else if( $this->is('get') ){
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