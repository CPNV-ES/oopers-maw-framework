<?php

namespace ORM\Mapping;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasMany
{

    public function __construct(
        public string $entity,
        public string $targetProperty
    )
    {
    }

}