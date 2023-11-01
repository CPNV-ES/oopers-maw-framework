<?php

namespace App\Models;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Column
{

	public function __construct(
        private ?string $columnName = null,
	)
	{
	}
	public function getColumnName(): ?string
	{
		return $this->columnName;
	}
}