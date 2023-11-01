<?php declare(strict_types=1);

use MVC\Http\HTTPMethod;
use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Http\Routing\Route;
use MVC\Http\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testRouteNotFound(): void
    {
        $request = new Request("/");
        $request->setMethod(HTTPMethod::GET);
        $router = new Router($request);

        $response = $router->run();
        self::assertEquals(HTTPStatus::NOT_FOUND,$response->status);
    }
}