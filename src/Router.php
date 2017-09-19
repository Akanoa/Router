<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 07:06
 */

namespace Noa\Router;

use GuzzleHttp\Psr7\Response;
use \GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Router
 * @package Noa\Router
 */
class Router {

    /**
     * @var Router $instance
     */
    private static $instance;

    /**
     * List of Routes
     * @var array $routes
     */
    private $routes = [];

    /**
     * Allowed HTTP verbs
     */
    public static $ALLOWED_METHODS = array(
        'GET',
        'POST',
        'PUT',
        'DELETE'
    );

    /**
     * @var array $namedRoutes
     */
    private $namedRoutes = [];

    /**
     * @var ServerRequestInterface $request
     */
    private $request = null;

    /**
     * @var Response $response
     */
    private $response = null;

    /**
     * Router constructor.
     * @param null|array $httpServerVars
     */
    public function __construct($httpServerVars=null)
    {
        $this->request = ServerRequest::fromGlobals();
        $this->response = new Response();
    }

    /**
     * Get a new instance of Router
     * @param null|array $httpServerVars
     * @return Router
     */
    public static function getInstance()
    {
        if(is_null(self::$instance)) {

            self::$instance = new Router();
        }

        return self::$instance;
    }

    /**
     * Destroy a static instance of Router
     */
    public static function destroy()
    {
        self::$instance = null;
    }

    /**
     * Add a new Route to Router
     * @param string $path
     * @param callable|string $callable
     * @param string $name
     * @param string $method
     * @return Route
     */
    protected function add($path, $callable, $name, $method)
    {

        if (!in_array($method, self::$ALLOWED_METHODS)) {

            throw new RouterException(RouterException::INVALID_METHOD);
        }

        $route = new Route($path, $callable);
        $this->routes[$method][] = $route;

        if(!$name && is_string($callable)) {

            $name = $callable;
        }

        if ($name) {

            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }

    /**
     * @param $path
     * @param $callable
     * @param null $name
     * @return Route
     */
    function get($path, $callable, $name=null) {

        return $this->add($path, $callable, $name, 'GET');
    }

    /**
     * @param $path
     * @param $callable
     * @param null $name
     * @return Route
     */
    function post($path, $callable, $name=null) {

        return $this->add($path, $callable, $name, 'POST');
    }

    /**
     * Add a new PUT route
     * @param string $path Patter of new PUT route
     * @param callable|string $callable
     * @param string|null $name
     * @return Route
     */
    function put($path, $callable, $name=null) {

        return $this->add($path, $callable, $name, 'PUT');
    }

    /**
     * Add a new DELETE route
     * @param string $path Patter of new DELETE route
     * @param callable|string $callable
     * @param string|null $name
     * @return Route
     */
    function delete($path, $callable, $name=null) {

        return $this->add($path, $callable, $name, 'DELETE');
    }

    /**
     * Return all routes order by method
     * @return array<Route>
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Return the array of named routes
     * @return mixed
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }

    /**
     * Try to match route with url path
     * @return mixed
     * @throws RouterException
     */
    public function run() {

        if (!in_array($this->request->getMethod(), array_keys($this->getRoutes()))){

            throw new RouterException(RouterException::INVALID_METHOD);
        }

        /**
         * @var Route $route
         */
        foreach ($this->getRoutes()[$this->request->getMethod()] as $route) {

            if($route->match($this->request->getUri()->getPath())) {

                return $route->call();
            }
        }

        throw new RouterException(RouterException::ROUTE_NOT_FOUND, $this->request->getUri()->getPath());
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        self::destroy();
    }

    /**
     * Return request object
     * @return ServerRequestInterface
     */
    public static function getRequest() {

        return self::$instance->request;
    }


    /**
     * Set request statically
     * @param MessageInterface $request
     */
    public static function setRequest(MessageInterface $request)
    {
        self::$instance->request = $request;

    }

    /**
     * @return Response
     */
    public static function getResponse()
    {
        return self::$instance->response;
    }

    /**
     * Set response after modification
     * @param Response $response
     */
    public static function setResponse(Response $response)
    {
        self::$instance->response = $response;
    }
}