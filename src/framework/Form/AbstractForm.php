<?php

namespace MVC\Form;

use MVC\Form\Field\AbstractField;
use MVC\Http\HTTPMethod;
use MVC\Http\Request;
use MVC\OptionsResolver;

abstract class AbstractForm
{

	/** @var AbstractField[] */
	protected array $fields = [];
	private string $entity_name;
	private object $entity;

	private OptionsResolver $options;
	private ?Request $request = null;

	public function __construct(object $entity)
	{
		$this->entity = $entity;
		$this->setEntityName(get_class($entity));
		$this->options = FormOptionResolverFactory::create();
	}

	public function addOption(string $key, mixed $value): self
	{
		$this->options->set($key, $value);
		return $this;
	}

	public function handleRequest(Request $request): self
	{
		$this->setRequest($request);

		if ($this->isSubmitted()) {
			$this->bindRequest();
		}

		return $this;
	}

	public function isSubmitted(): bool
	{
		if (is_null($this->request)) throw new FormException("Cannot verify if submitted because no Request have been handled. Please use Form::handleRequest().");
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

	private function bindRequest(): self
	{
		foreach ($this->request->data as $key => $data) {
			if (!array_key_exists($key, $this->fields)) continue;
			$field = $this->getField($key);
			$field->setValue($data);
		}
		return $this;
	}

	public function getField(string $key): AbstractField
	{
		return $this->fields[$key];
	}

	abstract public function buildForm(): void;

	public function renderView(): FormView
	{
		return FormView::createFromForm($this);
	}

	public function setEntityName(string $entity_name): AbstractForm
	{
		$this->entity_name = $entity_name;
		return $this;
	}

	public function getOptions(): array
	{
		return $this->options->resolve();
	}

	public function isValid(): bool
	{
		return array_reduce($this->getFields(), function ($past, $current) {
			if (!$past) return false;
			return !$current->hasError();
		}, true);
	}

	public function getFields(): array
	{
		return $this->fields;
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
		return $this->options->resolve()[$key] ?? null;
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

}