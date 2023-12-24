<?php

namespace MVC\Security;

use MVC\Session;

class CsrfManager
{

    public function __construct(
        private Session $session
    ) {
    }

    public function generate(string $key = '_token'): string
    {
        $token = crypt(md5($key), $key);
        $this->session->set($key, $token);
        return $token;
    }

}