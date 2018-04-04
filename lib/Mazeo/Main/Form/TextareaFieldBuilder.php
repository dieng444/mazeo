<?php

namespace Mazeo\Main\Form;

use Mazeo\Main\Entity\Entity;
use Mazeo\Request\Request;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;

class TextareaFieldBuilder extends FormFieldManager implements FieldBuilderInterface {

    /**
     * Builds textarea fields
     * @param array $form - the current form
     * @param Reflector $entity - the current reflector
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
            if($v['type'] === 'textarea') {
              if (!isset($v['forValidationOnly']) && !$v['forValidationOnly']) {
                if (in_array($k, $entity->getProperties()) || (array_key_exists('detached', $v) && $v['detached'])) {
                    $info = self::getFormFieldInfo($entity,$k,$v);
                    $row['helper_msg'] = $info['helper_msg'];
                    $row['label'] = $info['label'];
                    $value = null;
                    if (in_array($k, $entity->getProperties())) {
                        $getter = 'get' . ucfirst($k);
                        if (!is_null($class->$getter())) $value = $class->$getter();
                        /***Resubmitting case when form is invalid**/
                    } elseif (isset($v['detached']) && $v['detached']) {/***Detached fields*/
                        if ($request->isMethod('post')) $param = $request->getParam($k);
                        if (!empty($param)) $value = $param;
                    }
                    /***do not bind package name in this case*/
                    $row['input'] = '<textarea name="' . $info['name'] . '" ' . $info['attributes'] . ' id="' . $info['id'] . '">' . $value . '</textarea>';
                    $fields[$k] = $row;
                } else throw new MazeoException("The field \"{$k}\" in {$name}.yml form file does not matches any attributes of class
                {$entity->getName()} or maybe the option \"detached\" missed on it ");
            }
          }
        }
        return $fields;
    }
}
