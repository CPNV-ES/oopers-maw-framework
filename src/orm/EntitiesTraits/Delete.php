<?php

namespace ORM\EntitiesTraits;

use ORM\DatabaseOperations;

/**
 * This trait add the possibility to delete one entity entry of the class using this trait in the database
 */
trait Delete
{
    /**
     * Delete one entity entry with the given unique identifier in the database
     * @param DatabaseOperations $operations - The db operations executor that will be used
     * @param int $id - The unique identifier
     */
    public static function deleteById(DatabaseOperations $operations, int $id): void
    {
        $operations->delete(self::class, $id);
    }

    /**
     * Delete this entity entry in the database
     * @param DatabaseOperations $operations - The db operations executor that will be used
     */
    public function delete(DatabaseOperations $operations): void
    {
        $operations->delete($this, $this->getId());
    }

    /**
     * Get the unique identifier of the entity
     * @return mixed - The unique identifier
     */
    abstract private function getId(): mixed;
}