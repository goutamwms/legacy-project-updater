<?php

declare(strict_types=1);

/**
 * HTTP handler that exposes the current API build version.
 *
 * GET  /v1/handlers/version  →  {"version":"1.0.0","api_version":"v1"}
 *
 * Always returns JSON. Sets no-cache headers.
 */

require_once __DIR__ . '/../src/autoload.php';

$versionFile = __DIR__ . '/../VERSION';
$version = file_exists($versionFile) ? trim((string) file_get_contents($versionFile)) : '0.0.0';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo json_encode([
    'version'     => $version,
    'api_version' => 'v1',
    'name'        => 'PedalPal API',
]);
