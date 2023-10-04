<?php

namespace MVC\Http\Exception;

use MVC\Http\HTTPStatus;
use MVC\Http\Response\Response;

class MethodNotAllowedException extends HttpException
{

	const STATUS = HTTPStatus::METHOD_NOT_ALLOWED;

	public function getResponse(): Response
	{
		return new Response('Error 405 | Method Not Allowed', null, self::STATUS);
	}

}