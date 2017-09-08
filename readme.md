# WP_AJAX

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

# Introduction: [Medium Post](https://medium.com/@AnthonyBudd/wp-ajax-97d8f1d83e26#.pzyhw22zd)

***

### Features

* Simple to use
* Automatiocaaly dies when finished
* Lots of useful helpers


***

### Installation

Require WP_AJAX with composer

```
$ composer require anthonybudd/wp_ajax
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

If you would like to only allow signed in users to access your AJAX endpoint add the argument FALSE to the listen method.
```php
ExampleAJAX::listen(FALSE);
```

***

### JSON Response
If you want to respond to an AJAX request with data the JSONResponse() method will automatically set the content type header to ‘application/json’ and JSON encode the data provided to the method.

I am aware that WordPress has a function called [wp_send_json()](https://codex.wordpress.org/Function_Reference/wp_send_json) but, due to the fact that I know how much it annoys WP developers that I have included this method, I will not be removing it.

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
Example::url() // Returns the url of the ajax endpoint. Example http://ajax.local/wp/wp-admin/admin-ajax.php?action=example

$this->isLoggedIn(); // Returns TRUE or FALSE if the current visitor is a logged in user.

$this->has($key); // has() will return TRUE or FALSE if an element exists in the $_REQUEST array with a key of $key

$this->get($key, [ $default = NULL ]); // The get() method will return the specified HTTP request variable. If the variable does not exist it will return NULL by default. If you would like to set a custom string as the default, provide it as the second argument.

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
                $post['post_content'] = 'This request was either POST or PUT';
            }else if( $this->requestType('get') ){
                $post['post_content'] = 'This request was GET';
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
