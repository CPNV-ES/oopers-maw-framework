<?php

namespace MVC\Http\Routing;

use MVC\Http\HTTPStatus;

// TODO: Make extends from Route
class ErrorRoute
{

	/**
	 * @param HTTPStatus $status
	 * @param string $controller
	 * @param string $controllerMethod
	 */
	public function __construct(
		public HTTPStatus $status,
		public string $controller,
		public string $controllerMethod,
	)
	{
	}

}