<?php

namespace ORM\EntitiyTrait;

use ORM\DatabaseOperations;

/**
 * This trait add the possibility to get all entities of the class using this trait in the database
 */
trait GetAll
{
    /**
     * Get all entities instances as a map
     * @param DatabaseOperations $operations - The db operations executor that will be used
     * @param array $whereCondition - The where condition map key(column name) -> value
     * @return array - All entities matching the whereCondition (everyone by default) as a map with the key = id
     */
    public static function getAllInMap(DatabaseOperations $operations, array $whereCondition = []): array
    {
        return array_reduce(self::getAll($operations, $whereCondition), function ($res, $entity) {
            $res[$entity->getId()] = $entity;
            return $res;
        }, []);
    }

    /**
     * Get all entities instances
     * @param DatabaseOperations $operations - The db operations executor that will be used
     * @param array $whereCondition - The where condition map key(column name) -> value
     * @return array - All entities matching the whereCondition (everyone by default)
     */
    public static function getAll(DatabaseOperations $operations, array $whereCondition = []): array
    {
        return $operations->fetchAll(self::class, $whereCondition);
    }
}