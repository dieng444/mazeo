<?php

namespace Mazeo\Main\Form;

use Mazeo\Request\Request;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;
use Mazeo\Main\Entity\Entity;

/**
 * Class CheckboxFieldBuilder
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
class CheckboxFieldBuilder extends FormFieldManager implements FieldBuilderInterface {

    /**
     * Builds checkbox fields
     * @param array $form - the current form
     * @param Reflector $entity - the current entity
     * @param Entity $class - the current entity class
     * @return array
     * @throws MazeoException
     */
    public static function buildView($form, Reflector $entity, Entity $class)
    {
        $request = new Request();
        $fields = array();
        $name = strtolower($entity->getShortName());
        foreach ($form as $k => $v) {
            if ($v['type'] === 'checkbox') {
              if (!isset($v['forValidationOnly']) && !$v['forValidationOnly']) {
                if (in_array($k, $entity->getProperties()) || (isset($v['detached']) && $v['detached'])) {
                  $info = self::getFormFieldInfo($entity,$k,$v);
                  $checked = self::isChecked($v);
                  $value = self::getFieldDefaultValue($v);
                  $row['label'] = $info['label'];
                  $getterValue = null;
                  $param = '';
                  $row['helper_msg'] = $info['helper_msg'];
                  if (in_array($k, $entity->getProperties())) {
                    /***Attached field*/
                    $getter = 'get' . ucfirst($k);
                    /***$getterValue = $class->$getter();*/
                    if (!is_null($class->$getter())) $value = $class->$getter();
                    if (!(is_null($class->$getter())) && ($class->$getter() === $value)) { /** Rechecks the items  selected by user from the form*/
                      $checked = 'checked';
                    }
                  } elseif (isset($v['detached']) && $v['detached']) {/***Detached fields*/
                    /***
                    * Particular case  for checkbox, if the current checkbox does not checked by the user,
                    * it not taken into account in current post request so we need to check in_array($k,$request->getRequest('post')
                    */
                    if ($request->isMethod('post') && array_key_exists($k, $request->getRequest('post'))) $param = $request->getParam($k);
                    if (!empty($param)) $value = $param;
                    if (!empty($param) && $param === $value) {
                      /** Rechecks the items  selected by user from the form*/
                      $checked = 'checked';
                      /***
                      * $isResubmitting = true;
                      */
                    }
                  }
                  /***do not bind package name in this case*/
                  if (!empty($value)) $value = 'value="' . $value . '"';
                  $row['input'] = '<input type="' . $v['type'] . '" name="' . $info['name'] . '" ' . $info['attributes'] . ' id="' . $info['id'] . '" ' . $value . ' ' . $checked . '>';
                  $fields[$k] = $row;
                } else throw new MazeoException("The field \"{$k}\" in {$name}.yml form file does not matches any attributes of class
                {$entity->getName()} or maybe the option \"detached\" missed on it ");
              }
            }
        }
        return $fields;
    }
}
