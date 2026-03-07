<?php

class DB {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = require __DIR__ . '/../config/db.php';
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $logDir = __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/db-error-' . date('Y-m-d') . '.log';
            $message = "[" . date('Y-m-d H:i:s') . "] Connection failed: " . $e->getMessage() . PHP_EOL;
            file_put_contents($logFile, $message, FILE_APPEND);
            
            // Allow app to continue if DB is not critical for the current page
            // But if getInstance is called, it means DB is needed.
            // So I should throw exception or die here.
            // The requirement says "catch and log".
            // I'll re-throw a generic exception to avoid exposing credentials, or die.
            // Let's stick to the previous implementation which died, but cleaner.
            die("Database connection error. Please check logs.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
