<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;

class UnauthorizedException extends HttpException
{

	const STATUS = HTTPStatus::UNAUTHORIZED;

}