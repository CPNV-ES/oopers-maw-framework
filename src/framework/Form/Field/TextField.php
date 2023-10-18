<?php

namespace MVC\Form\Field;

class TextField extends AbstractField
{

	public function __construct(string $id, mixed $value, \ReflectionProperty $property, object $entity)
	{
		parent::__construct($id, $value, $property, $entity);
		$this->setOption('attributes', ['class' => ['form-input']]);
		$this->setOption('view_template', 'form.text-field');
	}
}