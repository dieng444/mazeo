<?php

namespace Mazeo\Request;
/**
 * Class HttpRequest - Manages the server request as a object
 * @author Macky Dieng
 * @copyright 2016 - Mazeberry
 */
class HttpRequest
{
    /**
     * @var string  - The request method
     */
    private $m = null;
    /**
     * @var array  - $_GET content
     */
    private $g = null;
    /**
     * @var array - $_POST content
     */
    private $p = null;
    /**
     * @var array - $_REQUEST content
     */
    private $r = null;
    /**
     * @var array  - Query string of the current request
     */
    private $q = null;
    /**
     * @var string  - Script name of the current request
     */
    private $s = null;
    /**
     * @var string  - Uri of the current request
     */
    private $ru = null;
    /**
     * @var string  - Relative uri of the current request
     */
    private $reluri = null;
    /**
     * @var string   - XmlHttpRequest
     */
    private $xhr = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->m = $_SERVER['REQUEST_METHOD'];
        $this->g = $_GET ;
        $this->p = $_POST;
        $this->r = $_REQUEST;
        $this->q = $_SERVER['QUERY_STRING'] ;
        $this->s = $_SERVER['SCRIPT_NAME'];
        $this->ru = $_SERVER['REQUEST_URI'];
        $this->xhr = $_SERVER['HTTP_X_REQUESTED_WITH'];
        $this->reluri = str_replace(dirname($this->s), '', $this->ru);
    }
    /**
     * Returns the current request Method
     * @return array
     */
    public function getMethod()
    {
        return $this->m;
    }
    private function filterParams($params)
    {
      $parsedPostData = array();
      foreach ($params as $k => $val) {
        if(is_numeric($val)) {
          if (is_float($val)) $parsedPostData[] = (float) $val;
          else $parsedPostData[$k] = (int) $val;
        } else $parsedPostData[$k] = $val;
      }
      return $parsedPostData;
    }
    /**
     * Returns the current request GET content
     * @return array
     */
    public function getGet()
    {
      return $this->filterParams($this->g);
    }
    /**
     * Returns the current request POST content
     * @return array
     */
    public function getPost()
    {
      return $this->filterParams($this->p);
    }
    /**
     * Returns the current request POST and GET content
     * @return array
     */
    public function getRequest()
    {
        return $this->r;
    }
    /**
     * Returns the the current request query string
     * @return array
     */
    public function getQuery()
    {
        return $this->q;
    }
    /**
     * Returns the current request script name
     * @return string
     */
    public function getScriptName()
    {
        return $this->s;
    }
    /**
     * Returns the current request uri
     * @return array
     */
    public function getRequestUri()
    {
        return $this->ru;
    }
    /**
     * Returns the current request relative uri
     * @return string
     */
    public function getRelativeRequestUri()
    {
        return $this->reluri;
    }
    /**
     * Returns the xhr request
     * @return string
     */
    public function getXhr()
    {
        return $this->xhr;
    }
    public function getFullDomain()
    {
      $server = $_SERVER['SERVER_NAME'];
      $protocol = strtolower(explode('/',$_SERVER['SERVER_PROTOCOL'])[0]);
      return $protocol.'://'.$server.'/';
    }
    public function getFullRequest()
    {
      $server = $_SERVER['SERVER_NAME'];
      $protocol = strtolower(explode('/',$_SERVER['SERVER_PROTOCOL'])[0]);
      return $protocol.'://'.$server.'/'.$this->ru;
    }
}
