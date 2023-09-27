<?php

namespace MVC\Http\Routing;

use MVC\Http\Request;

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

	public function getParams(Request $request): array
	{
		$methodParams = $this->reflectionMethod->getParameters();
		$red = [];
		return array_reduce($methodParams, function ($past, $item) use ($request) {
			if ($request->params->containsKey($item->getName())) {
				$past[$item->getName()] = $request->params->get($item->getName())->value;
			}
			return $past;
		}, $red);
	}

}