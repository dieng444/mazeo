<?php

namespace Mazeo\Main\Entity;

use Mazeo\Util\Util\MazeoException;

/**
 * Class Entity - The main entity class
 * @package Mazeo\Main\Entity
 * @author Macky Dieng
 * @copyright The author
 */
abstract class Entity implements EntityInterface
{
  /**
   * [NOT_ALLOWED_VALUES Attributes containing a value of the following table will not be initialized]
   */
  const NOT_ALLOWED_VALUES = array('0');

  /**
   * Class constructor
   * @param array $data - array of data with which initialized entity setters
   * @throws MazeoException
   */
  public function __construct(array $data = array())
  {
    if (sizeof($data) > 0 ) { $this->initialize($data); }
  }
  /**
   * Initialize subclasses setters
   * @method initialize
   * @param array $data - array of data with which initialized entity setters
   * @throws MazeoException
   */
  public function initialize(array $data = array())
  {
      if (sizeof($data) > 0 ) {
          $entity = explode('\\',get_called_class());
          $entityName = strtolower($entity[sizeof($entity) - 1]);
          /**
           * The second condition below (the &&) means that some times entity and
           * property are the same name in the current class
           */
          if (array_key_exists($entityName, $data) && !property_exists($this, $entityName)) $data = $data[$entityName]; //if data coming from form
          foreach ($data as $attribute => $value) {
            if ((!empty($value) || is_numeric($value)) && !in_array($value,self::NOT_ALLOWED_VALUES)) {
              $method = 'set'.ucfirst($attribute);
              if (method_exists($this, $method)) $this->$method($value);
            }
          }
      } else { throw new MazeoException("You can not initialize entity with empty data array"); exit; }
  }
  /**
   * Checks whether current object already exists or not
   * @return boolean
   **/
  public function isNew()
  {
    if (static::getId() === null) return true;
    else return false;
  }

  /**
   * @param $attribute
   * @param $key
   * @return mixed
   * @throws MazeoException
   */
  public final function getValueInJson($attribute,$key) {
      $method = 'get'.ucfirst($attribute);
      if(method_exists($this, $method)) {
          $arrayDecode = json_decode($this->$method(),true);
          if(array_key_exists($key, $arrayDecode)) {
              return $arrayDecode[$key];
          }
      } else {
          throw new MazeoException("The attribute \"{$attribute}\" does not exist in current class ".get_called_class());
      }
  }
}
