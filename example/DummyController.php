<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 17/09/2017
 * Time: 17:38
 */

namespace Noa\Router\Example;


class DummyController
{
    public function testGet() {
        return 'success Get';
    }

    public function testPut() {
        return 'success Put';
    }

    public function testPost() {
        return 'success Post';
    }

    public function testDelete() {
        return 'success Delete';
    }

    public function testWithParameter($param) {

        return 'success '.$param;
    }

    public function testWithMoreParameter($param, $param2) {

        return 'success '.$param.':'.$param2;
    }

    public function testWithMoreParameterConstraint($param, $param2) {

        return 'success constraint '.$param.':'.$param2;
    }
}