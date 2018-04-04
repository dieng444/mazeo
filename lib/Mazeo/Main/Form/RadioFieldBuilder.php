<?php

namespace Mazeo\Main\Form;

use Mazeo\Request\Request;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;
use Mazeo\Main\Entity\Entity;

/**
 * Class RadioFieldBuilder
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
class RadioFieldBuilder extends FormFieldManager implements FieldBuilderInterface {

    /**
     * Builds Radio fields
     * @param array $form - the current form
     * @param Reflector $entity - the current entity
     * @param Entity $class - the current entity class
     * @throws MazeoException
     * @return array
     */
    public static function buildView($form, Reflector $entity, Entity $class)
    {
        $fields = array();
        $request = new Request();
        $name = strtolower($entity->getShortName());
        foreach ($form as $k => $v) {
          if($v['type'] === 'radio') {
            if (!isset($v['forValidationOnly']) && !$v['forValidationOnly']) {
              if (in_array($k, $entity->getProperties()) || (isset($v['detached']) && $v['detached'])) {
                  $info = self::getFormFieldInfo($entity,$k,$v);
                  $checked = null;
                  if (isset($v['accepted_values'])) {
                    foreach ($v['accepted_values'] as $val) {
                      $checked = FormFieldManager::isChecked($v, $val['value']);
                      $value = $val['value'];
                      $param = '';
                      $id = self::generateSpecificId($val['value'],$entity->getName());
                      $row['label'] = '<label for="' . $info['id'] . '">' . $val['display'] . '</label>';
                      if (in_array($k, $entity->getProperties())) {/***Attached fields**/
                        $getter = 'get' . ucfirst($k); /***Resubmitting case when form is invalid, rechecks the items selected by user from the form*/
                        if (!is_null($class->$getter()) && $class->$getter() === $value) {
                          $checked = 'checked';
                        }
                      } elseif (isset($v['detached']) && $v['detached']) { /***Detached fields*/
                        if ($request->isMethod('post')) $param = $request->getParam($k);
                        if (!empty($param) && $param === $value) {
                          $checked = 'checked';
                        }
                      }
                      $row['helper_msg'] = $info['helper_msg'];
                      /***if it is a form validation, all unmarked fields are unchecked*/
                      $row['input'] = '<input type="' . $v['type'] . '" value="' . $value . '" name="' . $info['name'] . '" ' . $info['attributes'] . ' id="' . $id . '" ' . $checked . '>';
                      $fields[strtolower($val['value'])] = $row;
                    }
                  }
              } else throw new MazeoException("The field \"{$k}\" in {$name}.yml form file does not matches any attributes of class
              {$entity->getName()} or maybe the option \"detached\" missed on it ");
          }
        }
      }
      return $fields;
    }
}
