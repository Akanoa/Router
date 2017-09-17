<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 07:16
 */

namespace Noa\Router\Test;

use PHPUnit\Framework\TestCase;
use Noa\Router\Router;

function test() {

}


class Test {

    public function test() {

        return 'success';
    }
}

class RouterTest extends TestCase {

    public function sendRequest($verb, $url) {

        $_SERVER['REQUEST_METHOD'] = $verb;
        $_SERVER['REQUEST_URI'] = $url;
    }

    /**
     * @param $object
     * @param $methodName
     * @param $args
     * @return mixed
     */
    public function invokeNotPublicMethod($object, $methodName, $args)
    {
        $reflexion = new \ReflectionObject($object);
        $method = $reflexion->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    /**
     * Throw exception on unauthorized method
     */
    public function testAddWrongRouteMethod()
    {

        $router = new Router();

        $this->expectException(\Noa\Router\RouterException::class);
        $this->expectExceptionCode(\Noa\Router\RouterException::INVALID_METHOD);

        $args = array('/api/test/get', 'test', "routeTest", 'WRONG');
        $this->invokeNotPublicMethod($router, 'add', $args);
    }

    /**
     * Adding a not named route, with callable as closure
     */
    public function testAddNotNamedRouteCallableAsClosure()
    {
        // Check route adding
        $router = new Router();
        $route = $router->get('/api/test/get', function () { return 'test content';}, null);

        $this->assertInstanceOf('Noa\Router\Route', $route);
        $this->assertEquals('api/test/get', $route->getPath());
        $this->assertEquals('test content', $route->getCallable()());

    }


    /**
     * Adding a not named route, with callable name as route name
     */
    public function testAddNotNamedRoute() {
        // Check route adding
        $router = new Router();
        $route = $router->get('/api/test/get', 'test', null);

        $this->assertInstanceOf('Noa\Router\Route', $route);
        $this->assertEquals('api/test/get', $route->getPath());
        $this->assertEquals('test', $route->getCallable());
        $this->assertArrayHasKey('test', $router->getNamedRoutes());
        $this->assertEquals($route, $router->getNamedRoutes()['test']);

    }

    /**
     * Adding a named route
     */
    public function testAddNamedRoute() {
        // Check route adding
        $router = new Router();
        $route = $router->get('/api/test/get', 'test', "routeTest");

        $this->assertInstanceOf('Noa\Router\Route', $route);
        $this->assertEquals('api/test/get', $route->getPath());
        $this->assertEquals('test', $route->getCallable());
        $this->assertArrayHasKey('routeTest', $router->getNamedRoutes());
        $this->assertEquals($route, $router->getNamedRoutes()['routeTest']);

    }

    public function testGetRoutes() {

        $router = new Router();
        $router->get('/api/test/get', 'callableTestGet', "routeTestGet");
        $router->post('/api/test/post', 'callableTestPost', "routeTestPost");
        $router->put('/api/test/put', 'callableTestPut', "routeTestPut");
        $router->delete('/api/test/delete', 'callableTestDelete', "routeTestDelete");

        $routes = $router->getRoutes();

        $this->assertInternalType('array', $routes);

        /**
         * @var \Noa\Router\Route $route
         */
        foreach ($routes['GET'] as $route) {

            $this->assertInstanceOf('Noa\Router\Route', $route);
            $this->assertEquals('api/test/get', $route->getPath());
            $this->assertEquals('callableTestGet', $route->getCallable());
        }

        /**
         * @var \Noa\Router\Route $route
         */
        foreach ($routes['POST'] as $route) {

            $this->assertInstanceOf('Noa\Router\Route', $route);
            $this->assertEquals('api/test/post', $route->getPath());
            $this->assertEquals('callableTestPost', $route->getCallable());
        }

        /**
         * @var \Noa\Router\Route $route
         */
        foreach ($routes['PUT'] as $route) {

            $this->assertInstanceOf('Noa\Router\Route', $route);
            $this->assertEquals('api/test/put', $route->getPath());
            $this->assertEquals('callableTestPut', $route->getCallable());
        }

        /**
         * @var \Noa\Router\Route $route
         */
        foreach ($routes['DELETE'] as $route) {

            $this->assertInstanceOf('Noa\Router\Route', $route);
            $this->assertEquals('api/test/delete', $route->getPath());
            $this->assertEquals('callableTestDelete', $route->getCallable());
        }

    }

    public function testRunFailureVerbNotExist() {

        $this->sendRequest('WRONG', '/api/test/get');

        $router = new Router();
        $router->get('/api/test/get', function() {}, "routeTestGet");

        $this->expectException(\Noa\Router\RouterException::class);
        $this->expectExceptionCode(\Noa\Router\RouterException::INVALID_METHOD);
        $router->run();
    }

    public function testRunSuccessClosure() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();
        $router->get('/api/test/get', function() {
            return 'success';
        }, "routeTestGet");

        $result = $router->run();

        $this->assertEquals('success', $result);

    }

    public function testRunSuccessFunction() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();
        $router->get('/api/test/get', 'test', "routeTestGet");

        $result = $router->run();

        $this->assertEquals('success', $result);

    }

}
