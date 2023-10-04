<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;
use MVC\Http\Response\Response;

class ForbiddenException extends HttpException
{

    public const STATUS = HTTPStatus::FORBIDDEN;

    public function getResponse(): Response
    {
        return new Response("Error 403 | Forbidden", self::STATUS);
    }
}