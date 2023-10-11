<?php

namespace MVC\Form\Field;

use MVC\Form\FormException;
use function PHPUnit\Framework\returnArgument;

class AbstractField
{

	/**
	 * Used as identifier in html for link between labels and inputs
	 * @var string
	 */
	private string $id;

	private mixed $value;

	/**
	 * Name is HTML attribute allow PHP to create $_POST associated array with name and value of field
	 * @var string
	 */
	private string $name;

	private array $options = [];

	private array $availableOptions = [
		'label',
		'class',
		'constraints',
	];

	private array $error = [];

	private \ReflectionProperty $property;

	public function __construct(string $id, mixed $value, \ReflectionProperty $property)
	{
		$this->id = $id;
		$this->value = $value;
		$this->property = $property;
	}

	public static function createFromFormBuilder(string $property, string $type, object $entity, array $options = []): self
	{
		if (!$type instanceof AbstractField) throw new FormException("Unable to use `$type` as form field.");
		$propertyReflection = new \ReflectionProperty($entity, $property);
		$camelCase = str_replace(['_'], [''], ucwords($property, "\t\r\n\f\v_"));
		if (!$propertyReflection->getDeclaringClass()->hasMethod('get' . $camelCase)) throw new FormException("Unable to find getter for $property in {$propertyReflection->getDeclaringClass()->getName()}");
		if (!$propertyReflection->getDeclaringClass()->hasMethod('set' . $camelCase)) throw new FormException("Unable to find setter for $property in {$propertyReflection->getDeclaringClass()->getName()}");

		return (new $type)
			->mergeOptions($options)
			->setProperty($propertyReflection)
			->setValue($propertyReflection->getDeclaringClass()->getMethod('get' . $camelCase)->invoke($entity))
			->setId(uniqid($property . '_'));
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): AbstractField
	{
		$this->name = $name;
		return $this;
	}

	public function mergeOptions(array $options): AbstractField
	{
		$this->options = array_merge_recursive($this->options, $options);
		return $this;
	}

	abstract public function render(): string;

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

	public function getOption(string $key): array|string|null
	{
		return $this->options[$key] ?? null;
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

	public function setError(array $error): AbstractField
	{
		$this->error = $error;
		return $this;
	}

	public function hasError(): bool
	{
		return !empty($this->error);
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

	public function getAvailableOptions(): array
	{
		return $this->availableOptions;
	}

}