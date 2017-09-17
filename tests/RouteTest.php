<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 08:21
 */

use Noa\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testMatch() {

        // Simple route match
        $route = new Route('/api/test/get', 'test');
        $result = $route->match('/api/test/get');
        $this->assertTrue($result);

        // Simple route match with query string
        $route = new Route('/api/test/get', 'test');
        $result = $route->match('/api/test/get?test=value');
        $this->assertTrue($result, 'Route must exclude query string from route');

        // Simple route match with query string
        $route = new Route('/api/test/get', 'test');
        $result = $route->match('/api/test/get2?test=value');
        $this->assertFalse($result, "Mustn't match because route pattern is incorrect");

        // Simple route failure
        $route = new Route('/api/test/get', 'test');
        $result = $route->match('/api/test/get2');
        $this->assertFalse($result);

        // Parametrized route success
        $route = new Route('/api/test/:method', 'test');
        $result = $route->match('/api/test/get');
        $this->assertTrue($result);

        // Parametrized route failure
        $route = new Route('/api/test/:method', 'test');
        $result = $route->match('/api/test2/get');
        $this->assertFalse($result);

        // Parametrized route success (multiple parameter)
        $route = new Route('/api/method/:method/param/:param', 'test');
        $result = $route->match('/api/method/get/param/10');
        $this->assertTrue($result);
    }
}
