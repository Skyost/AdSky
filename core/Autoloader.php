<?php

namespace AdSky\Core;

class Autoloader {

    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload($class) {
        $namespace = explode('\\', $class);
        $namespace = array_map('strtolower', $namespace);

        $index = count($namespace) - 1;

        $namespace[$index] = ucfirst($namespace[$index]);
        $class = implode(DIRECTORY_SEPARATOR, $namespace);

        require_once __DIR__ . '/' . str_replace(strtolower(__NAMESPACE__), '', $class) . '.php';
    }

}