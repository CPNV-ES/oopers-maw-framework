<?php

namespace MVC\Form;

use MVC\Form\Field\AbstractField;
use MVC\Http\HTTPMethod;
use MVC\Http\Request;

abstract class AbstractForm
{

	/** @var AbstractField[] */
	protected array $fields = [];
	private string $entity_name;
	private object $entity;
	private array $options = [];
	private Request $request;
	private array $default_options = [
		'view_template' => 'required',
		'action_route' => 'optional',
	];

	public function __construct(object $entity, Request $request)
	{
		$this->entity = $entity;
		$this->request = $request;
	}

	abstract public function buildForm(): void;

	public function getEntityName(): string
	{
		return $this->entity_name;
	}

	public function setEntityName(string $entity_name): AbstractForm
	{
		$this->entity_name = $entity_name;
		return $this;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function setOptions(array $options): AbstractForm
	{
		$this->options = $options;
		return $this;
	}

	public function addOption(string $key, string $value): self
	{
		$this->options[$key] = $value;
		return $this;
	}

	public function getOption(string $key): mixed
	{
		return $this->options[$key];
	}

	public function getRequest(): Request
	{
		return $this->request;
	}

	public function setRequest(Request $request): AbstractForm
	{
		$this->request = $request;
		return $this;
	}

	public function getDefaultOptions(): array
	{
		return $this->default_options;
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function getField(string $key): AbstractField
	{
		return $this->fields[$key];
	}

	public function setFields(array $fields): AbstractForm
	{
		$this->fields = $fields;
		return $this;
	}

	protected function add(string $property, string $type, array $options = []): AbstractForm
	{
		$field = AbstractField::createFromFormBuilder($property, $type, $this->getEntity(), $options);
		$this->fields[$field->getName()] = $field;
		return $this;
	}

	public function getEntity(): object
	{
		return $this->entity;
	}

	public function setEntity(object $entity): AbstractForm
	{
		$this->entity = $entity;
		return $this;
	}

	public function isSubmitted(): bool
	{
		if ($this->getRequest()->method === HTTPMethod::POST) {
			$formKeys = array_keys($this->fields);
			foreach ($formKeys as $formKey) {
				if (!array_key_exists($formKey, $this->getRequest()->data->toArray())) return false;
			}
		}
		return true;
	}


}