<?php

namespace Mazeo\Util\Util;

use Mazeo\Main\Entity\Entity;
use \ReflectionClass;

 /**
  * Class Reflector
  * @package Mazeo\Util\Util
  * @author Macky Dieng
  */
class Reflector extends ReflectionClass {

    const MAPPING_TOKEN = array('@SingleEntity','@ManyEntity','@Option', '@Slug');

    const SINGLE_ENTITY = '@SingleEntity';

    const MANY_ENTITY = '@ManyEntity';

    const OPTION = '@Option';

    const SLUG_TOKEN = '@Slug';

    const PATTERN = "#(@[a-zA-Z]+[a-zA-Z0-9, ()_].*)#";

    const TARGET_ANNOTATION_KEYS = array('target' => 't','targetColumn'=>'tc','thisColumn'=> 'tc');

    const OPTION_ANNOTATION_KEYS = array('detached' => 'dtc');

    const SLUG_ANNOTATION_KEYS = array('fields' => 's');

     /**
      * Reflector constructor
      * @param Entity $entity- the current entity
      */
     public function __construct($entity)
     {
         parent::__construct($entity);
     }

     /**
      * Returns the parameters of a specific given method
      * @param $method - method concerned in the current entity
      * @return \ReflectionParameter[]
      */
     public function getParameters($method)
     {
         return parent::getMethod($method)->getParameters();
     }

    /**
     * Returns the attributes of the current entity
     * @param null $filter
     * @return array
     * @throws MazeoException
     */
     public function getProperties($filter = null)
     {
         $properties = parent::getProperties();
         $propertiesNames = array();
         if (sizeof($properties) > 0 ) {
             foreach ($properties as $property)  {
                 $propertiesNames[] = $property->getName();
             }
             return $propertiesNames;
         } else throw new MazeoException("The current class {$this->getShortName()} does not have any properties");
     }

    /**
     * @param $property
     * @return array|bool
     * @throws MazeoException
     */
     public function isMappingProperty($property)
     {
         $matches = $this->getMatches($property);
         $result = array();
         $isMapped = false;
         $match = null;
         if ($matches!==false) {
            $annotation = null;
            foreach ($matches[0] as $annotation) {
                if (preg_match('#'.self::SINGLE_ENTITY.'#', $annotation, $match1))  $match = $match1;
                elseif (preg_match('#'.self::MANY_ENTITY.'#', $annotation, $match2)) $match = $match2;
                 if (!is_array($match[0]) && in_array($match[0],self::MAPPING_TOKEN)) {
                     if ($match[0]===self::SINGLE_ENTITY)
                        $annotation = substr($annotation, strlen(self::SINGLE_ENTITY) + 1, strlen($annotation));
                     elseif ($match[0]===self::MANY_ENTITY)
                         $annotation = substr($annotation, strlen(self::MANY_ENTITY) + 1, strlen($annotation));
                     $tmpAnnotation = $annotation;
                     $annotation = json_decode($annotation,true);
                     if (!is_null($annotation) && is_array($annotation)) {
                         if (sizeof(array_diff_key(self::TARGET_ANNOTATION_KEYS,$annotation))===0) {
                             $result['annotation'] = $annotation;
                             $result['token'] = $match[0];
                             $isMapped = true;
                         } else throw new MazeoException("Relational binding instruction must have the keys \"target\", \"targetColumn\" and \"thisColumn\"");
                     } else throw new MazeoException("Incorrect binding string {$tmpAnnotation} on property \"{$property}\" from class ".parent::getName());
                 }
             }
         }
         if ($isMapped) return $result;
         else return $isMapped;
    }

    /**
     * Checks whether current property is detached
     * @param $property
     * @return bool
     * @throws MazeoException
     */
    public function isDetachedProperty($property)
    {
        return $this->processMatchedResult($property,self::OPTION,self::OPTION_ANNOTATION_KEYS,false);
    }
    /**
     * [isSlugProperty description]
     * @return boolean [description]
     */
    public function isSlugProperty($property)
    {
      return $this->processMatchedResult($property,self::SLUG_TOKEN,self::SLUG_ANNOTATION_KEYS,true);
    }
    public function processMatchedResult($property, $matchedOpt, $matchedKeyOpt, $canReturnRes)
    {
      $matches = $this->getMatches($property);
      $isMapped = false;
      $result = null;
      if ($matches!==false) {
          foreach ($matches[0] as $annotation) {
              if (preg_match('#' . $matchedOpt . '#', $annotation, $match)) {
                if (!is_array($match[0]) && in_array($match[0],self::MAPPING_TOKEN)) {
                    if ($match[0]===$matchedOpt) {
                        $annotation = substr($annotation, strlen($matchedOpt) + 1, strlen($annotation));
                        $tmpAnnotation = $annotation;
                        $annotation = json_decode($annotation, true);
                        if (!is_null($annotation) && is_array($annotation)) {
                            if (sizeof(array_diff_key($matchedKeyOpt, $annotation)) === 0) {
                                $isMapped = true;
                                $result = $annotation;
                            } else throw new MazeoException("\"Slug\" annotation must have the keys \"detached\"");
                        } else throw new MazeoException("Incorrect binding string {$tmpAnnotation} on property \"{$property}\" from class " . parent::getName());
                    }
                  }
              }
          }
      }
      if ($canReturnRes) return $result;
      else return $isMapped;
    }
    /**
     * Returns a matched values from a given string
     * @param $property
     * @return mixed
     */
    public function getMatches($property)
    {
        $comment_string = parent::getProperty($property)->getDocComment();
        preg_match_all(self::PATTERN, $comment_string, $matches, PREG_PATTERN_ORDER);
        return $matches;
    }
}
