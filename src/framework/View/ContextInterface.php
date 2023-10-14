<?php

namespace MVC\View;

use MVC\Http\Request;

interface ContextInterface
{

	/**
	 * Generate url from path name
	 * @param string $route
	 * @param array $params
	 * @return string
	 */
	public function url(string $route, array $params = []): string;

	/**
	 * Add var to context registry
	 * @param array $vars
	 * @return $this
	 */
	public function add(array $vars): ContextInterface;

	/**
	 * Retrieve all vars
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * Merge vars array
	 * @param array $vars
	 * @return ContextInterface
	 */
	public function mergeVars(array $vars): ContextInterface;

	/**
	 * Define vars
	 * @param array $vars
	 * @return $this
	 */
	public function setVars(array $vars): ContextInterface;

	/**
	 * Resolve item stored in context
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key): mixed;


}