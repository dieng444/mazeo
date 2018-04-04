<?php

namespace Mazeo\Main\Form;

use Mazeo\Main\Entity\Entity;
use Mazeo\Request\Request;
use Mazeo\Util\Cleaner\Cleaner;
use Mazeo\Util\Cleaner\HtmlCleaner;
use Mazeo\Util\Cleaner\WhiteSpaceCleaner;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;
use Mazeo\Util\Util\Util;
use Mazeo\Util\Validator\FieldValidator;
use Mazeo\Util\Validator\SelectListValidator;

/**
 * Class Form - The super form class
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
abstract class Form implements FormInterface {

    /**
     * Reflector class instance variable
     * @var Reflector
     */
    private $reflector;

    /**
     * @var form header
     */
    protected $header;

    /**
     * @var form action
     */
    protected $action;

    /**
     * @var form method
     */
    protected $method;

    /**
     * @var form enctype
     */
    protected $enctype;

    /**
     * @var form footer
     */
    protected $footer;

    /**
     * @var form fields
     */
    private $fields;

    /**
     * @var FormFieldManager instance variable
     */
    private $fieldManager;

    /**
     * @var EntityInterface - current entity linked to form
     */
    private $entity;

    /**
     * @var array - array of error
     */
    private $errors;

    /**
     * @var current form tag
     */
    const FORM_TAG = 'form';

    private $util;

    /**
     * Class constructor
     * @param Entity $entity - the current entity
     */
    public function __construct(Entity $entity)
    {
        $this->reflector = new Reflector($entity);
        $this->fieldManager = new FormFieldManager($this->reflector);
        $this->entity = $entity;
        $this->util = new Util();
        $this->errors = array();
        $this->action = null;
        $this->method = null;
        $this->enctype = null;
    }

    /**
     * Validate form
     * @throws MazeoException
     * @return boolean
     */
    public final function isValid()
    {
        $validators = $this->getMappedFile(true);
        $fields_error = array();
        $list_error = array();
        if (array_key_exists(strtolower($this->reflector->getShortName()), $validators)) {
            $validator = $validators[strtolower($this->reflector->getShortName())];
            if (in_array('FieldValidator', $validator))
                $fields_error = FieldValidator::validate($this->getMappedFile(), $this->entity);
            if (in_array('SelectListValidator', $validator))
                $list_error = SelectListValidator::validate($this->getMappedFile(), $this->entity);

            $this->errors = array_merge($fields_error, $list_error);
        } else throw new MazeoException("No validation entry corresponding to {$this->reflector->getName()} in the current validation file");
        if(sizeof($this->errors) > 0 ) return false;
        else return true;
    }
    /**
     * isPartiallyValid check whether some specifics properties of the current form are partially valid
     * @param  array  $fields specifics properties
     * @return boolean         description
     */
    public function isPartiallyValid($props)
    {
      $this->isValid();
      $errors = $this->getErrors();
      $isOK = true;
      foreach ($props as $prop) {
        if (isset($errors[$prop])) {
          $isOK = false;
        }
      }
      return $isOK;
    }
    /**
     * Builds all the fields of the form and Returns an instance of the current form
     * @param $filter - will only build and return fields contained in this array
     * @return \Mazeo\Main\Form\Form
     * @throws MazeoException
     */
    public function buildView($filter=array()) {
        $mappedForm = $this->getMappedFile();
        $this->header = $this->generateFormHeader($mappedForm);
        if (sizeof($filter) > 0) $mappedForm = $this->getFilteredFields($filter,$mappedForm);
        $listFields = SelectListFieldBuilder::buildView($mappedForm, $this->reflector, $this->entity);
        $textFields = TextFieldBuilder::buildView($mappedForm, $this->reflector, $this->entity);
        $radioFields = RadioFieldBuilder::buildView($mappedForm, $this->reflector, $this->entity);
        $checkboxFields = CheckboxFieldBuilder::buildView($mappedForm, $this->reflector, $this->entity);
        $textAreaFields = TextareaFieldBuilder::buildView($mappedForm, $this->reflector, $this->entity);
        $fileFields = FileFieldBuilder::buildView($mappedForm, $this->reflector, $this->entity);
        $this->fields = array_merge($textFields,$checkboxFields,$fileFields,$radioFields,$listFields,$textAreaFields);
        $this->footer = $this->generateFormFooter($mappedForm);
        return $this;
    }
    /**
     * Filter forom field to build
     * @param  array $filter the filter concerned
     * @param  array $mappedForm current form
     * @return array  - the filtered fields
     */
    private function getFilteredFields($filter,$mappedForm) {
      $filteredFields = array();
      foreach ($filter as $key) $filteredFields[$key] = $mappedForm[$key];
      $filteredFields[strtolower(self::FORM_TAG)] = $mappedForm[strtolower(self::FORM_TAG)];
      return $filteredFields;
    }
    /**
     * Generate the header of the current form
     * @param array $form - the current form definition
     * @throws MazeoException
     * @return string
     */
    private final function generateFormHeader(array $form)
    {
        $attributes = null;
        if(!array_key_exists('form',$form))
        { throw new MazeoException("Missing property \"form\" in {$this->reflector->getShortName()}.yml form file"); exit;}
        if(array_key_exists('action',$form['form'])) { if (is_null($this->action)) $this->action = $form['form']['action'];}
        else { throw new MazeoException("Missing property \"action\" in {$this->reflector->getShortName()}.yml form file"); exit; }
        if(array_key_exists('method',$form['form'])) { if (is_null($this->method)) $this->method = $form['form']['method']; }
        else { throw new MazeoException("Missing property \"method\" in {$this->reflector->getShortName()}.yml form file"); exit; }
        if(array_key_exists('enctype',$form['form'])) $this->enctype = 'enctype="'.$form['form']['enctype'].'"';
        if (isset($form['form']['attributes'])) $attributes = FormFieldManager::getAttributes($form['form']['attributes']);
        return '<form action="'.$this->action.'" method="'.$this->method.'" '.$this->enctype.' '.$attributes.'>';
    }

    /**
     * Generate the footer of the current form
     * @param array $form - the current form
     * @throws MazeoException
     * @return string
     */
    private final function generateFormFooter(array $form)
    {
        $type = null;
        $text = null;
        if(!array_key_exists('form',$form))
        { throw new MazeoException("Missing property \"form\" in {$this->reflector->getShortName()}.yml form file"); exit; }
        if(array_key_exists('submit',$form['form'])) $submit = $form['form']['submit'];
        else { throw new MazeoException("Missing property \"submit\" in {$this->reflector->getShortName()}.yml form file"); exit; }
        if(array_key_exists('type',$submit)) $type = $submit['type'];
        else { throw new MazeoException("Missing property \"type\" in {$this->reflector->getShortName()}.yml form file submit section"); exit; }
        if(array_key_exists('text',$submit)) $text = $submit['text'];
        else { throw new MazeoException("Missing property \"text\" in {$this->reflector->getShortName()}.yml form file submit section"); exit; }
        if (isset($submit['attributes'])) $attributes = FormFieldManager::getAttributes($submit['attributes']);
        return '<input type="'.$type.'" value="'.$text.'"'.' '.$attributes.'/></form>';
    }

    /**
     * Returns the mapped form file
     * @param boolean  $isValidationFile - specify whether it is validation file
     * @throws MazeoException
     * @return array
     */
    private final function getMappedFile($isValidationFile=false)
    {
        $normalizedName = explode('\\',$this->reflector->getName());
        $base  = 'src/'.$normalizedName[0].'/'.$normalizedName['1'].'/Resources/config';
        $mappedFile = null;
        if ($isValidationFile) {
            $file = $base.'/validating.yml';
            if (is_readable($file)) $mappedFile = yaml_parse_file($file);
            else {throw new MazeoException("Unable to charge form validation file from {$base}");exit;}
        } else {
            $file = $base.'/form/'.strtolower($this->reflector->getShortName()).'.yml';
            if (is_readable($file)) $mappedFile = yaml_parse_file($file);
            else { throw new MazeoException("Unable to charge form file from {$base}"); exit; }
        }
        return $mappedFile;
    }

    /**
     * Returns the fields of the mapped file
     * @param array $form - the current mapped form
     * @return array
     * @throws MazeoException
     */
    public final function getFormFields(array $form)
    {
        $properties = array();
        if(sizeof($form) > 0 ) foreach ($form as $k => $v) { if($k !== 'form') $properties[] = $k; }
        else {throw new MazeoException("Unable to parse empty form file from package {$this->reflector->getName()}"); exit; }
        return $properties;
    }

    /**
     * Binds the current entity to the the current request
     * @param Request $request - the current request object
     * @throws MazeoException
     */
    public final function bindRequest(Request $request)
    {
        $entityName = strtolower($this->reflector->getShortName());
        /***The filter below allows to get only the detached input values (not an attributes of current class)*/
        $data = (array_filter($request->getRequest('POST'), function($k) {
            $entityName = strtolower($this->reflector->getShortName());
            return $k !== $entityName;
        }, ARRAY_FILTER_USE_KEY));
        if (array_key_exists($entityName, $request->getRequest('POST'))) {
            $data = array_merge($data,$request->getRequest('POST')[$entityName]);
        }
        $cleanedData = Cleaner::launchCleaners($data, $this->getMappedFile());
        foreach ($cleanedData as $attr => $value) {
          $method = 'set'.ucfirst($attr);
          if (method_exists($this->entity, $method)) {
            $foreignManager = $this->reflector->isMappingProperty($attr);
            if ($foreignManager !== false) { /***If the current property have relational binding option*/
                $target = new $foreignManager['annotation']['target'](); /***The target relational entity manager*/
              if ($foreignManager['token'] === Reflector::SINGLE_ENTITY) {
                $cleanedData[$attr] = $target->findOne((int)$value);  /***Corresponding binned entity*/
              }
            }
          }
        }
        $this->entity->initialize($cleanedData);
    }

    /**
     * Returns the form header
     * @return string
     */
    public final function getHeader()
    {
        return $this->header;
    }

    /**
     * Returns the form footer
     * @return string
     */
    public final function getFooter()
    {
        return $this->footer;
    }

    /**
     * Returns a specific input form field
     * @param string $field - the current field
     * @return string
     */
    public final function getInput($field)
    {
        return $this->fields[$field]['input'];
    }

    /**
     * Returns a specific form field label
     * @return mixed
     */
    public final function getLabel($field)
    {
        return $this->fields[$field]['label'];
    }

    /**
     * Returns all fields of the current form
     * @return Array
     */
    public final function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns a specific form field helper message
     * @param string $field - the current field
     * @return string
     * @throws MazeoException
     */
    public final function getHelper($field)
    {
        if (isset($this->fields[$field]['helper_msg']) && !is_null($this->fields[$field]['helper_msg']))
            return $this->fields[$field]['helper_msg'];
        else {throw new MazeoException('No helper message specified for field '.$field); exit;}
    }

    /**
     * Modify form current action
     * @param string $action - the action to assign
     */
    public final function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Modify form current method
     * @param string $method - the new method to assign
     */
    public final function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Returns the current forms errors
     * @return array
     */
    public final function getErrors()
    {
        return $this->errors;
    }
    /**
     * Set the current form errors
     * @param array $errors
     */
    public final function setErrors($errors)
    {
        $this->errors = $errors;
    }
    /**
     * Returns a specific form field error message
     * @param string $field - the current field
     * @return string
     */
    public final function getError($field)
    {
        if (!is_null($this->errors[$field])) return $this->errors[$field];
        else return null;
    }
    public function getEntity()
    {
      return $this->entity;
    }
}
