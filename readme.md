# WP_AJAX

<p align="center"><img src="https://c1.staticflickr.com/1/415/31850480513_6cf2b5bdde_b.jpg"></p>

### A simple class for creating concise WordPress AJAX actions

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

Introduction: [Medium Post](https://medium.com/@AnthonyBudd/wp-model-6887e1a24d3c)

***

### Features

* Simple to use
* Automatiocaaly dies when finished
* Lots of useful helpers


***

### Installation

Require WP_Model with composer

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

### Register
Before you can create a post you will need to register the post type. You can do this by calling the inherited static method register() in your functions.php file.
Optionally, you can also provide this method with an array of arguments, this array will be sent directly to the second argument of Wordpress's [register_post_type()](https://codex.wordpress.org/Function_Reference/register_post_type) function.
```php
Product::register();

Product::register([
    'singular_name' => 'Product'
]);
```

***

### Creating and saving models
You can create a model using the following methods.
```php
$product = new Product();
$product->color = 'white';
$product->weight = 300;
$product->title = 'the post title';
$product->content = 'the post content';
$product->save();

$product = new Product([
    'color' => 'blue',
    'weight' => '250'
]);
$product->save();

$product = Product::insert([
    'color' => 'blue',
    'weight' => '250'
]);
```

***

## Retrieving Posts
### Find()

find() will return an instanciated model if a post exists in the database with the ID if a post cannot be found it will return NULL.

```php
$product = Product::find(15);
```

findorFail() will throw an exception if a post of the correct type cannot be found in the database.

```php
try {
    $product = Product::findorFail(15);
} catch (Exception $e) {
    echo "Product not found.";
}
```

### All()

all() will return all posts. Use with caution.

```php
$allProducts = Product::all();
```

### In()

To find multiple posts by ID you can us the in() method.

```php
$firstProducts = Product::in(1, 2, 3, 4);
$firstProducts = Product::in([1, 2, 3, 4]);
```

### Where()

where() is a simple interface into WP_Query, the method can accept two string arguments (meta_value and meta_key). For complex queries supply the method with a single array as the argument. The array will be automatically broken down into tax queries and meta queries, WP_Query will then be executed and will return an array of models.

```php
$greenProducts = Product::where('color', 'green');

$otherProducts = Product::where([
    [
        'key' => 'color',
        'value' => 'green',
        'compare' => '!='
    ],[
        'taxonomy' => 'category',
        'terms' => ['home', 'garden']
    ]
]);
```

### Custom Finders

The finder() method allows you to create a custom finder method.
To create a custom finder first make a method in your model named your finders name and prefixed with '_finder' this method must return an array. The array will be given directly to the constructer of a WP_Query. The results of the WP_Query will be returned by the finder() method.

If you would like to post-process the results of your custom finder you can add a '_postFinder' method. This method must accept one argument which will be the array of found posts.

```php

Class Product extends WP_Model
{
    ...

    public function _finderHeavy()
    {  
        return [
            'meta_query' => [
                [
                    'key' => 'weight',
                    'compare' => '>',
                    'type' => 'NUMERIC',
                    'value' => '1000'
                ]
            ]
        ];
    }

    // Optional
    public function _postFinderHeavy($results)
    {  
        return array_map(function($model){
            if($model->color == 'green'){
                return $model->color;
            }
        }, $results);
    }
}

$heavyProducts = Product::finder('heavy');
```

***

## Taxonomies

If you would like to have any taxonomies loaded into the model, add the optional public property $taxonomies (array of taxonomy slugs) to the class.
```php
Class Product extends WP_Model
{
    ...
    public $taxonomies = [
        'category',
    ];
}
```

You can set a models taxonomies by providing it in the array when instantiating the model, this array can be a combination of term slugs or term _ids.
The model's terms can be accesed by getting the property named the taxonimy name. 
```php
$product = Product::insert([
    'title' => 'product',
    'color' => 'blue',
    'weight' => '250',
    'category' => ['home', 3]
]);

$product->category; // ['Home', 'Office'];
```

If you want direct access to the taxonomy objects you can do this by using the getTaxonomy() method. The first argument is the taxonomy name, the second argument is optional and it is the property to be extracted from the term object. Not providing the second argument will return WP_Term objects.

```php
$product->getTaxonomy('category'); // [WP_Term, WP_Term];
$product->getTaxonomy('category', 'term_id'); // [2, 3];
$product->getTaxonomy('category', 'name'); // ['Home', 'Office'];
```

You can add a taxonomy by using the addTaxonomy() method. The first argument is the taxonomy name, the second argument can either be the term_id (must be an integer) or the term slug (must be provided as a string). If the term could not be found the method will return FALSE.

If you want to add multiple terms to a model you can use the addTaxonomies() method. The second argument must be an array of term slugs and/or term_ids.

```php
$product->addTaxonomy('category', 'home');
$product->addTaxonomy('category', 3);

$product->addTaxonomies('category', ['home', 'office']);
$product->addTaxonomies('category', ['home', 3]);
$product->addTaxonomies('category', [2, 3]);
```

To remove terms from the model you can use the removeTaxonomy() and removeTaxonomies() methods. These work in the same fashion as the addTaxonomy() and addTaxonomies() method as shown above.
```php
$product->removeTaxonomy('category', 'home');
$product->removeTaxonomy('category', 3);

$product->removeTaxonomies('category', ['home', 'office']);
$product->removeTaxonomies('category', [2, 3]);
```

To remove all terms associated to the model of the specified taxonomy use clearTaxonomy().
```php
$product->clearTaxonomy('category');
$product->getTaxonomy('category'); // [];
```

No change to the models taxonomies will be committed to the database untill you call the save() method.


***

### Delete()
delete() will trash the post.

```php
$product = Product::find(15);
$product->delete();
```

### hardDelete()
    hardDelete() will delete the post and set all of it's meta (in the database and in the object) to NULL.

```php
$product->hardDelete();
```

restore() will unTrash the post and restore the model. You cannot restore hardDeleted models.

```php
$product = Product::restore(15);
```

***

### Virtual Properties
If you would like to add virtual properties to your models, you can do this by adding a method named the virtual property's name prefixed with '_get'

```php

Class Product extends WP_Model
{
    ...

    public $virtual = [
        'humanWeight'
    ];

    public function _getHumanWeight()
    {  
        return $this->weight . 'Kg';
    }
}

$product = Product::find(15);
echo $product->humanWeight;
```

### Default Properties
To set default values for the attributes in your model use the $default property. The key of this array will be the attribute you wish to set a default value for and the value will be the default value.

```php

Class Product extends WP_Model
{
    ...

    publid $default = [
        'color' => 'black'
    ];
}

$product = new Product;
echo $product->color; // black
```

### Filter Properties
If you need a property to be parsed before the are returned you can use a filter method. You must add the attribute name to a array named $filter and create a method prefixed with ‘_filter’, this method must take one argument, this will be the property value.

```php

Class Product extends WP_Model
{
    ...

    publid $filter = [
        'weight'
    ];

    public function _filterWeight($value){
        return intVal($value);
    }
}

$product = Product::insert([
    'weight' => '250'
]);

echo $product->weight; // (int) 250
```

***

### Serialization

If you want to JSON encode a model and keep virtual properties you can do this by adding the property $serialize to the model.

Conversely, if you would like to hide a property you can do this by adding $protected to the model

```php

Class Product extends WP_Model
{
    ...

    public $serialize = [
        'humanWeight',
    ];

    public $protected = [
        'weight',
    ];

    public function _getHumanWeight()
    {  
        return $this->weight . 'Kg';
    }
}

$product = Product::find(15);
echo json_encode($product);
```

Result:
```json
{
    "ID":           15,
    "title":        "The post title",
    "content":      "The post content",
    "color":        "blue",
    "HumanWeight":  "250Kg"
}
```

***


### Events
WP_Model has an events system, this is the best way to hook into WP_Model's core functions. All events with the suffix -ing fire as soon as the method has been called. All events with the suffix -ed will be fired at the very end of the method. Below is a list of available events. All events will be supplied with the model that triggered the event

You can also trigger the save, insert and delete events from the admin section of wordpress.

- booting
- booted
- saving
- inserting
- inserted
- saved
- deleting
- deleted
- hardDeleting
- hardDeleted
- patching
- patched

When saving a new model the saving, inserting, inserted and saved events are all fired (in that order).

```php
Class Product extends WP_Model
{
    ...
    
    public function saving(){
        echo "The save method has been called, but nothing has been written to the database yet.";
    }
    
    public function saved($model){
        echo "The save method has completed and the post and it's meta data have been updated in the database.";
        echo "The Model's ID is". $model->ID;
    }
}
```

***

### Patching
By calling the static methods patchable whenever a form is submitted with the field _model. It will automatically create a new model or update an existing model if the field _id is also present.

```php
Product::patchable();
```

```html
<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="_model" value="product">
    
    <!-- Omitting this will create a new model --> 
    <input type="hidden" name="_id" value="15">

    <input type="text" name="color" value="red">
    <input type="submit" value="Submit" name="submit">
</form>
```

***

### Helper Properties

The $new property will return true if the model has not been saved in the Database yet.

The $dirty property will return true if the data in the model is different from what's currently stored in the database.


```php
$product = new Product;
$product->new; // Returns (bool) true

$product = Product::find(15);
$product->new; // Returns (bool) false

$product->color = 'red';
$product->dirty; // Returns (bool) true
$product->save();
$product->dirty; // Returns (bool) false

$product->title; // Post title

$product->content; // Post content


```

### Helper Methods

```php
Product::single(); // Returns the current model if on a single page or in the loop

Product::exists(15); // Returns (bool) true or false

$product->get($attribute, $default) // Get attribute from the model

$product->set($attribute, $value) // Set attribute of the model

$product->post() // Returns WP_Post object

$product->permalink() // Returns post permalink

$product->hasFeaturedImage() // Returns TRUE if a featured image has been set or FALSE if not

$product->featuredImage($defaultURL) // Returns featured image URL

$product->toArray() // Returns an array representaion of the model

Product::asList() // Returns array of posts keyed by the post's ID
[
    15 => Product,
    16 => Product,
    17 => Product
]

// You can also specify the value of each element in the array to be meta from the model.
Product::asList('post_title')
[
    15 => "Product 1",
    16 => "Product 2",
    17 => "Product 3"
]
```