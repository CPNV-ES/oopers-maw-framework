<?php

namespace App\Models;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Table
{
    public function __construct(
        private ?string $tableName = null,
    )
    {
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }
}