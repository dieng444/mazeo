<?php

namespace Mazeo\Util\Util;

/**
 * Class Translator - allows to manage the app translation
 * @package Mazeo\Util\Util
 * @author Macky Dieng
 * @copyright 2017 the author
 */
class Translator
{
  private static $instance;
  private $currentLang;
  const DEFAULT_LOCALE = 'fr_FR';
  const LOCALE_DIR = 'app/locale/';

  /**
   * [__construct description]
   */
  public function __construct()
  {
    $this->switchLanguage();
  }

  /**
  * Disable cloning
  */
  private function __clone()
  {
  }

  /**
   * @return Translator
   */
  public static function getInstance()
  {
      if (!(self::$instance instanceof self)) {
          self::$instance = new self();
      }
      return self::$instance;
  }
  private function parseDefaultLanguage($http_accept)
  {
    $langStr = explode(",",$http_accept); /*** value => es,en-us;q=0.3,de;q=0.1 for example*/
    $lang = array();
    foreach ($langStr as $val) {
      if (preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i",$val,$matches)) {
        $lang[$matches[1]] = (float)$matches[2]; #example this array content (fr_FR => 0.4) fr_FR,fr,q=0.4
      }
      else {
        $lang[$val] = 1.0;
      }
    }
    $qval = 0.0;
    foreach ($lang as $key => $value) {
      /**
       * #$qval contain always the highest q-value
       * default language highest q-value
       */
      if ($value > $qval) {
        $qval = (float)$value;
        $deflang = $key;
      }
    }
    return str_ireplace('-','_',$deflang);
  }
  /**
   * [getDefaultLanguage description]
   * @return [type] [description]
   */
   private function getDefaultLanguage()
   {
     $lang = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
     if (isset($lang) && strlen($lang) > 1) {
       $lang = $this->parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
     } else {
       $lang = $this->parseDefaultLanguage(NULL);
     }
     return $lang;
   }
  /**
   * [switchLanguage description]
   * @param  [type] $lang [description]
   * @return [type]       [description]
   */
  public function switchLanguage($lang=null)
  {
    $locale = null;
    if (!is_null($lang) && strlen($lang) > 1) {
      if (isset($_SESSION['userlang']) && !empty($_SESSION['userlang'])) {
        unset($_SESSION['userlang']); /*Delecting old language cookie/session if one exists*/
        /*setcookie('userlang'); #Deleting the old language cookie*/
      }
      $locale = $lang;
      $_SESSION['userlang'] = $locale;
      /*setcookie('userlang', $locale, time()+(60*60*24*30*12), '/', 'http://loura.local/');*/
    } else {
      if (isset($_SESSION['userlang']) && !empty($_SESSION['userlang'])) {
        $locale = $_SESSION['userlang']; /***Use user prefered language cookie/session if one exists*/
      } else {
        $locale = $this->getDefaultLanguage(); /*No cookie/session found, we can retrieve the user his default language value.*/
      }
    }
    $this->currentLang = $locale;
    setlocale(LC_TIME, "");
    setlocale(LC_ALL, $locale.'.UTF-8');
    putenv('LC_ALL='.$locale.'.UTF-8');
    if (is_dir(self::LOCALE_DIR)) {
      bindtextdomain($locale,self::LOCALE_DIR);
    } else {
      var_dump('The current local path does not exists on the current server'. ' ' .self::LOCALE_DIR);
    }
    textdomain($locale);
    /*$locale_info = localeconv();*/
  }
  /**
   * [getText description]
   * @param  [type] $text [description]
   * @return [type]       [description]
   */
  public function getText($text)
  {
    echo _($text);
  }
  public function getCurrentLang()
  {
    return $this->currentLang;
  }
}
