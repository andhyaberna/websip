<?php
// Autoloader not present, so we manually require
require_once __DIR__ . '/app/core/DB.php';

use App\Core\DB;

try {
    echo "Connecting to database...\n";
    $pdo = DB::getInstance();
    
    echo "Checking for 'deleted_at' column in 'users' table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'deleted_at'");
    $column = $stmt->fetch();
    
    if (!$column) {
        echo "Column 'deleted_at' not found. Adding it...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "Column 'deleted_at' added successfully.\n";
    } else {
        echo "Column 'deleted_at' already exists.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
