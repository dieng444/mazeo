<?php

namespace Mazeo\Util\Validator;

use Mazeo\Main\Entity\Entity;
use Mazeo\Request\Request;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;

/**
 * Class SelectListValidator
 * @package Mazeo\Util\Validator
 * @author Macky Dieng
 * @copyright 2016 - Mazeberry
 */
class SelectListValidator implements ValidatorInterface
{
    /**
     * Checks whether form select fields are valid
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
        $name = $class->getShortName();
        $request = new Request();
        foreach ($form as $key => $value) {
            if ($value['type']==='list') {
                if ($key!=='form') {
                    $getter = 'get' . ucfirst($key);
                    if (method_exists($entity, $getter)) {
                        if (isset($value['required'])) {
                          if (isset($value['accepted_values'])) {
                            if (!isset($value['accepted_values']['interval'])) {
                              if (!in_array($entity->$getter(), $value['accepted_values'])) $errors[$key] = $value["accepted_values_msg"];
                            } elseif (isset($value['accepted_values']['interval'])) {
                              $interval = $value['accepted_values']['interval'];
                              if ($entity->$getter() < $interval['min'] || $entity->$getter() > $interval['max']) {
                                $errors[$key] = $value["accepted_values_msg"];
                              }
                            }
                          } elseif (isset($value['source'])) {
                            self::performSourceAction($value,$key,$name,$request,$errors);
                          } else throw new MazeoException("The key \"accepted_values_msg\" missed on the field \"${key}\" in {$name}.yml form file");
                        }
                    } elseif (isset($value['detached']) && $value['detached']) {
                        if ($request->isMethod('post')) {
                            if (isset($value['required'])) {
                              if (isset($value['accepted_values']) && !isset($value['source'])) {
                                if (!in_array($request->getParam($key), $value['accepted_values'])) $errors[$key] = $value['accepted_values_msg'];
                                $val = $request->getParam($key);
                                if (!isset($value['accepted_values']['interval'])) {
                                  if (!in_array($val, $value['accepted_values'])) $errors[$key] = $value["accepted_values_msg"];
                                } elseif (isset($value['accepted_values']['interval'])) {
                                  $interval = $value['accepted_values']['interval'];
                                  if ($val < $interval['min'] || $val > $interval['max']) {
                                    $errors[$key] = $value["accepted_values_msg"];
                                  }
                                }
                              } elseif (isset($value['source'])) {
                                self::performSourceAction($value,$key,$name,$request,$errors);
                              }
                            }
                        }
                    } else throw new MazeoException("Unable to call method {$getter} from {$className}");
                }
            }
        }
        return $errors;
    }
    public function performSourceAction($value,$key,$name,$request,&$errors)
    {
      if (isset($value['source']['manager'])) {
          $manager = new $value['source']['manager']();
          $obj = $manager->findOne($request->getParam($key));
          if (isset($value['accepted_values_msg'])) {
            if (is_null($obj)) $errors[$key] = $value["accepted_values_msg"];
          } else throw new MazeoException("The key \"accepted_values_msg\" for option \"source\" missed on the field \"${key}\" in {$name}.yml form file");
      } else throw new MazeoException("The key \"manager\" for option \"source\" missed on the field {$key} in {$name}.yml form file");
    }
}
