<?php

namespace MVC\Http\Controller;

use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Http\Response\Response;
use MVC\Http\Routing\Exception\MissingRouteParamsException;
use MVC\Http\Routing\Exception\NotFoundRouteException;
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
     * Generate a redirection response to a internal route
    * @param string $routeName The internal name of the route
    * @param array|null $routeParams A array of parameters used in the route
    * @param bool $permanent Is the redirection permanent ? (301 status if yes, 302 if not)
    * @return Response The empty body response with a Location header
    * @throws MissingRouteParamsException
    * @throws NotFoundRouteException
     */
    protected function redirectToRoute(string $routeName, ?array $routeParams = null,bool $permanent = false): Response
    {
        return $this->redirect(Kernel::url($routeName,$routeParams),$permanent);
    }

    /**
     * Generate a redirection response
    * @param string $urlToRedirect The desired url to redirect
    * @param bool $permanent Is the redirection permanent ? (301 status if yes, 302 if not)
    * @return Response The empty body response with a Location header
     */
    protected function redirect(string $urlToRedirect,bool $permanent = false): Response
    {
        $response = new Response(status: $permanent ? HTTPStatus::HTTP_MOVED_PERMANENTLY : HTTPStatus::HTTP_FOUND);
        $response->headers->set('Location', $urlToRedirect);
        return $response;
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