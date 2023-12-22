<?php
namespace ORM\EntitiesTraits;

use ORM\DatabaseOperations;

/**
 * This trait add the possibility to get all entities of the class using this trait in the database
 */
trait GetAll
{
    /**
     * Get all entities instances
     * @param DatabaseOperations $operations - The db operations executor that will be used
     * @param array $whereCondition - The where condition map key(column name) -> value
     * @return array - All entities matching the whereCondition (everyone by default)
     */
    public static function getAll(DatabaseOperations $operations, array $whereCondition = []) : array
    {
        return $operations->fetchAll(self::class, $whereCondition);
    }
}