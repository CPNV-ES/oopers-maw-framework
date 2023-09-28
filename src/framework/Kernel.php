<?php

namespace MVC;

use MVC\Filesystem\ClassFinder;
use MVC\Http\Exception\HttpException;
use MVC\Http\Routing\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Router;
use Symfony\Component\Dotenv\Dotenv;

/**
 * The kernel is the application's entry point. Kernel initialization defines the application's basic tools. Its role is to read environment variables using the [symfony/dotenv](https://symfony.com/components/Dotenv).
 */
// TODO: Implement minimal configuration verification
// TODO: Implement customized HTTP Error
class Kernel
{
    public function __construct(string $envPath)
    {
        try {
			$dotenv = new Dotenv();
			$dotenv->load($envPath);

			$this->loadControllers();

			$this
				->registerRoutes()
				->registerErrors()
				->listen()
			;
        }
		catch (HttpException $exception) {
			$exception->getResponse()->executeAndDie();
		}
        catch (\Throwable $error) {
        	(new Http\Exception\InternalServerErrorException)->getResponse()->executeAndDie();
        }
    }

    private function registerRoutes(): self
    {
		Router::getInstance()->compileRoutes();
        return $this;
    }

	/**
	 * @throws BadRouteDeclarationException
	 */
	private function registerErrors(): self
    {
		if(!file_exists(self::projectDir().'/config/errors.php')) throw new BadRouteDeclarationException("Error routes declaration file not found.");
        $errors = require self::projectDir().'/config/errors.php';
        Router::getInstance()->errors($errors);
        return $this;
    }

	/**
	 * @throws \ReflectionException
	 */
	private function listen(): void
    {
        $response = Router::getInstance()->run();
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
	private function loadControllers(): void
	{
		array_map(fn($item) => class_exists($item), ClassFinder::getClassesInNamespace("App\\Controller"));
	}

}
