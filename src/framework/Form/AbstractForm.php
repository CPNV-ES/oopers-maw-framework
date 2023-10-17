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

	/**
	 * @var array{
	 *     view_template: string,
	 *     action_route: ?string,
	 * }
	 */
	private array $options = [];
	private Request $request;
	private array $default_options = [
		'view_template' => 'required',
		'action_route' => 'optional',
	];

	public function __construct(object $entity)
	{
		$this->entity = $entity;
		$this->setEntityName(get_class($entity));
		$this->defaultOptions();
	}

	public function defaultOptions(): self
	{
		return $this
			->addOption('method', 'POST')
			->addOption('attributes', [])
			->addOption('action_route', '');
	}

	public function handleRequest(Request $request): self
	{
		$this->setRequest($request);

		return $this;
	}

	public function addOption(string $key, mixed $value): self
	{
		$this->options[$key] = $value;
		return $this;
	}

	abstract public function buildForm(): void;

	public function renderView(): FormView
	{
		return FormView::createFromForm($this);
	}

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

	public function getDefaultOptions(): array
	{
		return $this->default_options;
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function setFields(array $fields): AbstractForm
	{
		$this->fields = $fields;
		return $this;
	}

	public function getField(string $key): AbstractField
	{
		return $this->fields[$key];
	}

	public function isSubmitted(): bool
	{
		if(!$this->request) throw new FormException("Cannot verify if submitted because no Request have been handled. Please use Form::handleRequest().");
		if ($this->getRequest()->method === HTTPMethod::POST) {
			$formKeys = array_keys($this->fields);
			foreach ($formKeys as $formKey) {
				if (!array_key_exists($formKey, $this->getRequest()->data->toArray())) return false;
			}

			return true;
		}
		return false;
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

	public function getOption(string $key): mixed
	{
		return $this->options[$key] ?? null;
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

}