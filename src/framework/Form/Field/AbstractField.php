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

	public function __construct(string $id, mixed $value) {
		$this->id = $id;
		$this->value = $value;
	}


}