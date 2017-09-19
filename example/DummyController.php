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

        sprintf('Calling with parameter %s',$param );
    }

    public function testWithMoreParameter($id, $method) {

        return sprintf('Calling method %s from object#%d without parameter',$method, $id );
    }

    public function testWithMoreParameterConstraint($id, $method, $param) {

        return sprintf('Calling method %s from object#%d with parameter %s',$method, $id, $param );
    }
}