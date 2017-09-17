<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 17/09/2017
 * Time: 12:30
 */

use Noa\Router\RouterException;
use PHPUnit\Framework\TestCase;

class RouterExceptionTest extends TestCase
{
    public function testUnknownException() {

        $exception = new RouterException(0);
        $this->assertEquals("Unknown Noa\Router code: ".'0', $exception->getMessage());

        $exception = new RouterException(-1);
        $this->assertEquals("Unknown Noa\Router code: ".'-1', $exception->getMessage());
    }

}
