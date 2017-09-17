<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 07:28
 */

namespace Noa\Router;

class Route {

    private $path;
    private $callable;
    private $constrains=array();
    private $matches=array();

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
     * @param $url
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

    public function call() {

        if (is_string($this->callable)) {

            $callableParamaters = explode("#", $this->callable);

            if(count(explode('\\', $callableParamaters[0])) == 1) {

                $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);

                $reflexion = new \ReflectionClass($trace[2]['class']);
                $namespace = $reflexion->getNamespaceName();

                $callableParamaters[0] = $namespace.'\\'.$callableParamaters[0];
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


            if (!is_callable($callableParamaters[0])) {

                throw new RouterException(RouterException::INVALID_CALLABLE);
            }

            return call_user_func($callableParamaters[0], $this->matches);
        }

        return call_user_func_array($this->callable, $this->matches);
    }


}