<?php

namespace ORM\Mapping;

use Attribute;

/**
 * A table is a class attribute that tells DBORM that the class is a table in an SQL database (the class must contain column attributes).
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class Table
{
    public function __construct(private ?string $tableName = null)
    {
    }

    /**
     * Get the sql table name
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->tableName;
    }
}