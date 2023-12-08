<?php

namespace ORM\Mapping;

use ORM\Exception\MappingException;
use ReflectionException;

class MappedEntity
{

    /**
     * @throws ReflectionException
     * @throws MappingException
     */
    static function getEntityTable(object|string $entity): Table
    {
        $reflection = new \ReflectionClass($entity);
        if (empty($reflection->getAttributes(Table::class))) throw new MappingException(sprintf("Class `%s` can't be loaded as an entity.", is_object($entity) ? get_class($entity) : $entity));
        return $reflection->getAttributes(Table::class)[0]->newInstance();
    }

    static function isValidEntity(object|string $entity): bool
    {
        try {
            self::getEntityTable($entity);
        } catch (MappingException|ReflectionException $e) {
            return false;
        }
        return true;
    }

}