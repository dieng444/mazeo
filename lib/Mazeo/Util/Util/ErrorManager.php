<?php

namespace Mazeo\Util\Util;

/**
 * Class ErrorManager
 * @package Mazeo\Util\Util
 * @author Macky Dieng
 */
class ErrorManager {

    /**
     * Display nonexistent method error message;
     * @param $class - the class concerned
     * @param $method - the method concerned in the class
     * @return string
     */
    public static function nonExistedMethodMsg($class, $method) {
        return "Method {$method} does not exist in class {$class}";
    }

    /**
     * Display missed parameter error message
     * @param $method - the method concerned
     * @param $param - the parameter of the method
     * @return string
     */
    public static function MissedParameterMsg($method, $param) {
        return "Method \"{$method}\" missing parameter \"{$param}\"";
    }

    /**
     * Display error message
     * @param string $message - message to display
     * @return string
     */
    public static function displayError($message){
        return $message;
    }
}
