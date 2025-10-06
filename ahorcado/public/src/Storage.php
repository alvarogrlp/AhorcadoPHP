<?php

namespace src;

class Storage
{
    private string $key;

    public function __construct(string $key = 'ahorcado')
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->key = $key;

        if (!isset($_SESSION[$this->key]) || !is_array($_SESSION[$this->key])) {
            $_SESSION[$this->key] = [];
        }
    }

    public function get(string $name, $default = null)
    {
        return $_SESSION[$this->key][$name] ?? $default;
    }

    public function set(string $name, $value): void
    {
        $_SESSION[$this->key][$name] = $value;
    }

    public function reset(): void
    {
        unset($_SESSION[$this->key]);
    }
}
