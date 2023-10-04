<?php

namespace MVC;

use MVC\Filesystem\ClassFinder;
use MVC\Http\Exception\HttpException;
use MVC\Http\Routing\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Exception\MissingRouteParamsException;
use MVC\Http\Routing\Exception\NotFoundRouteException;
use MVC\Http\Routing\Route;
use MVC\Http\Routing\Router;
use Symfony\Component\Dotenv\Dotenv;

/**
 * The kernel is the application's entry point. Kernel initialization defines the application's basic tools. Its role is to read environment variables using the [symfony/dotenv](https://symfony.com/components/Dotenv).
 */
// TODO: Implement minimal configuration verification
// TODO: Implement customized HTTP Error
class Kernel
{
	use Singleton;

	private Router $router;

    public function __construct(string $envPath)
    {
		$dotenv = new Dotenv();
		$dotenv->load($envPath);

		if(!isset($_ENV['APP_ENV'])) $_ENV['APP_ENV'] = "PROD";
		if(!in_array($_ENV['APP_ENV'], ['PROD', 'DEV'])) $_ENV['APP_ENV'] = "PROD";

		try {
			$this
				->init()
				->loadControllers()
				->registerRoutes()
				->registerErrors()
				->listen()
			;
        }
		catch (HttpException $exception) {
			$exception->getResponse()->execute();
		}
        catch (\Throwable $error) {
			if($_ENV['APP_ENV'] === 'DEV') dd($error);
        	(new Http\Exception\InternalServerErrorException)->getResponse()->execute();
        }
    }

	private function init(): self
	{
		$this->router = new Router();
		return $this;
	}

    private function registerRoutes(): self
    {
		$this->router->compileRoutes();
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
		if (is_null($route)) throw new NotFoundRouteException("Route named {$routeName} doesn't exist !");
		return $route->buildUrl($params);
	}

	/**
	 * @throws \ReflectionException
	 */
	private function registerErrors(): self
    {
		$this->router->compileErrorRoutes();
		return $this;
    }

	/**
	 * @throws \ReflectionException
	 */
	private function listen(): void
    {
        $response = $this->router->run();
        $response->execute();
    }

    public static function projectDir(): string
    {
        return dirname($_SERVER['DOCUMENT_ROOT']);
    }

	public static function kernelVarsToString(string $string): string
	{
		return str_replace(['%kernel.project_dir%'], [self::projectDir()], $string);
	}

	/**
	 * This method load all Controller to make them appears in get_declared_classes()
	 */
	private function loadControllers(): self
	{
		array_map(fn($item) => class_exists($item), ClassFinder::getClassesInNamespace("App\\Controller"));
		return $this;
	}

}
