<?php

namespace App\Core;

use PDO;
use PDOException;

class DB {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = require __DIR__ . '/../config/db.php';
        $charset = $config['charset'] ?? 'utf8mb4';
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$charset}";
        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $attempts = 0;
        $maxAttempts = 3;
        
        while ($attempts < $maxAttempts) {
            try {
                $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
                return;
            } catch (PDOException $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    $logDir = __DIR__ . '/../../storage/logs';
                    if (!is_dir($logDir)) {
                        mkdir($logDir, 0777, true);
                    }
                    $errorLog = $logDir . '/db-error-' . date('Y-m-d') . '.log';
                    $message = "[" . date('Y-m-d H:i:s') . "] Connection failed (Attempt $attempts/$maxAttempts): " . $e->getMessage() . PHP_EOL;
                    file_put_contents($errorLog, $message, FILE_APPEND);
                    die("Database connection failed. Please check logs.");
                }
                sleep(1);
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    public static function closeConnection() {
        if (self::$instance !== null) {
            self::$instance->pdo = null;
            self::$instance = null;
        }
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserializing
    public function __wakeup() {}
}
