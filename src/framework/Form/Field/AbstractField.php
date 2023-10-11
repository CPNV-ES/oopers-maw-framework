<?php

namespace MVC\Form\Field;

class AbstractField
{

	/**
	 * Used as identifier in html for link between labels and inputs
	 * @var string
	 */
	private string $id;

	private mixed $value;

	private array $options = [
		'label' => [
			'value' => 'Text Field',
			'class' => []
		],
		'class' => [],
	];

	private \ReflectionProperty $property;

	public function __construct(string $id, mixed $value, \ReflectionProperty $property) {
		$this->id = $id;
		$this->value = $value;
		$this->property = $property;
	}


}