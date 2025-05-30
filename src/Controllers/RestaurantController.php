<?php
namespace Src\Controllers;

use Src\Database;
use Src\Models\Restaurant;
use Src\Helpers;
use PDO;

/**
 * Controller class responsible for handling restaurant-related API operations using a MySQL database.
 */
class RestaurantController {
    private $db;

    /**
     * Initialize the controller and set up database connection.
     */
    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * List all restaurants.
     */
    public function list() {
        $stmt = $this->db->query("SELECT * FROM restaurants");
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Helpers::sendResponse($restaurants);
    }

    /**
     * Get a single restaurant by ID.
     *
     * @param int $id Restaurant ID
     */
    public function get(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            Helpers::sendResponse(['message' => 'Restaurant not found'], 404);
        }

        Helpers::sendResponse($restaurant);
    }

    /**
     * Create a new restaurant entry.
     * Expects JSON input with fields: name, location.
     */
   public function create() {
    $input = Helpers::getInputData();

    if (!isset($input['name'], $input['location'])) {
        Helpers::sendResponse(['message' => 'Missing required fields'], 400);
    }

    $stmt = $this->db->prepare("INSERT INTO restaurants (name, location) VALUES (:name, :location)");
    $stmt->execute([
        'name' => $input['name'],
        'location' => $input['location']
    ]);

    $id = $this->db->lastInsertId();

    // Fetch the full newly inserted row including created_at
    $stmt = $this->db->prepare("SELECT * FROM restaurants WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $newRestaurant = $stmt->fetch(PDO::FETCH_ASSOC);

    Helpers::sendResponse($newRestaurant, 201);
}


    /**
     * Update an existing restaurant by ID.
     * Accepts partial updates (name, location).
     *
     * @param int $id Restaurant ID
     */
    public function update(int $id) {
        $input = Helpers::getInputData();

        $stmt = $this->db->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            Helpers::sendResponse(['message' => 'Restaurant not found'], 404);
        }

        $name = $input['name'] ?? $restaurant['name'];
        $location = $input['location'] ?? $restaurant['location'];

        $updateStmt = $this->db->prepare("UPDATE restaurants SET name = :name, location = :location WHERE id = :id");
        $updateStmt->execute([
            'name' => $name,
            'location' => $location,
            'id' => $id
        ]);

        Helpers::sendResponse(['id' => $id, 'name' => $name, 'location' => $location]);
    }

    /**
     * Delete a restaurant by ID.
     * Also triggers deletion of related menu items via callback.
     *
     * @param int $id Restaurant ID
     * @param callable $deleteRelatedMenuItems Callback to delete related menu items
     */
    public function delete(int $id, callable $deleteRelatedMenuItems) {
        $stmt = $this->db->prepare("SELECT * FROM restaurants WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            Helpers::sendResponse(['message' => 'Restaurant not found'], 404);
        }

        $deleteStmt = $this->db->prepare("DELETE FROM restaurants WHERE id = :id");
        $deleteStmt->execute(['id' => $id]);

        $deleteRelatedMenuItems($id);

        http_response_code(204);
        exit;
    }
}
