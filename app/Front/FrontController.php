<?php

namespace Front;

use Mazeo\Request\HttpStatusCode;
use Mazeo\Request\HttpStatusText;
use Mazeo\Router\Dispatcher;
use Mazeo\Templater\TwigTemplateRender;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;
use \PDOException;
use Loura\CommonBundle\Entity\Log;
use Loura\CommonBundle\Manager\LogManager;
use Loura\CommonBundle\Entity\Util;

/**
 * Class FrontController -  Call the dispatcher to dispatch action
 */
class FrontController
{
  /**
   * Runs the dispatcher
   */
  public static function run()
  {
    try {
      Dispatcher::dispatch();
    } catch (MazeoException $e) {
      self::triggerException($e);
    } catch (PDOException $e) {
      self::triggerException($e);
    } catch (\ErrorException $e) {
      self::triggerException($e);
    } catch (\Twig_Error $e) {
      self::triggerException($e);
    } catch (\ReflectionException $e) {
      self::triggerException($e);
    }
  }
  private final function triggerException(\Exception $e)
  {
    $templater = new TwigTemplateRender();
    $env = yaml_parse_file('app/parameters.yml')['env'];
    if (isset($env['prod']) && $env['prod'] === true) {
      $lm = new LogManager();
      $log = new Log(array(
        'crachedAt' => Util::getCurrentDate(),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'userAgent' => $_SERVER['HTTP_USER_AGENT']
      ));
      $lm->save($log);
      $templater->render('appException:error-occured.twig', array());
    } else {
      $currentException = new Reflector($e);
      $statusMsg = array_merge(HttpStatusText::getText(HttpStatusCode::INTERNAL_SERVER_ERROR));
      $exception = array('message' => $e->getMessage(), 'stackTrace' => $e->getTraceAsString(),
      'name'=> $currentException->getShortName(), 'line' => $e->getLine(), 'file' => $e->getFile());
      $return = array('statusMsg' => $statusMsg, 'exception' => $exception);
      $templater->render('appException:exceptionStackTrace.html.twig', $return);
    }
  }
}
