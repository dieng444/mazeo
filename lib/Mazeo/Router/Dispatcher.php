<?php

 namespace Mazeo\Router;

 use Mazeo\Request\HttpStatusCode;
 use Mazeo\Request\HttpRequest;
 use Mazeo\Request\HttpStatusText;
 use Mazeo\Util\Util\ErrorManager;
 use Mazeo\Util\Util\MazeoException;
 use Mazeo\Templater\TwigTemplateRender;
 use Mazeo\Util\Util\Reflector;

 /**
  * Class Dispatcher
  * @package Mazeo\Router
  * @author Macky Dieng
  */
 class Dispatcher
 {
   /**
    * Manages route and request for know which action to execute
    * @throws MazeoException
    */
   public static function dispatch()
   {
      $routeBases = yaml_parse_file('app/routing.yml');
      $defaultRoute = null;
      $http = new HttpRequest();  /**@var $http HttpRequest*/
      $uri = $http->getRequestUri();
      $relativeUri = $http->getRelativeRequestUri();
      $routeMatched = false;
      $routeCollection = self::getRouteCollection($routeBases);
      $collection = $routeCollection['collection'];
      $defaultRoute = $routeCollection['defaultRoute'];
      $matchedRoute = null;
      if ($defaultRoute!==null && $uri===$defaultRoute->getFullPath()) {  /***Default route case*/
        self::executeDefaultRoute($defaultRoute); exit;
      }
      if(sizeof($collection) > 0) {
        if(array_key_exists($relativeUri,$collection)) { /***Exact route matching without parameters **/
          $routeMatched = true;
          $matchedRoute = $collection[$relativeUri];
        }
        else { /*** Case route with parameters **/
          foreach ($collection as $key => $route) {
            /***The first (:) token position in the route full path string*/
            $routeTokenPos = strpos($route->getFullPath(), ':'); /***Allows to get the exact route passed in the uri**/
            if ($routeTokenPos !== false) {
              /***
               * Getting the path part from the beginning of uri string to the first
               * (:) token position in the route full path string
               */
              $uriPath = substr($uri, 0, $routeTokenPos);
              $pos = (strlen($route->getPath()) - 1);
              $tabUriQueryStringClean = self::getRouteParameters($uri, $pos);
              if ($uriPath === $route->getPath()) {
                /**
                 * The condition below means that some times, we can have many routes
                 * with the same path part ($route->getPath()) but they are different number of parameters.
                 * It because of that case we checking here for know if the number of current route parameters
                 * match number of current uri parameters. And that because number of parameters will always
                 * be different when two routes have the same path name.
                 */
                if (sizeof($route->getQueryString()) === sizeof($tabUriQueryStringClean)) {
                  $matchedRoute = $route;
                  $routeMatched = true;
                }
              }
            }
          }
        }
        if ($routeMatched) {
          $controllerParts = explode(':',$matchedRoute->getFullControllerDefinition());
          $_SESSION['currentRoute'] = $matchedRoute;
        } else self::invokeStatusError('appHttp:404.html.twig',HttpStatusCode::NOT_FOUND);
        if((sizeof($controllerParts)!==4) || substr_count($matchedRoute->getFullControllerDefinition(),':')!==3) {
          throw new MazeoException("Wrong route controller definition"); exit;
        }
        $pos = (strlen($matchedRoute->getPath()) - 1); /***Last character position in the path*/
        $fullPathPos = (strlen($matchedRoute->getFullPath()) - 1); /***Last character position in the full path we found '/'*/
        if(substr($matchedRoute->getFullPath(), $fullPathPos, 1)==="/") {
          throw new MazeoException("The character \"/\" is not authorized at the end of {$matchedRoute->getName()} route path"); exit;
        }
        if ((($http->getMethod()!==null) && ($http->getMethod()===strtoupper($matchedRoute->getMethod()))) || strtoupper($matchedRoute->getMethod()) === 'MIXED') {
            if (sizeof($matchedRoute->getQueryString()) > 0) { /***Case routes with parameters*/
              if (substr($uri, $pos, 1) !== "/") { /***When route have parameters, the next character after matched path must be mandatory "/"*/
                self::invokeStatusError('appHttp:404.html.twig',HttpStatusCode::NOT_FOUND);
              } else {
                $tabUriQueryStringClean = self::getRouteParameters($uri, $pos);
                if (sizeof($matchedRoute->getQueryString()) === sizeof($tabUriQueryStringClean)) { /***Verify whether route and uri parameters count matched.*/
                    if (substr($uri, (strlen($uri) - 1), 1) === "/") { /*** No "/" after route last parameter */
                      self::invokeStatusError('appHttp:404.html.twig',HttpStatusCode::NOT_FOUND);
                    } else {
                      $class = self::getControllerInfo($matchedRoute);
                      $controller = $class['controller'];
                      $action = $class['action'];
                      $ctrlNs = $class['ctrlNs'];
                      if (method_exists($controller, $action)) {
                        $reflector = new Reflector($controller);
                        $parameters = $reflector->getParameters($action);
                        if (empty($parameters)) return $controller->$action();
                        if (sizeof($matchedRoute->getQueryString()) !== (sizeof($parameters))) {/***It's not mandatory to specify route parameters on controller action*/
                          throw new MazeoException("Number of {$matchedRoute->getPath()} route parameters must matched method {$action} parameters number in {$ctrlNs}"); exit;
                        } else {
                          foreach ($parameters as $param)
                            $tabParams[] = $param->getName();
                          foreach ($matchedRoute->getQueryString() as $k => $v) {
                            if ($matchedRoute->getQueryString()[$k] !== $tabParams[$k]) {
                              throw new MazeoException ("The key {$v} is not a parameter of method {$action} in {$ctrlNs}"); exit;
                            }
                          }
                          call_user_func_array(array($controller, $action), $tabUriQueryStringClean);
                        }
                      } else throw new MazeoException("Method {$action} does'nt exist in class {$ctrlNs}");
                    }
                  } else { self::invokeStatusError('appHttp:404.html.twig',HttpStatusCode::NOT_FOUND); }
                }
              } else { /*** Case routes without parameters */
                if (strlen($uri) !== $pos) { /***Here the $uri length must equal to the path length*/
                    self::invokeStatusError('appHttp:404.html.twig',HttpStatusCode::NOT_FOUND);
                } else {
                  $class = self::getControllerInfo($matchedRoute);
                  $controller = $class['controller'];
                  $action = $class['action'];
                  $ctrlNs = $class['ctrlNs'];
                  if (method_exists($controller, $action)) $controller->$action();
                  else throw new MazeoException("Method {$action} does'nt exist in class {$ctrlNs}");
              }
            }
          } else  {
            throw new MazeoException("The current route {$matchedRoute->getName()} can not be called with method {$http->getMethod()}"); exit;
        }
        if (!$routeMatched) { self::invokeStatusError('appHttp:404.html.twig',HttpStatusCode::NOT_FOUND); }
      } else { throw new MazeoException('No route found in routes base files'); }
   }
   /**
    * Parse route array to route object
    * @param $k string - current route array key
    * @param $v array - current route array value
    * @param $file string - the route routing file
    * @throws MazeoException
    * @return \Mazeo\Router\Route
    */
   private static function parseRouteArrayToObject($k, array $v, $file) {
     $data = array();
     if (strlen($k) > 0) $data['name'] = $k;
     else { throw new MazeoException("Route \"name\" missing in the {$file} routing file"); exit; };
     if(array_key_exists('path', $v)) $data['fullPath'] = $v['path'];
     else { throw new MazeoException("Route \"path\" missing in the {$k} route definition"); exit; }
     if(array_key_exists('controller', $v)) $data['controller'] = $v['controller'];
     else { throw new MazeoException("Route \"controller\" missing in the {$k} route definition"); exit; }
     if(array_key_exists('method', $v)) $data['method'] = $v['method'];
     else { throw new MazeoException("Route \"method\" missing in the {$k} route definition"); exit; }
     if (array_key_exists('requirements', $v)) $data['requirements'] = $v['requirements'];
     return new Route($data);
   }
   /**
    * Execute default route
    * @param  \Mazeo\Router\Route $defaultRoute - default route to execute
    * @throws MazeoException
    */
   private static function executeDefaultRoute(Route $defaultRoute) {
     $class = $defaultRoute->getVendor().'\\'. $defaultRoute->getPackage().'\\Controller'.'\\'.$defaultRoute->getController().'Controller';
     $controller = new $class();
     $action = $defaultRoute->getAction().'Action';
     if (method_exists($controller, $action)) $controller->$action();
     else throw new MazeoException(ErrorManager::nonExistedMethodMsg($class,$action)." in ".__CLASS__." on line 155");
   }
   /**
    * Returns the current route parameters
    * @param $uri - the current request uri
    * @param $pos - the matched path position
    * @return array
    */
   public static final function getRouteParameters($uri, $pos) {
     $uriQueryString = substr($uri, $pos); /***Obtaining parameters after the matched path*/
     $tabUriQueryString = explode("/",$uriQueryString);
     $tabUriQueryStringClean = array();
     foreach ($tabUriQueryString as $val) {
       if(!empty($val)) {
         $tabUriQueryStringClean[] = $val;
         //if(is_numeric($val)) {
        //    if (is_float($val)) $tabUriQueryStringClean[] = (float) $val;
        //    else $tabUriQueryStringClean[] = (int) $val;
        //  } else $tabUriQueryStringClean[] = $val;
       }
     }
     return $tabUriQueryStringClean;
   }
   /**
    * Returns a given route controller information
    * @param \Mazeo\Router\Route $route - the current route
    * @return array
    */
   public static final function getControllerInfo(Route $route) {
     $controllerClass = $route->getVendor().'\\'.$route->getPackage().'\\Controller'.'\\'.$route->getController().'Controller';
     $tab['controller']  = new $controllerClass();
     $tab['action'] = $route->getAction().'Action';
     $tab['ctrlNs'] = $controllerClass;
     return $tab;
   }
   /**
    * Returns the route collection of given bases
    * @param array $routeBases - the given route bases
    * @throws MazeoException
    * @return array
    */
   public static final function getRouteCollection(array $routeBases) {
     $routeArray = array();
     $collection = array();
     $defaultRoute = null;
     $samePathIndex = 0;
     foreach ($routeBases as $key => $val) {
       $routes = array();
       if ($key!=='default_route') {
         $base = $val['base'];
         $base = explode(':', $base);
         $file = 'src/'.$base[0].'/'.$base[1].'/Resources/config/routing.yml';
         if(file_exists($file)) {
           if (filesize($file) > 0 ) $routes = yaml_parse_file($file);
         }
         else { throw new MazeoException("Can not charge routing file from the package {$base[0]}/{$base[1]}"); exit; }
         if (sizeof($routes) > 0 ) {
           foreach ($routes as $k => $v) $collection[] = self::parseRouteArrayToObject($k,$v,$file);
         }
       } else $defaultRoute = self::parseRouteArrayToObject($key,$val,'app/routing.yml');
     }
     foreach ($collection as $route) {
       $name = str_replace('/','',$route->getPath());
       if (array_key_exists($name, $routeArray)) {
         $name = $name.'_'.$samePathIndex;
         $samePathIndex++;
       }
       $routeArray[$name] = $route;
     }
     $tab['defaultRoute'] = $defaultRoute;
     $tab['collection'] = $routeArray;
     return $tab;
   }
   /**
    * Invokes server status error message
    * @param string  $view - the view in which the message will be displayed
    * @param int $statusCode - Server status code appropriate for the current message
    */
   private static function invokeStatusError($view, $statusCode) {
     http_response_code($statusCode);
     $render = new TwigTemplateRender();
     $render->render($view, HttpStatusText::getText($statusCode)); exit;
   }
 }
