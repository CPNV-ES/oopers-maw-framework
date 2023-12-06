<?php

namespace ORM\Mapping;

use Attribute;

/**
 * A column is a property attribute that tells DBORM that the attribute is a column in a SQL database.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Column
{
    public function __construct(private string $columnName)
    {
    }

    /**
     * Get the sql column name
     * @return string
     */
    public function getName(): string
    {
        return $this->columnName;
    }
}