<?php

namespace MVC\Http\Routing\Annotation;

use Attribute;
use MVC\Http\HTTPStatus;

/**
 * Attribute used to declare routes in controller read with Reflection API
 */
#[Attribute(flags: Attribute::TARGET_METHOD)]
class ErrorRoute
{

    public function __construct(
        public HTTPStatus $status,
    ) {
    }

}