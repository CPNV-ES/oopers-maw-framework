<?php

namespace MVC\Form;

use MVC\Http\Request;

class AbstractForm
{

	private string $entity_name;
	private object $entity;
	private array $options = [];
	private Request $request;


}