<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;
use MVC\Http\Response;

class InternalServerErrorException extends HttpException
{

    public const STATUS = HTTPStatus::INTERNAL_SERVER_ERROR;

    public function getResponse(): Response
    {
        return new Response('Error 500 | Internal Server Error', self::STATUS);
    }

}