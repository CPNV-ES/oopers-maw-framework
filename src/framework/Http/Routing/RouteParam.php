<?php

namespace MVC\Http\Routing;

class RouteParam
{

	public function __construct(
		public string $name,
		public ?string $className = null,
		public ?string $value = null,
	)
	{
	}

}