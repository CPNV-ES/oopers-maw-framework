<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;

class NotFoundException extends HttpException
{

	const STATUS = HTTPStatus::NOT_FOUND;

}