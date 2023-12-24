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
        $token = password_hash($key, PASSWORD_BCRYPT);
        $this->session->set($key, $token);
        return $token;
    }

    public function verify($key, $toVerify): bool
    {
        return hash_equals($this->session->get($key), $toVerify);
    }

}