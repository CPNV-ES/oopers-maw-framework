<?php

namespace MVC\Security;

use MVC\Session;

class CsrfGenerator
{

    public function __construct(
        private Session $session
    )
    {
    }

}