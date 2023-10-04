<?php

namespace MVC\Http\Response;

use MVC\Http\HTTPStatus;

/**
 * Response extended to be compatible with JSON
 */
class JsonResponse extends Response
{

	public function __construct(string $content = "", array $headers = [], $status = HTTPStatus::OK)
	{
		parent::__construct($content, $status, $headers);
		$this->headers->set('Content-Type', 'application/json');
	}

	/**
	 * Take any json encode-able type and turn it into a JSON string and assign it to content property
	 * @param array|string|object $content
	 * @return Response
	 */
	public function setContent(array|string|object $content): Response
	{
		if(is_array($content)) $this->content = json_encode($content);
		if(is_string($content)) $this->content = $content;
		return $this;
	}

}