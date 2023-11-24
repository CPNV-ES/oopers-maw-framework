<?php

namespace ORM;

abstract class TypeResolver
{
    /**
     * Resolve a php builtin type from a raw value to the associated PHP type.
     * @throws ORMException
     */
    public function fromRawToPhpType($raw, $type)
    {
        if (!$type->isBuiltin()) throw new ORMException("The TypeResolver can only handle builtin php types");
        return $raw;
    }

    /**
     * Return a raw value from an object of the associated PHP type.
     * @throws ORMException
     */
    public function fromPhpTypeToRaw($object, $type)
    {
        if (!$type->isBuiltin()) throw new ORMException("The TypeResolver can only handle builtin php types");
        return $object;
    }
}