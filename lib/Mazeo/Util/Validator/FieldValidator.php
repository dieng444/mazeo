<?php

namespace Mazeo\Util\Validator;

use Mazeo\Main\Entity\Entity;
use Mazeo\Request\Request;
use Mazeo\Util\Util\Reflector;
use Mazeo\Util\Util\MazeoException;

/**
 * Class FieldValidator
 * @package Mazeo\Util\Validator
 * @author Macky Dieng
 * @copyright 2016 - Mazeberry
 */
class FieldValidator implements ValidatorInterface
{
    /**
     * Checks whether the lengths of the form fields are respected
     * @param array $form - the current form
     * @param Entity $entity - the current entity linked to the form
     * @throws MazeoException
     * @return array
     */
    public static function validate(array $form, Entity $entity)
    {
        $errors = array();
        $class = new Reflector($entity);
        $className = $class->getName();
        $request = new Request();
        foreach ($form as $key => $value) {
            if ($key!=='form') {
                $getter = 'get'.ucfirst($key);
                if (method_exists($entity, $getter)) { /***Attached fields*/
                    $getterValue = $entity->$getter();
                    if(isset($value['blank']) && $getterValue === null && !$value["blank"]) {
                        if (array_key_exists('empty_error_msg',$value)) $errors[$key] = $value["empty_error_msg"];
                        else throw new MazeoException("Missing option \"empty_error_msg\" on the attribute \"{$key}\" from {$className} form file");
                    } elseif (isset($value['min_length']) && $getterValue!==null && strlen($getterValue) < $value['min_length']) { /***Length error*/
                        if (isset($value['length_error_msg'])) $errors[$key] = $value["length_error_msg"];
                        else throw new MazeoException("Missing option \"length_error_msg\" from {$className} form file");
                    } elseif (isset($value['format']) && $getterValue!==null && !preg_match($value['format'], $getterValue)) { /***Wrong format*/
                        if (isset($value['format_error_msg'])) $errors[$key] = $value["format_error_msg"];
                        else throw new MazeoException("Missing option \"format_error_msg\" from {$className} form file");
                    } elseif (isset($value['mustBeIdenticalTo'])) {
                      $getter = 'get'.ucfirst($value['mustBeIdenticalTo']);
                      if (method_exists($entity, $getter)) $identicalFieldValue = $entity->$getter();
                      else $identicalFieldValue = $request->getParam($value['mustBeIdenticalTo']);
                      if ($getterValue!==$identicalFieldValue) $errors[$key] = $value['identical_error_msg'];
                    }
                } elseif (isset($value['detached']) && $value['detached']) { /***Detached fields*/
                    /***
                     * Particular case  for checkbox, if the current checkbox does not checked by the user,
                     * it not taken into account in current post request so we need to check in_array($k,$request->getRequest('post')
                     */
                    if ($request->isMethod('post') && isset($request->getRequest('post')[$key])) {
                        $param = $request->getParam($key);
                        if (isset($value['blank']) && empty($param)) {
                            if (isset($value['empty_error_msg'])) $errors[$key] = $value["empty_error_msg"];
                            else throw new MazeoException("Missing option \"empty_error_msg\" from {$className} form file in ".__CLASS__);
                        } elseif (isset($value['min_length']) && strlen($param) < $value['min_length']) {
                            if (isset($value['length_error_msg'])) $errors[$key] = $value["length_error_msg"];
                            else throw new MazeoException("Missing option \"length_error_msg\" from {$className} form file in ".__CLASS__." on line 59");
                        } elseif (isset($value['format']) && $param !==null && !preg_match($value['format'], $param)) {
                            if (isset($value['format_error_msg'])) $errors[$key] = $value["format_error_msg"];
                            else throw new MazeoException("Missing option \"format_error_msg\" from {$className} form file in ".__CLASS__." on line 63");
                        } elseif (isset($value['mustBeIdenticalTo'])) {
                          $getter = 'get'.ucfirst($value['mustBeIdenticalTo']);
                          if (method_exists($entity, $getter)) $identicalFieldValue = $entity->$getter();
                          else $identicalFieldValue = $request->getParam($value['mustBeIdenticalTo']);
                          if ($param!==$identicalFieldValue) $errors[$key] = $value['identical_error_msg'];
                        }
                    }
                } else throw new MazeoException("Unable to call method {$getter} from {$className} or maybe option \"detached\" missed on the attribute");
            }
        }
        return $errors;
    }
}
