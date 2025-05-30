<?php
namespace Src\Controllers;

use Src\Database;
use Src\Helpers;
use Src\Models\MenuItem;
use PDO;

/**
 * Controller class responsible for handling menu items related to restaurants.
 */
class MenuController {
    private $db;

    /**
     * Initialize the controller and set up database connection.
     */
    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * List all menu items for a given restaurant.
     *
     * @param int $restaurantId Restaurant ID to filter menu items
     */
    public function list(int $restaurantId) {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE restaurant_id = :restaurant_id");
        $stmt->execute(['restaurant_id' => $restaurantId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Helpers::sendResponse($items);
    }

    /**
     * Get a single menu item by restaurant ID and menu item ID.
     *
     * @param int $restaurantId Restaurant ID
     * @param int $menuItemId Menu item ID
     */
    public function get(int $restaurantId, int $menuItemId) {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = :id AND restaurant_id = :restaurant_id");
        $stmt->execute([
            'id' => $menuItemId,
            'restaurant_id' => $restaurantId
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            Helpers::sendResponse(['message' => 'Menu item not found'], 404);
        }

        Helpers::sendResponse($item);
    }

    /**
     * Create a new menu item for a given restaurant.
     * Expects JSON input with fields: name, description, price.
     *
     * @param int $restaurantId Restaurant ID
     */
    public function create(int $restaurantId) {
        $input = Helpers::getInputData();

        if (!isset($input['name'], $input['description'], $input['price'])) {
            Helpers::sendResponse(['message' => 'Missing required fields'], 400);
        }

        $stmt = $this->db->prepare("
            INSERT INTO menu_items (restaurant_id, name, description, price)
            VALUES (:restaurant_id, :name, :description, :price)
        ");
        $stmt->execute([
            'restaurant_id' => $restaurantId,
            'name' => $input['name'],
            'description' => $input['description'],
            'price' => $input['price']
        ]);

        $id = $this->db->lastInsertId();

        $newItem = [
            'id' => $id,
            'restaurant_id' => $restaurantId,
            'name' => $input['name'],
            'description' => $input['description'],
            'price' => $input['price'],
        ];

        Helpers::sendResponse($newItem, 201);
    }

    /**
     * Update an existing menu item.
     * Accepts partial updates (name, description, price).
     *
     * @param int $restaurantId Restaurant ID
     * @param int $menuItemId Menu item ID
     */
    public function update(int $restaurantId, int $menuItemId) {
        $input = Helpers::getInputData();

        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = :id AND restaurant_id = :restaurant_id");
        $stmt->execute([
            'id' => $menuItemId,
            'restaurant_id' => $restaurantId
        ]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            Helpers::sendResponse(['message' => 'Menu item not found'], 404);
        }

        $name = $input['name'] ?? $item['name'];
        $description = $input['description'] ?? $item['description'];
        $price = isset($input['price']) ? $input['price'] : $item['price'];

        $updateStmt = $this->db->prepare("
            UPDATE menu_items SET name = :name, description = :description, price = :price
            WHERE id = :id AND restaurant_id = :restaurant_id
        ");
        $updateStmt->execute([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'id' => $menuItemId,
            'restaurant_id' => $restaurantId
        ]);

        Helpers::sendResponse([
            'id' => $menuItemId,
            'restaurant_id' => $restaurantId,
            'name' => $name,
            'description' => $description,
            'price' => $price
        ]);
    }

    /**
     * Delete a menu item by restaurant ID and menu item ID.
     *
     * @param int $restaurantId Restaurant ID
     * @param int $menuItemId Menu item ID
     */
    public function delete(int $restaurantId, int $menuItemId) {
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = :id AND restaurant_id = :restaurant_id");
        $stmt->execute([
            'id' => $menuItemId,
            'restaurant_id' => $restaurantId
        ]);

        if ($stmt->rowCount() === 0) {
            Helpers::sendResponse(['message' => 'Menu item not found'], 404);
        }

        http_response_code(204);
        exit;
    }

    /**
     * Delete all menu items associated with a specific restaurant.
     *
     * @param int $restaurantId Restaurant ID
     */
    public function deleteAllByRestaurantId(int $restaurantId) {
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE restaurant_id = :restaurant_id");
        $stmt->execute(['restaurant_id' => $restaurantId]);
    }
}
