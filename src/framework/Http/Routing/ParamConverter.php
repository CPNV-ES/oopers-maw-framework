<?php

namespace MVC\Http\Routing;

use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Kernel;
use ORM\Table;

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
		private readonly AbstractRoute $route
	) {
		$this->reflectionMethod = new \ReflectionMethod($this->route->getController(), $this->route->getControllerMethod());
	}

	/**
	 * Loop threw method parameters and parameters with same names will be return in array <key as $variable, value as parameter passed value>
	 * @param Request $request
	 * @return array<string, mixed>
	 * @throws \Exception
	 */
	public function getParams(Request $request): array
	{
		$methodParams = $this->reflectionMethod->getParameters();
		return array_reduce($methodParams, function ($past, $item) use ($request) {
			/** @var \ReflectionParameter $item */


            $attrs = [];
            if ($item->getType() instanceof \ReflectionUnionType) {
                foreach ($item->getType()->getTypes() as $type) {
                    if (!class_exists($type->getName())) continue;
                    $paramType = $type->getName();
                    $attrs = (new \ReflectionClass($type->getName()))->getAttributes(Table::class);
                }
            } else {
                if (class_exists($item->getType()->getName())) {
                    $attrs = (new \ReflectionClass($item->getType()->getName()))->getAttributes(Table::class);
                    $paramType = $item->getType()->getName();
                }
            }

            if (count($attrs) === 1) {

            }

			if ($request->params->containsKey($item->getName())) {
				$past[$item->getName()] = $request->params->get($item->getName())->value;
			} else {
				$past[$item->getName()] = match ($item->getType()->getName()) {
					HTTPStatus::class => $this->convertStatus($item->getName()),
					default => Kernel::getInstance()->container->get($item->getType()->getName())
				};
			}

			return $past;
		}, []);
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	private function convertStatus(string $name): mixed
	{
		if(!$this->reflectionMethod->getAttributes(\MVC\Http\Routing\Annotation\ErrorRoute::class)[0]) throw new \InvalidArgumentException("Parameter `$name` in {$this->reflectionMethod->class} in method named `{$this->reflectionMethod->name}`");
		$attribute = $this->reflectionMethod->getAttributes(\MVC\Http\Routing\Annotation\ErrorRoute::class)[0];
		/** @var \MVC\Http\Routing\Annotation\ErrorRoute $attrInstance */
		$attrInstance = $attribute->newInstance();
		return $attrInstance->status;
	}

}