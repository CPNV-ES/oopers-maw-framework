<?php

namespace ORM\Mapping;

use Attribute;
use ORM\Exception\MappingException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasMany extends PropertyMapping
{

    private \ReflectionProperty $targetProperty;

    public function __construct(
        private readonly string $entity,
        string $targetProperty
    )
    {
        if (!MappedEntity::isValidEntity($this->entity)) throw new MappingException(sprintf("Unable to find `%s` class for relational mapping.", $this->entity));
        $this->targetProperty = new \ReflectionProperty($this->entity, $targetProperty);
    }

}