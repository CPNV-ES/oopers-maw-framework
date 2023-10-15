<?php

namespace MVC\Form\Field;

use MVC\View\View;

class TextField extends AbstractField
{

	public function __construct(string $id, mixed $value, \ReflectionProperty $property)
	{
		parent::__construct($id, $value, $property);
		$this->setOption('attributes', ['class' => ['form-input']]);
		$this->setOption('view_template', 'form.text-field');
	}

	public function render(): string
	{
		$view = new View($this->getOption('view_template'));
		return $view->render(['field' => $this]);
	}
}