<?php

namespace MVC\View;

interface ViewInterface
{

	public function __construct(string $template);


	/**
	 * Method that return an HTML string got from path view and construction
	 * @param array $context
	 * @return string
	 */
	public function render(array $context = []): string;

}