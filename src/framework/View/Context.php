<?php

namespace MVC\View;

use MVC\Http\Request;
use MVC\Http\Routing\Exception\NotFoundRouteException;
use MVC\Kernel;

/**
 * Object passed to vue to allow getting app context information
 */
class Context implements ContextInterface
{

	public Request $request;
	private array $vars;

	/**
	 * @inheritDoc
	 */
	public function url(string $route, array $params = []): string
	{
		try {
			return Kernel::url($route, $params);
		} catch (NotFoundRouteException $e) {
			return "";
		}
	}

	/**
	 * @inheritDoc
	 */
	public function setVars(array $vars): Context
	{
		$this->vars = $vars;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function add(array $vars): Context
	{
		$this->vars = $vars;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		$out = $this->vars;
		$out['request'] = $this->request;
		return $out;
	}

	/**
	 * @inheritDoc
	 */
	public function __get(string $key): mixed
	{
		if (!array_key_exists($key, $this->vars)) return null;
		return $this->vars[$key];
	}
}