<?php

namespace MVC\Http\Exception;

use Exception;

/**
 * @const HTTPStatus STATUS
 */
abstract class HttpException extends Exception implements HttpExceptionInterface
{

    public const STATUS = null;

}