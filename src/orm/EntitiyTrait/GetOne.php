<?php

namespace ORM\EntitiyTrait;

use MVC\Http\Exception\NotFoundException;
use ORM\DatabaseOperations;

/**
 * This trait add the possibility to get one entity of the class using this trait in the database
 */
trait GetOne
{
    /**
     * Get one entity with the given unique identifier or throw a NotFoundException
     * @param DatabaseOperations $operations - The db operations executor that will be used
     * @param int $id - The unique identifier
     * @return mixed - The entity
     * @throws NotFoundException
     */
    public static function getOneByID(DatabaseOperations $operations, int $id): mixed
    {
        return self::getOne($operations, ['id' => $id], false);
    }

    /**
     * Get one entity with the given whereCondition
     * @param DatabaseOperations $operations - The db operations executor that will be used
     * @param array $whereCondition - The where condition map key(column name) -> value
     * @param bool $allowNull - Allow returning null (if not, throw a NotFoundException)
     * @return mixed - The entity or null if allowed
     * @throws NotFoundException
     */
    public static function getOne(
        DatabaseOperations $operations,
        array $whereCondition = [],
        bool $allowNull = true
    ): mixed {
        return $allowNull ?
            $operations->fetchOne(self::class, $whereCondition) :
            $operations->fetchOneOrThrow(self::class, $whereCondition);
    }
}