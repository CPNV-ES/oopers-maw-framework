<?php declare(strict_types=1);
namespace Tests;

use MVC\Http\HTTPMethod;
use MVC\Http\HTTPStatus;
use MVC\Http\Request;
use MVC\Http\Routing\Route;
use MVC\Http\Routing\Router;
use MVC\View\ViewException;
use PHPUnit\Framework\TestCase;
use TypeError;

final class RouterTest extends TestCase
{
    public function testRouteNotFound(): void
    {
        //given
        $request = new Request("/");
        $request->setMethod(HTTPMethod::GET);
        $router = new Router($request);

        //when
        $response = $router->run();

        //then
        self::assertEquals(HTTPStatus::NOT_FOUND,$response->status);
    }
    public function testNotValidMethodInController(){
        //given
        $request = new Request("/");
        $request->setMethod(HTTPMethod::GET);
        $router = new Router($request);
        $routes = $router->getRoutes();
        $routes->add(new Route("/",TestController::class,"notValidMethod"));

        //when + then
        $this->expectException(TypeError::class);
        $router->run();
    }
    public function testRouteFound(){
        //given
        $request = new Request("/");
        $request->setMethod(HTTPMethod::GET);
        $router = new Router($request);
        $routes = $router->getRoutes();
        $routes->add(new Route("/",TestController::class,"simpleResponse"));

        //when
        $response = $router->run();

        //then
        self::assertEquals(HTTPStatus::OK,$response->status);
        self::assertEquals("OK",$response->content);
    }
    public function testParameterRoute(){
        //given
        $request = new Request("/users/1");
        $request->setMethod(HTTPMethod::GET);
        $router = new Router($request);
        $routes = $router->getRoutes();
        $routes->add(new Route("/users/[:value]",TestController::class,"simpleResponseWithParameter"));

        //when
        $response = $router->run();

        //then
        self::assertEquals(HTTPStatus::OK,$response->status);
        self::assertEquals("1",$response->content);
    }

}