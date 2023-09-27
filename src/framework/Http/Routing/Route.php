<?php

namespace MVC\Http\Routing;

use MVC\Http\Controller\AbstractController;
use MVC\Http\Controller\ControllerInterface;
use MVC\Http\HTTPMethod;
use MVC\Http\Routing\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Exception\MissingRouteParamsException;

class Route
{

	public string $pattern;
	public array $attributes = [];

	private array $matchTypes = [
		'i'  => '[0-9]++',
		'a'  => '[0-9A-Za-z]++',
		'h'  => '[0-9A-Fa-f]++',
		'*'  => '.+?',
		'**' => '.++',
		''   => '[^/\.]++'
	];

	/**
	 * @param string $url
	 * @param string $controller
	 * @param string $controllerMethod
	 * @param array|string $acceptedMethods
	 * @param string|null $name
	 * @throws BadRouteDeclarationException
	 */
	public function __construct(
		public string $url,
		public string $controller,
		public string $controllerMethod,
		public array|string $acceptedMethods = [HTTPMethod::GET],
		public ?string $name = null,
	)
	{
		[$attrs, $pattern] = self::getUrlRegexAndAttrs($this->url);

		$this->pattern = $pattern;
		$this->setAttributes($attrs);
		if(!$this->validateController()) throw new BadRouteDeclarationException("Enable to declare route `{$this->url}` due to invalid Controller declaration.");
	}

	public function isValidMethod(HTTPMethod $method): bool
	{
		return in_array($method, $this->acceptedMethods);
	}

	private function setAttributes(array $attributes): void
	{
		$reflectionParameters = (new \ReflectionMethod($this->controller, $this->controllerMethod))->getParameters();
		$type = null;
		foreach ($attributes as $attribute) {
			foreach ($reflectionParameters as $param) {
				if($attribute === $param->getName()) {
					if (!$param->hasType()) break;
					$type = $param->getType()->getName();
				}
			}

			$this->attributes[$attribute] = new RouteParam($attribute, $type);
		}
	}


	private function validateController(): bool
	{
		try {
			$r = new \ReflectionClass($this->controller);
			if(!$r->implementsInterface(ControllerInterface::class)) return false;
			$m = $r->getMethod($this->controllerMethod);
		} catch (\ReflectionException) {
			return false;
		}
		return true;
	}

	private function getUrlRegexAndAttrs(string $route): array
	{
		$attrs = [];
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
			$matchTypes = $this->matchTypes;
			foreach ($matches as $match) {
				[$block, $pre, $type, $param, $optional] = $match;

				$attrs[] = $param;

				if (isset($matchTypes[$type])) $type = $matchTypes[$type];

				if ($pre === '.') $pre = '\.';

				$optional = $optional !== '' ? '?' : null;

				$pattern = '(?:'
					. ($pre !== '' ? $pre : null)
					. '('
					. ($param !== '' ? "?P<$param>" : null)
					. $type
					. ')'
					. $optional
					. ')'
					. $optional;

				$route = str_replace($block, $pattern, $route);
			}
		}

		return [
			$attrs,
			"`^$route$`u"
		];
	}


	public function buildUrl(?array $params = null): string
	{
		$url = $this->url;

		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $this->url, $matches, PREG_SET_ORDER)) {
			$matchTypes = $this->matchTypes;
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

}