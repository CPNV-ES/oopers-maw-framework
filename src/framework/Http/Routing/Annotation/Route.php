<?php

namespace MVC\Http\Routing\Annotation;

use Attribute;
use MVC\Http\HTTPMethod;

/**
 * Attribute used to declare routes in controller read with Reflection API
 */
#[Attribute(flags: Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route
{

	private array $methods = [];

	public function __construct(
		public string $path,
		public ?string $name = null,
		array|string $methods = 'GET'
	)
	{
		$this->setMethods($methods);
	}

	private function setMethods(string|array $methods): void
	{
		if (is_string($methods)) {
			$this->methods[] = HTTPMethod::from($methods);
			return;
		}
		$this->methods = array_map(fn($item) => $item instanceof HTTPMethod ? $item : HTTPMethod::from($item), $methods);
	}

	public function getMethods(): array
	{
		return $this->methods;
	}




}