<?php

namespace Mazeo\Util\Util;

/**
 * Class Session - allows to manipulate an session array as a object
 * @package Mazeo\Util\Util
 * @author Macky Dieng
 * @copyright 2017 the author
 */
class Session
{
  private static $instance;

  /**
  * Disable cloning
  */
  private function __clone()
  {
  }

  /**
   * @return Session
   */
  public static function getInstance()
  {
      if (!(self::$instance instanceof self)) {
          self::$instance = new self();
      }
      return self::$instance;
  }
  /**
   * [add content to the current session]
   * @param [type] $key   [element key]
   * @param [type] $value [elment value]
   */
  public function add($key,$value)
  {
    $_SESSION[$key] = $value;
  }
  /**
   * [Retrieve a given element from the session object]
   * @param  [type] $key [the key of element to retrieve]
   */
  public function exist($key)
  {
    return isset($_SESSION[$key]);
  }
  /**
   * [Retrieve a given element from the session object]
   * @param  [type] $key [the key of element to retrieve]
   */
  public function get($key)
  {
    if ($this->exist($key)) {
      return $_SESSION[$key];
    } else {
      return false;
    }
  }
  /**
   * [remove remove a given element from the current session]
   * @param $key [element key]
   */
  public function remove($key)
  {
    unset($_SESSION[$key]);
  }
  /**
   * [Return temporaly session content]
   * @param  [type] $key [the key of element to retrieve]
   */
  public function getFlash($key)
  {
    $flashContent = $_SESSION[$key];
    $this->remove($key);
    return $flashContent;
  }
}
