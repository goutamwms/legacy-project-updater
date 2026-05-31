<?php

declare(strict_types=1);

/**
 * HTTP handler for bike endpoints.
 *
 * URL: /v1/handlers/bike?action=…
 *
 * Actions (via ?action=…):
 *   list     → GET ?action=list&type=beach        generic type lookup
 *   beach    → GET ?action=beach                  shortcut for beach cruisers
 *   mountain → GET ?action=mountain               shortcut for mountain bikes
 *   electric → GET ?action=electric               shortcut for electric bikes
 *   rent     → POST ?action=rent {bikeType, bikeId}
 *   reset    → POST ?action=reset                 restore all defaults
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

$action = isset($_GET['action']) && is_string($_GET['action']) ? $_GET['action'] : '';

$registry = ApplicationServices::getBikeRegistry();
$accessoryService = ApplicationServices::getAccessoryService();

if ($registry === null || $accessoryService === null) {
    http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
    echo json_encode(['Success' => false, 'Message' => 'Services not initialized.']);
    exit;
}

switch ($action) {

    case 'list':
        $type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : '';
        $service = $type !== '' ? $registry->get($type) : null;
        if ($service === null) {
            http_response_code(HttpStatus::BAD_REQUEST);
            echo json_encode(['Success' => false, 'Message' => 'Unknown bike type: ' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8')]);

            break;
        }
        echo json_encode($service->getAll());

        break;

    case 'beach':
        echo json_encode($registry->get('beach')?->getAll() ?? []);

        break;

    case 'mountain':
        echo json_encode($registry->get('mountain')?->getAll() ?? []);

        break;

    case 'electric':
        echo json_encode($registry->get('electric')?->getAll() ?? []);

        break;

    case 'rent':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
            echo json_encode(['Success' => false, 'Message' => 'Method not allowed. POST only.']);

            break;
        }

        $body = file_get_contents('php://input');
        if ($body === false) {
            http_response_code(HttpStatus::BAD_REQUEST);
            echo json_encode(['Success' => false, 'Message' => 'Unable to read request body.']);

            break;
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            http_response_code(HttpStatus::BAD_REQUEST);
            echo json_encode(['Success' => false, 'Message' => 'Invalid JSON in request body.']);

            break;
        }

        if (!is_array($data)) {
            http_response_code(HttpStatus::BAD_REQUEST);
            echo json_encode(['Success' => false, 'Message' => 'Invalid request body. Expected a JSON object.']);

            break;
        }

        $bikeType = isset($data['bikeType']) && is_string($data['bikeType']) ? $data['bikeType'] : '';
        $bikeId   = isset($data['bikeId'])   && is_numeric($data['bikeId']) ? (int)$data['bikeId'] : 0;

        $service = $bikeType !== '' ? $registry->get($bikeType) : null;
        if ($service === null) {
            http_response_code(HttpStatus::BAD_REQUEST);
            echo json_encode(['Success' => false, 'Message' => 'Unknown bikeType. Expected "beach" or "mountain".']);

            break;
        }

        $success = $service->rentBike($bikeId);
        if ($success) {
            echo json_encode(['Success' => true, 'Message' => 'Bike rented successfully.']);
        } else {
            echo json_encode(['Success' => false, 'Message' => 'Bike is not available or does not exist.']);
        }

        break;

    case 'reset':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
            echo json_encode(['Success' => false, 'Message' => 'Method not allowed.']);

            break;
        }

        foreach ($registry->getAll() as $service) {
            $service->resetToDefaults();
        }
        $accessoryService->resetToDefaults();

        echo json_encode(['Success' => true, 'Message' => 'All data reset to defaults.']);

        break;

    default:
        http_response_code(HttpStatus::BAD_REQUEST);
        echo json_encode(['Success' => false, 'Message' => 'Unknown action: ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8')]);

        break;
}
