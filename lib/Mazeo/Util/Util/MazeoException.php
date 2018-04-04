<?php

namespace Mazeo\Util\Util;

/**
 * Class MazeoException - manages framework exceptions
 * @package Mazeo\Util\Util
 * @author Macky Dieng
 */
class MazeoException extends \Exception {

    /**
     * MazeoException constructor
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message,$code);
    }
    /**
     * @override
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }
}
