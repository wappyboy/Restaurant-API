<?php
// Include all necessary files
require_once __DIR__ . '/src/Helpers.php';
require_once __DIR__ . '/src/Database.php';          // <--- Add this
require_once __DIR__ . '/src/Models/Restaurant.php';
require_once __DIR__ . '/src/Models/MenuItem.php';
require_once __DIR__ . '/src/Controllers/RestaurantController.php';
require_once __DIR__ . '/src/Controllers/MenuController.php';

use Src\Helpers;
use Src\Controllers\RestaurantController;
use Src\Controllers\MenuController;

// Allow CORS for testing purposes (adjust for production)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Define data files
$restaurantsFile = __DIR__ . '/data/restaurants.json';
$menuFile = __DIR__ . '/data/menu.json';

// Initialize controllers
$restaurantController = new RestaurantController();
$menuController = new MenuController();

// Parse the URI and route requests accordingly
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriParts = explode('/', trim($uri, '/'));

// Basic route validation: Must start with /restaurants
if (count($uriParts) == 0 || $uriParts[0] !== 'restaurants') {
    Helpers::sendResponse(['message' => 'Endpoint not found'], 404);
}

$method = $_SERVER['REQUEST_METHOD'];
$restaurantId = isset($uriParts[1]) ? (int)$uriParts[1] : null;
$subResource = $uriParts[2] ?? null;
$queryParams = $_GET;

// Routing logic: Delegate to correct controller based on HTTP method and URI pattern
try {
    if ($method === 'GET') {
        if ($restaurantId === null) {
            // GET /restaurants - list all restaurants
            $restaurantController->list();
        } elseif ($subResource === 'menu') {
            // GET /restaurants/{id}/menu - list menu items
            if (isset($queryParams['id'])) {
                // GET single menu item by id
                $menuController->get($restaurantId, (int)$queryParams['id']);
            } else {
                $menuController->list($restaurantId);
            }
        } else {
            // GET /restaurants/{id} - get restaurant details
            $restaurantController->get($restaurantId);
        }
    } elseif ($method === 'POST') {
        if ($restaurantId === null) {
            // POST /restaurants - create new restaurant
            $restaurantController->create();
        } elseif ($subResource === 'menu') {
            // POST /restaurants/{id}/menu - create menu item for restaurant
            $menuController->create($restaurantId);
        } else {
            Helpers::sendResponse(['message' => 'Invalid endpoint'], 400);
        }
    } elseif ($method === 'PUT') {
        if ($restaurantId === null) {
            Helpers::sendResponse(['message' => 'Restaurant ID required'], 400);
        }
        if ($subResource === 'menu') {
            if (!isset($queryParams['id'])) {
                Helpers::sendResponse(['message' => 'Menu item ID required'], 400);
            }
            $menuController->update($restaurantId, (int)$queryParams['id']);
        } else {
            $restaurantController->update($restaurantId);
        }
    } elseif ($method === 'DELETE') {
        if ($restaurantId === null) {
            Helpers::sendResponse(['message' => 'Restaurant ID required'], 400);
        }
        if ($subResource === 'menu') {
            if (!isset($queryParams['id'])) {
                Helpers::sendResponse(['message' => 'Menu item ID required'], 400);
            }
            $menuController->delete($restaurantId, (int)$queryParams['id']);
        } else {
            // Delete restaurant and its related menu items
            $restaurantController->delete($restaurantId, function($restaurantId) use ($menuController) {
                $menuController->deleteAllByRestaurantId($restaurantId);
            });
        }
    } else {
        Helpers::sendResponse(['message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    Helpers::sendResponse(['message' => 'Server error: ' . $e->getMessage()], 500);
}
