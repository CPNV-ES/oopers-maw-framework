<?php

namespace ORM;

class QueryBuilder
{

    private array $aliases = [];

    public function __construct(
        private string $from,
        ?string $alias
    )
    {
        $this->aliases[$this->from] = $alias;
        $this->from .= " " . $alias;
    }

}