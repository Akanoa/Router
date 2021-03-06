<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 07:16
 */

namespace Noa\Router\Test;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Noa\Router\RouterException;
use PHPUnit\Framework\TestCase;
use Noa\Router\Router;
use Psr\Http\Message\ServerRequestInterface;


class Test {

    public function test() {

        return 'success';
    }

    public function testPSR7() {

        // Instance a Router
        Router::getInstance();

        // get response object
        $response = Router::getResponse();

        // some treatments
        $response->getBody()->write("some content");
        $response = $response->withStatus(201);

        // set the modified response
        Router::setResponse($response);

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

    public function testGetInstance() {

        $router = Router::getInstance();
        $this->assertInstanceOf(Router::class, $router);

        $router2 = Router::getInstance();
        $this->assertEquals($router, $router2);
    }

    /**
     * Throw exception on unauthorized method
     */
    public function testAddWrongRouteMethod()
    {

        $router = new Router();

        $this->expectException(RouterException::class);
        $this->expectExceptionCode(RouterException::INVALID_METHOD);

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
        $route = $router->get('/api/test/get', 'Noa\Router\Test\test', null);

        $this->assertInstanceOf('Noa\Router\Route', $route);
        $this->assertEquals('api/test/get', $route->getPath());
        $this->assertEquals('Noa\Router\Test\test', $route->getCallable());
        $this->assertArrayHasKey('Noa\Router\Test\test', $router->getNamedRoutes());
        $this->assertEquals($route, $router->getNamedRoutes()['Noa\Router\Test\test']);

    }

    /**
     * Adding a named route
     */
    public function testAddNamedRoute() {
        // Check route adding
        $router = new Router();
        $route = $router->get('/api/test/get', 'Noa\Router\Test\test', "routeTest");

        $this->assertInstanceOf('Noa\Router\Route', $route);
        $this->assertEquals('api/test/get', $route->getPath());
        $this->assertEquals('Noa\Router\Test\test', $route->getCallable());
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

    public function testRunFailureNoMatchingRoute() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();
        $router->get('/api/test/get2', function() {}, "routeTestGet");

        $this->expectException(\Noa\Router\RouterException::class);
        $this->expectExceptionCode(\Noa\Router\RouterException::ROUTE_NOT_FOUND);
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

    public function testRunFailureWrongCallableType() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();
        $router->get('/api/test/get', true, "routeTestGet");

        $this->expectException(\Noa\Router\RouterException::class);
        $this->expectExceptionCode(\Noa\Router\RouterException::INVALID_CALLABLE);
        $result = $router->run();

    }

    public function testRunSuccessClassMethod() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();
        $router->get('/api/test/get', 'Noa\Router\Test\Test#test');

        $result = $router->run();

        $this->assertEquals('success', $result);

    }

    public function testRunSuccessFunctionCustomHttpVars()
    {

        $this->sendRequest('GET', '/api/test/get');
        $_SERVER['CUSTOM_REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];

        $router = new Router(array(
            "method" => 'CUSTOM_REQUEST_METHOD'
        ));
        $router->get('/api/test/get', 'Noa\Router\Test\Test#test', "routeTestGet");

        $result = $router->run();

        $this->assertEquals('success', $result);
    }

    public function testRunSuccessFunction() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();
        $router->get('/api/test/get', 'Noa\Router\Test\test', "routeTestGet");

        $this->expectException(RouterException::class);
        $this->expectExceptionCode(RouterException::INVALID_CALLABLE);
        $router->run();

    }

    public function testRunFailureFullyDefinedFunction() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();

        $router->get('/api/test/get', 'Noa\Router\Test\test2', "routeTestGet");

        $this->expectException(RouterException::class);
        $this->expectExceptionCode(RouterException::INVALID_CALLABLE);
        $router->run();

    }

    public function testRunFailureFullyDefinedClassMethod() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();

        $router->get('/api/test/get', 'Noa\Router\Test\Test#test2', "routeTestGet");

        $this->expectException(RouterException::class);
        $this->expectExceptionCode(RouterException::INVALID_CALLABLE);
        $router->run();

    }



    public function testRunFailureFunction() {

        $this->sendRequest('GET', '/api/test/get');

        $router = new Router();


        $router->get('/api/test/get', 'test', "routeTestGet");

        $this->expectException(RouterException::class);
        $this->expectExceptionCode(RouterException::INVALID_CALLABLE);
        $router->run();

    }

    public function testGetRequest() {

        $this->sendRequest('GET', '/api/test/get');

        Router::destroy();
        $router = Router::getInstance();

        $request = $router->getRequest();

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/api/test/get', $request->getUri()->getPath());
    }

    public function testSetRequest() {

        $this->sendRequest('GET', '/api/test/get');

        Router::destroy();
        Router::getInstance();

        $request = ServerRequest::fromGlobals();
        Router::setRequest($request);

        $this->assertEquals($request, Router::getRequest());

    }

    public function testGetStaticRequest() {

        $this->sendRequest('GET', '/api/test/get');

        Router::destroy();
        Router::getInstance();

        $request = Router::getRequest();

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/api/test/get', $request->getUri()->getPath());
    }

    public function testGetresponse() {

        $this->sendRequest('GET', '/api/test/get');

        Router::destroy();
        Router::getInstance();

        $this->assertInstanceOf(Response::class, Router::getResponse());
    }

    public function testSetresponse() {

        $this->sendRequest('GET', '/api/test/get');

        Router::destroy();
        Router::getInstance();

        $response = new Response();
        Router::setResponse($response);

        $this->assertInstanceOf(Response::class, Router::getResponse());
        $this->assertEquals($response, Router::getResponse());
    }

    public function testGetresponseModifiedBody() {

        $this->sendRequest('GET', '/test/psr7');

        Router::destroy();
        $router = Router::getInstance();
        $router->get("/test/psr7", "Noa\Router\Test\Test#testPSR7");

        $router->run();

        ob_start();
        echo  Router::getResponse()->getBody();
        $body = ob_get_clean();

        $this->assertInstanceOf(Response::class, Router::getResponse());
        $this->assertEquals("some content", $body);
        $this->assertEquals(201, Router::getResponse()->getStatusCode());
    }

}
