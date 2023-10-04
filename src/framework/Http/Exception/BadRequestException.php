<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;
use MVC\Http\Response\Response;

class BadRequestException extends HttpException
{

    public const STATUS = HTTPStatus::BAD_REQUEST;

    public function getResponse(): Response
    {
        return new Response('Error 400 | Bad Request', self::STATUS);
    }

}