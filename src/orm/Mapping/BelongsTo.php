<?php

namespace ORM\Mapping;

use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BelongsTo extends PropertyMapping
{

    public function __construct(
        public string $inversedBy,
        public ?string $entity = null
    )
    {
    }

    public function setEntityFromPropertyReflection(ReflectionProperty $property): self
    {
        if ($property->getType() instanceof \ReflectionUnionType) {
            foreach ($property->getType()->getTypes() as $type) {
                if (class_exists($type->getName())) $this->entity = $type->getName();
            }
        } else
        {
            $this->entity = $property->getType()->getName();
        }
        return $this;
    }

}