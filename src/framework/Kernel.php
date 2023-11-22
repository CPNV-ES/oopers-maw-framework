<?php

namespace MVC;

use MVC\Filesystem\ClassFinder;
use MVC\Http\Exception\HttpException;
use MVC\Http\Exception\InternalServerErrorException;
use MVC\Http\Routing\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Exception\MissingRouteParamsException;
use MVC\Http\Routing\Exception\NotFoundRouteException;
use MVC\Http\Routing\Route;
use MVC\Http\Routing\Router;
use MVC\Service\ContainerService;
use Symfony\Component\Dotenv\Dotenv;

/**
 * The kernel is the application's entry point. Kernel initialization defines the application's basic tools. Its role is to read environment variables using the [symfony/dotenv](https://symfony.com/components/Dotenv).
 */
// TODO: Implement minimal configuration verification
// TODO: Implement customized HTTP Error
class Kernel
{

    protected static $_instance;
    public Container $container;
    private Router $router;

    public function __construct(string $envPath)
    {
        self::$_instance = $this;

        $dotenv = new Dotenv();
        $dotenv->load($envPath);

        if (!isset($_ENV['APP_ENV'])) {
            $_ENV['APP_ENV'] = "PROD";
        }
        if (!in_array($_ENV['APP_ENV'], ['PROD', 'DEV'])) {
            $_ENV['APP_ENV'] = "PROD";
        }

        $this
            ->init()
            ->loadControllers();
    }

    /**
     * This method load all Controller to make them appears in get_declared_classes()
     */
    private function loadControllers(): self
    {
        array_map(fn($item) => class_exists($item), ClassFinder::getClassesInNamespace("App\\Controller"));
        return $this;
    }

    private function init(): self
    {
        $this->container = new Container();
        ContainerService::containerInit($this->container);
        $this->container->setInstance(__CLASS__, $this);
        $this->router = new Router();
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
        $route = self::getInstance()->router->getNamedRoutes()->get($routeName);
        if (is_null($route)) {
            throw new NotFoundRouteException("Route named {$routeName} doesn't exist !");
        }
        return $route->buildUrl($params);
    }

    public static function getInstance()
    {
        return self::$_instance;
    }

    public static function kernelVarsToString(string $string): string
    {
        return str_replace(['%kernel.project_dir%'], [self::projectDir()], $string);
    }

    public static function projectDir(): string
    {
        return dirname($_SERVER['DOCUMENT_ROOT']);
    }

    /**
     * Execute the best route found by the router
     */
    public function executeRoute(): void
    {
        try {
            $this->router->compileRoutes();
            $this->router->compileErrorRoutes();
            $response = $this->router->run();
            $response->execute();
        } catch (HttpException $exception) {
            $exception->getResponse()->execute();
        } catch (\Throwable $error) {
            if ($_ENV['APP_ENV'] === 'DEV') {
                dd($error);
            }
            (new InternalServerErrorException())->getResponse()->execute();
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function listen(): void
    {
        $response = $this->router->run();
        $response->execute();
    }

    /**
     * @throws \ReflectionException
     */
    private function registerErrors(): self
    {
        $this->router->compileErrorRoutes();
        return $this;
    }

    private function registerRoutes(): self
    {
        $this->router->compileRoutes();
        return $this;
    }

}
