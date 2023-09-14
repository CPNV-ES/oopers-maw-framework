<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;

class MethodNotAllowedException extends HttpException
{

	const STATUS = HTTPStatus::METHOD_NOT_ALLOWED;

}