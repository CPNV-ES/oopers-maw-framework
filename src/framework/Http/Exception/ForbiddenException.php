<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;

class ForbiddenException extends HttpException
{

	const STATUS = HTTPStatus::FORBIDDEN;

}