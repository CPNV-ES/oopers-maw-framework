<?php

namespace MVC\Http\Routing;

use Doctrine\Common\Collections\ArrayCollection;
use MVC\Http\Controller\AbstractController;
use MVC\Http\Exception\BadRouteDeclarationException;
use MVC\Http\Exception\HttpException;
use MVC\Http\Exception\MethodNotAllowedException;
use MVC\Http\Exception\NotFoundException;
use MVC\Http\HTTPMethod;
use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Http\Response\Response;

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

    public function __construct(
        ?Request $currentRequest = null
    ) {
        $this->currentRequest = is_null($currentRequest) ? Request::createFromCurrent() : $currentRequest;
        $this->routes = new ArrayCollection();
        $this->namedRoutes = new ArrayCollection();
    }


    /**
     * @throws BadRouteDeclarationException
     */
    public function add(string $url, array $controller, ?string $name = null, array $methods = [HTTPMethod::GET]): Route
    {
        $route = new Route($url, $controller, $methods, $name);
        $this->routes[] = $route;
        if(!is_null($route->name)) $this->namedRoutes[$route->name] = $route;

        return $route;
    }

    /**
     * @throws BadRouteDeclarationException
     */
    public function routes(array $routes): self
    {
		array_map(function ($route) {
			$this->add($route[0], $route[1], $route[2] ?? null, $route[3] ?? [HTTPMethod::GET]);
		}, $routes);

        return $this;
    }

    public function errors(array $errors): self
    {
		if(empty($errors)) return $this;
		dd($errors);
        foreach ($errors as $error) {
            $this->errors[$error[0] instanceof HTTPStatus ? $error[0]->value : $error[0]] = new ErrorRoute($error[0], $error[1]);
        }

        return $this;
    }


    /**
     * @return Response
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
        dd($this->routes);
        foreach ($this->routes as $route) {
            $match = preg_match_all($route->pattern, $request->uri, $matches);
            if($match === 0) {
                continue;
            }
            if(!$route->isValidMethod($request->method)) {
                throw new MethodNotAllowedException("Method {$request->method->value} not allowed.");
            }

            /** @var RouteParam $attr */
            foreach ($route->attributes as $attr) {
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
     */
    private function route(Route|ErrorRoute $route): Response
    {
        if(is_array($route->controller)) {
            $controllerMethod = $route->controller[1];

            /** @var AbstractController $controller */
            $controller = new $route->controller[0]($this->currentRequest);

            return $controller->$controllerMethod();
        } else {
            throw new \RuntimeException("Bad routing declaration.");
        }
    }



}
