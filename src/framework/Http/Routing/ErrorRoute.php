<?php

namespace MVC\Http\Routing;

use MVC\Http\HTTPStatus;

/**
 * Class used to define which Controller action to execute for given HTTPStatus
 * @property HTTPStatus $status Define which status to use
 */
class ErrorRoute extends AbstractRoute
{

	/**
	 * @param HTTPStatus $status
	 * @param string $controller
	 * @param string $controllerMethod
	 */
	public function __construct(
		private readonly HTTPStatus $status,
		string                      $controller,
		string                      $controllerMethod,
	)
	{
		$this
			->setName('_error.' . strtolower($this->status->name))
			->setController($controller)
			->setControllerMethod($controllerMethod);
	}

	/**
	 * @return HTTPStatus
	 */
	public function getStatus(): HTTPStatus
	{
		return $this->status;
	}


}