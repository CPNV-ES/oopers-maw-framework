<?php

use MVC\Http\Routing\Exception\NotFoundRouteException;
use MVC\Http\Routing\Exception\MissingRouteParamsException;
use MVC\Http\Routing\Router;

/**
 * Allow to generate url with parameters
 * @param string $routeName
 * @param array|null $params
 * @return string
 * @throws NotFoundRouteException
 * @throws MissingRouteParamsException
 */
function generateUrl(string $routeName, ?array $params = null): string
{
	return Router::url($routeName, $params);
}