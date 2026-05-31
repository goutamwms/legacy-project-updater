<?php

declare(strict_types=1);

require_once __DIR__ . '/src/HttpStatus.php';

/**
 * Router script for PHP's built-in development server.
 *
 * Strips the version prefix from API routes so that
 *   /v1/handlers/bike  →  /handlers/bike.php
 *
 * Supports both extensionless clean URLs and legacy .php URLs.
 *
 * Usage: php -S localhost:8080 router.php
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

if ($path === null || preg_match('#^/v\d+/#', $path) !== 1 || !str_starts_with($path, '/v1')) {
    return false;
}

$newPath = preg_replace('#^/v\d+/#', '/', $path);

$qs = $_SERVER['QUERY_STRING'] ?? '';
$_SERVER['REQUEST_URI']  = $newPath . ($qs !== '' ? '?' . $qs : '');
$_SERVER['SCRIPT_NAME']  = $newPath;
$_SERVER['PHP_SELF']     = $newPath;

// PHP files: require them directly so the script runs with updated $_SERVER vars
$file = $_SERVER['DOCUMENT_ROOT'] . (
    str_ends_with($newPath, '.php')
        ? $newPath
        : $newPath . '.php'
);
if (file_exists($file)) {
    require $file;

    return true;
}

http_response_code(HttpStatus::NOT_FOUND);
echo json_encode(['Success' => false, 'Message' => 'File not found.']);

return true;
