<?php

namespace MVC\Http\Routing;

use MVC\Http\Request;

/**
 * Interpret Controller action parameters to bind request parameters in called method
 */
class ParamConverter
{

	private \ReflectionMethod $reflectionMethod;

	/**
	 * @throws \ReflectionException
	 */
	public function __construct(
		string $className,
		string $method
	) {
		$this->reflectionMethod = new \ReflectionMethod($className, $method);
	}

	/**
	 * Loop threw method parameters and parameters with same names will be return in array <key as $variable, value as parameter passed value>
	 * @param Request $request
	 * @return array<string, mixed>
	 */
	public function getParams(Request $request): array
	{
		$methodParams = $this->reflectionMethod->getParameters();
		return array_reduce($methodParams, function ($past, $item) use ($request) {
			if ($request->params->containsKey($item->getName())) {
				$past[$item->getName()] = $request->params->get($item->getName())->value;
			}
			return $past;
		}, []);
	}

}