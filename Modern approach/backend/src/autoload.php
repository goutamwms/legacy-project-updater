<?php

declare(strict_types=1);

/**
 * Simple PSR-4 autoloader for the PedalPal namespace.
 * Replace with Composer's vendor/autoload.php when available.
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'PedalPal\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
