<?php

namespace ORM;

class QueryBuilder
{

    private array $aliases = [];

    private array $fields = [];

    public function __construct(
        private string $table,
        ?string $alias
    )
    {
        $this->aliases[$this->table] = $alias;
        $this->table .= " " . $alias;
    }

    public function select(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function query(): string
    {
        return "SELECT {$this->getSelect()} FROM {$this->table}";
    }

    private function getFrom(): string
    {
        return $this->table . " " . $this->aliases[$this->table];
    }

    private function getSelect(): string
    {
        $fields = [];
        foreach ($this->fields as $key => $field) {
            if (is_string($key)) {
                $fields[] = $field . " AS " . "'$key'";
            } else {
                $fields[] = $field;
            }
        }
        return implode(', ', $fields);
    }

}