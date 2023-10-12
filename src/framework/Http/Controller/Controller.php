<?php

namespace MVC\Http\Controller;

use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Http\Response\Response;
use MVC\Kernel;
use MVC\View\View;

/**
 * Base controller that add general features like render the view
 */
abstract class Controller
{

	protected ?string $layout = 'base';
	protected ?string $viewPath = '%kernel.project_dir%/views/';

	public function __construct(
		protected Request $request,
	)
	{
	}

	protected function getPathOfView(string $name): string
	{
		return Kernel::kernelVarsToString(($this->viewPath . str_replace(['.'], ['/'], $name) . '.php'));
	}


	/**
	 * Render a view and return a Response with rendered view as content
	 * @param string $view View path formatted _(e.g. posts/index.php => posts.index)_
	 * @param array $content
	 * @param HTTPStatus $status
	 * @return Response
	 */
	protected function render(string $view, array $content = [], HTTPStatus $status = HTTPStatus::OK): Response
	{
		return new Response($this->renderView($view, $content), $status);
	}

	/**
	 * Render a view and return the rendered view as single string
	 * @param string $view View path formatted _(e.g. posts/index.php => posts.index)_
	 * @param array $content
	 * @return string
	 */
	public function renderView(string $view, array $content = []): string
	{
		$content = (new View($view))->render($content);
		if ($this->layout) {
			$content = (new View($this->layout))->render(['body' => $content]);
		}
		return $content;
	}

}