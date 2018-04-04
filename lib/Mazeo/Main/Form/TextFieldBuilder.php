<?php

namespace Mazeo\Main\Form;

use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;
use Mazeo\Main\Form\SameInputFormat;
use Mazeo\Main\Entity\Entity;
use Mazeo\Request\Request;

/**
 * Class TextFieldBuilder
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
class TextFieldBuilder extends FormFieldManager implements FieldBuilderInterface {

    /**
     * The accepted field type for this class
     */
    const ACCEPTED_TYPE = array('text','password');

    /**
     * Builds text fields
     * @param array $form - the current form
     * @param Reflector $entity - the current entity
     * @return array
     * @param Entity $class - the current entity class
     * @throws MazeoException
     * @return array
     */
    public static function buildView($form, Reflector $entity, Entity $class) {
      $fields = array();
      $request = new Request();
      $name = strtolower($entity->getShortName());
      foreach ($form as $k => $v) {
        if(in_array($v['type'], self::ACCEPTED_TYPE)) {
          if (!isset($v['forValidationOnly']) && !$v['forValidationOnly']) {
            if (in_array($k, $entity->getProperties()) || (array_key_exists('detached', $v) && $v['detached'])) {
              $info = self::getFormFieldInfo($entity,$k,$v);
              $value = self::getFieldValue($v,$entity,$class,$k,$request);
              $row['label'] = $info['label'];
              if (isset($v['helper_msg'])) $row['helper_msg'] = $info['helper_msg'];
              $row['input'] = '<input type="' . $v['type'] . '" name="' . $info['name'] . '" ' . $info['attributes'] . ' id="' . $info['id'] . '" ' . $value . '>';
              $fields[$k] = $row;
            } else throw new MazeoException("The field \"{$k}\" in {$name}.yml form file from entity \"{$entity->getName()}\"does not matches any attributes of class
            {$entity->getName()} or maybe the option \"detached\" missed on it ");
          }
        }
      }
      return $fields;
    }

    /**
     * @param $v
     * @param $entity
     * @param $class
     * @param $k
     * @param $request
     * @return null|string
     */
    public final function getFieldValue($v,$entity,$class,$k,$request) {
        $value = null;
        if(isset($v['default_value'])) $value = 'value="'. $v['default_value'] . '"';
        if (in_array($k, $entity->getProperties())) {
            $getter = 'get' . ucfirst($k);
            if (!is_null($class->$getter())) {
              $value = 'value="' . $class->$getter() . '"'; /***Edition mode**/
            }
        } elseif (isset($v['detached']) && $v['detached']) {
            if ($request->isMethod('post')) {
              $param = $request->getParam($k);
              $value = !empty($param) ? 'value="' . $param . '"' : null;
            }
        }
        return $value;
    }
}
