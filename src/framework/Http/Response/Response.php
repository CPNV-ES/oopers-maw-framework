<?php

namespace MVC\Http\Response;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MVC\Http\HTTPStatus;

/**
 * Represent response with can be build from scratch and executed without any other requirements
 */
class Response
{

	/**
	 * Not all responses have one: responses with a status code that sufficiently answers the request without the need for corresponding payload (like `201` **`Created`** or `204` **`No Content`**) usually don't.
	 * @see [MDN HTTP Body](https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#body_2)
	 * @var string
	 */
	public string $content = "";

	/**
	 * A status code, indicating success or failure of the request. Common status codes are 200, 404, or 302
	 * @see [MDN HTTP Status Code](https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#status_line)
	 * @var HTTPStatus|mixed
	 */
	public HTTPStatus $status;

	/**
	 * HTTP headers for responses follow the same structure as any other header: a case-insensitive string followed by a colon (`':'`) and a value whose structure depends upon the type of the header. The whole header, including its value, presents as a single line.
	 * @see [MDN HTTP Headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#headers_2)
	 * @var Collection
	 */
	public Collection $headers;

	public function __construct(?string $content = null, ?string $uri = "", $status = HTTPStatus::OK, array|ArrayCollection $headers = new ArrayCollection())
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

	/**
	 * Will use all defined information in the Response to set using PHP functions Headers/Status Code/Body of the response
	 * Method is not blocking for blocking:
	 * @uses self::executeAndDie()
	 * @return void
	 */
	public function execute(): void
	{
		http_response_code($this->status->value);
		foreach ($this->headers as $key => $header) {
			header($key . ' ' . $header);
		}
		if ($this->getContent()) echo $this->getContent();
	}

	/**
	 * Call execute method and exit program
	 * @return void
	 */
	public function executeAndDie(): void
	{
		$this->execute();
		exit(0);
	}


}