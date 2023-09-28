<?php

namespace MVC\Http;

use MVC\Http\Exception\HttpException;

// TODO: Complete Status list
/**
 * Enum of different HTTP Status with numeral code and allow to get Exception that correspond to status for blocking status
 */
enum HTTPStatus: int
{
	case OK = 200;
	case BAD_REQUEST = 400;
	case UNAUTHORIZED = 401;
	case FORBIDDEN = 403;
	case NOT_FOUND = 404;
	case METHOD_NOT_ALLOWED = 405;
	case INTERNAL_SERVER_ERROR = 500;


	public function getException(): HttpException|false
	{
		if ($this->value < 400) return false;
		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($this->name))));
		$str = 'MVC\\Http\\Exception\\' . $str.'Exception';
		if(!class_exists($str)) return false;
		return new $str();
	}

}
