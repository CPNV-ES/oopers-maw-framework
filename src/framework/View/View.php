<?php

namespace MVC\View;

use MVC\Kernel;

/**
 * Class used represent a view and render using ob PHP functions
 */
class View implements ViewInterface
{

	protected ?string $views_path = '%kernel.project_dir%/views/';

	private string $template;

	private array $context;

	public function __construct(string $template, array $context = [])
	{
		$this->template = $template;
		$this->context = $context;
	}

	public function render(array $context = []): string
	{
		$this->context = array_merge_recursive($this->context, $context);
		$path = $this->views_path;

		$path = str_replace(['.'], ['/'], Kernel::kernelVarsToString($path . $this->template));

		if (!str_ends_with($this->template, '.php')) {
			$path .= '.php';
		}

		try {
			ob_start();
			require $path;
			$content = ob_get_clean();
		} catch (\Throwable $e) {
			throw new ViewException("Unable to render view `{$this->template}`.", previous: $e);
		}
		return $content;
	}

	private function get(string $name): mixed
	{
		return $this->context[$name];
	}

	private function url(string $name, array $parameters = []): string
	{
		return Kernel::url($name, $parameters);
	}

	public function __get(string $name): mixed
	{
		return $this->get($name);
	}

	public function __toString(): string
	{
		return $this->render();
	}
}