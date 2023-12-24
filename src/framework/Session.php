<?php

namespace MVC;

use Doctrine\Common\Collections\ArrayCollection;

class Session extends ArrayCollection
{

    public function __construct()
    {
        $this->start();
        parent::__construct($_SESSION);
    }

    public function start(): static
    {
        session_start(['name' => $_ENV['APP_NAME'] ?? "sid"]);
        return $this;
    }

    public function remove(int|string $key)
    {
        if ($this->containsKey($key)) {
            unset($_SESSION[$key]);
        }
        return parent::remove($key);
    }

    public function set(int|string $key, mixed $value)
    {
        $_SESSION[$key] = $value;
        parent::set($key, $value);
    }

    public function add(mixed $element)
    {
        $_SESSION[] = $element;
        parent::add($element);
    }

}