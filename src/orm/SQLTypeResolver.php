<?php

namespace ORM;

use DateTime;
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
            return $object->name;
        }
        $stringType = $type->getName();
        if ($stringType == "DateTime") {
            return date("YYYY-MM-DD HH:MI:SS", $object);
        }
        return null;
    }
}