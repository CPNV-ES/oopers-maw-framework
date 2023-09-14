<?php

namespace MVC\Http\Controller;

use MVC\Http\Request;

abstract class AbstractController
{

	public function __construct(
		public Request $request,
	)
	{
	}

}