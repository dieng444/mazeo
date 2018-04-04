<?php

namespace Mazeo\Main\Manager;

use Mazeo\Database\Database;
use Mazeo\Main\Entity\Entity;
use Mazeo\Util\Util\ErrorManager;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;
use Mazeo\Util\Util\Util;
use Mazeo\Util\Util\Asset;

/**
 * Class Manager
 * @package Mazeo\Main\Manager
 * @author Macky Dieng
 */
abstract class Manager implements ManagerInterface
{

    /**
     * Lower than
     */
    CONST LT = "lt";

    /**
     * Greater than
     */
    CONST GT = "gt";

    /**
     * Equal
     */
    CONST EQ = "eq";

    /**
     * Like with sensitive case
     */
    CONST ILIKE = "ilike";

    /**
     * [OPT_KEYS_TAB description]
     * @var array
     */
    CONST OPTS_KEYS_TAB = array('ORDER BY','LIMIT','OFFSET');

    CONST SLUG_SEPARATOR = '.';

    /**
     * @var \PDO - PDO instance variable
     */
    protected $db;

    /**
     * @var Entity - current entity linked to the current manager
     */
    private $entity;

    /**
     * @var array - array of entity
     */
    private $entities;

    /**
     * @var Reflector - the reflector instance variable
     */
    private $reflector;

    /**
     * @var $asset - Asset instance container
     */
    private $asset;

    /**
     * Manager constructor
     */
    public final function __construct()
    {
        $this->db = Database::getInstance()->getConnexion();
        $currentManager = new Reflector($this);
        $nsParts = explode('\\',$currentManager->getName());
        /***
         * Here we have some thing like UserManager and we are looking for the
         * corresponding entity "User" in the current Entity package.
         * */
        $this->entity = $nsParts[0].'\\'.$nsParts[1].'\\Entity'.'\\'.substr($nsParts[3],0,(strlen($nsParts[3]) - (strlen($nsParts[2]))));
        $this->entities = array();
        $this->reflector = new Reflector($this->entity);
    }
    /**
     * Retrieves a single mapped instance of current entity
     * this method bind the current data table returned by the request
     * to a single instance of current entity and return that instance just after
     * @param int $id - ID of the entity to find
     * @param array $wishedFields - return the new object with only these specified attributes
     * @throws MazeoException
     * @return Entity
     */
    public final function findOne($id,array $wishedFields=array())
    {
        if (!is_null($id)) {
          $fieldStr = $this->getFieldQueryStr($wishedFields);
          $fieldStr = 'select '.$fieldStr. ' '. 'from ';
          $query = $this->db->prepare($fieldStr .static::TABLE_NAME.' where ' .static::ID.' = :id');
          $query->bindValue(':id', (int)$id);
          $query->execute();
          $data = $query->fetch();
          if (!$data) return null;
          else return $this->getMappedEntity($data);
        } else { throw new MazeoException(ErrorManager::MissedParameterMsg('findOne','id')); exit; }
    }
    /**
     * Retrieves a single instance of current entity by filter
     * the method bind the current data table returned by the request
     * to a single instance of current entity and return that instance just after
     * @param array $filter - the filter to use for retrieve the corresponding row
     * @param array $wishedFields - return the new object with only these specified attributes
     * @throws MazeoException
     * @return Entity
     */
    public final function findOneBy(array $filter,array $wishedFields=array())
    {
        $query = '';
        if(is_array($filter) && sizeof($filter) > 0) {
            $params = $this->getFilterParams($filter, $query);
            $fieldStr = $this->getFieldQueryStr($wishedFields);
            $fieldStr = 'select '.$fieldStr. ' '. 'from ';
            $query = $this->db->prepare($fieldStr . static::TABLE_NAME . $query);
            $query->execute($params);
            $data = $query->fetch();
            if (!$data) return null;
            else return $this->getMappedEntity($data);
        } else { throw new MazeoException(ErrorManager::MissedParameterMsg('findOneBy','filter')); exit; }
    }
    /**
     * Retrieves all mapped instances of current entity, this method bound each row of
     * returned data to a single instance of current entity and return them then
     * @param array $options - If these options are specified, then they are taken
     * into effect during the execution of the request
     * @param array $wishedFields - return the new object with only these specified attributes
     * @throws MazeoException
     * @return array
     */
    public final function findAll(array $options = array(), array $wishedFields=array())
    {
      $fieldStr = $this->getFieldQueryStr($wishedFields);
      $fieldStr = 'select '.$fieldStr. ' '. 'from ';
      $query  = $fieldStr . static::TABLE_NAME;
      $query = $this->generateOptionsQuery($options,$query);
      $query = $this->db->prepare($query);
      $query->execute();
      $data = $query->fetchAll();
      return $this->getMappedEntities($data);
    }
    /**
     * Retrieves all mapped instances of current entity by filter this method bound each row of
     * returned data to a single instance of current entity and return them then
     * @param array $filter - the filter to use for retrieve the corresponding rows
     * @param array $options - If these options are specified, then they are taken
     * into effect during the execution of the request
     * @param array $wishedFields - return the new object with only these specified attributes
     * @throws MazeoException
     * @return array
     */
    public final function findAllBy(array $filter, array $options = array(), array $wishedFields=array())
    {
        $query = '';
        if(is_array($filter) && sizeof($filter) > 0) {
            $params = $this->getFilterParams($filter, $query);
            $query = $this->generateOptionsQuery($options,$query);
            $fieldStr = $this->getFieldQueryStr($wishedFields);
            $fieldStr = 'select '.$fieldStr. ' '. 'from ';
            $query = $this->db->prepare($fieldStr . static::TABLE_NAME . $query);
            $query->execute($params);
            $data = $query->fetchAll();
            return $this->getMappedEntities($data);
        } else { throw new MazeoException(ErrorManager::MissedParameterMsg('findAllBy','filter')); exit; }
    }
    /**
     * Insert or update current entity
     * if the ID of the entity is null, then it's new entity so will be inserted
     * otherwise if it not null so we have existing object so it will just be updated
     * @param $entity - the current entity to save
     * @throws MazeoException
     * @return boolean
     */
    public final function save(Entity $entity)
    {
        if(!is_null($entity)) {
            if ($entity->isNew()) return $this->insert($entity);
            else return $this->update($entity);
        } else { throw new MazeoException("You can not invoke method \"save\" on null entity"); exit; }
    }
    /**
     * Perform insert request on current entity
     * @param Entity $entity - the current $entity to insert
     * @throws MazeoException
     * @return boolean
     */
    private final function insert(Entity $entity)
    {
        $query = 'INSERT INTO '.static::TABLE_NAME.' (';
        $properties = $this->reflector->getProperties(); /**Current entity properties list*/
        $column_exception = static::COLUMN_EXCEPTION;
        $this->getInsertColumns($properties, $column_exception, $query);
        if(substr($query,strlen(trim($query)) - 1, 1) === ',') {/***Cleaning the query and removing the last comma.*/
            $query = substr(trim($query),0, strlen(trim($query)) - 1);
        }
        $query .= ') VALUES (';
        $this->getInsertValues($properties, $entity, $query);
        if(substr($query,strlen(trim($query)) - 1, 1) === ',') {/***Cleaning the query and removing the last comma.*/
            $query = substr(trim($query),0, strlen(trim($query)) - 1);
        }
        $query .= ')';
        return $this->db->query($query);
    }
    /**
     * Perform update request on current entity
     * @param Entity $entity
     * @throws MazeoException
     * @return boolean
     */
    private final function update(Entity $entity)
    {
        $query = 'UPDATE '.static::TABLE_NAME .' SET ';
        $columns = $this->parseTable();
        $fields = $this->getTableColumns();
        $values = array();
        $currentClass = new Reflector($entity);
        $currentClassName = $currentClass->getName();
        foreach ($columns as $col => $prop) {
            $getter = 'get'.$prop;
            if ($prop !== static::ID && in_array($col,$fields)) $query .= '"'.$col. '" = '.':'.$col.', ';
            if (property_exists($entity,$prop)) {
                $foreignManager = $this->reflector->isMappingProperty($prop);
                $matchedProperty = $this->reflector->isDetachedProperty($prop);
                if ($foreignManager !== false)  {
                    $col = $foreignManager['annotation']['thisColumn']; /***If the current property have relational binding option*/
                    if (!is_null($entity->$getter())) {
                      if ($entity->$getter() instanceof Entity) {
                        $class = new Reflector($entity->$getter());
                        $className = $class->getName();
                        if (method_exists($entity->$getter(), 'getId')) $values[':'.$col] = $entity->$getter()->getId();
                        else {throw new MazeoException("The binding entity {$className} in {$currentClassName} must have a \"getId\" method");exit;}
                      } else {throw new MazeoException("The binding property \"$prop\" must be an instance of Mazeo\\Main\\Entity\\Entity, property from current class {$currentClassName} "); exit;}
                    } else {
                      $values[':'.$col] = $entity->$getter();
                    }
                } elseif(!$matchedProperty) {
                  /***
                   * This !$matchedProperty condition means that when we have
                   * property in the current class who doest not have a
                   * corresponding column in the entity's table.
                   * That means we need the property in class but not in the
                   * table (computed property for example)
                   */
                    $values[':'.$col] = $entity->$getter();
                }
            }
        }
        if (!method_exists($entity,'get'.ucfirst(static::ID))) {
            throw new MazeoException("The current entity must have an \"".'get'.ucfirst(static::ID)."\" method"); exit;
        }
        $query = substr(trim($query),0, strlen(trim($query)) - 1); /***Cleaning the query and removing the last comma.*/
        $query .= ' WHERE "'.static::ID.'" = :'.static::ID;
        $query = $this->db->prepare($query);
        return $query->execute($values);
    }
    /**
     * Remove specific $entity from $database
     * @param Entity $entity - the current entity to remove
     * @throws MazeoException
     */
    public final function remove(Entity $entity)
    {
        if (!is_null($entity)) {
            if (method_exists($entity,'get'.ucfirst(static::ID))) $getter = 'get'.ucfirst(static::ID);
            else {throw new MazeoException("The current entity must have an \"".'get'.ucfirst(static::ID)."\" method");exit;}
            $query = $this->db->prepare('DELETE FROM '.static::TABLE_NAME.' WHERE '.static::ID.' = :'.static::ID);
            $query->bindValue(':'.static::ID, $entity->$getter());
            return $query->execute();
        } else {throw new MazeoException("You can not invoke method \"remove\" on null object");}
    }
    /**
     * Remove an list of entries in database
     * @param Entity $entity - foreign entity from entries will b deleted
     * @param String $prop - property on which foreign manager is binded in the current entity ("this_column" in annotation string)
     * @throws MazeoException
     */
    public final function removeAllBy(Entity $entity, $prop)
    {
        if (!is_null($entity)) {
            if (method_exists($entity,'get'.ucfirst(static::ID))) $getter = 'get'.ucfirst(static::ID);
            else {throw new MazeoException("The current entity must have an \"".'get'.ucfirst(static::ID)."\" method");exit;}
            $query = $this->db->prepare('DELETE FROM '.static::TABLE_NAME.' WHERE '.$prop.' = :'.$prop);
            $query->bindValue(':'.$prop, $entity->$getter());
            return $query->execute();
        } else {throw new MazeoException("You can not invoke method \"remove\" on null object");}
    }
    /**
     * helper method, that return wished fields query string
     * @param  array $wishedFields
     * @return String
     */
    private function getFieldQueryStr($wishedFields)
    {
      $fieldStr = '';
      if (sizeof($wishedFields) > 0) {
        $fieldStr = 'id,';
        foreach ($wishedFields as $field) {$fieldStr .= $field.',';}
        $fieldStr = substr(trim($fieldStr),0, strlen(trim($fieldStr)) - 1);
      } else { $fieldStr = '*'; }
      return $fieldStr;
    }
    /**
     * Return count of current table entries
     * @return int
     */
    public final function getCount() {
        return $this->db->query('select count(id) from '.static::TABLE_NAME)->fetch()['count'];
    }
    /**
     * @param array $filter - the filter to use for retrieve the corresponding row
     * @throws MazeoException
     * @return int
     */
    public final function getCountBy(array $filter){
        $query = '';
        if(is_array($filter) && sizeof($filter) > 0) {
            $params = $this->getFilterParams($filter, $query);
            $query = $this->db->prepare('select count(id) as count from '.static::TABLE_NAME.$query);
            return $query->execute($params)->fetch()['count'];
        } else { throw new MazeoException(ErrorManager::MissedParameterMsg('findCountBy','filter')); exit; }
    }
    /**
     * Builds and returns mapped entity
     * @param array $data - the current data table
     * @throws MazeoException
     * @return null
     */
    private final function getMappedEntity(array $data)
    {
      $newData = array();
      $entity = null;
      $column_exception = static::COLUMN_EXCEPTION;
      $properties = $this->reflector->getProperties();
      if (sizeof($data) > 0) {
        if (sizeof($column_exception) > 0 && $column_exception[0] === true) {
          $fields = $this->parseTable();
          /***$fields format look like this :  first_name => firstName*/
          foreach($fields as $k => $v) {
            if (in_array($v, $properties)) {
              $foreignManager = $this->reflector->isMappingProperty($v);
              if($foreignManager !== false) { /***If the current property have relational binding option*/
                $target = new $foreignManager['annotation']['target'](); /***The target relational entity manager*/
                $targetColumn = $foreignManager['annotation']['targetColumn'];
                $thisColumn = $foreignManager['annotation']['thisColumn'];
                if ($foreignManager['token'] === Reflector::SINGLE_ENTITY) {
                  /***
                  * Sometimes we just need a mapped field in insert mode and not in retrieve mode.
                  * To make the next condition possible, just add the needFields parameters
                  * to our finders while indicating the mapped fields to ignore
                  */
                  if (isset($data[$thisColumn])) {
                    $newData[$v] = $target->findOneBy(array(array($targetColumn => $data[$thisColumn],"eq")));  //Corresponding binned entity
                  }
                }
              } else $newData[$v] = $data[$k];
            }
          }
          $entity = new $this->entity($newData);
        } else {
          foreach ($properties as $prop) {
            $foreignManager = $this->reflector->isMappingProperty($prop);
            if($foreignManager !== false) { /***If the current property have relational binding option*/
              $target = new $foreignManager['annotation']['target'](); /***The target relational entity manager*/
              $targetColumn = $foreignManager['annotation']['targetColumn'];
              $thisColumn = $foreignManager['annotation']['thisColumn'];
              if ($foreignManager['token'] === Reflector::SINGLE_ENTITY) {
                if (isset($data[$thisColumn])) { /***for ignoring fields in retrieve mode*/
                  $data[$prop] = $target->findOneBy(array(array($targetColumn => $data[$thisColumn],"eq")));  //Corresponding binned entity
                }
              }
            }
          }
          $entity = new $this->entity($data);
        }
      }
      return $entity;
    }
    /**
     * Returns a list of mapped entity
     * @param $data
     * @return array
     */
    private final function getMappedEntities($data)
    {
        $entities = array();
        if(sizeof($data) > 0)  {
            foreach ($data as $row) {$entities[] = $this->getMappedEntity($row);}
        }
        return $entities;
    }
    /**
     * Builds and returns options request
     * @param array $options
     * @param $query
     * @return string
     * @throws MazeoException
     */
    private final function generateOptionsQuery(array $options, $query)
    {
        if (sizeof($options) > 0) {
            /***Case select with parameters options*/
            $orderby = "";
            $limit = "";
            $offset = "";
            foreach ($options as $k => $v) {
              $k = strtoupper($k);
              if (in_array($k,self::OPTS_KEYS_TAB)) {
                if ($k === 'ORDER BY') {
                  if (is_array($v)) {
                    $orderOptionCounter = 0;
                    foreach ($v as $keyV => $uV) {
                      if (is_null($keyV) || !in_array(strtoupper($uV), array('DESC', 'ASC'))) {
                        throw new MazeoException("The second parameter of \"ORDER BY\" must be DESC or ASC");
                      }
                      if (!$orderOptionCounter) $orderby .= ' ORDER BY ';
                      if ($orderOptionCounter) $orderby .= ",";
                      if (preg_match("%->>%", $keyV)) $columnName = '"' . preg_split("%->>%", $keyV)[0] . '"->>' . preg_split("%->>%", $keyV)[1];
                      else $columnName = $keyV;
                      $orderby .= $columnName . ' ' . $uV;
                      $orderOptionCounter++;
                    }
                  } else throw new MazeoException("The \"ORDER BY\" parameter value must be an array type of " . gettype($k) . " found");
                }
                if ($k === 'LIMIT') {
                  if (gettype($v) === 'integer') $limit .= ' LIMIT ' . $v;
                  else if ($v === null) $limit .= ' LIMIT NULL';
                  else throw new MazeoException("\"LIMIT\" parameter value must be a integer or null, type of " . gettype($v) . " given");
                }
                if ($k === 'OFFSET') {
                  if (gettype($v) === 'integer') $offset .= ' OFFSET ' . $v;
                  else throw new MazeoException("\"OFFSET\" parameter value must be a integer, type of " . gettype($v) . " given");
                }
              } else {
                throw new MazeoException("Unknown keyword \"{$k}\" passed as parameter on one of the current manager functions.");
              }
            }
        }
        $query .= $orderby.$limit.$offset;
        return $query;
    }
    /**
     * Builds and returns filter parameters
     * @param $filter - the filter table concerned -
     * it's an array of array where each row contains two fields :
     * an operator, optional by default equal
     * a field column in database - value
     * @param $query - the current $query request
     * @return array
     */
    private final function getFilterParams($filter, &$query)
    {
        $params = array();
        $i = 0;
        if (sizeof($filter) > 0) {
            foreach ($filter as $key => $data) {
                foreach ($data as $k => $v) {
                    if(is_numeric($k)) {
                        break;
                    }
                    switch ($data[0]) {
                        case self::LT:
                            $operator = "<";
                            break;
                        case self::GT:
                            $operator = ">";
                            break;
                        case self::EQ:
                            $operator = "=";
                            break;
                        case self::ILIKE:
                            $operator = "ILIKE";
                            $v = "%".$v."%";
                            break;
                        default:
                            $operator = "=";
                            break;
                    }
                    /***Preparing column name and binding param when using JSON*/
                    if (preg_match("%->>%", $k)) {
                        $key = preg_split("%->>%", $k)[0] . "_" . preg_replace("%'%", "", preg_split("%->>%", $k)[1]);
                        $k = '"' . preg_split("%->>%", $k)[0] . '"->>\'' . preg_replace("%'%", "", preg_split("%->>%", $k)[1]) . '\'';
                    }
                    else { /***Preparing column name and binding param when not using JSON*/
                        $key = $k;
                        $k = '"' . $k . '"';
                    }
                    if ($i !== 0) {
                        if (is_null($v)) $query .= ' and ' . $k . ' IS NULL';
                        else {
                            $query .= ' and ' . $k . ' '.$operator.' :' . $key;
                            $params[':' . $key] = $v;
                        }
                    } else {
                        if (is_null($v)) $query .= ' where ' . $k . ' IS NULL';
                        else {
                            $query .= ' where ' . $k . ' '.$operator.' :' . $key;
                            $params[':' . $key] = $v;
                        }
                    }
                    $i++;
                }
            }
        }
        return $params;
    }
    /**
     * Builds and returns a given entity columns
     * @param array $properties - current entity properties
     * @param array $column_exception - column exception
     * @param $query
     */
    private final function getInsertColumns(array $properties, array $column_exception, &$query)
    {
        foreach ($properties as $k => $prop) {
            if ($prop !== static::ID) {
                $foreignManager = $this->reflector->isMappingProperty($prop);
                $detachedProp = $this->reflector->isDetachedProperty($prop);
                if ($foreignManager !== false) $prop = $foreignManager['annotation']['thisColumn']; /***If the current property have relational binding option*/
                if (!$detachedProp) {
                  if (sizeof($column_exception) > 0 && $column_exception[0] === true) { /***Table with separator exception case*/
                    /***
                    * Here we are looking for uppercase letters position in the
                    * current entity properties to transform they names for
                    * make corresponding with the entity table columns
                    * as they are defined in the database
                    **/
                    $parts = preg_split("/([A-Z])/", $prop);
                    if (sizeof($parts) > 0) {
                      preg_match_all("/([A-Z])/", $prop, $match); /***All capital letters at the beginning of any word*/
                      $column = $parts[0];
                      $j = 0;
                      for ($i = 1; $i < sizeof($parts); $i++) {
                        $column .= '_'.strtolower($match[0][$j]).$parts[$i];
                        $j++;
                      }
                    } else $column = $prop;
                    /***The last column "sizeof($properties) - 1)" should not have comma**/
                    if ((sizeof($properties) - 1) !== $k) $query .= '"'.$column.'",';
                    else $query .= '"'.$column.'"';
                  } else { /***Normal case table without separator exception*/
                    if ((sizeof($properties) - 1) !== $k) $query .= '"'.$prop.'",';
                    else $query .= '"'.$prop.'"';
                  }
                }
            }
        }
    }
    /**
     * Builds and returns a given entity columns values
     * @param array $properties
     * @param Entity $entity
     * @param $query
     * @throws MazeoException
     */
    private final function getInsertValues(array $properties, Entity $entity, &$query)
    {
        $currentClass = new Reflector($entity);
        $currentClassName = $currentClass->getName();
        foreach ($properties as $k => $prop) {
            $getter = 'get'.ucfirst($prop);
            if ($prop !== static::ID) {
              $detachedProp = $this->reflector->isDetachedProperty($prop);
              $slugProp = $this->reflector->isSlugProperty($prop);
              if (!$detachedProp) {
                if (!is_null($entity->$getter()) || is_array($slugProp)) {
                  if ((is_numeric($entity->$getter()) || is_null($entity->$getter())) && !is_array($slugProp)) {
                    $query .= $entity->$getter() . ',';
                  }
                  elseif ($entity->$getter() instanceof Entity) {
                    $class = new Reflector($entity->$getter());
                    $className = $class->getName();
                    if (method_exists($entity->$getter(), 'getId')) $query .= $entity->$getter()->getId() . ',';
                    else { throw new MazeoException("The binding entity {$className} in {$currentClassName} must have a \"getId\" method"); exit; }
                  } elseif (is_array($slugProp)) { /***Slug field management*/
                    $fields = $slugProp['fields'];
                    $text = '';
                    foreach ($fields as $field) {
                      $getter = 'get'.ucfirst($field);
                      if (method_exists($entity, $getter)) {
                        if (!is_null($entity->$getter())) {
                          $text .= $entity->$getter();
                        } else { throw new MazeoException("The value of a field specified as slug can not be null, the field concerned \"{$field}\".");exit; }
                      } else { throw new MazeoException("Slugify field \"{$field}\" have no matches method in {$currentClassName}");exit; }
                    }
                    $slug = Asset::slugify($text);
                    if (in_array('slug',$properties)) {
                      $obj = $this->findOneBy(array(array('slug' => $slug,"eq")));
                      if (!is_null($obj)) {
                        $res = $this->db->query('select slug from '.static::TABLE_NAME.
                        ' where slug like '.'\'%'.$slug.self::SLUG_SEPARATOR.'%\''.' group by id having id  = max(id)
                        order by id desc limit 1')->fetchColumn();
                        if ($res) {
                          $slugParts = explode(self::SLUG_SEPARATOR,$res);
                          if (is_array($slugParts) && sizeof($slugParts) > 1) {
                            $numToIncrease = (int)$slugParts[1];
                            $slug = $slug.self::SLUG_SEPARATOR.++$numToIncrease;
                          }
                        } else $slug = $slug.self::SLUG_SEPARATOR.'1';
                      }
                    } else throw new MazeoException("Current class {$currentClassName} must have an attribute named \"slug\" for be slugyfied", 1);

                    $query .= '\'' . $slug . '\',';
                  } else {
                    if (preg_match("#'#",$entity->$getter())) {
                      $query .= $this->db->quote($entity->$getter()) . ',';
                    } else $query .= '\'' . $entity->$getter() . '\',';
                  }
                } else $query .= 'null,';
              }
            }
        }
        $query = substr(trim($query),0, strlen(trim($query)) - 1); /***Cleaning the query and removing the last comma.*/
    }
    /**
     * Parse current entity table fields from database to
     * mapped it with the current entity attributes
     * @return array
     * @throws MazeoException
     */
    private final function parseTable()
    {
        $query = $this->db->query("select column_name from INFORMATION_SCHEMA.COLUMNS where table_name = '".static::TABLE_NAME."'");
        $query->execute();
        $tableFields = $query->fetchAll(\PDO::FETCH_COLUMN);
        $cleanedFields = array();
        if (sizeof($tableFields) >0 ) {
            foreach ($tableFields as $field) {
                $column = $field;
                $field = explode(static::COLUMN_EXCEPTION[1],$field);
                if (sizeof($field) > 0 ) {
                    $property = '';
                    foreach ($field as $k => $v) {
                        if ($k!==0) $property .= ucfirst($v);
                        else $property = $v;
                    }
                    $cleanedFields[$column] = $property;
                }
            }
            $properties = $this->reflector->getProperties();
            foreach ($properties as $prop) {
                if (!in_array($prop, $cleanedFields)) $cleanedFields[$prop] = $prop;
            }
        } else throw new MazeoException("Current table ".static::TABLE_NAME." have no columns");
        return $cleanedFields;
    }
    /**
     * Returns the last inserted entity ID
     * @return string
     */
    public final function getLastInsertedId()
    {
        return $this->db->query('select max('.static::ID.') from ' .static::TABLE_NAME)->fetchColumn();
    }
    /**
     * Returns the last inserted entity Object
     * @return Entity
     */
    public final function getLastInsertedEntity()
    {
        return $this->findOne($this->db->query('select max('.static::ID.') from ' .static::TABLE_NAME)->fetchColumn());
    }
    /**
     * @return array
     */
    private function getTableColumns()
    {
        $query = $this->db->query("select column_name from INFORMATION_SCHEMA.COLUMNS where table_name = '".static::TABLE_NAME."'");
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }
    /**
     * findIntoInterval performs SQL betwen request
     * @param  type $field        The fiield concerned in the table
     * @param  type $low          The lower bound
     * @param  type $high         The higher
     * @param  type $multi        Specifies whether the request concern one or more lines in the table
     * @param  array  $wishedFields List of fields that we want to keep in the result
     * @return type               array
     */
    public final function findIntoInterval($field,$low,$high,$multi,$wishedFields=array())
    {
        if (!is_null($field)) {
          $fieldStr = $this->getFieldQueryStr($wishedFields);
          $fieldStr = 'select '.$fieldStr. ' '. 'from ';
          $query = $this->db->prepare($fieldStr .static::TABLE_NAME.' where ' .$field.' between :low and :high');
          $params = array(':low' => $low, ':high' => $high);
          $query->execute($params);
          if ($multi) {
            $data = $query->fetchAll();
            $data = $this->getMappedEntities($data);
          } else {
            $data = $query->fetch();
            $data = $this->getMappedEntity($data);
          }
          return $data;
        } else { throw new MazeoException(ErrorManager::MissedParameterMsg('between','field')); exit; }
    }
    /**
     * performCustomRequest description
     * @param  type  $query  description
     * @param  type  $params description
     * @param  boolean $multi  description
     * @return type          description
     */
    public function performCustomRequest($query,$params,$multi=false)
    {
      $result = null;
      $req = $this->db->prepare($query);
      $req->execute($params);
      if ($multi) {
        $result = $this->getMappedEntities($req->fetchAll());
      } else {
        $result = $this->getMappedEntity($req->fetch());
      }
      return $result;
    }
}
