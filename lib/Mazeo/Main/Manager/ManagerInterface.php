<?php

namespace Mazeo\Main\Manager;
use Mazeo\Main\Entity\Entity;

/**
 * Interface ManagerInterface
 * @package Mazeo\Main\Manager
 * @author Macky Dieng
 
 */
interface ManagerInterface
{
    /**
     * Retrieves a single instance of current entity which mapped
     * with its corresponding table
     * @param int $id - Identify of the entity to find
     * @return Entity
     */
    public function findOne($id);

    /**
     * Retrieves a single instance of current entity by filter which mapped
     * with its corresponding table
     * @param array $filter - the filter to use for retrieve the corresponding row
     * @return mixed
     */
    public function findOneBy(array $filter);

    /**
     * Retrieves all mapped instances of current entity
     * @param array $options - If these options are specified, then they are taken
     * into effect during the execution of the request
     * @return array
     */
    public function findAll(array $options=array());

    /**
     * Retrieves all mapped instances of current entity by filter
     * @param array $filter - the filter to use for retrieve the corresponding rows
     * @param array $options - If these options are specified, then they are taken
     * into effect during the execution of the request
     * @return array
     */
    public function findAllBy(array $filter, array $options=array());

    /**
     * Attaches an given entity to the entities array, the & token
     * will allows to access the charged object directly after insertion
     * @param Entity $entity - the current entity to attach
    public function attach(Entity $entity);
     */

    /**
     * Insert or update all attached entities
     * this method will insert a persisted entities
     * if they are new (they identifies are null) or will update themes in the other case
     * @param Entity $entity - the current entity to save
     */
    public function save(Entity $entity);

    /**
     * Remove all attached entities from the database
     * @param Entity $entity - the current entity to remove
     */
    public function remove(Entity $entity);
}