<?php

namespace MVC\View;

interface ViewInterface
{

	public function __construct(string $template, array $context);

	/**
	 * Method that return an HTML string got from path view and construction
	 * @param array $context
	 * @return string
	 */
	public function render(array $context = []): string;

	/**
	 * Alias to render executed when class is invoke as string
	 * @see [PHP Manual __toString](https://www.php.net/manual/en/language.oop5.magic.php#object.tostring)
	 * @return string
	 */
	public function __toString(): string;

}