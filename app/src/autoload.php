<?php

declare(strict_types=1);

/**
 * PSR-4 Autoloader
 * 
 * Maps class names to file paths following PSR-4 standard
 */

spl_autoload_register(function (string $class): void {
    // Project namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $baseDir = __DIR__ . '/../src/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relativeClass = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
