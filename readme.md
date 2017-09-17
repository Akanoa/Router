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
    
    use Noa\Router;
    
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

### How to define routes

A route is a group of three properties:
  - The HTTP verb matching with route
  - The path pattern of the route
  - The function to call in case of match
  
For example to create a route matching GET /test, you can proceed like this:

    $router->get('/test', function() {
        return 'success';
    });