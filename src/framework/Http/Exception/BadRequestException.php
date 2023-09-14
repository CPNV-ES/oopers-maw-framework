<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;

class BadRequestException extends HttpException
{

	const STATUS = HTTPStatus::BAD_REQUEST;

}