<?php

namespace Mazeo\Router;
/**
 * Class Route - Allows to create a new route
 * @package Mazeo\Router
 * @author Macky Dieng
 */
class Route
{
    /**
     * @var string The route name
     */
    private $name;
    /**
     * @var string The route path
     */
    private $path;
    /**
     * @var string Full path such as the user defined it in the routing file
     */
    private $fullPath;
    /**
     * @var string The route package
     */
    private $package;

    /**
     * @var string The route controller
     */
    private $controller;

    /**
     * @var string Full controller name such as the user defined it in the routing file
     */
    private $fullControllerDefinition;
    /**
     * @var array The route query string
     */
    private $queryString = array();
    /**
     * @var string The route action
     */
    private $action;

    /**
     * @var string The controller vendor
     */
    private $vendor;
    /**
     * @var string The route method
     */
    private $method;
    /**
     * @var array The route requirements
     */
    private $requirements;

    /**
     * Classs constructor
     * @param array $data - the route array data
     */
    public function __construct($data)
    {
        $this->path = "";
        $this->fullControllerDefinition = $data['controller'];
        if ($data['fullPath'] == '/') $this->path = $data['fullPath'];
        else {
            $pathParts = explode('/', $data['fullPath']);
            foreach ($pathParts as $part) {
                if (strstr($part, ':')) $this->queryString[] = str_replace(':', '', $part);
                else $this->path .= $part.'/';
            }
        }
        list($vendor, $pack, $controller, $act) = explode(':', $data['controller']);
        $this->vendor = ucfirst($vendor);
        $this->package = ucfirst($pack);
        $this->controller = ucfirst($controller);
        $this->action = strtolower($act);
        $this->name = $data['name'];
        $this->fullPath = $data['fullPath'];
        $this->method = (array_key_exists('method', $data)) ? $data['method'] : null;
        $this->requirements = (array_key_exists('requirements', $data)) ? $data['requirements'] : null;
    }
    /**
     * Returns route name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Returns route path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * Returns the full path name, such as user
     * defined it in the routing fil
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }
    /**
     * Returns the full controller definition name
     * such as the user defined it in the routing fil
     * @return string
     */
    public function getFullControllerDefinition()
    {
        return $this->fullControllerDefinition;
    }
    /**
     * Returns route query string
     * @return array
     */
    public function getQueryString()
    {
        return $this->queryString;
    }
    /**
     * Returns route vendor
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }
    /**
     * Returns route package
     */
    public function getPackage()
    {
        return $this->package;
    }
    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }
    /**
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }
    /**
     * Returns route action
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns route method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the reroute requirements
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }
}
