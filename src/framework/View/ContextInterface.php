<?php

namespace MVC\View;

use MVC\Http\Request;

interface ContextInterface
{

	/**
	 * Used to resolve current request
	 * @return Request
	 */
	public function getRequest(): Request;

	/**
	 * Generate url from path name
	 * @param string $route
	 * @param array $params
	 * @return string
	 */
	public function path(string $route, array $params = []): string;

	/**
	 * Resolve item stored in context
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key): mixed;


}