<?php

namespace ORM\EntityTrait;

use ORM\DatabaseOperations;

/**
 * This trait add the possibility to store an entity of the class using this trait in the database
 */
trait Create
{
    /**
     * Store the entity in the database and apply the newly created id to the instance
     * @param DatabaseOperations $operations - The db operations executor that will be used
     */
    public function create(DatabaseOperations $operations): void
    {
        $this->setId($operations->create($this));
    }

    /**
     * Set the identifier of the entity
     * @param int $id - The new identifier
     * @return mixed
     */
    abstract private function setId(int $id): mixed;
}