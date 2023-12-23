<?php

namespace MVC\Form\Field;

use ReflectionProperty;

class TextAreaField extends AbstractField
{

    public function __construct(string $id, mixed $value, ReflectionProperty $property, object $entity)
    {
        parent::__construct($id, $value, $property, $entity);
        $this->setOption('attributes', ['class' => ['form-input']]);
        $this->setOption('view_template', 'form.text_area-field');
    }
}