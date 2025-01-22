<?php

/**
 * Autoload function for automatically including class files based on their namespace.
 *
 * This function registers an autoload function with PHP's SPL autoloading mechanism.
 * When a class is instantiated, PHP will call this function to include the corresponding
 * file based on the class's fully qualified name (namespace).
 */
spl_autoload_register(function (string $class): void {
    // Convert the namespace to a file path
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    // Check if the file exists and include it
    if (file_exists($file)) {
        require_once $file;
    }
});
