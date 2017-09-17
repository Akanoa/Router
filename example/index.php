<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 17/09/2017
 * Time: 17:27
 */

namespace Noa\Router\Example;

use Noa\Router\Router;

require_once '../vendor/autoload.php';

$router = Router::getInstance();

// Simple GET route with closure without parameter as callable
$router->get('/test/closure', function (){
    return 'success closure';
});

// A pattern could match multiple HTTP verb, of course you can associate the same controller to all of them
$router->get('/test', 'Noa\Router\Example\DummyController#testGet');
$router->put('/test', 'Noa\Router\Example\DummyController#testPut');
$router->post('/test', 'Noa\Router\Example\DummyController#testPost');
$router->delete('/test', 'Noa\Router\Example\DummyController#testDelete');

// You can parametrized by adding a semi-colon before route part
// Thus all url like:
//  - /test/test
//  - /test/12
//  - /test/whatever
// Will match this route
$router->get('/test/:param', 'Noa\Router\Example\DummyController#testWithParameter');

// The same thing could be achieve with closure
$router->get('/test/closure/:param', function ($param){
    return 'success closure '.$param;
});

// Sometimes you want to match a route only if parameter match a specific regex, the with method allows to add constraints on parameter (route part beginning by ":")
// Those two routes have the same verb and the same pattern but are considered as different routes because constraint on :param2
// You can chain constraints as many as you want
$router->get('/test/:param/param/:param2', 'Noa\Router\Example\DummyController#testWithMoreParameterConstraint')
    ->with('param2', '[0-9]+')
    ->with('param', '[a-z]+');

$router->get('/test/:param/param/:param2', 'Noa\Router\Example\DummyController#testWithMoreParameter');

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