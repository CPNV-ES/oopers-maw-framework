<?php

namespace MVC\Http\Routing;

use MVC\Http\Routing\ErrorRoute;
use MVC\Http\Routing\Route;

/**
 * That represent an dynamic component in a URL _(e.g. /user/`[username]`/edit)_. With it type extracted from Route annotation in controller and value populated after URL parsing.
 * @property string $name Name of parameter that is the same in the controller action parameters and in route declaration.
 * @property ?string $type Class name of parameter or type _(e.g. User, int, string)_
 * @property ?string $value Value parsed from URL _(e.g. /user/`dimitri_rutz`/edit)_
 */
class RouteParam
{

	public function __construct(
		public string  $name,
		public ?string $type = null,
		public ?string $value = null,
	)
	{
	}

}