<?php
/**
 * Created by PhpStorm.
 * User: Noa
 * Date: 16/09/2017
 * Time: 08:37
 */

namespace Noa\Router;


use Throwable;

class RouterException extends \Exception
{

    const INVALID_METHOD = 1;
    const INVALID_CALLABLE = 2;
    const ROUTE_NOT_FOUND = 3;

    public function __construct($code = 0, $complement = '',Throwable $previous = null)
    {
        switch ($code) {
            case self::INVALID_METHOD:
                $message = "Invalid Route method";
                break;
            case self::INVALID_CALLABLE:
                $message = "Invalid callable supplied";
                break;
            case self::ROUTE_NOT_FOUND:
                $message = "No matching route: ".$complement;
                break;
            default:
                $message = "Unknown Noa\Router";
                break;
        }

        parent::__construct($message, $code, $previous);
    }
}