<?php

namespace MVC\View;

use MVC\View\ViewInterface;

class View implements ViewInterface
{

	private string $view_path;

	public function __construct(string $view_path)
	{
		$this->view_path = $view_path;
	}

	public function render(array $context = []): string
	{
		// TODO: Implement render() method.
	}
}