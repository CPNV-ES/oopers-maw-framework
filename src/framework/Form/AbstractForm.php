<?php

namespace MVC\Form;

use MVC\Http\Request;

class AbstractForm
{

	private string $entity_name;
	private object $entity;
	private array $options = [];
	private Request $request;

	public function __construct(object $entity, Request $request) {
		$this->entity = $entity;
		$this->request = $request;
	}


}