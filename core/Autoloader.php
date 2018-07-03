<?php

namespace AdSky\Core;

/**
 * Autoloader class.
 */

class Autoloader {

    /**
     * Registers the autoloader.
     */

    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Allows PHP to loads a class.
     *
     * @param string $class The class.
     */

    public static function autoload($class) {
        $namespace = explode('\\', $class);
        $class = end($namespace);
        $namespace = array_map('strtolower', $namespace);

        $namespace[count($namespace) - 1] = $class;
        $class = implode('\\', $namespace);

        require_once __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, substr(str_replace(strtolower(__NAMESPACE__), '', $class), 1)) . '.php';
    }

}