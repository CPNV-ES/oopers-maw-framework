<?php

namespace MVC\Http\Routing;

use MVC\Http\Controller\AbstractController;
use MVC\Http\Exception\BadRouteDeclarationException;
use MVC\Http\HTTPMethod;

class Route
{

	public string $pattern;
	public array $attributes;

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
	 * @param array $controller
	 * @param array $acceptedMethods
	 * @param string|null $name
	 * @throws BadRouteDeclarationException
	 */
	public function __construct(
		public string $url,
		public array $controller,
		public array $acceptedMethods = [HTTPMethod::GET],
		public ?string $name = null
	)
	{
		[$attrs, $pattern] = self::getUrlRegexAndAttrs($this->url);
		$this->attributes = [];
		$this->pattern = $pattern;
		$this->setAttributes($attrs);
		if(!$this->validateController()) throw new BadRouteDeclarationException("Impossible to declare route `{$this->url}` due to invalid Controller declaration.");
	}

	public function isValidMethod(HTTPMethod $method): bool
	{
		return in_array($method, $this->acceptedMethods);
	}

	private function setAttributes(array $attributes): void
	{
		$reflectionParameters = (new \ReflectionMethod($this->controller[0], $this->controller[1]))->getParameters();
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
			$r = new \ReflectionClass($this->controller[0]);
			if(!$r->isSubclassOf(AbstractController::class)) return false;
			$m = $r->getMethod($this->controller[1]);
		} catch (\ReflectionException $e) {
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
				list($block, $pre, $type, $param, $optional) = $match;

				$attrs[] = $param;

				if (isset($matchTypes[$type])) {
					$type = $matchTypes[$type];
				}
				if ($pre === '.') {
					$pre = '\.';
				}

				$optional = $optional !== '' ? '?' : null;

				//Older versions of PCRE require the 'P' in (?P<named>)
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

}