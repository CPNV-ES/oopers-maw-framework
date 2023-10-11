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

	public function __construct(object $entity, Request $request) {
		$this->entity = $entity;
		$this->request = $request;
	}

	abstract public function buildForm(): void;

	public function addOption(string $key, string $value): self
	{
		$this->options[$key] = $value;
		return $this;
	}


}