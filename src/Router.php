<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 07:06
 */

namespace Noa\Router;


/**
 * Class Router
 * @package Noa\Router
 */
class Router {

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
     * @var array $httpServerVars
     */
    private $httpServerVars;

    /**
     * @var
     */
    private $namedRoutes = [];

    /**
     * Router constructor.
     *
     */
    public function __construct($httpServerVars=null)
    {
        $this->httpServerVars = array(
            'url' => 'REQUEST_URI',
            'method' => 'REQUEST_METHOD'
        );

        if ($httpServerVars){
            foreach ($httpServerVars as $httpServerVar => $value) {

                if (isset($this->httpServerVars[$httpServerVar])) {

                    $this->httpServerVars[$httpServerVar] = $value;
                }
            }
        }
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
     * @return array<Route>
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     *
     * @return mixed
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }

    public function run() {

        if (!in_array($_SERVER[$this->httpServerVars['method']], array_keys($this->getRoutes()))){

            throw new RouterException(RouterException::INVALID_METHOD);
        }

        $url = $_SERVER[$this->httpServerVars['url']];

        /**
         * @var Route $route
         */
        foreach ($this->getRoutes()[$_SERVER[$this->httpServerVars['method']]] as $route) {

            if($route->match($url)) {

                return $route->call();
            }
        }

        throw new RouterException(RouterException::ROUTE_NOT_FOUND, $url);
    }
}