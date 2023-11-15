<?php

namespace MVC\Form\Field;

class ChoiceField extends AbstractField
{

	public function __construct(string $id, mixed $value, \ReflectionProperty $property, object $entity)
	{
		parent::__construct($id, $value, $property, $entity);
		$this->setOption('view_template', 'form.choice-field');
	}

	/**
	 * @return ChoiceParam[]
	 */
	public function getChoices(): array
	{
		return $this->getOption('choices');
	}

	public function build()
	{
		$value = $this->getEntityGetMethod()->invoke($this->entity);
		$this->setOption('choices', array_map(function ($choice) use ($value) {
			return $choice->defineAsSelected($value);
		}, $this->getOption('choices')));
		return $this;
	}

}