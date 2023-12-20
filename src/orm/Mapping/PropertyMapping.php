<?php

namespace ORM\Mapping;

class PropertyMapping
{

    private \ReflectionProperty $property;

    public function getProperty(): \ReflectionProperty
    {
        return $this->property;
    }

    public function setProperty(\ReflectionProperty $property): self
    {
        $this->property = $property;
        return $this;
    }

}