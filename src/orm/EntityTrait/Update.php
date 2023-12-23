<?php

namespace ORM\EntityTrait;

use ORM\DatabaseOperations;

/**
 * This trait add the possibility to update an entity of the class using this trait in the database
 */
trait Update
{
    /**
     * Update the entity in the database
     * @param DatabaseOperations $operations - The db operations executor that will be used
     */
    public function update(DatabaseOperations $operations)
    {
        $operations->update($this);
    }
}