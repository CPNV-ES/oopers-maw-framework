<?php

namespace MVC\Http\Routing;

use MVC\Http\HTTPStatus;

class ErrorRoute
{

	public string $pattern;
	public array $attributes;

	/**
	 * @param HTTPStatus|array $status
	 * @param array $controller
	 */
	public function __construct(
		public HTTPStatus|array $status,
		public array $controller,
	)
	{
	}

}