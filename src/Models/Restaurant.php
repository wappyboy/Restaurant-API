<?php
namespace Src\Models;

/**
 * Restaurant model represents a restaurant entity.
 */
class Restaurant {
    public $id;
    public $name;
    public $address;
    public $phone;

    /**
     * Constructor to initialize a Restaurant object.
     *
     * @param int $id Unique identifier
     * @param string $name Restaurant name
     * @param string $address Restaurant address
     * @param string $phone Contact phone number
     */
    public function __construct(int $id, string $name, string $address, string $phone) {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
    }

    /**
     * Convert Restaurant object to associative array for JSON encoding.
     *
     * @return array Restaurant data as array
     */
    public function toArray(): array {
        return get_object_vars($this);
    }
}
