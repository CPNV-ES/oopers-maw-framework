<?php

namespace MVC\Http\Controller;

use MVC\Http\Request;
use MVC\Http\Response\Response;
use MVC\Kernel;

abstract class Controller implements ControllerInterface
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
	* @param string $view View path formatted <em>(e.g. posts/index.php => posts.index)<em>
	* @param array $content
	* @return Response
	 */
	public function render(string $view, array $content = []): Response
	{
		extract($content);
		ob_start();
		require(self::nameToPath($view));
		$content = ob_get_clean();
		ob_start();
		require(Kernel::kernelVarsToString($this->viewPath . "templates/" . $this->layout . '.php'));
		$rendered = ob_get_clean();
		return new Response($rendered);
	}

}