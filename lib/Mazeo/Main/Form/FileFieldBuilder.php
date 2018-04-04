<?php

namespace Mazeo\Main\Form;

use Mazeo\Main\Entity\Entity;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;

/**
 * Class FileFieldBuilder
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
class FileFieldBuilder extends FormFieldManager implements FieldBuilderInterface {


    /**
     * Builds file fields
     * @param array $form - the current form
     * @param Reflector $entity - the current reflector
     * @param Entity $class - the current entity class
     * @throws MazeoException
     * @return array
     */
    public static function buildView($form, Reflector $entity, Entity $class)
    {
        $fields = array();
        $name = strtolower($entity->getShortName());
        foreach ($form as $k => $v) {
            if($v['type'] === 'file') {
              if (!isset($v['forValidationOnly']) && !$v['forValidationOnly']) {
                if (in_array($k, $entity->getProperties()) || (array_key_exists('detached', $v) && $v['detached'])) {
                    $info = self::getFormFieldInfo($entity,$k,$v);
                    $value = null;
                    $row['input'] = '<input type="' . $v['type'] . '" name="' . $info['name'] . '" ' . $info['attributes'] . ' id="' . $info['id'] . '">';
                    $fields[$k] = $row;
                } else throw new MazeoException("The field \"{$k}\" in {$name}.yml form file does not matches any attributes of class
                {$entity->getName()} or maybe the option \"detached\" missed on it ");
            }
          }
        }
        return $fields;
    }
}
