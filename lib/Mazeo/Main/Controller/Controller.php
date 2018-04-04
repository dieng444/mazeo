<?php

namespace Mazeo\Main\Controller;

use Mazeo\Logging\AuthManager;
use Mazeo\Request\Request;
use Mazeo\Router\Dispatcher;
use Mazeo\Templater\TwigTemplateRender;
use Mazeo\Util\Util\MazeoException;

/**
 * Class Controller - the main controller class
 * @package Mazeo\Controller;
 * @author Macky Dieng
 */
abstract class Controller implements ControllerInterface
{
    /**
     * @var Request - Request instance variable
     */
    protected $request;

    /**
     * @var Entity - the current connected user
     */
    protected $user;

    /**
     * @var AuthManager
     */
    protected $auth;

    /**
     * @var TwigTemplateRender
     */
    private $templater;

    /**
     * Class construtor
     */
    public function __construct() {
        $this->request = new Request();
        $this->auth = AuthManager::getInstance();
        $this->templater = new TwigTemplateRender();
    }

    /**
     * Performs redirection
     * @param string $url - url on which to make redirection
     * @param array $queryString - the route parameters
     * @throws MazeoException
     */
    public final function redirect($url, $queryString=array()) {
      header("Location: {$url}"); exit;
        /*$routeBases = yaml_parse_file('app/routing.yml');
        $routes = Dispatcher::getRouteCollection($routeBases);
        $routeCollection = $routes['collection'];
        array_push($routeCollection,$routes['defaultRoute']);
        $routeNames = array();
        $params = "";
        foreach ($routeCollection as $route) $routeNames[$route->getName()] = $route;
        if (array_key_exists($url, $routeNames)) {
            if(sizeof($queryString) > 0) {
                foreach ($queryString as $p) $params .= '/' . $p;
                $path = substr($routeNames[$url]->getPath(),0,strlen($routeNames[$url]->getPath())-1).$params;
                header("Location: {$path}"); exit; /***Redirection with parameters*
            } else { header("Location: {$routeNames[$url]->getFullPath()}"); exit; }
        } else throw new MazeoException("Unable to make redirection on route {$url}");*/
    }

    /**
     * Display a given template
     * @param string $view  - template to display
     * @param array $data - data to display in the template
     */
    public final function display($view, array $data)
    {
        $this->templater->render($view,$data);
    }

    /**
     * Returns the current connected user
     * @return \Mazeo\Main\Entity\Entity
     */
    public final function getUser()
    {
        return AuthManager::getInstance()->getUser();
    }
}
