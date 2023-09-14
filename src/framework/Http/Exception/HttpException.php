<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;

/**
 * @const HTTPStatus STATUS
 */
class HttpException extends \Exception
{

	const STATUS = HTTPStatus::OK;

}