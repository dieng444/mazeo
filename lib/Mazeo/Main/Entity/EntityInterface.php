<?php

namespace Mazeo\Main\Entity;

/**
 * EntityInterface
 * @package Mazeo\Main\Entity
 * @author Macky Dieng
 */
interface EntityInterface
{
    /**
     * Allows to initialize subclasses setters
     * @param array $data  - array of data with which initialized entity setters
     */
    public function initialize(array $data = array());
}
