<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;
use MVC\Http\Response;

class UnauthorizedException extends HttpException
{

    public const STATUS = HTTPStatus::UNAUTHORIZED;

    public function getResponse(): Response
    {
        return new Response('Error 401 | Unauthorized', self::STATUS);
    }

}