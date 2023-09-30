<?php

namespace MVC\Http\Routing;

use MVC\Http\Controller\Controller;

/**
 * Abstraction of any route type
 * @property class-string $controller ClassName of controller
 * @property string $controllerMethod Method name of controller
 */
abstract class AbstractRoute
{

	protected ?string $name = null;
	protected readonly string $controller;
	protected readonly string $controllerMethod;

	protected function validateController(): bool
	{
		try {
			$r = new \ReflectionClass($this->controller);
			if (!$r->isSubclassOf(Controller::class)) return false;
			$m = $r->getMethod($this->controllerMethod);
		} catch (\ReflectionException) {
			return false;
		}
		return true;
	}

	/**
	 * @return string
	 */
	public function getController(): string
	{
		return $this->controller;
	}

	/**
	 * @param string $controller
	 * @return AbstractRoute
	 */
	public function setController(string $controller): AbstractRoute
	{
		$this->controller = $controller;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getControllerMethod(): string
	{
		return $this->controllerMethod;
	}

	/**
	 * @param string $controllerMethod
	 * @return AbstractRoute
	 */
	public function setControllerMethod(string $controllerMethod): AbstractRoute
	{
		$this->controllerMethod = $controllerMethod;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string|null $name
	 * @return AbstractRoute
	 */
	public function setName(?string $name): AbstractRoute
	{
		$this->name = $name;
		return $this;
	}


}