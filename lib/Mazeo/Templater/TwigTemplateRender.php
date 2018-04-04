<?php

namespace Mazeo\Templater;

use Mazeo\Util\Util\Asset;
use Mazeo\Logging\AuthManager;
use Mazeo\Util\Util\MazeoException;
use Twig_Error_Loader;
use \Twig_Loader_Filesystem;
use Mazeo\Util\Util\Session;
use Mazeo\Util\Util\Translator;
use Mazeo\Request\Request;

/**
 * Class TwigTemplater
 * @package Mazeo\Templater
 * @author Macky Dieng
 */
class TwigTemplateRender extends Twig_Loader_Filesystem
{

    /**
     * Display a given template
     * @param String $view - the current view definition
     * @param Array $data - data to display in the template file
     * @throws MazeoException
     */
    public final function render($view, array $data)
    {
      $global = array();
      $session = Session::getInstance();
      $trans = Translator::getInstance();
      $global['route'] = $session->get('currentRoute') ? $session->get('currentRoute') : null;
      $this->initPackagesPath();
      $paths = $this->constructPackagesPath();
      $this->launchAddingPath($paths);
      $viewPart = explode(':',$view);
      $namespace = $viewPart[0];
      $template = $viewPart[1];
      $twig = new \Twig_Environment($this, array('debug' => true));
      $twig->addGlobal('asset', new Asset());
      $twig->addGlobal('auth', AuthManager::getInstance());
      $twig->addGlobal('session', $session);
      $twig->addGlobal('global', $global);
      $twig->addGlobal('post', $_POST);
      $twig->addGlobal('get', $_GET);
      $twig->addGlobal('_', $trans);
      echo $twig->render('@'.$namespace.'/'.$template,$data);
    }
    /**
     * Construct path of all packages and save them into
     * a session variable
     */
    private final function initPackagesPath()
    {
      $packageBase = yaml_parse_file('app/routing.yml');
      $packageBase = (array_filter($packageBase, function($k) {return $k !== 'default_route'; }, ARRAY_FILTER_USE_KEY));
      $packageDirArray = array();
      if (is_array($packageBase) && sizeof($packageBase) > 0) {
        if (!isset($_SESSION['packageDirArray']) || sizeof($packageBase) !== sizeof($_SESSION['packageDirArray'])) {
            foreach ($packageBase as $key => $package) {
              if ($key!=='default_route') {
                if(isset($package['base'])) {
                  $packagePart = explode(':',$package['base']);
                  $packageDirArray[] = 'src/'.$packagePart[0].'/'.$packagePart[1].'/'.'Resources/views/';
                }
              }
            }
            $packageDirArray[] = 'app/Resources/views/';
            $_SESSION['packageDirArray'] = $packageDirArray;
            unset($_SESSION['allDirPath']); /***Destroying the directories path session when package dir are updated*/
        }
      }
    }
    /**
     * Construct path of all packages and save them into
     * @return Array
     */
    private final function constructPackagesPath()
    {
        $allDirPath = array();
        if (!isset($_SESSION['allDirPath'])) {
            foreach ($_SESSION['packageDirArray'] as $path) {
              $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
              foreach ($objects as $name => $object) {
                if (!preg_match('#\..$#', $name)) {
                  $name = str_replace('/.', '', $name); /***Removing repeated parent directories (like views/.. for dir /views)*/
                  if (!in_array($name . '/', $allDirPath)) { /***Removing repeated self repository (like views/. for dir views/)*/
                    if (!preg_match('#\.#', $name)) { /***Do not take files into account*/
                      $namespace = $this->buildNamespace($name);
                      $allDirPath[$namespace] = $name.'/';
                    }
                  }
                }
              }
            }
            $_SESSION['allDirPath'] = $allDirPath;
            return $allDirPath;
        } else return $_SESSION['allDirPath'];
    }

    /**
     * Builds namespace for an given path name
     * @param $name
     * @return null|string
     */
    private final function buildNamespace($name)
    {
      $templateParts = explode('/',$name);
      $namespace = null;
      $tmpPath = '';
      $index = null;
      /***
       * In the following below, "index" equal to the started path of the namespace
       * Eg : for template path naming src/Acme/User/Resources/views/User/ index will equal to (5)
       * here after exploding the path name of course. So the retrieving will start
       * from the last "/User" position. That means src, Resources and views directories
       * does not taken into account.
       */
      if (preg_match('#app/Resources#',$name)) {
        $index = 3;
        /***
         * Here the namespace equal to "app" the global template area
         */
        $namespace = $templateParts[0];
      } else {
        $index = 5;
        /***
         * Here we are finding app an d package name like "AcmeUser" for package User located in the app Acme
         */
        $namespace = $templateParts[1].$templateParts[2];
      }
      /***
       * This loop concat all directories behind the "views" directories from the path.
       */
      for ($i = $index; $i < sizeof($templateParts); $i++) {if (isset($templateParts[$i])) $tmpPath .= $templateParts[$i];}
      $namespace .= $tmpPath;
      return $namespace;
    }

    /**
     * Allows to add all path in the system configuration
     * @param array $paths
     * @throws Twig_Error_Loader
     */
    private final function launchAddingPath(array $paths)
    {
        foreach ($paths as $namespace => $path) {
          $this->addPath($path,$namespace);
        }
    }
}
