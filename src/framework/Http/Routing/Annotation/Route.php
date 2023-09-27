<?php

namespace MVC\Http\Routing\Annotation;

use Attribute;
use MVC\Http\HTTPMethod;

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
		$this->methods = array_map(fn($item) => HTTPMethod::from($item), $methods);
	}




}