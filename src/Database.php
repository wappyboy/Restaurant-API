<?php
namespace Src;

use PDO;
use PDOException;

class Database {
    private static $host = 'localhost';
    private static $dbName = 'restaurant_api';
    private static $username = 'root'; 
    private static $password = 'Rapeco!23';     
    private static $conn;

    /**
     * Establish and return a PDO database connection.
     */
    public static function connect() {
        if (!self::$conn) {
            try {
                self::$conn = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$dbName,
                                       self::$username, self::$password);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection error: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
