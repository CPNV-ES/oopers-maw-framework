<?php

namespace MVC\Http\Exception;

use MVC\Http\Response\Response;

interface HttpExceptionInterface
{

    public function getResponse(): Response;

}