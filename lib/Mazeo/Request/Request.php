<?php

namespace Mazeo\Request;

use Mazeo\Response\JsonResponse;
use Mazeo\Response\Response;
use Mazeo\Router\Dispatcher;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Session;

/**
 * Class Request - Manages the request coming from the users.
 * @package Mazeo\Request
 * @author Macky Dieng
 */
class Request
{
  /**
   * Contain all params catched from the uri (POST and GET)
   * @var array
   */
  private $request = array();

  /**
   * Contains POST parameters catched from the uri
   * @var array
   */
  private $post = array();

  /**
   * Contain GET parameters catched from the uri
   * @var array
   */
  private $get = array();

  /**
   * Contain $http \Mazeo\Request\HttpRequest
   * @var string
   */
  public $http;

  /**
   * Request constructor.
   */
  public function __construct()
  {
    $this->http = new HttpRequest();
    $routeBases = yaml_parse_file('app/routing.yml');
    $routeCollection = Dispatcher::getRouteCollection($routeBases);
    if($this->http->getMethod()==='GET') {
      $this->init($routeCollection);
    } else {
      $this->post = $this->http->getPost();
      $this->request = $this->post;
    }
  }
  /**
   * Initialize request params values
   * @param array $routeCollection - list of route
   */
  private final function init($routeCollection) {
    $collection = $routeCollection['collection'];
    $uri = $this->http->getRequestUri();
    $relativeUri = $this->http->getRelativeRequestUri();
    foreach ($collection as $route) {
        $tmpPath = str_replace('/', '', $route->getPath());
        $pattern = "#" . preg_quote(trim($tmpPath)) . "#i";
        if (preg_match($pattern, $relativeUri, $match)) {
          $pos = (strlen($route->getPath()) - 1);
          if (sizeof($route->getQueryString()) > 0) {
            $tabUriQueryStringClean = Dispatcher::getRouteParameters($uri, $pos);
            foreach ($route->getQueryString() as $k => $val)
                $this->get[$val] = $tabUriQueryStringClean[$k];
          } else $this->get = $this->http->getGet();
        }
    }
    $this->request = array_merge($this->get,$this->http->getPost());
}
  /**
   * Returns all data received from the server
   * according to the parameter data type (get or post)
   * @param string $dataType - the request data type
   * @return array
   */
  public function getRequest($dataType=null)
  {
    $data = array();
    if ($dataType!==null && strtoupper($dataType) === 'POST') $data = $this->post;
    elseif ($dataType !==null && strtoupper($dataType) === 'GET') $data = $this->get;
    else $data = $this->request;
    return $data;
  }

  /**
   * Returns a specific data value, according to the key passed in parameter
   * @param $key
   * @return mixed
   * @throws MazeoException
   */
  public function getParam($key)
  {
    if(array_key_exists($key,$this->request)) return $this->request[$key];
    else return false;
  }
  /**
   * Allows to know what is the current http method
   * @param string $method - the desired method
   * @throws \Exception
   * @return boolean
   */
  public function isMethod($method)
  {
    $method = strtoupper($method);
    if ($this->http->getMethod() === $method && $method === 'POST') return true;
    elseif ($this->http->getMethod() === $method && $method === 'GET') return true;
    elseif($method !== 'POST' && $method !== 'GET')
        throw new MazeoException("Unaccepted method {$method} passed as parameter");
    else return false;
  }
  /**
   * Verify either the request type is an XmlHttpRequest
   * @return boolean
   */
  public function isXhr()
  {
    if (!empty($this->http->getXhr()) && strtolower($this->http->getXhr()) == 'xmlhttprequest') return true;
    else return false;
  }

  /**
   * Allows to send response to the client
   * @param $responseToSend - the current response to send
   * @return mixed
   */
  public function sendResponse($responseToSend,$responseType="") {
    $response  = null;
    if ($this->isXhr() || strtoupper($responseType) === 'JSON') $response = new JsonResponse($responseToSend);
    else $response = new Response($responseToSend);
    $response->send();
  }
}
