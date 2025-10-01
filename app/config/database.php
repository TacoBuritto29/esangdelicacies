<?php
// Simple MySQL connection helper for XAMPP
// Usage:
//   $db = Database::getConnection();
//   $stmt = $db->prepare('SELECT 1');

class Database {
    private static $connection = null;

    public static function getConnection(): mysqli {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        // Default XAMPP MySQL settings
        $host = getenv('ESANG_DB_HOST') ?: '127.0.0.1';
        $user = getenv('ESANG_DB_USER') ?: 'root';
        $pass = getenv('ESANG_DB_PASS') ?: '';
        $dbName = getenv('ESANG_DB_NAME') ?: 'ESANG_DB';

        $mysqli = @new mysqli($host, $user, $pass);
        if ($mysqli->connect_errno) {
            http_response_code(500);
            die(json_encode([
                'ok' => false,
                'error' => 'Database connection failed',
                'details' => $mysqli->connect_error,
            ]));
        }

        // Create database if not exists, then select it
        $mysqli->query('CREATE DATABASE IF NOT EXISTS `'.$dbName.'`');
        if (!$mysqli->select_db($dbName)) {
            http_response_code(500);
            die(json_encode([
                'ok' => false,
                'error' => 'Failed to select database',
            ]));
        }

        // Ensure utf8mb4
        $mysqli->set_charset('utf8mb4');
        self::$connection = $mysqli;
        return self::$connection;
    }
}

// JSON header helper for APIs
function json_headers(): void {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

