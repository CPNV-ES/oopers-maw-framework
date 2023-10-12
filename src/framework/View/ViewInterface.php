<?php

namespace MVC\View;

interface ViewInterface
{

	public function __construct(string $view_path);

	public function render(array $context = []): string;

}