<?php
namespace Src;

/**
 * Helper class containing utility methods for:
 * - Loading and saving JSON data
 * - Sending standardized JSON responses
 * - Parsing input JSON payloads
 */
class Helpers {
    /**
     * Load data from a JSON file and decode it into an associative array.
     * Returns empty array if file doesn't exist or is empty.
     *
     * @param string $file Path to JSON file
     * @return array Parsed data
     */
    public static function loadData(string $file): array {
        if (!file_exists($file)) return [];
        $json = file_get_contents($file);
        return json_decode($json, true) ?? [];
    }

    /**
     * Save associative array data into a JSON file with pretty print.
     *
     * @param string $file Path to JSON file
     * @param array $data Data to encode and save
     * @return void
     */
    public static function saveData(string $file, array $data): void {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Send HTTP JSON response with given data and status code, then exit.
     *
     * @param mixed $data Data to send as JSON
     * @param int $statusCode HTTP status code (default 200)
     * @return void
     */
    public static function sendResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Get input data from JSON payload in the HTTP request body.
     * Returns an empty array if no valid JSON found.
     *
     * @return array Parsed input data
     */
    public static function getInputData(): array {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}
