<?php

namespace ORM\Mapping;

use ORM\Exception\MappingException;
use ReflectionException;

class MappedEntity
{

    private string $entity;

    private Table $table;

    /**
     * @var array<Column>
     */
    private array $columns;

    /**
     * @var array<BelongsTo|HasMany>
     */
    private array $relations;

    public function __construct(
        object|string $entity
    )
    {
        $r = new \ReflectionClass($entity);
        foreach ($r->getProperties() as $property) {
            $attrs = $property->getAttributes();
            if (empty($attrs)) continue;
            foreach ($attrs as $attr) {
                match ($attr->getName()) {
                    Column::class => $this->columns[] = ($attr->newInstance())->setProperty($property),
                    HasMany::class, BelongsTo::class => $this->relations[] = ($attr->newInstance())->setProperty($property)
                };
            }
        }
        self::getEntityTable($entity);
    }

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