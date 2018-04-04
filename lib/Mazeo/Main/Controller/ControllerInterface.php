<?php

namespace Mazeo\Main\Controller;

/**
 * Interface ControllerInterface
 * @package Mazeo\Main\Controller
 * @author Macky Dieng
 */
interface ControllerInterface
{
    /**
     * Display a given template
     * @param string $view - template to display
     * @param array $data - data to display in the template
     */
    public function display($view, array $data);

    /**
     * Performs redirection
     * @param string $url - url on which to make redirection
     * @param array $queryString - the route parameters
     * @throws MazeoException
     */
    public function redirect($url, $queryString=array());
}
