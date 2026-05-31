<?php

declare(strict_types=1);

/**
 * HTTP handler for accessory endpoints.
 *
 * URL: /v1/handlers/accessory
 *
 * GET  ?bikeType=beach  → compatible accessories
 * POST                  → submit an order
 *
 * Always returns JSON. Sets no-cache headers.
 */

require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/HttpStatus.php';
require_once __DIR__ . '/../services/ApplicationServices.php';

$dataFolder = __DIR__ . '/../SampleData';
ApplicationServices::initialize($dataFolder);

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$method = $_SERVER['REQUEST_METHOD'];

$accessoryService = ApplicationServices::getAccessoryService();
if ($accessoryService === null) {
    http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
    echo json_encode(['Success' => false, 'Message' => 'Services not initialized.']);
    exit;
}

if ($method === 'GET') {
    $bikeType = isset($_GET['bikeType']) && is_string($_GET['bikeType']) ? $_GET['bikeType'] : '';
    if ($bikeType !== '') {
        $accessories = $accessoryService->getCompatibleWith($bikeType);
    } else {
        $accessories = $accessoryService->getAll();
    }

    $result = [];
    foreach ($accessories as $acc) {
        /** @var array{AccessoryID: int, Name: string, Category: string, Description: string, UnitPrice: float, StockCount: int, CompatibleWith: list<string>} $acc */
        $result[] = [
            'AccessoryID'    => $acc['AccessoryID'],
            'Name'           => $acc['Name'],
            'Category'       => $acc['Category'],
            'Description'    => $acc['Description'],
            'UnitPrice'      => $acc['UnitPrice'],
            'StockCount'     => $acc['StockCount'],
            'CompatibleWith' => $acc['CompatibleWith'],
        ];
    }

    echo json_encode($result);

} elseif ($method === 'POST') {
    $body = file_get_contents('php://input');
    if ($body === false) {
        http_response_code(HttpStatus::BAD_REQUEST);
        echo json_encode([
            'Success'              => false,
            'Message'              => 'Unable to read request body.',
            'TotalPrice'           => 0.0,
            'DiscountAmount'       => 0.0,
            'BundleDiscountApplied' => false,
        ]);
        exit;
    }

    try {
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        http_response_code(HttpStatus::BAD_REQUEST);
        echo json_encode([
            'Success'              => false,
            'Message'              => 'Invalid JSON body. Expected an array of {AccessoryID, Quantity} objects.',
            'TotalPrice'           => 0.0,
            'DiscountAmount'       => 0.0,
            'BundleDiscountApplied' => false,
        ]);
        exit;
    }

    if (!is_array($data)) {
        http_response_code(HttpStatus::BAD_REQUEST);
        echo json_encode([
            'Success'              => false,
            'Message'              => 'Invalid request body. Expected an array.',
            'TotalPrice'           => 0.0,
            'DiscountAmount'       => 0.0,
            'BundleDiscountApplied' => false,
        ]);
        exit;
    }

    /** @var list<array{AccessoryID: int, Quantity: int}> $data */
    $result = $accessoryService->processOrder($data);
    echo json_encode($result);

} else {
    http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
    echo json_encode([
        'Success' => false,
        'Message' => 'Method not allowed. GET to browse. POST to buy.',
    ]);
}
