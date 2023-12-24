<?php

namespace MVC\Form\Field;

use ReflectionProperty;

class HiddenField extends AbstractField
{
    public function __construct(string $id, mixed $value, ReflectionProperty $property, object $entity)
    {
        parent::__construct($id, $value, $property, $entity);
        $this->setOption('attributes', ['type' => "hidden"]);
        $this->setOption('view_template', 'form.text-field');
    }
}