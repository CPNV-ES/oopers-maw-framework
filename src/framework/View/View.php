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

	public function add(string $key, mixed $value): self
	{
		$this->context[$key] = $value;
		return $this;
	}

	public function __get(string $name): mixed
	{
		return $this->get($name);
	}

	/**
	 * Resolve data passed to view from controller
	 *
	 * @param string $name
	 * @return mixed
	 */
	private function get(string $name): mixed
	{
		return array_key_exists($name, $this->context) ? $this->context[$name] : null;
	}

	public function __toString(): string
	{
		return $this->render();
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

    /**
     * Include and render another view inside the actual template that is rendering
     * Method is private because de scope of view file keep scope of render method
     * @param string $template
     * @param array $context
     * @return string - The generated content
     * @throws ViewException
     */
    private function include(string $template, array $context = []): string
    {
        return (new View($template))->render($context);
    }

	/**
	 * Used to generate url from views.
	 * Method is private because de scope of view file keep scope of render method
	 *
	 * @param string $name
	 * @param array $parameters
	 * @return string
	 * @throws \MVC\Http\Routing\Exception\MissingRouteParamsException
	 * @throws \MVC\Http\Routing\Exception\NotFoundRouteException
	 */
	private function url(string $name, array $parameters = []): string
	{
		return Kernel::url($name, $parameters);
	}
}