<?php

namespace MVC\Http\Routing;

use MVC\Http\HTTPMethod;
use MVC\Http\Routing\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Exception\MissingRouteParamsException;
use ReflectionException;
use ReflectionMethod;

/**
 * A Route is a representation of route containing parameters see RouteParam RegExp pattern to match to URL
 * @property
 * @property HTTPMethod[] $acceptedMethods Array of method(s) can be used with route
 * @property ?string $name Name can be null if is set it can be used to identify route and to generate url
 */
class Route extends AbstractRoute
{

    /**
     * Parameters passed in URL
     * @see RouteParam
     * @var RouteParam[]
     */
    public array $parameters = [];

    /**
     * RegExp pattern
     * @var string|mixed
     */
    private string $pattern;

    /**
     * @var string $url URL template string
     */
    private string $url;

    private array $acceptedMethods = [];

    /**
     * @param string $url
     * @param class-string $controller
     * @param string $controllerMethod
     * @param array $acceptedMethods
     * @param string|null $name
     * @throws BadRouteDeclarationException
     * @throws ReflectionException
     */
    public function __construct(
        string $url,
        string $controller,
        string $controllerMethod,
        array $acceptedMethods = [HTTPMethod::GET],
        ?string $name = null
    ) {
        $this
            ->setName($name)
            ->setUrl($url)
            ->setAcceptedMethods($acceptedMethods)
            ->setController($controller)
            ->setControllerMethod($controllerMethod);

        if (!$this->validateController()) {
            throw new BadRouteDeclarationException(
                "Enable to declare route `{$this->url}` due to invalid Controller declaration."
            );
        }

        $compiler = new RouteCompiler($this->url);
        $this
            ->setPattern($compiler->getPattern())
            ->setParameters(
                $compiler->extractRouteParameters(
                    (new ReflectionMethod($this->controller, $this->controllerMethod))->getParameters()
                )
            );
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     * @return Route
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Route
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Generate URL with params
     * @param array|null $params
     * @return string
     * @throws MissingRouteParamsException
     */
    public function buildUrl(?array $params = null): string
    {
        $url = $this->url;

        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $this->url, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                [$block, $pre, $type, $param, $optional] = $match;

                if ($pre) {
                    $block = substr($block, 1);
                }

                if (isset($params[$param])) {
                    // Part is found, replace for param value
                    $url = str_replace($block, $params[$param], $url);
                } elseif ($optional && $index !== 0) {
                    // Only strip preceding slash if it's not at the base
                    $url = str_replace($pre . $block, '', $url);
                } else {
                    throw new MissingRouteParamsException("Mandatory '{$param}' URL parameter not provided !");
                }
            }
        }

        return $url;
    }

    /**
     * Verify if passed Method cas trigger current route
     * @param HTTPMethod $method
     * @return bool
     */
    public function isValidMethod(HTTPMethod $method): bool
    {
        return in_array($method, $this->acceptedMethods);
    }

    /**
     * @param RouteParam $routeParam
     * @return Route
     */
    public function addParameter(RouteParam $routeParam): self
    {
        $this->parameters[$routeParam->name] = $routeParam;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Route
     */
    public function setUrl(string $url): Route
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return HTTPMethod[]
     */
    public function getAcceptedMethods(): array
    {
        return $this->acceptedMethods;
    }

    /**
     * @param HTTPMethod[] $acceptedMethods
     * @return Route
     */
    public function setAcceptedMethods(array $acceptedMethods): Route
    {
        $this->acceptedMethods = $acceptedMethods;
        return $this;
    }


}