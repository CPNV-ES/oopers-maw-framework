<?php

namespace MVC\Http\Routing;

use MVC\Http\HTTPStatus;

// TODO: Make extends from Route
/**
 * Class used to define which Controller action to execute for given HTTPStatus
 * @property HTTPStatus $status Define which status to use
 * @property class-string $controller ClassName of controller
 * @property string $controllerMethod Method name of controller
 */
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