<?php

namespace MVC\View;

interface ViewInterface
{

	public function render(array $context = []): string;

}