<?php

namespace ORM;

use ORM\Exception\ORMException;
use ReflectionNamedType;

abstract class TypeResolver
{
    abstract public function isTypeSupported(ReflectionNamedType $type): bool;

    /**
     * Resolve a php builtin type from a raw value to the associated PHP type.
     * @throws ORMException
     */
    abstract public function fromRawToPhpType(mixed $raw, ReflectionNamedType $type): mixed;

    /**
     * Return a raw value from an object of the associated PHP type.
     * @throws ORMException
     */
    abstract public function fromPhpTypeToRaw(mixed $object, ReflectionNamedType $type): mixed;
}