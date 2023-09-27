<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;

/**
 * @const HTTPStatus STATUS
 */
abstract class HttpException extends \Exception implements HttpExceptionInterface
{

	const STATUS = null;

}