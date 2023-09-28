<?php

namespace MVC\Http\Controller;

use MVC\Http\Request;
use MVC\Http\Response\Response;
use MVC\Kernel;

/**
 * Base controller that add general features like render the view
 */
abstract class Controller
{

	protected ?string $layout;
	protected ?string $viewPath;

	public function __construct(
		protected Request $request,
	)
	{
	}

	protected function nameToPath(string $name): string
	{
		return Kernel::kernelVarsToString(($this->viewPath . str_replace(['.'], ['/'], $name) . '.php'));
	}

	/**
	 * Render a view and return a Response with rendered view as content
	 * @param string $view View path formatted _(e.g. posts/index.php => posts.index)_
	 * @param array $content
	 * @return Response
	 */
	protected function render(string $view, array $content = []): Response
	{
		return new Response($this->renderView($view, $content));
	}

	/**
	 * Render a view and return the rendered view as single string
	 * @param string $view View path formatted _(e.g. posts/index.php => posts.index)_
	 * @param array $content
	 * @return string
	 */
	public function renderView(string $view, array $content = []): string
	{
		extract($content);
		ob_start();
		require(self::nameToPath($view));
		$content = ob_get_clean();
		ob_start();
		require(Kernel::kernelVarsToString($this->viewPath . "templates/" . $this->layout . '.php'));
		return ob_get_clean();
	}

}