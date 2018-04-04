<?php

namespace Mazeo\Main\Form;

use Mazeo\Main\Entity\Entity;
use Mazeo\Util\Util\Reflector;

/**
 * Interface FieldBuilderInterface
 * @package Mazeo\Main\Form
 * @author Macky Dieng
 */
interface FieldBuilderInterface {

    /**
     * Builds form fields
     * @param array $form - the current form
     * @param Reflector $entity - the current reflector
     * @param Entity $class - the current entity class
     * @return array
     */
    public static function buildView($form, Reflector $entity, Entity $class);
}
