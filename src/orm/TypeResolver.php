<?php

namespace ORM;

use ReflectionNamedType;

abstract class TypeResolver
{
    abstract public function isTypeSupported(ReflectionNamedType $type):bool;
    /**
     * Resolve a php builtin type from a raw value to the associated PHP type.
     * @throws ORMException
     */
    abstract public function fromRawToPhpType(Mixed $raw, ReflectionNamedType $type):Mixed;

    /**
     * Return a raw value from an object of the associated PHP type.
     * @throws ORMException
     */
    abstract public function fromPhpTypeToRaw(Mixed $object,ReflectionNamedType $type):Mixed;
}