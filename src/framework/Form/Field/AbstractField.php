<?php

namespace MVC\Form\Field;

use MVC\Form\FormException;
use MVC\View\View;

abstract class AbstractField
{

	/**
	 * Used as identifier in html for link between labels and inputs
	 * @var string
	 */
	private string $id;

	protected mixed $value;

	/**
	 * Name is HTML attribute allow PHP to create $_POST associated array with name and value of field
	 * @var string
	 */
	private string $name;

	private array $options = [];

	private array $availableOptions = [
		'label',
		'attributes',
		'constraints',
	];

	private array $error = [];

	protected \ReflectionProperty $property;

	protected \ReflectionMethod $entityGetMethod;
	protected \ReflectionMethod $entitySetMethod;
	protected object $entity;
	private bool $changes = false;

	protected array $children = [];

	public function __construct(string $id, mixed $value, \ReflectionProperty $property, object $entity)
	{
		$this->id = $id;
		$this->value = $value;
		$this->property = $property;
		$this->entity = $entity;
	}

	public static function createFromFormBuilder(string $property, string $type, object $entity, array $options = []): self
	{
		if (!is_subclass_of($type, AbstractField::class)) throw new FormException("Unable to use `$type` as form field.");
		$propertyReflection = new \ReflectionProperty($entity, $property);
		$camelCase = str_replace(['_'], [''], ucwords($property, "\t\r\n\f\v_"));
		if (!$propertyReflection->getDeclaringClass()->hasMethod('get' . $camelCase)) throw new FormException("Unable to find getter for $property in {$propertyReflection->getDeclaringClass()->getName()}");
		if (!$propertyReflection->getDeclaringClass()->hasMethod('set' . $camelCase)) throw new FormException("Unable to find setter for $property in {$propertyReflection->getDeclaringClass()->getName()}");

		return (new $type($property, $propertyReflection->getDeclaringClass()->getMethod('get' . $camelCase)->invoke($entity), $propertyReflection, $entity))
			->setName($property)
			->setEntitySetMethod($propertyReflection->getDeclaringClass()->getMethod('set' . $camelCase))
			->setEntityGetMethod($propertyReflection->getDeclaringClass()->getMethod('get' . $camelCase))
			->mergeOptions($options)
			->build();
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
		if ($this->getOption('constraint')) {
			$this->setError($this->getOption('constraint')($value));
		}
		$this->value = $value;
		$this->updateEntity();
		return $this;
	}

	public function getOption(string $key): mixed
	{
		return $this->options[$key] ?? null;
	}

	protected function updateEntity(): self
	{
		if ($this->getEntityValue() === $this->getValue() || $this->hasError()) return $this;
		$this
			->setEntityValue($this->getValue())
			->changes = true;;
		return $this;
	}

	private function getEntityValue(): self
	{
		$this->getEntityGetMethod()->invoke($this->getEntity());
		return $this;
	}

	public function getEntityGetMethod(): \ReflectionMethod
	{
		return $this->entityGetMethod;
	}

	public function setEntityGetMethod(\ReflectionMethod $entityGetMethod): AbstractField
	{
		$this->entityGetMethod = $entityGetMethod;
		return $this;
	}

	public function getEntity(): object
	{
		return $this->entity;
	}

	public function setEntity(object $entity): AbstractField
	{
		$this->entity = $entity;
		return $this;
	}

	public function hasError(): bool
	{
		return !empty($this->error);
	}

	private function setEntityValue(mixed $value): self
	{
		$this->getEntitySetMethod()->invoke($this->getEntity(), $value);
		return $this;
	}

	public function getEntitySetMethod(): \ReflectionMethod
	{
		return $this->entitySetMethod;
	}

	public function setEntitySetMethod(\ReflectionMethod $entitySetMethod): AbstractField
	{
		$this->entitySetMethod = $entitySetMethod;
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

	public function setOption(string $key, array|string $option): AbstractField
	{
		$this->options[$key] = $option;
		return $this;
	}

	public function getErrorMessage(): string
	{
		return $this->getError()['message'];
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

	public function getLabel(): string
	{
		return is_array($this->getOption('label')) ? $this->getOption('label')['text'] : $this->getOption('label');
	}

	public function getAttributes(): string
	{
		$out = "";
		foreach ($this->getOption('attributes') as $key => $item) {
			if (is_array($item)) {
				$out .= "$key=\"" . implode(" ", $item) . "\"";
			} else {
				$out .= "$key=\"$item\"";
			}
		}
		return $out;
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		$view = new FieldView($this->getOption('view_template'), ['field' => $this]);
		return $view->render();
	}

	/**
	 * Method called after having defined all variables about the field
	 * Must be overwritten to add specific behavior
	 *
	 * When overwritten method MUST return current instance
	 * @return self
	 */
	public function build(): self
	{
		return $this;
	}

	public function getChildren(): array
	{
		return $this->children;
	}

	public function setChildren(array $children): AbstractField
	{
		$this->children = $children;
		return $this;
	}


}