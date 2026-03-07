<?php

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
        
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            $logDir = __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/db-error-' . date('Y-m-d') . '.log';
            $message = "[" . date('Y-m-d H:i:s') . "] Connection failed: " . $e->getMessage() . PHP_EOL;
            file_put_contents($logFile, $message, FILE_APPEND);
            
            // Rethrow exception for the caller to handle if needed, or die.
            // For Status page to work, we need to catch it.
            // But for normal app flow, we might want to die.
            // Let's modify to throw exception. The caller (like index.php or StatusController) should handle it.
            // However, existing code might expect it to die.
            // I'll throw a RuntimeException which is unchecked.
            throw new Exception("Database connection error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
