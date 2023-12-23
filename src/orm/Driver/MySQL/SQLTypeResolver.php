<?php

namespace ORM\Driver\MySQL;

use DateTime;
use ORM\TypeResolver;
use ReflectionNamedType;

class SQLTypeResolver extends TypeResolver
{
    public function isTypeSupported(ReflectionNamedType $type): bool
    {
        if ($type->isBuiltin()) {
            return true;
        }
        if (enum_exists($type)) {
            return true;
        }
        $stringType = $type->getName();
        return $stringType == "DateTime";
    }

    public function fromRawToPhpType($raw, $type): mixed
    {
        if ($type->isBuiltin()) {
            return $raw;
        }
        $stringType = $type->getName();
        if (enum_exists($type)) {
            return $stringType::from($raw);
        }
        if ($stringType == "DateTime") {
            return new DateTime($raw);
        }
        return null;
    }

    public function fromPhpTypeToRaw($object, $type): mixed
    {
        if ($type->isBuiltin()) {
            return $object;
        }
        if (enum_exists($type)) {
            return $object->value;
        }
        $stringType = $type->getName();
        if ($stringType == "DateTime") {
            return $object->format('Y-m-d H:i:s');
        }
        return null;
    }
}