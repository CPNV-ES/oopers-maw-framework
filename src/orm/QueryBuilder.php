<?php

namespace ORM;

class QueryBuilder
{

    private array $aliases = [];

    private array $fields = [];

    private array $wheres = [];

    public function __construct(
        private ?string $table = null,
        ?string $alias = null,
    )
    {
        $this->aliases[$this->table] = $alias;
    }

    public function select(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function toSQL(): string
    {
        $query = "SELECT {$this->getSelect()} FROM {$this->getFrom()}";
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(" AND ", $this->wheres);
        }
        return $query;
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
                $fields[] = $this->aliases[$this->table] . "." . $field . " AS " . "'$key'";
            } else {
                $fields[] = $this->aliases[$this->table] . "." . $field;
            }
        }
        return implode(', ', $fields);
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->table = $table;
        $this->aliases[$table] = $alias;
        return $this;
    }

    public function where(string $condition): self
    {
        $this->wheres = [$condition];
        return $this;
    }

}