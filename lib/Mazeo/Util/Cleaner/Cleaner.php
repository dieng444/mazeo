<?php

namespace Mazeo\Util\Cleaner;

/**
 * Class Cleaner
 * @package Mazeo\Util\Cleaner
 * @author Macky Dieng
 */
class Cleaner {

    /**
     * @var array - cleaners container
     */
    private static $cleanerContainer = array('HtmlCleaner','WhiteSpaceCleaner');

    /**
     * Launch all cleaners
     * @param array $data - Data to clean
     * @param array $form - the current form linked to the data
     * @return array
     */
    public static function launchCleaners(array $data, array $form) {
      foreach (self::$cleanerContainer as $cleaner) {
        if (method_exists(get_called_class(), $cleaner)) self::$cleaner($data, $form);
      }
      return $data;
    }

    /**
     * Remove html entities around form fields content
     * @param array $data - the data to clean
     * @param array $form - the current form linked to the data
     */
    private static final function HtmlCleaner(array  &$data, $form = array()) {
      foreach ($data as $key => $value) {
        if (isset($form[$key])) {
          if (!isset($form[$key]['accept_html']) && $form[$key]['accept_html']===true) {
            $data[$key] = strip_tags($value);
          }
        }
      }
    }
    /**
     * Remove white space around form fields content
     * @param array $data - the data to clean
     * @param array $form - the current form linked to the data
     */
    private static final function WhiteSpaceCleaner(array  &$data, $form = array()) {
        foreach ($data as $key => $value) {
          if (!is_array($value)) {
            $data[$key] = trim($value);
          }
        }
    }
}
