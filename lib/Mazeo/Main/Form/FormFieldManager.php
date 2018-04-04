<?php

namespace Mazeo\Main\Form;

use Mazeo\Main\Entity\Entity;
use Mazeo\Util\Util\Reflector;

/**
 * Class FormFieldManager
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
class FormFieldManager {

    /**
     * Generate id for custom form input element
     * @param string  $fieldName - the current field name
     * @param string $formEntity - the current associated entity full namespace
     * @return string
     */
    public static final function generateSpecificId($fieldName, $formEntity) {
        $ns = explode('\\',$formEntity);
        $id = strtolower($ns[0]).'_'.strtolower($ns[1]).'_'.strtolower($ns[3]).'_'.strtolower($fieldName);
        return $id;
    }

    /**
     * Generate id of form input element
     * @param Reflector  $entity - the current field name
     * @param string $fieldName - the current field name
     * @return string
     */
    public final function generateId(Reflector $entity, $fieldName) {
        $ns = explode('\\',$entity->getName());
        $id = strtolower($ns[0]).'_'.strtolower($ns[1]).'_'.strtolower($ns[3]).'_'.strtolower($fieldName);
        return $id;
    }
    /**
     * Generate attributes of form input element
     * @param array $values - the array of values from which to generate attributes
     * @return string
     */
    public static final function getAttributes(array $values) {
        $attributes = null;
        $class = '';
        if(isset($values['class'])) {
            foreach ($values['class'] as $cls) $class .= ' ' . $cls;
            $attributes = 'class="' . trim($class) . '"';
        }
        foreach ($values as $key => $value) {
          if ($key!=='class') { $attributes .= ' '.$key.'="'.$value.'"'; }
        }
        /*if (isset($values['placeholder'])) $attributes .= ' placeholder="'.$values['placeholder'].'"';
        if (isset($values['id'])) $attributes .= ' id="'.$values['id'].'"';
        if (isset($values[''])) $attributes .= ' id="'.$values['id'].'"';
        if (isset($values['id'])) $attributes .= ' id="'.$values['id'].'"';*/
        return $attributes;
    }
    /**
     * Checks whether current field have checked option
     * @param array $field - the current field array
     * @param string $val - the value from which to checks
     * @return string
     */
    public static final function isChecked(array $field, $val=null) {
        if (isset($field['checked']))
          if ($field['checked'] === $val || $field['checked'] === true) return 'checked';
    }
    /**
     * Returns the default value of given field
     * @param array $field - the given field array
     * @return string
     */
    public static final function getFieldDefaultValue(array $field)
    {
        if (isset($field['value'])) return $field['value'];
    }

    /**
     * @param Reflector $entity
     * @param $fieldName
     * @param array $fieldValues
     * @return array
     */
    public final function getFormFieldInfo(Reflector $entity, $fieldName, array $fieldValues)
    {
        $return = array();
        $attributes = null;
        $labelAttributes = null;
        $helperMsg = null;
        $label = null;
        $name = null;
        $id = null;
        $id = self::generateId($entity,$fieldName);
        if (isset($fieldValues['attributes'])) $attributes = self::getAttributes($fieldValues['attributes']);
        if (isset($fieldValues['label']))  {
            if (isset($fieldValues['label']['attributes']))
                $labelAttributes = self::getAttributes($fieldValues['label']['attributes']);
            $label = '<label for="' . $id . '" '.$labelAttributes.'>' . $fieldValues['label']['label'] . '</label>';
        }
        if (isset($fieldValues['detached'])) $name = $fieldName;
        else $name = strtolower($entity->getShortName()).'[' . $fieldName . ']';
        if (isset($fieldValues['helper_msg'])) $helperMsg = $fieldValues['helper_msg'];
        $return['attributes'] = $attributes;
        $return['helper_msg'] = $helperMsg;
        $return['name'] = $name;
        $return['label'] = $label;
        $return['id'] = $id;
        return $return;
    }

}
