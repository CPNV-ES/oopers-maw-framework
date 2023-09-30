<?php

namespace MVC\Http\Routing;

use MVC\Http\Controller\Controller;
use MVC\Http\HTTPMethod;
use MVC\Http\Routing\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Exception\MissingRouteParamsException;

/**
 * A Route is a representation of route containing parameters see RouteParam RegExp pattern to match to URL
 * @property string $url URL template string
 * @property class-string $controller ClassName of controller
 * @property string $controllerMethod Method name of controller
 * @property HTTPMethod[] $acceptedMethods Array of method(s) can be used with route
 * @property ?string $name Name can be null if is set it can be used to identify route and to generate url
 */
class Route
{

	/**
	 * RegExp pattern
	 * @var string|mixed
	 */
	private string $pattern;

	/**
	 * Parameters passed in URL
	 * @see RouteParam
	 * @var RouteParam[]
	 */
	public array $parameters = [];

	/**
	 * @param string $url
	 * @param class-string $controller
	 * @param string $controllerMethod
	 * @param array|string $acceptedMethods
	 * @param string|null $name
	 * @throws BadRouteDeclarationException
	 * @throws \ReflectionException
	 */
	public function __construct(
		public string $url,
		public string $controller,
		public string $controllerMethod,
		public array $acceptedMethods = [HTTPMethod::GET],
		public ?string $name = null,
	)
	{
		if(!$this->validateController()) throw new BadRouteDeclarationException("Enable to declare route `{$this->url}` due to invalid Controller declaration.");
		$compiler = new RouteCompiler($this->url);
		$this
			->setPattern($compiler->getPattern())
			->setParameters($compiler->extractRouteParameters((new \ReflectionMethod($this->controller, $this->controllerMethod))->getParameters()))
		;
	}

	/**
	 * Verify if passed Method cas trigger current route
	 * @param HTTPMethod $method
	 * @return bool
	 */
	public function isValidMethod(HTTPMethod $method): bool
	{
		return in_array($method, $this->acceptedMethods);
	}


	private function validateController(): bool
	{
		try {
			$r = new \ReflectionClass($this->controller);
			if(!$r->isSubclassOf(Controller::class)) return false;
			$m = $r->getMethod($this->controllerMethod);
		} catch (\ReflectionException) {
			return false;
		}
		return true;
	}

	/**
	 * Generate URL with params
	 * @param array|null $params
	 * @return string
	 * @throws MissingRouteParamsException
	 */
	public function buildUrl(?array $params = null): string
	{
		$url = $this->url;

		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $this->url, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $index => $match) {
				[$block, $pre, $type, $param, $optional] = $match;

				if ($pre) {
					$block = substr($block, 1);
				}

				if (isset($params[$param])) {
					// Part is found, replace for param value
					$url = str_replace($block, $params[$param], $url);
				} elseif ($optional && $index !== 0) {
					// Only strip preceding slash if it's not at the base
					$url = str_replace($pre . $block, '', $url);
				} else {
					throw new MissingRouteParamsException("Mandatory '{$param}' URL parameter not provided !");
				}

			}
		}

		return $url;
	}


	/**
	 * @return string
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}

	/**
	 * @param string $pattern
	 * @return Route
	 */
	public  function setPattern(string $pattern): self
	{
		$this->pattern = $pattern;
		return $this;
	}

	/**
	 * @param array $parameters
	 * @return Route
	 */
	public  function setParameters(array $parameters): self
	{
		$this->parameters = $parameters;
		return $this;
	}

	/**
	 * @param RouteParam $routeParam
	 * @return Route
	 */
	public  function addParameter(RouteParam $routeParam): self
	{
		$this->parameters[$routeParam->name] = $routeParam;
		return $this;
	}



}