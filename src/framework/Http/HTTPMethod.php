<?php

namespace MVC\Http;

/**
 * Enum of different HTTP Method/verbs
 */
enum HTTPMethod: string
{
	case GET = "GET";
	case POST = "POST";
	case PUT = "PUT";
	case PATCH = "PATCH";
	case DELETE = "DELETE";
}
