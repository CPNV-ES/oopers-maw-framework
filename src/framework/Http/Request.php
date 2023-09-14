<?php

namespace MVC\Http;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MVC\Http\Routing\Route;
use MVC\Http\Routing\RouteParam;

class Request
{
	/**
	 * Populated when request method is POST
	 * @var Collection
	 */
	public Collection $data;

	public Collection $headers;

	/**
	 * Populated with query string data in url
	 * @var Collection
	 */
	public Collection $query;

	/**
	 * Method of request
	 * @var HTTPMethod
	 */
	public HTTPMethod $method;

	/**
	 * Arguments passed in URL
	 * @var Collection|null
	 */
	public ?Collection $params = null;

	public ?Route $matchedRoute = null;


	public function __construct(public ?string $uri)
	{
		$this->query = new ArrayCollection();
		$this->data = new ArrayCollection();
		$this->headers = new ArrayCollection();
	}

	public static function createFromCurrent(): self
	{
		$uri = explode('?',$_SERVER['REQUEST_URI'])[0];
		$method = HTTPMethod::from($_SERVER['REQUEST_METHOD']);
		$req = (new Request($uri))
			->setMethod($method)
		;
		foreach (getallheaders() as $key => $header) {
			$req->headers->set($key, $header);
		}
		if($method === HTTPMethod::POST) {
			$req->setData($_POST);
		}
		if(!empty($_GET)) {
			$req->query = new ArrayCollection($_GET);
		}

		return $req;
	}

	/**
	 * @param RouteParam $param
	 * @return Request
	 */
	public function addParam(RouteParam $param): Request
	{
		if(is_null($this->params)) $this->params = new ArrayCollection();
		$this->params->set($param->name, $param);
		return $this;
	}

	/**
	 * @param Collection|array $data
	 * @return Request
	 */
	public function setData(Collection|array $data): Request
	{
		if (is_array($data)) $data = new ArrayCollection($data);
		$this->data = $data;
		return $this;
	}

	/**
	 * @param Collection|array $query
	 * @return Request
	 */
	public function setQuery(Collection|array $query): Request
	{
		if (is_array($query)) $query = new ArrayCollection($query);
		$this->query = $query;
		return $this;
	}

	/**
	 * @param HTTPMethod|string $method
	 * @return Request
	 */
	public function setMethod(HTTPMethod|string $method): Request
	{
		$this->method = is_string($method) ? HTTPMethod::from($method) : $method;
		return $this;
	}




}