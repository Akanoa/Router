<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 07:28
 */

namespace Noa\Router;

/**
 * Class Route
 * @package Noa\Router
 */
class Route {

    /**
     * @var string $path
     */
    private $path;

    /**
     * @var mixed $callable
     */
    private $callable;

    /**
     * @var array $constrains
     */
    private $constrains = array();

    /**
     * @var array $matches
     */
    private $matches = array();

    public function __construct($path, $callable) {

        $this->path = trim($path, '/');
        $this->callable = $callable;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * Add a constraint on route parameter
     * @param $name
     * @param $regex
     * @return $this The route with constraint
     */
    public function with($name, $regex) {

        $this->constrains[$name] = str_replace('(', '(?:', $regex);

        return $this;
    }
    /**
     * @param $match
     * @return string
     */
    private function constrainsMatch($match) {

        if(isset($this->constrains[$match[1]])) {

            return '('.$this->constrains[$match[1]].')';
        }

        return '([^/]+)';
    }

    /**
     * Check whether URL match to route
     * @param string $url URL to check
     * @return bool
     */
    public function match($url) {

        $split = explode('?', $url);
        $url = trim($split[0], '/');

        $path = preg_replace_callback('#:([\w]+)#', [$this, 'constrainsMatch'], $this->path);

        $regex = "#^$path$#i";

        if(!preg_match($regex, $url, $matches)) {

            return false;
        }

        // remove first regex matches element
        array_shift($matches);
        $this->matches = $matches;

        return true;
    }

    /**
     * Try to call function, throw an exception if provided callable is not a real callable
     * @return mixed Return data from callable
     * @throws RouterException The route's callable isn't a real callable
     */
    public function call() {

        // If the callable isn't a closure
        if (is_string($this->callable)) {

            $callableParamaters = explode("#", $this->callable);

            if(count(explode('\\', $callableParamaters[0])) == 1) {

                throw new RouterException(RouterException::INVALID_CALLABLE);
            }

            if(count($callableParamaters) == 2) {
                $controllerName = $callableParamaters[0];
                $controllerInstance = new $controllerName();
                $action = $callableParamaters[1];

                if (!is_callable(array($controllerInstance, $action))) {

                    throw new RouterException(RouterException::INVALID_CALLABLE);
                }

                return call_user_func_array(array($controllerInstance, $action), $this->matches);
            }
        }

        if (!is_callable($this->callable)) {

            throw new RouterException(RouterException::INVALID_CALLABLE);
        }

        return call_user_func_array($this->callable, $this->matches);
    }


}