<?php

namespace MVC;

use http\Exception\RuntimeException;
use MVC\Filesystem\PathResolver;
use MVC\Http\Exception\BadRouteDeclarationException;
use MVC\Http\Routing\Router;
use Symfony\Component\Dotenv\Dotenv;

class Kernel
{

    private string $projectDir;

    private array $stringArguments;

    private array $minimalEnvConfig = [
        'VIEW_DIR_PATH',
    ];

    private array $minimalFileConfig = [
        '%kernel.project_dir%/config/',
        '%kernel.project_dir%/config/routes.php',
    ];

    private Router $router;

    public function __construct(string $envPath)
    {
        //try {
        $dotenv = new Dotenv();
        $dotenv->load($envPath);

        $this->stringArguments = [
            'kernel.project_dir' => self::projectDir(),
            'config.routes' => self::projectDir() . '/config/routes.php',
        ];

        $this
            ->registerRoutes()
            ->registerErrors()
            ->listen()
        ;
        //}
        //catch (\Throwable $error) {
        //	InternalServerErrorException::getResponse()->executeAndDie();
        //}
    }

    /**
     * @throws BadRouteDeclarationException
     */
    private function registerRoutes(): self
    {
        $this->router = new Router();
		if(!file_exists('../config/errors.php')) throw new RuntimeException("Error routes declaration file not found.");
        $routes = require '../config/routes.php';
        $this->router->routes($routes);
        return $this;
    }

    private function registerErrors(): self
    {
		if(!file_exists('../config/errors.php')) throw new RuntimeException("Error routes declaration file not found.");
        $errors = require '../config/errors.php';
        $this->router->errors($errors);
        return $this;
    }

    private function listen(): void
    {
        $response = $this->router->run();
        $response->execute();
    }

    private function verifyRequiredMinimalConfig()
    {

    }

    public static function projectDir(): string
    {
        return dirname($_SERVER['DOCUMENT_ROOT']);
    }

}
