<?php

namespace MVC\Form;

use MVC\Http\Request;

abstract class AbstractForm
{

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

	public function getEntity(): object
	{
		return $this->entity;
	}

	public function setEntity(object $entity): AbstractForm
	{
		$this->entity = $entity;
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


}