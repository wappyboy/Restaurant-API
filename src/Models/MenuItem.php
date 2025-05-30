<?php
namespace Src\Models;

/**
 * MenuItem model represents an individual menu item belonging to a restaurant.
 */
class MenuItem {
    public $id;
    public $restaurant_id;
    public $name;
    public $description;
    public $price;

    /**
     * Constructor to initialize a MenuItem object.
     *
     * @param int $id Unique identifier of the menu item
     * @param int $restaurant_id ID of the restaurant this menu item belongs to
     * @param string $name Name of the menu item
     * @param string $description Description of the menu item
     * @param float $price Price of the menu item
     */
    public function __construct(int $id, int $restaurant_id, string $name, string $description, float $price) {
        $this->id = $id;
        $this->restaurant_id = $restaurant_id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
    }

    /**
     * Convert MenuItem object to an associative array suitable for JSON encoding.
     *
     * @return array Menu item data as array
     */
    public function toArray(): array {
        return get_object_vars($this);
    }
}
