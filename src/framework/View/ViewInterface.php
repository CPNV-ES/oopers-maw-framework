<?php

namespace MVC\View;

interface ViewInterface
{

	public function __construct(string $template);

	public function render(array $context = []): string;

}