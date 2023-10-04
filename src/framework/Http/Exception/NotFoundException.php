<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;
use MVC\Http\Response\Response;

class NotFoundException extends HttpException
{

	const STATUS = HTTPStatus::NOT_FOUND;

	public function getResponse(): Response
	{
		return new Response('Error 404 | Not Found', null, self::STATUS);
	}

}