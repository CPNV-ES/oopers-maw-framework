<?php

namespace MVC\Http\Response;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MVC\Http\HTTPStatus;

class Response
{

	public string $content = "";

	public HTTPStatus $status;

	public Collection $headers;

	public function __construct(string $content = "", ?string $uri = "", $status = HTTPStatus::OK, array|ArrayCollection $headers = new ArrayCollection())
	{
		$this->headers = $headers;
		$this->headers->set('Content-Type', 'text/html');
		$this->content = $content;
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * @param string $content
	 * @return Response
	 */
	public function setContent(string $content): Response
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * @return HTTPStatus
	 */
	public function getStatus(): HTTPStatus
	{
		return $this->status;
	}

	/**
	 * @param HTTPStatus|int $status
	 * @return Response
	 */
	public function setStatus(HTTPStatus|int $status): Response
	{
		if (is_int($status)) $status = HTTPStatus::from($status);
		$this->status = $status;
		return $this;
	}

	public function execute(): void
	{
		http_response_code($this->status->value);
		foreach ($this->headers as $key => $header) {
			header($key . ' ' . $header);
		}
		echo $this->getContent();
	}

	public function executeAndDie(): void
	{
		$this->execute();
		exit(0);
	}


}