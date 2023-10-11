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

	private array $error = [];

	private \ReflectionProperty $property;

	public function __construct(string $id, mixed $value, \ReflectionProperty $property)
	{
		$this->id = $id;
		$this->value = $value;
		$this->property = $property;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function setId(string $id): AbstractField
	{
		$this->id = $id;
		return $this;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}

	public function setValue(mixed $value): AbstractField
	{
		$this->value = $value;
		return $this;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function setOptions(array $options): AbstractField
	{
		$this->options = $options;
		return $this;
	}

	public function addOption(string $key, array|string $option): AbstractField
	{
		$this->options[$key] = $option;
		return $this;
	}

	public function getError(): array
	{
		return $this->error;
	}

	public function hasError(): bool
	{
		return !empty($this->error);
	}

	public function setError(array $error): AbstractField
	{
		$this->error = $error;
		return $this;
	}

	public function getProperty(): \ReflectionProperty
	{
		return $this->property;
	}

	public function setProperty(\ReflectionProperty $property): AbstractField
	{
		$this->property = $property;
		return $this;
	}


}