<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;
use MVC\Http\Response\Response;

class UnauthorizedException extends HttpException
{

	const STATUS = HTTPStatus::UNAUTHORIZED;

	public function getResponse(): Response
	{
		return new Response('Error 401 | Unauthorized', null, self::STATUS);
	}

}