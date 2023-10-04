<?php

namespace MVC\Http\Routing;

use Doctrine\Common\Collections\ArrayCollection;
use MVC\Http\Controller\Controller;
use MVC\Http\Exception\HttpException;
use MVC\Http\Exception\MethodNotAllowedException;
use MVC\Http\Exception\NotFoundException;
use MVC\Http\HTTPMethod;
use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Http\Response\Response;
use MVC\Http\Routing\Annotation as Annotation;
use MVC\Http\Routing\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Exception\NotFoundRouteException;
use MVC\Http\Routing\Exception\MissingRouteParamsException;
use MVC\Singleton;
use ReflectionClass;
use ReflectionException;

/**
 * When your application receives a request, it calls a controller action to generate the response. The router contains which action to run for each incoming URL. It also provides other useful features, like generating SEO-friendly URLs (e.g. /user/dimitri-rutz instead of index.php?user_id=58).
 */
class Router
{

	/**
	 * @var ArrayCollection|Route[]
	 */
	private ArrayCollection|array $routes;

	/**
	 * @var ArrayCollection|ErrorRoute[]
	 */
	private ArrayCollection|array $errors;

	/**
	 * @var ArrayCollection|Route[]
	 */
	private ArrayCollection|array $namedRoutes;

	private Request $currentRequest;

	private ?Route $matchedRoute = null;

	public function __construct(
		?Request $currentRequest = null
	)
	{
		$this->currentRequest = is_null($currentRequest) ? Request::createFromCurrent() : $currentRequest;
		$this->errors = new ArrayCollection();
		$this->routes = new ArrayCollection();
		$this->namedRoutes = new ArrayCollection();
	}

	/**
	 * Generate URL from route name including optional parameters
	 * @throws MissingRouteParamsException
	 * @throws NotFoundRouteException
	 * @deprecated You MUST use call URL generation from Kernel
	 */
	public static function url(string $routeName, ?array $params = null): string
	{
		/** @var Route|null $route */
		$route = Router::getInstance()->namedRoutes->get($routeName);
		if (is_null($route)) throw new NotFoundRouteException("Route named {$routeName} doesn't exist !");
		return $route->buildUrl($params);
	}

	/**
	 * When method called it will parse each Controller using Reflection PHP API and extract from class and method of controller Route using Route attribute
	 * @return $this To make it fluent
	 * @throws BadRouteDeclarationException Threw when route is not declared properly.
	 * @throws ReflectionException
	 */
	public function compileRoutes(): self
	{
		$controllerDeclaredRoutes = array_reduce(get_declared_classes(), function ($past, $currentClass) {
			if (!is_subclass_of($currentClass, Controller::class)) return $past;

			$class = new ReflectionClass($currentClass);
			$parentRoute = $class->getAttributes(Annotation\Route::class)[0] ?? null;
			$routes = array_reduce($class->getMethods(), function ($p, $c) use ($parentRoute) {
				if (!empty($c->getAttributes(Annotation\Route::class))) {
					$attr = $c->getAttributes(Annotation\Route::class)[0];
					if ($parentRoute) {
						$out = new Route(
							$parentRoute->getArguments()[0] . $attr->getArguments()[0],
							$c->class,
							$c->getName(),
							$attr->getArguments()['methods'] ?? [HTTPMethod::GET],
							isset($attr->getArguments()['name']) ? isset($parentRoute->getArguments()['name']) ? $parentRoute->getArguments()['name'] . $attr->getArguments()['name'] : $attr->getArguments()['name'] : null
						);
					} else {
						$out = new Route(
							$attr->getArguments()[0],
							$c->class,
							$c->getName(),
							$attr->getArguments()['methods'] ?? [HTTPMethod::GET],
							$attr->getArguments()['name'] ?? null
						);
					}

					if ($attr->getArguments()['name']) $this->namedRoutes->set($out->getName(), $out);

					$p[] = $out;
				}
				return $p;
			}, []);
			return [...$past, ...$routes];
		}, []);

		$this->routes = $controllerDeclaredRoutes;
		return $this;
	}

	/**
	 * When method called it will parse each Controller using Reflection PHP API and extract from method of controller ErrorRoute using ErrorRoute attribute
	 * @return $this To make it fluent
	 * @throws ReflectionException
	 */
	public function compileErrorRoutes(): self
	{
		$controllerDeclaredRoutes = array_reduce(get_declared_classes(), function ($past, $currentClass) {
			if (!is_subclass_of($currentClass, Controller::class)) return $past;

			$class = new ReflectionClass($currentClass);
			$routes = array_reduce($class->getMethods(), function ($p, $c) {
				/** @var \ReflectionMethod $c */
				if (!empty($c->getAttributes(Annotation\ErrorRoute::class))) {
					/** @var Annotation\ErrorRoute $attr */
					$attr = $c->getAttributes(Annotation\ErrorRoute::class)[0]->newInstance();

					$out = new ErrorRoute(
						$attr->status,
						$c->class,
						$c->getName(),
					);

					$p[$out->getStatus()->name] = $out;
				}
				return $p;
			}, []);
			return [...$past, ...$routes];
		}, []);

		$this->errors = new ArrayCollection($controllerDeclaredRoutes);
		return $this;
	}

	/**
	 * Create routes from an array of array declaration method
	 * @throws BadRouteDeclarationException
	 * @deprecated Due to Attribute route declaration
	 */
	public function routes(array $routes): self
	{
		array_map(function ($route) {
			$this->add($route[0], $route[1], $route[2] ?? null, $route[3] ?? [HTTPMethod::GET]);
		}, $routes);

		return $this;
	}

	/**
	 * Create route from direct call
	 * @throws BadRouteDeclarationException
	 * @throws ReflectionException
	 * @deprecated Due to Attribute route declaration
	 */
	public function add(string $url, array $controller, ?string $name = null, array $methods = [HTTPMethod::GET]): Route
	{
		$route = new Route($url, $controller[0], $controller[1], $methods, $name);
		$this->routes[] = $route;
		if (!is_null($route->getName())) $this->namedRoutes[$route->getName()] = $route;
		return $route;
	}

	/**
	 * Declare errors from __
	 * @param array $errors
	 * @return $this
	 * @deprecated Due to Attribute route declaration
	 */
	public function errors(array $errors): self
	{
		if (empty($errors)) return $this;
		foreach ($errors as $error) {
			$this->errors[$error[0] instanceof HTTPStatus ? $error[0]->value : $error[0]] = new ErrorRoute($error[0], $error[1][0], $error[1][1]);
		}

		return $this;
	}

	/**
	 * Call findMatchingRoute method with current request and if an HttpException is threw it catch and return Response related to exception
	 * @return Response
	 * @throws ReflectionException
	 */
	public function run(): Response
	{
		try {
			$this->setMatchingRoute($this->currentRequest);
		} catch (HttpException $e) {
			if($this->errors->containsKey($e::STATUS->name)){
				return $this->callRouteAction($this->errors->get($e::STATUS->name));
			} else {
				return $e->getResponse();
			}
		}
		return $this->callRouteAction($this->matchedRoute);
	}

	/**
	 * @throws NotFoundException
	 * @throws MethodNotAllowedException
	 */
	private function setMatchingRoute(Request $request): void
	{
		foreach ($this->routes as $route) {
			$match = preg_match_all($route->getPattern(), $request->uri, $matches);
			if ($match === 0) continue;

			if (!$route->isValidMethod($request->method)) {
				throw new MethodNotAllowedException("Method {$request->method->value} not allowed.");
			}

			/** @var RouteParam $attr */
			foreach ($route->getParameters() as $attr) {
				$attr->value = $matches[$attr->name][0];
				$request->addParam($attr);
			}
			$this->matchedRoute = $route;
			return;
		}
		throw new NotFoundException();
	}

	/**
	 * @param AbstractRoute $route
	 * @return Response
	 * @throws ReflectionException
	 */
	private function callRouteAction(AbstractRoute $route): Response
	{
		$controllerMethod = $route->getControllerMethod();
		$controllerName = $route->getController();

		$paramConverter = new ParamConverter($route);
		$params = $paramConverter->getParams($this->currentRequest);

		/** @var Controller $controller */
		$controller = new $controllerName($this->currentRequest);

		return $controller->$controllerMethod(...$params);
	}

	/**
	 * @return array|ArrayCollection
	 */
	public function getRoutes(): ArrayCollection|array
	{
		return $this->routes;
	}

	/**
	 * @return array|ArrayCollection
	 */
	public function getErrors(): ArrayCollection|array
	{
		return $this->errors;
	}

	/**
	 * @return array|ArrayCollection
	 */
	public function getNamedRoutes(): ArrayCollection|array
	{
		return $this->namedRoutes;
	}

	/**
	 * @return Request
	 */
	public function getCurrentRequest(): Request
	{
		return $this->currentRequest;
	}

	/**
	 * @return Route|null
	 */
	public function getMatchedRoute(): ?Route
	{
		return $this->matchedRoute;
	}


}