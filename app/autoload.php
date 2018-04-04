<?php

/**
 * Class Autoloader - Manages classes self loading
 * @author Macky Dieng
 */
class Autoloader
{
  /**
   * @var
   * Constant of the framework configuration end point directory
   */
  const APP_DIR = 'app/';

  /**
   * @var
   * Constant of the library end point directory
   */
  const LIB_DIR = 'lib/';

  /**
   * @var
   * Constant of the framework source end point directory
   */
  const SRC_DIR = 'src/';

  /**
   * Class constructor - register all specified Loader
   */
  public function __construct()
  {
    spl_autoload_register(array($this, 'classLoader'));
    spl_autoload_register(array($this, 'libraryLoader'));
    spl_autoload_register(array($this, 'appClassLoader'));
  }

  /**
   * Manages self loading of packages in the "src" directory
   * @param object $class : Class to load
   * @return void
   */
  public static function classLoader($class)
  {
    $namespace = explode('\\', $class);
    $path = implode('/', $namespace);
    $fullPath = self::SRC_DIR.$path.'.php';
    if (is_readable($fullPath)) {
      require_once($fullPath);
    }
  }

  /**
   * Manages self loading of packages in the "lib" directory
   * @param object $class - Class to load
   * @return void
   */
  public static function libraryLoader($class)
  {
    $namespace = explode('\\', $class);
    $path = implode('/', $namespace);
    $fullPath = self::LIB_DIR.$path.'.php';
    if (is_readable($fullPath)) {
      require_once($fullPath);
    }
  }
  /**
   * Manages self loading of packages in the "app" directory
   * @param object $class - Class to load
   * @return void
   */
  public static function appClassLoader($class)
  {
    $namespace = explode('\\', $class);
    $path = implode('/', $namespace);
    $fullPath = self::APP_DIR.$path.'.php';
    if (is_readable($fullPath)) {
      require_once($fullPath);
    }
  }
}
