<?php

namespace MVC\View;

use MVC\View\ViewInterface;

class View implements ViewInterface
{

	protected ?string $views_path = '%kernel.project_dir%/views/';

	private string $template;

	public function __construct(string $template)
	{
		$this->template = $template;
	}

	public function render(array $context = []): string
	{
		// TODO: Implement render() method.
	}
}