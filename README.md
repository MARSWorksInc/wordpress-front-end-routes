# MarsPress FrontEndRoute
### Installation
Require the composer package in your composer.json with `marspress/front-end-route` with minimum `dev-main` OR run `composer require marspress/front-end-route`

### Usage
You will first need to create a Route Set. You can think of these like a Post Type and the Routes in the set are like the posts of that type.

#### Route Set
`new \MarsPress\FrontEndRoute\Route_Set()` takes 4 parameters, 1 required and 3 optional.
* Query Variable (required)(string)
  * A unique query variable that will be registered in the available Query Variables to the WP and WP_Query objects.
  * The query variable should only contain lowercase characters, hyphens, and underscores.
  * If your query var is already registered in WordPress, your Route Set will not be initiated fully within WordPress and an admin notice will be displayed to administrators in wp-admin screens. 
  * Resource: https://codex.wordpress.org/WordPress_Query_Vars
* Slug (optional)(string)
  * The slug for your route set without leading or proceeding slashes. E.g. `route/set`
  * This can be considered a Post Type Slug.
  * Defaults to the `query variable`.
* Body Class (optional)(string)
  * The CSS class that will be added to the rendered body tag.
  * Defaults to the `query variable`.
* Template (optional)(string)
  * The PHP template file to load for the route set.
  * This has to be an absolute path to the file on the server. E.g. `/app/wp-content/themes/twentytwentyone-child/templates/route_set.php`
  * Generally you should use the WordPress methods to get the base path and concatenate onto them:
    * `get_stylesheet_directory()`, use this for Child Themes;
    * `get_template_directory()`, use this for Parent Themes;
    * `plugin_dir_path(__FILE__)`, use this for Plugins.
  * Alternatively, you can just use the PHP constant `__DIR__`.
  * Defaults to the theme templates if they exist, otherwise, it will default to the Parent Theme `index.php`.

##### Available Methods
* `$routeSet->is_route();`
  * Will return the route slug if the current route is part of the set;
  * Returns false otherwise.
* `$routeSet->get_query_variable();`
  * Will return the query variable of the Route Set.

#### Route
`new \MarsPress\FrontEndRoute\Route()` takes 3 parameters, 2 required and 1 optional.
* Slug (required)(string)
  * A unique slug to the Route Set.
  * If the slug already exists in the Route Set, the Route will not be initiated into the set and an admin notice will be displayed to administrators in wp-admin screens.
* Title (required)(string)
  * The Route's title.
  * This will render into the html title tag.
* Template (optional)(string)
  * The PHP template file to load for the route.
  * See Route Set template parameter for further details.

#### Creating a Route Set and adding Routes to it
Given the Route Set:
```php
$routeSet = new \MarsPress\FrontEndRoute\Route_Set(
    'test_route',
    'route/set',
    'test-route-page',
);
```
```php
$routeSet->add_routes(
    new \MarsPress\FrontEndRoute\Route( 'this-is-a-route', 'This is a route' ),
    new \MarsPress\FrontEndRoute\Route( 'this-is-another-route', 'This is another route' ),
);
```
The above would create two routes: `/route/set/this-is-a-route` and `/route/set/this-is-another-route`

The `add_routes` method can take any number of parameters as long as they are a `Route` instance.

### Template Loading
The default template loading is that of WordPress', falling back to the Parent Theme's index.php file.

#### Default WordPress Theme Scope
Resource: https://developer.wordpress.org/themes/template-files-section/page-template-files/ 

The PHP files should reside in the root of your Parent or Child theme.

To use WordPress' template scope, the prefix used is `marspress-route-`, followed by the Route Set's query variable, then the Route's slug.

* `marspress-route-test_route.php` would load for all Routes in the `test_route` Route Set;
* `marspress-route-test_route-this-is-a-route.php` would load only for the `this-is-a-route` Route in the `test_route` Route Set;
* If neither of the above exist on the server, the PArent Theme's index.php will load.

#### Route Set Template Parameter
The Route Set class constructor can take an optional parameter for a template. This will load the given template for all the Routes in the set. This is regardless of the existence of the Theme templates listed above.

This is useful if you are using this dependency inside a plugin and need to load templates from the plugin directory. E.g. your template parameter would look something like this: `__DIR__ . '/templates/route_set.php'`

If the template file does not exist on the server, it will not be loaded and an admin notice will be displayed to administrators in wp-admin screens.

#### Route Template Parameter
The Route class constructor can take an optional parameter for a template. This will load the given template for the one Route. This will override all other template loading functionality.

This is useful if you want to load a specific template for a single Route. If you want a more structured and expandable templates loading of Routes and Route Sets, see the section `Advanced Template Loading Methodology` 

If the template file does not exist on the server, it will not be loaded and an admin notice will be displayed to administrators in wp-admin screens.

### Advanced Template Loading Methodology
Though you are able to load templates for specific Routes using the WordPress template scope such as `marspress-route-test_route-this-is-a-route.php`, it is recommended that you use the scope for the Route Set `marspress-route-test_route.php` and handle the Route template loading from inside that template.

Given the Route Set:
```php
$routeSet = new \MarsPress\FrontEndRoute\Route_Set(
    'test_route',
    'route/set',
    'test-route-page',
);
```
Add a PHP file to the root of your Child Theme, name the file `marspress-route-test_route.php`

Your PHP file should have this content:
```php
<?php
get_header();

if( $route = $routeSet->is_route() ){

    echo '<h1>This is a query var template</h1>';
    echo "<h2>The route is {$route}</h2>";

    $templatePart = get_stylesheet_directory() . "/template-parts/{$routeSet->get_query_variable()}/{$route}.php";
    if( file_exists( $templatePart ) ){

        require_once( $templatePart );

    }else{
    
        $defaultTemplatePart = get_stylesheet_directory() . "/template-parts/{$routeSet->get_query_variable()}/default.php";
        if( file_exists( $defaultTemplatePart ) ){
        
            require_once( $defaultTemplatePart );
        
        }
        
    }

}

get_footer();
```

With the above code, you can manage the Route templates within an organized directory. Given the above example, your route templates would go into `template-parts/test_route/<route-slug>.php` and if the Route's template does not exist in the given directory, it will try to load a default template: `template-parts/test_route/default.php`.

This method is useful if your Route Sets need to contain different html structures, and when the Routes need to inherit the Route Set structure.