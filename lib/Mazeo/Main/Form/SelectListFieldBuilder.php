<?php

namespace Mazeo\Main\Form;

use Mazeo\Main\Manager\Manager;
use Mazeo\Request\Request;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;
use Mazeo\Main\Entity\Entity;

/**
 * Class SelectListFieldBuilder
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
class SelectListFieldBuilder extends FormFieldManager implements FieldBuilderInterface {


    /**
     * Builds select fields
     * @param array $form - the current form
     * @param Reflector $entity - the current entity
     * @param Entity $class - the current entity class
     * @throws MazeoException
     * @return array
     */
    public static function buildView($form, Reflector $entity, Entity $class)
    {
        $fields = array();
        $name = strtolower($entity->getShortName());
        $request = new Request();
        $defaultValue = 0;
        foreach ($form as $k => $v) {
            if($v['type'] === 'list') {
              if (!isset($v['forValidationOnly']) && !$v['forValidationOnly']) {
                if (in_array($k, $entity->getProperties()) || (array_key_exists('detached',$v) && $v['detached'])) {
                  $info = self::getFormFieldInfo($entity,$k,$v);
                  $getter = 'get'.ucfirst($k);
                  $row['helper_msg'] = $info['helper_msg'];
                  $row['label'] = $info['label'];
                  $list = '';
                  $param = '';
                  if (isset($v['accepted_values'])) {
                    foreach ($v['accepted_values'] as $key => $value) {
                      if (in_array($k, $entity->getProperties()) && !isset($v['detached'])) { /***Attached field*/
                        if (empty($list)) {
                          $list = '<select name="'.$name.'['.$k.']'.'" '.$info['attributes'].' id="'.$info['id'].'">';
                          if(array_key_exists('default_value', $v))
                          $list .= '<option value="'.$defaultValue.'">'.$v['default_value'].'</option>';
                        }
                        if (method_exists($class, $getter)) {
                          if ($key==='interval') { /***case of interval number*/
                            if (!isset($value['min'])) {throw new MazeoException("The key \"min\" missing for option \"interval\" on list field \"{$k}\" in {$name}.yml form file"); exit; }
                            if (!isset($value['max'])) {throw new MazeoException("The key \"max\" missing for option \"interval\" on list field \"{$k}\" in {$name}.yml form file"); exit; }
                            for ($intervalValue = $value['min']; $intervalValue <= $value['max']; $intervalValue++) {
                              if (!is_null($class->$getter()) && $class->$getter() === $intervalValue) /***Edit and resubmitting case**/
                              $list .= '<option value="' . $intervalValue . '" selected>' . $intervalValue . '</option>'; //Option selected
                              else $list .= '<option value="' . $intervalValue . '">' . $intervalValue . '</option>';
                            }
                          } else {
                            if (!is_null($class->$getter()) && $class->$getter() === $value) /***Edit and resubmitting case**/
                            $list .= '<option value="' . $value . '" selected>' . $key . '</option>'; //Option selected
                            else $list .= '<option value="' . $value . '">' . $key . '</option>';
                          }
                        }
                      } elseif (array_key_exists('detached',$v) && $v['detached']) {
                        if (empty($list)) {
                          $list = '<select name="'.$k.'" '.$info['attributes'].' id="'.$info['id'].'">';
                          if(array_key_exists('default_value', $v))
                          $list .= '<option value="'.$defaultValue.'">'.$v['default_value'].'</option>';
                        }
                        if ($key==='interval') { /***case of interval number*/
                          if (!isset($value['min'])) {throw new MazeoException("The key \"min\" missing for option \"interval\" on list field \"{$k}\" in {$name}.yml form file"); exit; }
                          if (!isset($value['max'])) {throw new MazeoException("The key \"max\" missing for option \"interval\" on list field \"{$k}\" in {$name}.yml form file"); exit; }
                          for ($intervalValue = $value['min']; $intervalValue <= $value['max']; $intervalValue++) {
                            if($request->isMethod('post')) $param = $request->getParam($k);
                            if (!empty($param) && $param === $intervalValue) $list .= '<option value="' . $intervalValue . '" selected>' . $intervalValue . '</option>';
                            else $list .= '<option value="' . $intervalValue . '">' . $intervalValue . '</option>';
                          }
                        } else  {
                          if($request->isMethod('post')) $param = $request->getParam($k);
                          if (!empty($param) && $param === $value) $list .= '<option value="' . $value . '" selected>' . $key . '</option>';
                          else $list .= '<option value="' . $value . '">' . $key . '</option>';
                        }
                      } else throw new MazeoException("Method \"{$getter}\" does not exist in class {$entity->getName()}");
                    }
                  } elseif (array_key_exists('source', $v)) { //Source retrieved from database
                    $filter = array();
                    $selected = array();
                    $ignore = array();
                    $only = array();
                    $selectedColumn = null;
                    $selectedValue = null;
                    $ignoreColumn = null;
                    $ignoreValue = null;
                    $onlyColumn = null;
                    $onlyValue = null;
                    $options = array();
                    $defultValueIsAdded = false;
                    if (array_key_exists('manager', $v['source'])) {
                      $manager = $v['source']['manager'];
                      if (!class_exists($manager))
                      throw new MazeoException("Yaml form field \"{$k}\" binding source \"{$manager}\"
                      must be an instance of Mazeo\\Main\\Manager\\Manager type of ".gettype($manager) ." found");
                    } else throw new MazeoException("The key \"manager\" missing for option \"source\" in {$name}.yml form file");
                    if (array_key_exists('fields', $v['source'])) $columns = $v['source']['fields'];
                    else throw new MazeoException("The key \"field\" missing for option \"source\" in {$name}.yml form file");
                    if (array_key_exists('filter', $v['source'])) $filter = $v['source']['filter'];
                    if (array_key_exists('options', $v['source'])) $options = $v['source']['options'];
                    if (array_key_exists('selected', $v['source']))  {
                      $selected = $v['source']['selected'];
                      if (isset($selected['classAttr'])) {
                        $selectedColumn = 'get'.ucfirst($selected['classAttr']);
                        $selectedValue = null;
                      }
                      else {
                        if (array_key_exists('column', $selected)) $selectedColumn = 'get'.ucfirst($selected['column']);
                        else throw new MazeoException("The key \"column\" missing for option \"selected\" on list field \"{$k}\" in {$name}.yml form file");
                        if (array_key_exists('value', $selected)) $selectedValue = $selected['value'];
                        else throw new MazeoException("The key \"value\" missing for option \"selected\" on list field \"{$k}\" in {$name}.yml form file");
                      }

                    }
                    if (array_key_exists('ignore', $v['source']) && !array_key_exists('only',$v['source']))  {
                      $ignore = $v['source']['ignore'];
                      if (array_key_exists('column', $ignore)) $ignoreColumn = 'get'.ucfirst($ignore['column']);
                      else throw new MazeoException("The key \"column\" missing for option \"ignore\" on list field \"{$k}\" in {$name}.yml form file");
                      if (array_key_exists('value', $ignore)) $ignoreValues = $ignore['value'];
                      else throw new MazeoException("The key \"value\" missing for option \"selected\" on list field \"{$k}\" in {$name}.yml form file");
                    }
                    if(array_key_exists('only',$v['source']))  {
                      $only = $v['source']['only'];
                      if (array_key_exists('column', $only)) $onlyColumn = 'get'.ucfirst($only['column']);
                      else throw new MazeoException("The key \"column\" missing for option \"ignore\" on list field \"{$k}\" in {$name}.yml form file");
                      if (array_key_exists('value', $only)) $onlyValues = $only['value'];
                      else throw new MazeoException("The key \"value\" missing for option \"selected\" on list field \"{$k}\" in {$name}.yml form file");
                    }
                    $manager = new $manager();
                    if (sizeof($filter) > 0) $entities= $manager->findAllBy($filter,$options);
                    else $entities = $manager->findAll($options);
                    $idMethod = 'get'.ucfirst($manager::ID);
                    foreach ($entities as $item) {
                      if (array_key_exists('format', $v['source'])) {
                        $format = $v['source']['format'];
                        $itemEntity = new Reflector($item);
                        foreach ($columns as $field) {
                          $method = 'get'.ucfirst($field);
                          if (method_exists($item,$method)) $format = preg_replace("%".$field."%", $item->$method(), $format);
                          else throw new MazeoException("Unable to call method \"{$method}\" from {$itemEntity->getName()}");
                        }
                        if (method_exists($item, $idMethod)) {
                            $itemId = $item->$idMethod();
                            if (method_exists($class,$getter) && !isset($v['detached'])) {
                              if (empty($list)) {
                                $list = '<select name="'.$name.'['.$k.']'.'" '.$info['attributes'].' id="'.$info['id'].'">';
                              }
                              if (array_key_exists('default_value',$v) && !$defultValueIsAdded) {
                                $defultValueIsAdded = true;
                                $list .= '<option value="'.$defaultValue.'">'.$v['default_value'].'</option>';
                              }
                              if (!is_null($class->$getter()) && $class->$getter()->getId() === $itemId)  { /*Edit mode or resubmitting case*/
                                $list .= '<option value="'.$itemId.'" selected>'.$format.'</option>';
                              } elseif(!is_null($selectedColumn) && method_exists($item,$selectedColumn) && strtolower($item->$selectedColumn()) === strtolower($selectedValue)) {
                                /*When developer specify an specific value to preselected according to the getter of an given column*/
                                $list .= '<option value="'.$itemId.'" selected>'.$format.'</option>';
                              }
                              /**
                              * Dynamical Class attribute selected case, allow to selected item by it's entity id
                              */
                              elseif (!is_null($selectedColumn) && method_exists($class,$selectedColumn) && $class->$selectedColumn()==$itemId) {
                                $list .= '<option value="'.$class->$selectedColumn().'" selected>'.$format.'</option>'; //Option selected
                              }
                              else {
                                if (sizeof($ignore) > 0) {
                                  if(method_exists($item,$ignoreColumn) && !in_array($item->$ignoreColumn(),$ignoreValues)) {
                                    $list .= '<option value="'.$itemId.'">'.$format.'</option>';
                                  }
                                } elseif (sizeof($only) > 0) {
                                  if(method_exists($item,$onlyColumn) && in_array($item->$onlyColumn(),$onlyValues)) {
                                    $list .= '<option value="'.$itemId.'">'.$format.'</option>';
                                  }
                                } else $list .= '<option value="' . $itemId . '">' . $format . '</option>';
                              }
                            } elseif (isset($v['detached']) && $v['detached']) {
                                if (empty($list)) { /*Initializing select balize*/
                                  $list = '<select name="'.$k.'" '.$info['attributes'].' id="'.$info['id'].'">';
                                }
                                if (isset($v['default_value']) && !$defultValueIsAdded) {/*Selected default opt if one exist*/
                                  $defultValueIsAdded = true;
                                  $list .= '<option value="'.$defaultValue.'">'.$v['default_value'].'</option>';
                                }
                                if($request->isMethod('post')) { /*Getting user opt choise in resubmitting mode*/
                                  $param = $request->getParam($k);
                                }
                                if (!empty($param) && $param === $itemId) { /*Resubmitting case*/
                                  $list .= '<option value="' . $itemId . '" selected>' . $format . '</option>';
                                } elseif (method_exists($class, $getter) && !is_null($class->$getter()) && $class->$getter()->getId() === $itemId)  { /*Edit or resubmitting case*/
                                  $list .= '<option value="'.$itemId.'" selected>'.$format.'</option>';
                                } elseif (!is_null($selectedColumn) && method_exists($item,$selectedColumn) && strtolower($item->$selectedColumn()) === strtolower($selectedValue)) {
                                  /*Here we are specifying explicitly the value of  which column to select */
                                   $list .= '<option value="'.$itemId.'" selected>'.$format.'</option>'; //Option selected
                                } elseif (!is_null($selectedColumn) && method_exists($class,$selectedColumn) && $class->$selectedColumn()==$itemId) {
                                  /*Here the developer are specifying which attribute in the source class will be used to selected item in the targe manager fields*/
                                  $list .= '<option value="'.$class->$selectedColumn().'" selected>'.$format.'</option>'; //Option selected
                                } else {
                                  if (sizeof($ignore) > 0) {
                                    if(method_exists($item,$ignoreColumn) && !in_array($item->$ignoreColumn(),$ignoreValues)) {
                                      $list .= '<option value="'.$itemId.'">'.$format.'</option>';
                                    }
                                  } elseif (sizeof($only) > 0) {
                                    if(method_exists($item,$onlyColumn) && in_array($item->$onlyColumn(),$onlyValues)) {
                                      $list .= '<option value="'.$itemId.'">'.$format.'</option>';
                                    }
                                  } else {
                                    $list .= '<option value="' . $itemId . '">' . $format . '</option>';
                                  }
                                }
                              }
                            } else throw new MazeoException("Method \"{$idMethod}\" missing in the manager {$v['source']['manager']} corresponding entity");
                          } else throw new MazeoException("The key \"format\" missing for option \"source\" on field \"{$k}\" in {$name}.yml
                          form file ");
                        }
                      }
                      $list .='</select>';
                      $row['input'] = $list;
                      $fields[$k] = $row;
                    } else throw new MazeoException("The field \"{$k}\" in {$name}.yml form file does not matches any attributes of class
                    {$entity->getName()} or maybe the option \"detached\" missed on it ");
                  }
              }
          }
        return $fields;
    }
}
