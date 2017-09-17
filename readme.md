[![Build Status](https://travis-ci.org/Akanoa/Router.svg?branch=master)](https://travis-ci.org/Akanoa/Router)
[![codecov](https://codecov.io/gh/Akanoa/Router/branch/master/graph/badge.svg)](https://codecov.io/gh/Akanoa/Router)
# Yet another Router
This a simple PHP Router handling complex route patterns

## Description
Provides a simple way to handle parametrized routes, with zero-dependency package.

## Installation

    composer require noa/router

## Usages
### How to create a Router

Router handles routes, you must create a router before addind route.

    <?php
    require_once vendor/autoload.php
    
    use Noa\Router\Router;
    
    $router = new Router();

#### Custom HTTP server vars

In order to match route with HTTP request, the router must know which *$_SERVER* fields contain the request method and the request URI

By default the router is configure like this:

    array(
        'method' => 'REQUEST_METHOD"
        'url'    => 'REQUEST_URI'
    );

In case your HTTP Server don't follow this configuration, you can easily redefine this array with your custom fields

    // Custom configuration
    $configuration = array(
        'method' => 'CUSTOM_REQUEST_METHOD"
        'url'    => 'REQUEST_URI'
    );
    
    // then create the router with custom configuration
    $router = new Router($configuration);

#### Get router instance

You can also get a Router instance through a static call, with or without custom configuration 

    $router = Router::getInstance($configuration);
    
When you want to destroy this instance to create a new one, just
    
    Router::destroy();

### How to define routes

A route is a group of three properties:
- The HTTP verb matching with route
- The path pattern of the route
- The function to call in case of match

You can define routes as many as you want.

For example purpose, here is a DummyController

    namespace Noa\Router\Example;


    class DummyController
    {
        public function testGet() {
             return 'success Get';
        }
    
        public function testPut() {
            return 'success Put';
        }
    
        public function testPost() {
            return 'success Post';
        }
    
        public function testDelete() {
            return 'success Delete';
        }
    
        public function testWithParameter($param) {
    
            return 'success '.$param;
        }
    
        public function testWithMoreParameter($param, $param2) {
    
            return 'success '.$param.':'.$param2;
        }
    
        public function testWithMoreParameterConstraint($param, $param2) {
    
            return 'success constraint '.$param.':'.$param2;
        }
    }
  
__Simple GET route__

This the simpler route possible

    $router->get('/test/closure', function (){
        return 'success closure';
    });
    
__Controller__

Out of closure, you can use class method as controller

The callable must follow this pattern: 

    \Namespace\Of\Class\ClassName#method

__Pattern matching following HTTP verb__
 
A pattern could match multiple HTTP verb, of course you can associate the same controller to all of them.

    $router->get('/test', 'Noa\Router\Example\DummyController#testGet');
    $router->put('/test', 'Noa\Router\Example\DummyController#testPut');
    $router->post('/test', 'Noa\Router\Example\DummyController#testPost');
    $router->delete('/test', 'Noa\Router\Example\DummyController#testDelete');

__Parametrized route__
 
You can parametrized by adding a semi-colon before route part.
Thus all url like:
- /test/test
- /test/12
- /test/whatever

Will match this route:


    $router->get('/test/:param', 'Noa\Router\Example\DummyController#testWithParameter');
        
The same thing could be achieve with closure.

    $router->get('/test/closure/:param', function ($param){
        return 'success closure '.$param;
    });

__Constraints on parameter__
        
Sometimes you want to match a route only if parameter match a specific regex, the with method allows to add constraints on parameter (route part beginning by "**:**")

Those two routes have the same verb and the same pattern but are considered as different routes because constraint on :param2.

You can chain constraints as many as you want.

    $router->get('/test/:param/param/:param2', 'Noa\Router\Example\DummyController#testWithMoreParameterConstraint')
        ->with('param2', '[0-9]+')
        ->with('param', '[a-z]+');
    
    $router->get('/test/:param/param/:param2', 'Noa\Router\Example\DummyController#testWithMoreParameter');

### Launch route matcher

The run method will call the route controller if the request URL and HTTP verb match with one the route

The return of *run* method will the controller return, feel free to do what you want with the return.

In this example we only echoing this return.

If non of the route matches, an exception is raised.

    try {
    
        echo $router->run();
        
    } catch (\Noa\Router\RouterException $e) {
    
        switch ($e->getCode()) {
            case \Noa\Router\RouterException::ROUTE_NOT_FOUND:
                // Some 404 page
                break;
            default:
                // Something else
                break;
        }
    }

All this code is available into example folder.

You can use PHP intern server by

    cd example
    php -S localhost:8082 -t .

Then you can request with curl or Postman to test route matching