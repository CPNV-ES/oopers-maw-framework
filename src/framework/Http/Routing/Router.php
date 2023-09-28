<?php

namespace MVC\Http\Routing;

use Doctrine\Common\Collections\ArrayCollection;
use MVC\Http\Controller\Controller;
use MVC\Http\Controller\ControllerInterface;
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

/**
 * When your application receives a request, it calls a controller action to generate the response. The router contains which action to run for each incoming URL. It also provides other useful features, like generating SEO-friendly URLs (e.g. /user/dimitri-rutz instead of index.php?user_id=58).
 */
class Router
{

	use Singleton;

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

    public function __construct(
        ?Request $currentRequest = null
    ) {
        $this->currentRequest = is_null($currentRequest) ? Request::createFromCurrent() : $currentRequest;
        $this->routes = new ArrayCollection();
        $this->namedRoutes = new ArrayCollection();
    }


    /**
	 * Create route from direct call
     * @throws BadRouteDeclarationException
	 * @deprecated Due to Attribute route declaration
     */
    public function add(string $url, array $controller, ?string $name = null, array $methods = [HTTPMethod::GET]): Route
    {
        $route = new Route($url, $controller[0], $controller[1], $methods, $name);
        $this->routes[] = $route;
        if(!is_null($route->name)) $this->namedRoutes[$route->name] = $route;
        return $route;
    }

	/**
	 * When method called it will parse each Controller using Reflection PHP API and extract from class and method of controller Route using Route attribute
	 * @return $this To make it fluent
	 * @throws BadRouteDeclarationException Threw when route is not declared properly.
	 * @throws \ReflectionException
	 */
	public function compileRoutes(): self
	{
		$controllerDeclaredRoutes = array_reduce(get_declared_classes(), function ($past, $currentClass) {
			if (!is_subclass_of($currentClass, Controller::class)) return $past;

			$class = new \ReflectionClass($currentClass);
			$parentRoute = $class->getAttributes(Annotation\Route::class)[0] ?? null;
			$routes = array_reduce($class->getMethods(), function ($p, $c) use ($parentRoute) {
				if (!empty($c->getAttributes(Annotation\Route::class))) {
					$attr = $c->getAttributes(Annotation\Route::class)[0];
					if($parentRoute){
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

					if ($attr->getArguments()['name']) $this->namedRoutes->set($out->name, $out);

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
	 * Declare errors from __
	 * @param array $errors
	 * @return $this
	 */
    public function errors(array $errors): self
    {
		if(empty($errors)) return $this;
        foreach ($errors as $error) {
            $this->errors[$error[0] instanceof HTTPStatus ? $error[0]->value : $error[0]] = new ErrorRoute($error[0], $error[1][0], $error[1][1]);
        }

        return $this;
    }


	/**
	 * Generate URL from route name including optional parameters
	 * @throws MissingRouteParamsException
	 * @throws NotFoundRouteException
	 */
	public static function url(string $routeName, ?array $params = null): string
	{
		/** @var Route|null $route */
		$route = Router::getInstance()->namedRoutes->get($routeName);
		if (is_null($route)) throw new NotFoundRouteException("Route named {$routeName} doesn't exist !");
		return $route->buildUrl($params);
	}


    /**
	 * Call findMatchingRoute method with current request and if an HttpException is threw it catch and return Response related to exception
     * @return Response
	 * @throws \ReflectionException
	 */
    public function run(): Response
    {
        try {
            $this->findMatchingRoute($this->currentRequest);
        } catch (HttpException $e) {
            return $this->route($this->errors[$e::STATUS->value]);
        }
        return $this->route($this->currentRequest->matchedRoute);
    }

    /**
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    private function findMatchingRoute(Request $request): void
    {
        foreach ($this->routes as $route) {
            $match = preg_match_all($route->getRegExp(), $request->uri, $matches);
            if($match === 0) continue;

            if(!$route->isValidMethod($request->method)) {
                throw new MethodNotAllowedException("Method {$request->method->value} not allowed.");
            }

            /** @var RouteParam $attr */
            foreach ($route->parameters as $attr) {
                $attr->value = $matches[$attr->name][0];
                $request->addParam($attr);
            }
            $this->currentRequest->matchedRoute = $route;
            return;
        }
        throw new NotFoundException();
    }

    /**
     * @param Route|ErrorRoute $route
     * @return Response
	 * @throws \ReflectionException
	 */
	// TODO: Find new name
    private function route(Route|ErrorRoute $route): Response
    {
		$controllerMethod = $route->controllerMethod;

		$paramConverter = new ParamConverter($route->controller, $route->controllerMethod);
		$params = $paramConverter->getParams($this->currentRequest);

		/** @var ControllerInterface $controller */
		$controller = new $route->controller($this->currentRequest);

		return $controller->$controllerMethod(...$params);
    }



}
