<?php

namespace MVC\Http\Routing;

use ReflectionParameter;

/**
 * Used to define a Route regexp and eventual parameters
 */
class RouteCompiler
{

    private array $matchTypes = [
        'i' => '[0-9]++',
        'a' => '[0-9A-Za-z]++',
        'h' => '[0-9A-Fa-f]++',
        '*' => '.+?',
        '**' => '.++',
        '' => '[^/\.]++'
    ];

    private array $parsed;


    public function __construct(
        private readonly string $route
    ) {
        $this->parsed = $this->parseTemplateUrl($route);
    }


    /**
     * @param string $route
     * @return array
     */
    private function parseTemplateUrl(string $route): array
    {
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            return $matches;
        }
        return [];
    }


    /**
     * Use action controller method parameters to define types of parameters passed in url
     * @param ReflectionParameter[] $methodParameters
     * @return RouteParam[]
     */
    public function extractRouteParameters(array $methodParameters): array
    {
        $out = [];
        foreach ($this->parsed as $parameter) {
            $parameter = $parameter[3];
            $type = null;
            foreach ($methodParameters as $param) {
                if ($parameter === $param->getName()) {
                    if ($param->hasType()) {
                        $type = $param->getType()->getName();
                    }
                }
            }
            $out[] = new RouteParam($parameter, $type);
        }

        return $out;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        $routeUrl = $this->route;
        foreach ($this->parsed as $match) {
            [$block, $pre, $type, $param, $optional] = $match;
            if (isset($this->matchTypes[$type])) {
                $type = $this->matchTypes[$type];
            }
            if ($pre === '.') {
                $pre = '\.';
            }
            $optional = $optional !== '' ? '?' : null;

            $pattern = '(?:'
                . ($pre !== '' ? $pre : null)
                . '('
                . ($param !== '' ? "?P<$param>" : null)
                . $type
                . ')'
                . $optional
                . ')'
                . $optional;

            $routeUrl = str_replace($block, $pattern, $routeUrl);
        }
        return "`^$routeUrl$`u";
    }


}