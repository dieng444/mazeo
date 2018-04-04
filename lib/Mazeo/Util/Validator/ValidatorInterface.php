<?php

namespace Mazeo\Util\Validator;
use Mazeo\Main\Entity\Entity;

/**
 * Interface ValidatorInterface
 * @package Mazeo\Util\Validator
 */
interface ValidatorInterface {

    /**
     * Allows to validate a given form
     * @param array $form - the current form to validate
     * @param Entity $entity  - the current entity linked to the form
     * @return array
     */
    public static function validate(array $form, Entity $entity);
}
