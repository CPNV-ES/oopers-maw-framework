<?php

namespace MVC\Form\Field;

class ChoiceOption
{

    public function __construct(
        public string|object|array $value,
        public string $label,
        public bool $selected = false
    ) {
    }

    public function isSelected(): bool
    {
        return $this->selected;
    }

    public function defineAsSelected($value): self
    {
        if (is_object($value) && enum_exists(get_class($value))) {
            $this->selected = $this->value == $value->value;
        } else {
            $this->selected = $this->value == $value;
        }

        return $this;
    }
}