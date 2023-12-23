<?php
namespace Tests;

use MVC\Http\Controller;
use MVC\Http\Response\Response;

class TestController extends Controller{
    public function notValidMethod(){

    }
    public function simpleResponse(){
        return new Response("OK");
    }
    public function simpleResponseWithParameter($value){
        return new Response($value);
    }
    public function renderViewRoute(){
        return $this->render("test");
    }
}