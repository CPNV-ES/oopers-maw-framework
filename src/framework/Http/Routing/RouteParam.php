<?php

namespace MVC\Http\Routing;

use MVC\Http\Routing\ErrorRoute;
use MVC\Http\Routing\Route;

/**
 * That represent an dynamic component in a URL <em>(e.g. /user/<code>[username]</code>/edit)</em>. With it type extracted from Route annotation in controller and value populated after URL parsing.
 * @property string $name Name of parameter that is the same in the controller action parameters and in route declaration.
 * @property ?string $type Class name of parameter or type <em>(e.g. User, int, string)</em>
 * @property ?string $value Value parsed from URL <em>(e.g. /user/<code>dimitri_rutz</code>/edit)</em>
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